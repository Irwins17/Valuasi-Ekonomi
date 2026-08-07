<?php

namespace App\Services\Valuation;

use App\Services\Valuation\Exceptions\ValuationException;
use App\Services\Valuation\Math\LogisticRegression;
use App\Services\Valuation\Math\OlsRegression;

/**
 * Economic valuation calculators for the three methods used in this
 * platform's Benefit records: Travel Cost Method (TCM), Contingent
 * Valuation Method (CVM — open-ended and dichotomous-choice), and Effect
 * on Production (EOP).
 *
 * Every public method takes a plain, already-cleaned associative array
 * (e.g. mapped from TcmData/CvmData/EopData rows, an import file, or a
 * request payload) and returns a plain associative array containing both
 * the regression diagnostics and the final economic value, so callers can
 * display or persist whichever parts they need. All methods throw
 * ValuationException on invalid input or a mathematically undefined /
 * theoretically contradictory result.
 */
class EconomicValuationCalculator
{
    /**
     * Travel Cost Method.
     *
     * Fits the demand function V_i = beta0 + beta1*TC_i + sum(betak*Xki) by
     * OLS, then derives the consumer surplus per visit and the total annual
     * recreation value of the site.
     *
     * @param  array{
     *     observations: array<int, array{visits: float|int, travel_cost: float|int, socio_economic?: array<string, float|int>}>,
     *     total_annual_visits: float|int,
     * }  $data
     * @return array{
     *     regression: array,
     *     cs_per_visit: float,
     *     total_annual_visits: float,
     *     total_cs: float,
     * }
     */
    public function calculateTCM(array $data): array
    {
        $observations = $data['observations'] ?? [];
        if (! is_array($observations) || count($observations) === 0) {
            throw new ValuationException('TCM: data "observations" wajib diisi dan tidak boleh kosong.');
        }

        // Socio-economic variable names (X_k) are inferred from the first
        // observation, then required to be present+consistent on every row.
        $socioKeys = array_keys($observations[0]['socio_economic'] ?? []);

        $y = [];
        $x = [];
        foreach (array_values($observations) as $i => $row) {
            if (! isset($row['visits'], $row['travel_cost'])) {
                throw new ValuationException("TCM: observasi ke-{$i} harus memiliki 'visits' dan 'travel_cost'.");
            }
            if ($row['visits'] < 0) {
                throw new ValuationException("TCM: jumlah kunjungan (visits) pada observasi ke-{$i} tidak boleh negatif.");
            }
            if ($row['travel_cost'] < 0) {
                throw new ValuationException("TCM: biaya perjalanan (travel_cost) pada observasi ke-{$i} tidak boleh negatif.");
            }

            $x[] = $this->extractSocioEconomicRow($row, $socioKeys, 'TCM', $i, [(float) $row['travel_cost']]);
            $y[] = (float) $row['visits'];
        }

        $featureNames = array_merge(['travel_cost'], $socioKeys);
        $regression = OlsRegression::fit($y, $x, $featureNames);

        $beta1 = $regression['coefficients']['travel_cost'];

        // Guard: CS_per_visit = -1/beta1 is undefined when beta1 = 0, and
        // economically meaningless when beta1 > 0 — demand theory requires a
        // downward-sloping curve (higher travel cost => fewer visits).
        if ($beta1 == 0.0) {
            throw new ValuationException('TCM: koefisien travel cost (beta1) bernilai 0, sehingga surplus konsumen per kunjungan (-1/beta1) tidak dapat dihitung (pembagian dengan nol).');
        }
        if ($beta1 >= 0) {
            throw new ValuationException(sprintf(
                'TCM: koefisien travel cost (beta1 = %.6f) bernilai positif/nol, bertentangan dengan hukum permintaan (kenaikan biaya perjalanan seharusnya menurunkan jumlah kunjungan). Periksa kembali data travel cost dan variabel sosio-ekonomi sebelum melanjutkan.',
                $beta1
            ));
        }

        if (! isset($data['total_annual_visits']) || $data['total_annual_visits'] <= 0) {
            throw new ValuationException('TCM: "total_annual_visits" wajib diisi dan harus lebih besar dari 0.');
        }

        // CS per visit = -1 / beta1 (the area under the estimated demand curve, per visit)
        $csPerVisit = -1 / $beta1;

        // Total CS = CS per visit x total number of visits to the site in a year
        $totalCs = $csPerVisit * (float) $data['total_annual_visits'];

        return [
            'regression' => $regression,
            'cs_per_visit' => $csPerVisit,
            'total_annual_visits' => (float) $data['total_annual_visits'],
            'total_cs' => $totalCs,
        ];
    }

