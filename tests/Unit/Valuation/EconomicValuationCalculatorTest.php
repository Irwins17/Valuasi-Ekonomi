<?php

namespace Tests\Unit\Valuation;

use App\Services\Valuation\EconomicValuationCalculator;
use App\Services\Valuation\Exceptions\ValuationException;
use PHPUnit\Framework\TestCase;

class EconomicValuationCalculatorTest extends TestCase
{
    private EconomicValuationCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new EconomicValuationCalculator;
    }

    // ── TCM ──────────────────────────────────────────────────────────────

    public function test_tcm_ols_model_recovers_exact_coefficients_from_noiseless_linear_data(): void
    {
        // visits = 100 - 0.001 * travel_cost, exactly, no noise.
        $travelCosts = [10000, 20000, 30000, 40000, 50000];
        $observations = array_map(fn ($tc) => [
            'travel_cost' => $tc,
            'visits' => 100 - 0.001 * $tc,
        ], $travelCosts);

        $result = $this->calc->calculateTCM([
            'observations' => $observations,
            'total_annual_visits' => 500000,
            'model' => 'ols',
        ]);

        $this->assertSame('ols', $result['model']);
        $this->assertEqualsWithDelta(-0.001, $result['regression']['coefficients']['travel_cost'], 1e-9);
        $this->assertEqualsWithDelta(100.0, $result['regression']['intercept'], 1e-6);
        $this->assertEqualsWithDelta(1.0, $result['regression']['r_squared'], 1e-9);

        // CS_per_visit = -1/beta1 = -1/-0.001 = 1000
        $this->assertEqualsWithDelta(1000.0, $result['cs_per_visit'], 1e-6);
        // Total_CS = 1000 * 500000
        $this->assertEqualsWithDelta(5.0e8, $result['total_cs'], 1e-3);
    }

    public function test_tcm_defaults_to_the_poisson_count_model(): void
    {
        // ln(visits) = 4 - 0.00002 * travel_cost, realised exactly.
        $travelCosts = [10000, 20000, 30000, 40000, 50000, 60000];
        $observations = array_map(fn ($tc) => [
            'travel_cost' => $tc,
            'visits' => exp(4 - 0.00002 * $tc),
        ], $travelCosts);

        $result = $this->calc->calculateTCM([
            'observations' => $observations,
            'total_annual_visits' => 100000,
        ]);

        $this->assertSame('poisson', $result['model'], 'count data should use the Poisson model unless told otherwise');
        $this->assertEqualsWithDelta(-0.00002, $result['regression']['coefficients']['travel_cost'], 1e-9);
        $this->assertEqualsWithDelta(4.0, $result['regression']['intercept'], 1e-7);

        // CS_per_visit = -1/beta1 = 50,000
        $this->assertEqualsWithDelta(50000.0, $result['cs_per_visit'], 1e-3);
        $this->assertEqualsWithDelta(50000.0 * 100000, $result['total_cs'], 1e-1);
    }

    public function test_tcm_poisson_handles_respondents_with_zero_visits(): void
    {
        // The case a linear-on-log model cannot represent at all.
        $observations = [];
        for ($i = 0; $i < 30; $i++) {
            $tc = 5000 + $i * 4000;
            $observations[] = [
                'travel_cost' => $tc,
                'visits' => round(exp(3.0 - 0.00025 * $tc)),
            ];
        }

        $result = $this->calc->calculateTCM([
            'observations' => $observations,
            'total_annual_visits' => 20000,
        ]);

        $this->assertContains(0.0, array_map(fn ($o) => (float) $o['visits'], $observations));
        $this->assertLessThan(0, $result['regression']['coefficients']['travel_cost']);
        $this->assertGreaterThan(0, $result['cs_per_visit']);
    }

    public function test_tcm_rejects_an_unknown_model(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateTCM([
            'observations' => [
                ['travel_cost' => 10000, 'visits' => 90],
                ['travel_cost' => 20000, 'visits' => 80],
                ['travel_cost' => 30000, 'visits' => 70],
            ],
            'total_annual_visits' => 1000,
            'model' => 'tobit',
        ]);
    }

    public function test_tcm_throws_when_beta1_is_positive(): void
    {
        // Visits rising with travel cost — contradicts the law of demand.
        $observations = [
            ['travel_cost' => 10000, 'visits' => 10],
            ['travel_cost' => 20000, 'visits' => 20],
            ['travel_cost' => 30000, 'visits' => 30],
        ];

        $this->expectException(ValuationException::class);
        $this->expectExceptionMessageMatches('/hukum permintaan/');

        $this->calc->calculateTCM([
            'observations' => $observations,
            'total_annual_visits' => 1000,
        ]);
    }

    public function test_tcm_throws_when_total_annual_visits_missing(): void
    {
        $observations = [
            ['travel_cost' => 10000, 'visits' => 90],
            ['travel_cost' => 20000, 'visits' => 80],
            ['travel_cost' => 30000, 'visits' => 70],
        ];

        $this->expectException(ValuationException::class);
        $this->calc->calculateTCM(['observations' => $observations]);
    }

    public function test_tcm_throws_on_negative_travel_cost(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateTCM([
            'observations' => [
                ['travel_cost' => -1000, 'visits' => 10],
                ['travel_cost' => 20000, 'visits' => 8],
                ['travel_cost' => 30000, 'visits' => 6],
            ],
            'total_annual_visits' => 1000,
        ]);
    }

    public function test_tcm_throws_on_empty_observations(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateTCM(['observations' => [], 'total_annual_visits' => 1000]);
    }

    // ── CVM Open-Ended ───────────────────────────────────────────────────

    public function test_cvm_open_ended_mean_matches_sample_mean_without_socio_vars(): void
    {
        $observations = [
            ['wtp' => 100], ['wtp' => 200], ['wtp' => 300], ['wtp' => 400], ['wtp' => 500],
        ];

        $result = $this->calc->calculateCVMOpenEnded([
            'observations' => $observations,
            'population' => 1000,
        ]);

        $this->assertEqualsWithDelta(300.0, $result['ewtp_sample_mean'], 1e-9);
        $this->assertEqualsWithDelta(300.0, $result['ewtp_regression_at_mean'], 1e-6);
        $this->assertEqualsWithDelta(300.0, $result['ewtp'], 1e-6);
        $this->assertEqualsWithDelta(300000.0, $result['total_wtp'], 1e-3);
    }

    public function test_cvm_open_ended_with_socio_economic_variable(): void
    {
        // wtp = 50 + 0.0001 * income, exactly.
        $incomes = [1000000, 2000000, 3000000, 4000000, 5000000];
        $observations = array_map(fn ($inc) => [
            'wtp' => 50 + 0.0001 * $inc,
            'socio_economic' => ['income' => $inc],
        ], $incomes);

        $result = $this->calc->calculateCVMOpenEnded([
            'observations' => $observations,
            'population' => 2000,
        ]);

        $this->assertEqualsWithDelta(50.0, $result['regression']['intercept'], 1e-6);
        $this->assertEqualsWithDelta(0.0001, $result['regression']['coefficients']['income'], 1e-9);
        // mean(income) = 3,000,000 -> wtp at mean = 50 + 0.0001*3,000,000 = 350
        $this->assertEqualsWithDelta(350.0, $result['ewtp'], 1e-6);
        $this->assertEqualsWithDelta($result['ewtp_sample_mean'], $result['ewtp_regression_at_mean'], 1e-6);
        $this->assertEqualsWithDelta(700000.0, $result['total_wtp'], 1e-3);
    }

    public function test_cvm_open_ended_throws_on_negative_wtp(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateCVMOpenEnded([
            'observations' => [['wtp' => -10], ['wtp' => 20], ['wtp' => 30]],
            'population' => 1000,
        ]);
    }

    public function test_cvm_open_ended_throws_when_population_missing(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateCVMOpenEnded([
            'observations' => [['wtp' => 10], ['wtp' => 20], ['wtp' => 30]],
        ]);
    }

    // ── CVM Dichotomous Choice ───────────────────────────────────────────

    public function test_cvm_dichotomous_converges_with_negative_bid_coefficient(): void
    {
        // Acceptance probability declines as the bid rises, with some
        // overlap at bid=20/30 so the sample isn't perfectly separable.
        $bids = [10, 10, 20, 20, 30, 30, 40, 40, 50, 50];
        $responses = [1, 1, 1, 0, 1, 0, 0, 0, 0, 0];

        $observations = [];
        foreach ($bids as $i => $bid) {
            $observations[] = ['bid' => $bid, 'response' => $responses[$i]];
        }

        $result = $this->calc->calculateCVMDichotomous([
            'observations' => $observations,
            'population' => 5000,
        ]);

        $this->assertTrue($result['regression']['converged']);
        $this->assertLessThan(0, $result['regression']['coefficients']['bid']);
        // EWTP should land within the tested bid range (10-50).
        $this->assertGreaterThan(0, $result['ewtp']);
        $this->assertEqualsWithDelta($result['ewtp'] * 5000, $result['total_wtp'], 1e-6);
    }

    public function test_cvm_dichotomous_throws_on_non_binary_response(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateCVMDichotomous([
            'observations' => [
                ['bid' => 10, 'response' => 2],
                ['bid' => 20, 'response' => 0],
                ['bid' => 30, 'response' => 1],
            ],
            'population' => 1000,
        ]);
    }

    public function test_cvm_dichotomous_throws_on_non_positive_bid(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateCVMDichotomous([
            'observations' => [
                ['bid' => 0, 'response' => 1],
                ['bid' => 20, 'response' => 0],
                ['bid' => 30, 'response' => 1],
            ],
            'population' => 1000,
        ]);
    }

    public function test_cvm_dichotomous_throws_when_population_missing(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateCVMDichotomous([
            'observations' => [
                ['bid' => 10, 'response' => 1],
                ['bid' => 20, 'response' => 0],
                ['bid' => 30, 'response' => 0],
            ],
        ]);
    }

    // ── EOP ──────────────────────────────────────────────────────────────

    public function test_eop_direct_mode_with_q0_q1(): void
    {
        $result = $this->calc->calculateEOP([
            'market_price' => 25000000,
            'q0' => 12000,
            'q1' => 10500,
        ]);

        $this->assertEqualsWithDelta(-1500.0, $result['delta_q'], 1e-9);
        $this->assertEqualsWithDelta(0.0, $result['delta_c'], 1e-9);
        // Delta_NV = 25,000,000 * (10500 - 12000) - 0
        $this->assertEqualsWithDelta(-37500000000.0, $result['delta_nv'], 1e-3);
    }

    public function test_eop_direct_mode_with_delta_q_and_delta_c(): void
    {
        $result = $this->calc->calculateEOP([
            'market_price' => 100,
            'delta_q' => 50,
            'delta_c' => 200,
        ]);

        // Delta_NV = 100 * 50 - 200 = 4800
        $this->assertEqualsWithDelta(4800.0, $result['delta_nv'], 1e-9);
    }

    public function test_eop_regression_mode_recovers_production_function_and_delta_nv(): void
    {
        // Q = 1000 + 2*K + 3*L + 500*E, exactly, no noise.
        $rows = [
            ['capital' => 10, 'labor' => 5, 'environmental_index' => 1],
            ['capital' => 20, 'labor' => 5, 'environmental_index' => 1],
            ['capital' => 10, 'labor' => 10, 'environmental_index' => 1],
            ['capital' => 20, 'labor' => 10, 'environmental_index' => 1],
            ['capital' => 15, 'labor' => 7, 'environmental_index' => 2],
            ['capital' => 25, 'labor' => 8, 'environmental_index' => 3],
        ];
        $observations = array_map(function ($r) {
            $r['output'] = 1000 + 2 * $r['capital'] + 3 * $r['labor'] + 500 * $r['environmental_index'];

            return $r;
        }, $rows);

        $result = $this->calc->calculateEOP([
            'market_price' => 25000000,
            'observations' => $observations,
            'scenario' => [
                'before' => ['capital' => 15, 'labor' => 7, 'environmental_index' => 2],
                'after' => ['capital' => 15, 'labor' => 7, 'environmental_index' => 1],
            ],
        ]);

        $this->assertEqualsWithDelta(1000.0, $result['production_function']['intercept'], 1e-3);
        $this->assertEqualsWithDelta(2.0, $result['production_function']['coefficients']['capital'], 1e-3);
        $this->assertEqualsWithDelta(3.0, $result['production_function']['coefficients']['labor'], 1e-3);
        $this->assertEqualsWithDelta(500.0, $result['production_function']['coefficients']['environmental_index'], 1e-3);

        // Q0 (E=2) = 1000+30+21+1000 = 2051; Q1 (E=1) = 1000+30+21+500 = 1551
        $this->assertEqualsWithDelta(2051.0, $result['q0'], 1e-2);
        $this->assertEqualsWithDelta(1551.0, $result['q1'], 1e-2);
        $this->assertEqualsWithDelta(-500.0, $result['delta_q'], 1e-2);
        // Delta_NV = 25,000,000 * -500 - 0
        $this->assertEqualsWithDelta(-12500000000.0, $result['delta_nv'], 1e4);
    }

    public function test_eop_throws_on_non_positive_market_price(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateEOP(['market_price' => 0, 'q0' => 100, 'q1' => 90]);
    }

    public function test_eop_throws_when_no_production_data_given(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateEOP(['market_price' => 100]);
    }

    public function test_eop_throws_on_negative_cost(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateEOP([
            'market_price' => 100,
            'q0' => 100,
            'q1' => 90,
            'c0' => -10,
            'c1' => 20,
        ]);
    }
}
