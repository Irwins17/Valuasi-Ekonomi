<?php

namespace Tests\Unit\Valuation;

use App\Services\Valuation\Exceptions\ValuationException;
use App\Services\Valuation\Math\Distributions;
use App\Services\Valuation\Math\PoissonRegression;
use App\Services\Valuation\Math\ProbitRegression;
use PHPUnit\Framework\TestCase;

/**
 * Numerical checks for the count and probit solvers.
 *
 * Each regression is fed data generated exactly from a known model — the
 * response equals its own conditional mean with no noise — so the maximum
 * likelihood estimate must reproduce the generating coefficients. That turns
 * "does the solver work" into an exact assertion rather than a judgement call
 * about sampling error.
 */
class RegressionMathTest extends TestCase
{
    // ── Distributions ────────────────────────────────────────────────────

    public function test_normal_cdf_matches_known_quantiles(): void
    {
        $this->assertEqualsWithDelta(0.5, Distributions::normalCdf(0), 1e-9);
        $this->assertEqualsWithDelta(0.9750021, Distributions::normalCdf(1.96), 1e-6);
        $this->assertEqualsWithDelta(0.05, Distributions::normalCdf(-1.6449), 1e-5);
        $this->assertEqualsWithDelta(0.995, Distributions::normalCdf(2.5758), 1e-5);
    }

    public function test_normal_cdf_is_symmetric(): void
    {
        foreach ([0.3, 1.3, 2.9, 4.1] as $x) {
            $this->assertEqualsWithDelta(
                1.0,
                Distributions::normalCdf($x) + Distributions::normalCdf(-$x),
                1e-9,
                "Phi({$x}) + Phi(-{$x}) should be 1"
            );
        }
    }

    public function test_normal_pdf_peak_and_log_gamma(): void
    {
        $this->assertEqualsWithDelta(0.3989423, Distributions::normalPdf(0), 1e-7);
        $this->assertEqualsWithDelta(log(24), Distributions::logGamma(5), 1e-9);
        $this->assertEqualsWithDelta(0.0, Distributions::logGamma(1), 1e-9);
        $this->assertEqualsWithDelta(log(120), Distributions::logFactorial(5), 1e-9);
    }

    // ── Poisson ──────────────────────────────────────────────────────────

    public function test_poisson_recovers_generating_coefficients(): void
    {
        // ln(mu) = 0.5 + 0.3x, with y set to mu exactly.
        $x = [];
        $y = [];
        for ($i = 0; $i < 60; $i++) {
            $xi = ($i % 12) / 4.0;
            $x[] = [$xi];
            $y[] = exp(0.5 + 0.3 * $xi);
        }

        $fit = PoissonRegression::fit($y, $x, ['tc']);

        $this->assertTrue($fit['converged']);
        $this->assertEqualsWithDelta(0.5, $fit['intercept'], 1e-7);
        $this->assertEqualsWithDelta(0.3, $fit['coefficients']['tc'], 1e-7);
        $this->assertSame(0.0, $fit['dispersion']);
    }

    public function test_poisson_handles_a_negative_slope_and_zero_counts(): void
    {
        // A downward-sloping demand curve with genuine zeros in the data —
        // the case that rules out regressing ln(visits) directly.
        $x = [];
        $y = [];
        for ($i = 0; $i < 40; $i++) {
            $tc = $i * 1.0;
            $x[] = [$tc];
            $y[] = round(exp(2.0 - 0.15 * $tc));
        }

        $fit = PoissonRegression::fit($y, $x, ['tc']);

        $this->assertTrue($fit['converged']);
        $this->assertLessThan(0, $fit['coefficients']['tc']);
        $this->assertContains(0.0, $y, 'the fixture should contain zero-visit rows');
    }

    public function test_negative_binomial_reduces_to_poisson_when_equidispersed(): void
    {
        $x = [];
        $y = [];
        for ($i = 0; $i < 60; $i++) {
            $xi = ($i % 12) / 4.0;
            $x[] = [$xi];
            $y[] = exp(0.5 + 0.3 * $xi);
        }

        $fit = PoissonRegression::fitNegativeBinomial($y, $x, ['tc']);

        $this->assertEqualsWithDelta(0.5, $fit['intercept'], 1e-4);
        $this->assertEqualsWithDelta(0.3, $fit['coefficients']['tc'], 1e-4);
    }