    /**
     * CVM — Open-Ended approach.
     *
     * Fits WTP_i = alpha0 + sum(alphak*Xki) by OLS. The mean WTP can
     * equivalently be read as the plain sample average of WTP_i, or as the
     * regression fitted at the mean of every X_k — both are returned so the
     * two are visibly consistent (an OLS-with-intercept identity).
     *
     * @param  array{
     *     observations: array<int, array{wtp: float|int, socio_economic?: array<string, float|int>}>,
     *     population: float|int,
     * }  $data
     * @return array{
     *     regression: array,
     *     ewtp_sample_mean: float,
     *     ewtp_regression_at_mean: float,
     *     ewtp: float,
     *     population: float,
     *     total_wtp: float,
     * }
     */
    public function calculateCVMOpenEnded(array $data): array
    {
        $observations = $data['observations'] ?? [];
        if (! is_array($observations) || count($observations) === 0) {
            throw new ValuationException('CVM (open-ended): data "observations" wajib diisi dan tidak boleh kosong.');
        }

        $socioKeys = array_keys($observations[0]['socio_economic'] ?? []);

        $y = [];
        $x = [];
        foreach (array_values($observations) as $i => $row) {
            if (! isset($row['wtp'])) {
                throw new ValuationException("CVM (open-ended): observasi ke-{$i} harus memiliki 'wtp'.");
            }
            if ($row['wtp'] < 0) {
                throw new ValuationException("CVM (open-ended): nilai WTP pada observasi ke-{$i} tidak boleh negatif.");
            }

            $x[] = $this->extractSocioEconomicRow($row, $socioKeys, 'CVM (open-ended)', $i);
            $y[] = (float) $row['wtp'];
        }

        $regression = OlsRegression::fit($y, $x, $socioKeys);

        // Mean WTP, method 1: plain sample average of the raw WTP values.
        $sampleMeanWtp = array_sum($y) / count($y);

        // Mean WTP, method 2: alpha0 + sum(alphak * mean(Xk)) — the demand
        // function evaluated at the average respondent. For an OLS fit with
        // an intercept this is always identical to the sample mean; computing
        // both is a cheap correctness check on the regression solver.
        $meanAtRegression = $regression['intercept'];
        foreach ($socioKeys as $key) {
            $meanAtRegression += $regression['coefficients'][$key] * $this->columnMean($observations, $key);
        }

        if (! isset($data['population']) || $data['population'] <= 0) {
            throw new ValuationException('CVM (open-ended): "population" wajib diisi dan harus lebih besar dari 0.');
        }

        $ewtp = $meanAtRegression;

        // Total WTP = EWTP x total populasi relevan (N)
        $totalWtp = $ewtp * (float) $data['population'];

        return [
            'regression' => $regression,
            'ewtp_sample_mean' => $sampleMeanWtp,
            'ewtp_regression_at_mean' => $meanAtRegression,
            'ewtp' => $ewtp,
            'population' => (float) $data['population'],
            'total_wtp' => $totalWtp,
        ];
    }

