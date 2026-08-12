<?php

namespace Tests\Unit\Valuation;

use App\Models\ProjectValuationSetting;
use App\Services\Valuation\EconomicValuationCalculator;
use App\Services\Valuation\Exceptions\ValuationException;
use Tests\TestCase;

/**
 * Present value, NPV and BCR.
 *
 * The expected figures are chosen so they can be checked by hand: at 10% a
 * year, 1,100 one year out is worth exactly 1,000 today, 1,210 two years out
 * likewise, and so on. That keeps the assertions independent of the formula
 * being tested instead of restating it.
 */
class PresentValueValuationTest extends TestCase
{
    private EconomicValuationCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new EconomicValuationCalculator;
    }

    private function settings(int $baseYear = 2026, float $rate = 10.0): ProjectValuationSetting
    {
        return new ProjectValuationSetting([
            'base_year' => $baseYear,
            'discount_rate' => $rate,
            'analysis_period' => 10,
            'currency' => 'IDR',
            'eop_value_basis' => 'net',
        ]);
    }

    // ── calculatePV ──────────────────────────────────────────────────────

    public function test_pv_at_the_base_year_is_the_value_itself(): void
    {
        $this->assertEqualsWithDelta(1000.0, $this->calc->calculatePV(1000, 2026, 2026, 10.0), 1e-9);
    }

    public function test_pv_discounts_future_amounts_exactly(): void
    {
        // 1,100 a year out at 10% is 1,000 today; 1,210 two years out; 1,331 three.
        $this->assertEqualsWithDelta(1000.0, $this->calc->calculatePV(1100, 2027, 2026, 10.0), 1e-9);
        $this->assertEqualsWithDelta(1000.0, $this->calc->calculatePV(1210, 2028, 2026, 10.0), 1e-9);
        $this->assertEqualsWithDelta(1000.0, $this->calc->calculatePV(1331, 2029, 2026, 10.0), 1e-9);
    }

    public function test_a_zero_discount_rate_leaves_the_amount_untouched(): void
    {
        $this->assertEqualsWithDelta(5000.0, $this->calc->calculatePV(5000, 2036, 2026, 0.0), 1e-9);
    }

    public function test_a_custom_rate_discounts_accordingly(): void
    {
        // 6% over 2 years: 1,000 / 1.1236
        $this->assertEqualsWithDelta(
            1000 / 1.1236,
            $this->calc->calculatePV(1000, 2028, 2026, 6.0),
            1e-9
        );
    }

    public function test_amounts_dated_before_the_base_year_are_not_compounded_upward(): void
    {
        // n is floored at 0, so an earlier year is treated as present-year
        // money rather than being inflated above its face value.
        $this->assertEqualsWithDelta(1000.0, $this->calc->calculatePV(1000, 2020, 2026, 10.0), 1e-9);
        $this->assertEqualsWithDelta(1000.0, $this->calc->calculatePV(1000, 2025, 2026, 50.0), 1e-9);
    }

    public function test_pv_rejects_a_discount_rate_that_destroys_the_discount_factor(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculatePV(1000, 2027, 2026, -100.0);
    }

    // ── NPV ──────────────────────────────────────────────────────────────

    public function test_npv_is_benefits_less_costs(): void
    {
        $this->assertEqualsWithDelta(1500.0, $this->calc->calculateNPV(2000, 500), 1e-9);
    }

    public function test_npv_can_be_negative(): void
    {
        $this->assertEqualsWithDelta(-250.0, $this->calc->calculateNPV(750, 1000), 1e-9);
    }

    // ── BCR ──────────────────────────────────────────────────────────────

    public function test_bcr_divides_discounted_benefits_by_discounted_costs(): void
    {
        $this->assertEqualsWithDelta(4.0, $this->calc->calculateBCR(2000, 500), 1e-9);
        $this->assertEqualsWithDelta(0.75, $this->calc->calculateBCR(750, 1000), 1e-9);
    }

    public function test_bcr_is_undefined_rather_than_zero_when_there_are_no_costs(): void
    {
        // Zero would read as "no benefit at all", which is the opposite of
        // what a project with benefits and no recorded cost means.
        $this->assertNull($this->calc->calculateBCR(2000, 0));
        $this->assertNull($this->calc->calculateBCR(0, 0));
    }

    public function test_bcr_rejects_negative_costs(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->calculateBCR(1000, -500);
    }

    // ── Aggregation ──────────────────────────────────────────────────────

    public function test_discounted_tev_sums_the_present_value_of_every_benefit(): void
    {
        $total = $this->calc->calculateDiscountedTEV([
            ['value' => 1100, 'year' => 2027],   // -> 1000
            ['value' => 1210, 'year' => 2028],   // -> 1000
            ['value' => 500,  'year' => 2026],   // -> 500
        ], $this->settings());

        $this->assertEqualsWithDelta(2500.0, $total, 1e-9);
    }

    public function test_items_without_a_year_are_treated_as_base_year_amounts(): void
    {
        $total = $this->calc->sumPresentValue([
            ['value' => 1000],
            ['value' => 2000, 'year' => null],
        ], $this->settings());

        $this->assertEqualsWithDelta(3000.0, $total, 1e-9);
    }

    public function test_an_empty_list_has_no_present_value(): void
    {
        $this->assertEqualsWithDelta(0.0, $this->calc->sumPresentValue([], $this->settings()), 1e-9);
    }

    public function test_settings_expose_the_rate_as_a_fraction(): void
    {
        $this->assertEqualsWithDelta(0.06, $this->settings(2026, 6.0)->discountRateFraction(), 1e-12);
    }
}