    public function test_poisson_rejects_negative_counts(): void
    {
        $this->expectException(ValuationException::class);
        PoissonRegression::fit([-1.0, 2.0, 3.0, 4.0], [[1.0], [2.0], [3.0], [4.0]], ['tc']);
    }

    public function test_poisson_rejects_too_few_observations(): void
    {
        $this->expectException(ValuationException::class);
        PoissonRegression::fit([1.0, 2.0], [[1.0], [2.0]], ['tc']);
    }

    // ── Probit ───────────────────────────────────────────────────────────

    public function test_probit_recovers_generating_coefficients(): void
    {
        // y set to Phi(eta) exactly, so the MLE must return the true betas.
        $x = [];
        $y = [];
        for ($i = 0; $i < 80; $i++) {
            $bid = 10.0 + ($i % 40) * 2.5;
            $x[] = [$bid];
            $y[] = Distributions::normalCdf(-1.5 + 0.02 * $bid);
        }

        $fit = $this->fitProbitOnFractions($y, $x, ['bid']);

        // Tolerances account for discretisation in the fixture: each response
        // probability is realised as a whole number of 1s out of `repeats`,
        // which perturbs the empirical proportion by up to 1/(2*repeats) and
        // shows up mostly in the intercept. The slope is unaffected to 1e-4.
        $this->assertEqualsWithDelta(-1.5, $fit['intercept'], 5e-3);
        $this->assertEqualsWithDelta(0.02, $fit['coefficients']['bid'], 1e-4);
    }

    public function test_probit_and_logit_agree_on_the_fifty_percent_crossing(): void
    {
        // The two links are parameterised differently, so their coefficients
        // are not comparable — but the bid at which acceptance passes 50%
        // is a property of the data, and both should locate it in the same
        // place. A large disagreement would mean one of the solvers is wrong.
        $bids = [10, 10, 20, 20, 30, 30, 40, 40, 50, 50, 60, 60, 70, 70];
        $responses = [1, 1, 1, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0];
        $x = array_map(fn ($b) => [(float) $b], $bids);

        $probit = ProbitRegression::fit($responses, $x, ['bid']);
        $logit = \App\Services\Valuation\Math\LogisticRegression::fit($responses, $x, ['bid']);

        $probitCrossing = -$probit['intercept'] / $probit['coefficients']['bid'];
        $logitCrossing = -$logit['intercept'] / $logit['coefficients']['bid'];

        $this->assertEqualsWithDelta($logitCrossing, $probitCrossing, 2.0);
        $this->assertGreaterThan(10, $probitCrossing);
        $this->assertLessThan(70, $probitCrossing);
    }

    public function test_probit_converges_on_binary_data_with_negative_slope(): void
    {
        $bids = [10, 10, 20, 20, 30, 30, 40, 40, 50, 50, 60, 60];
        $responses = [1, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0];

        $x = array_map(fn ($b) => [(float) $b], $bids);
        $fit = ProbitRegression::fit($responses, $x, ['bid']);

        $this->assertTrue($fit['converged']);
        $this->assertLessThan(0, $fit['coefficients']['bid']);
    }

    public function test_probit_rejects_non_binary_response(): void
    {
        $this->expectException(ValuationException::class);
        ProbitRegression::fit([0, 1, 2, 1], [[1.0], [2.0], [3.0], [4.0]], ['bid']);
    }

    /**
     * Realises each response probability as a block of binary answers whose
     * proportion of 1s matches it, so the solver still receives the 0/1 input
     * it validates while the likelihood is effectively the fractional one.
     */
    private function fitProbitOnFractions(array $y, array $x, array $names): array
    {
        $repeats = 400;
        $expandedY = [];
        $expandedX = [];

        foreach ($y as $i => $p) {
            $ones = (int) round($p * $repeats);
            for ($r = 0; $r < $repeats; $r++) {
                $expandedY[] = $r < $ones ? 1 : 0;
                $expandedX[] = $x[$i];
            }
        }

        return ProbitRegression::fit($expandedY, $expandedX, $names);
    }
}