    /**
     * CVM — Dichotomous Choice approach (single-bounded logit).
     *
     * Fits ln(P/(1-P)) = alpha0 + beta*Bid_i + sum(gammak*Xki) by maximum
     * likelihood, then derives EWTP as the bid level at which the estimated
     * probability of answering "yes" is exactly 50% for the average
     * respondent.
     *
     * @param  array{
     *     observations: array<int, array{bid: float|int, response: int, socio_economic?: array<string, float|int>}>,
     *     population: float|int,
     * }  $data
     * @return array{
     *     regression: array,
     *     ewtp: float,
     *     population: float,
     *     total_wtp: float,
     * }
     */
    public function calculateCVMDichotomous(array $data): array
    {
        $observations = $data['observations'] ?? [];
        if (! is_array($observations) || count($observations) === 0) {
            throw new ValuationException('CVM (dichotomous): data "observations" wajib diisi dan tidak boleh kosong.');
        }

        $socioKeys = array_keys($observations[0]['socio_economic'] ?? []);

        $y = [];
        $x = [];
        foreach (array_values($observations) as $i => $row) {
            if (! isset($row['bid'], $row['response'])) {
                throw new ValuationException("CVM (dichotomous): observasi ke-{$i} harus memiliki 'bid' dan 'response'.");
            }
            if ($row['bid'] <= 0) {
                throw new ValuationException("CVM (dichotomous): nilai tawaran (bid) pada observasi ke-{$i} harus lebih besar dari 0.");
            }
            if (! in_array($row['response'], [0, 1], true)) {
                throw new ValuationException("CVM (dichotomous): 'response' pada observasi ke-{$i} harus bernilai 0 (Tidak) atau 1 (Ya).");
            }

            $x[] = $this->extractSocioEconomicRow($row, $socioKeys, 'CVM (dichotomous)', $i, [(float) $row['bid']]);
            $y[] = (int) $row['response'];
        }

        $featureNames = array_merge(['bid'], $socioKeys);
        $regression = LogisticRegression::fit($y, $x, $featureNames);

        $beta = $regression['coefficients']['bid'];

        // Guard: EWTP = (alpha0 + sum(gammak*mean(Xk))) / (-beta) is undefined
        // when beta = 0, and economically meaningless when beta >= 0 — a
        // higher bid should reduce, not increase, the probability of a "yes".
        if ($beta == 0.0) {
            throw new ValuationException('CVM (dichotomous): koefisien bid (beta) bernilai 0, sehingga EWTP (dibagi -beta) tidak dapat dihitung.');
        }
        if ($beta >= 0) {
            throw new ValuationException(sprintf(
                'CVM (dichotomous): koefisien bid (beta = %.6f) bernilai positif/nol, bertentangan dengan teori (tawaran lebih tinggi seharusnya menurunkan probabilitas menjawab "Ya"). Periksa kembali data bid dan respons.',
                $beta
            ));
        }

        if (! isset($data['population']) || $data['population'] <= 0) {
            throw new ValuationException('CVM (dichotomous): "population" wajib diisi dan harus lebih besar dari 0.');
        }

        // Numerator: alpha0 + sum(gammak * mean(Xk)) — bid itself is excluded
        // here, since EWTP is precisely the bid value that solves P = 0.5.
        $numerator = $regression['intercept'];
        foreach ($socioKeys as $key) {
            $numerator += $regression['coefficients'][$key] * $this->columnMean($observations, $key);
        }

        // EWTP = (alpha0 + sum(gammak*mean(Xk))) / (-beta)
        $ewtp = $numerator / (-$beta);

        // Total WTP = EWTP x total populasi relevan (N)
        $totalWtp = $ewtp * (float) $data['population'];

        return [
            'regression' => $regression,
            'ewtp' => $ewtp,
            'population' => (float) $data['population'],
            'total_wtp' => $totalWtp,
        ];
    }

    /**
     * Effect on Production.
     *
     * Two input modes are supported for getting Q0/Q1 (pick whichever fits
     * the data you have):
     *  - "regression" mode — pass `observations` (a panel with `output` plus
     *    input variables such as capital/labor/environmental_index) and a
     *    `scenario` (before/after values of those inputs). The production
     *    function Q = beta0 + beta1*K + beta2*L + betae*E is fit by OLS and
     *    used to predict Q0 and Q1.
     *  - "direct" mode — pass `q0`/`q1` (or `delta_q`) straight away, e.g.
     *    already-measured production figures (as in the EopData table).
     * Cost is optional either way: pass `c0`/`c1` (or `delta_c`); it
     * defaults to 0 (no cost change) when omitted.
     *
     * @param  array{
     *     market_price: float|int,
     *     observations?: array<int, array<string, float|int>>,
     *     scenario?: array{before: array<string, float|int>, after: array<string, float|int>},
     *     q0?: float|int, q1?: float|int, delta_q?: float|int,
     *     c0?: float|int, c1?: float|int, delta_c?: float|int,
     * }  $data
     * @return array{
     *     production_function: ?array,
     *     market_price: float,
     *     q0: ?float, q1: ?float, delta_q: float,
     *     c0: ?float, c1: ?float, delta_c: float,
     *     delta_nv: float,
     * }
     */
    public function calculateEOP(array $data): array
    {
        if (! isset($data['market_price']) || $data['market_price'] <= 0) {
            throw new ValuationException('EOP: "market_price" (Pq) wajib diisi dan harus lebih besar dari 0.');
        }
        $marketPrice = (float) $data['market_price'];

        $productionFunction = null;
        $q0 = null;
        $q1 = null;

        if (! empty($data['observations']) && ! empty($data['scenario'])) {
            [$q0, $q1, $productionFunction] = $this->estimateProductionChange($data['observations'], $data['scenario']);
        } elseif (array_key_exists('delta_q', $data)) {
            // Delta_Q supplied directly — Q0/Q1 individually aren't needed.
        } elseif (isset($data['q0'], $data['q1'])) {
            $q0 = (float) $data['q0'];
            $q1 = (float) $data['q1'];
            if ($q0 < 0 || $q1 < 0) {
                throw new ValuationException('EOP: nilai produksi (q0/q1) tidak boleh negatif.');
            }
        } else {
            throw new ValuationException('EOP: sediakan salah satu dari (a) "observations" + "scenario" untuk mengestimasi fungsi produksi, (b) "q0" & "q1" langsung, atau (c) "delta_q" langsung.');
        }

        // Delta_Q = Q1 - Q0 (or the value given directly)
        $deltaQ = array_key_exists('delta_q', $data) ? (float) $data['delta_q'] : ($q1 - $q0);

        if (array_key_exists('delta_c', $data)) {
            $deltaC = (float) $data['delta_c'];
            $c0 = isset($data['c0']) ? (float) $data['c0'] : null;
            $c1 = isset($data['c1']) ? (float) $data['c1'] : null;
        } elseif (isset($data['c0'], $data['c1'])) {
            $c0 = (float) $data['c0'];
            $c1 = (float) $data['c1'];
            if ($c0 < 0 || $c1 < 0) {
                throw new ValuationException('EOP: biaya produksi (c0/c1) tidak boleh negatif.');
            }
            // Delta_C = C1 - C0
            $deltaC = $c1 - $c0;
        } else {
            // No cost data supplied: assume the production cost is unchanged.
            $c0 = 0.0;
            $c1 = 0.0;
            $deltaC = 0.0;
        }

        // Delta_NV = Pq * (Q1 - Q0) - (C1 - C0)
        $deltaNv = $marketPrice * $deltaQ - $deltaC;

        return [
            'production_function' => $productionFunction,
            'market_price' => $marketPrice,
            'q0' => $q0,
            'q1' => $q1,
            'delta_q' => $deltaQ,
            'c0' => $c0,
            'c1' => $c1,
            'delta_c' => $deltaC,
            'delta_nv' => $deltaNv,
        ];
    }

    /**
     * Fits Q = beta0 + beta1*K + beta2*L + betae*E (or a subset of those
     * inputs — whichever keys are present besides "output") by OLS, then
     * predicts Q for the "before" and "after" environmental scenario rows.
     *
     * @return array{0: float, 1: float, 2: array} [$q0, $q1, $regressionResult]
     */
    private function estimateProductionChange(array $observations, array $scenario): array
    {
        if (count($observations) === 0) {
            throw new ValuationException('EOP: data "observations" tidak boleh kosong.');
        }
        if (! isset($scenario['before'], $scenario['after'])) {
            throw new ValuationException('EOP: "scenario" harus memiliki key "before" dan "after".');
        }

        $inputKeys = array_values(array_diff(array_keys($observations[0]), ['output']));
        if (empty($inputKeys)) {
            throw new ValuationException('EOP: setiap observasi harus memiliki minimal satu variabel input (mis. capital, labor, environmental_index) selain "output".');
        }

        $y = [];
        $x = [];
        foreach (array_values($observations) as $i => $row) {
            if (! array_key_exists('output', $row)) {
                throw new ValuationException("EOP: observasi ke-{$i} harus memiliki 'output' (Q).");
            }
            if ($row['output'] < 0) {
                throw new ValuationException("EOP: nilai output (Q) pada observasi ke-{$i} tidak boleh negatif.");
            }

            $inputRow = [];
            foreach ($inputKeys as $key) {
                if (! array_key_exists($key, $row)) {
                    throw new ValuationException("EOP: observasi ke-{$i} tidak memiliki variabel '{$key}' yang konsisten dengan observasi lain.");
                }
                $inputRow[] = (float) $row[$key];
            }

            $x[] = $inputRow;
            $y[] = (float) $row['output'];
        }

        $regression = OlsRegression::fit($y, $x, $inputKeys);

        $predict = function (array $point) use ($regression, $inputKeys): float {
            // Q_hat = beta0 + sum(betak * X_k), evaluated at the given scenario point
            $value = $regression['intercept'];
            foreach ($inputKeys as $key) {
                if (! array_key_exists($key, $point)) {
                    throw new ValuationException("EOP: skenario tidak memiliki variabel '{$key}' yang dibutuhkan oleh fungsi produksi.");
                }
                $value += $regression['coefficients'][$key] * (float) $point[$key];
            }

            return $value;
        };

        $q0 = $predict($scenario['before']);
        $q1 = $predict($scenario['after']);

        return [$q0, $q1, $regression];
    }

    /**
     * Validates that observation `$row` has every key in `$socioKeys` under
     * `socio_economic`, and returns the independent-variable row to feed
     * into the regression: `$leadingValues` (e.g. travel_cost, bid) followed
     * by the socio-economic values in `$socioKeys` order.
     *
     * @param  string[]  $socioKeys
     * @param  float[]  $leadingValues
     * @return float[]
     */
    private function extractSocioEconomicRow(array $row, array $socioKeys, string $method, int $index, array $leadingValues = []): array
    {
        $socio = $row['socio_economic'] ?? [];
        $socioRow = [];
        foreach ($socioKeys as $key) {
            if (! array_key_exists($key, $socio)) {
                throw new ValuationException("{$method}: observasi ke-{$index} tidak memiliki variabel sosio-ekonomi '{$key}' yang konsisten dengan observasi lain.");
            }
            $socioRow[] = (float) $socio[$key];
        }

        return array_merge($leadingValues, $socioRow);
    }

    /**
     * Mean(X_k) across all observations, for a socio-economic variable key.
     */
    private function columnMean(array $observations, string $key): float
    {
        $values = array_map(fn ($o) => (float) $o['socio_economic'][$key], $observations);

        return array_sum($values) / count($values);
    }
}
