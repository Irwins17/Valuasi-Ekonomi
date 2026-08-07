<?php

namespace Tests\Unit\Valuation;

use App\Services\Valuation\EcosystemServiceValuationCalculator;
use App\Services\Valuation\Exceptions\ValuationException;
use PHPUnit\Framework\TestCase;

class EcosystemServiceValuationCalculatorTest extends TestCase
{
    private EcosystemServiceValuationCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new EcosystemServiceValuationCalculator;
    }

    // === landCoverItemValue against real Lampiran 1 figures ==================

    public function test_land_cover_item_value_matches_cemara_laut_reklamasi_indeks_1(): void
    {
        // A.1.1 Cemara Laut, Area Reklamasi, Lampiran 1: 33.18 m3/ha x
        // Rp3.231.311 x 79.86 ha = Rp8.562.181.833 (PDF-stated total).
        $value = $this->calc->landCoverItemValue(33.18, 3231311, 79.86);

        $this->assertEqualsWithDelta(8_562_181_833, $value, 1000);
    }

    public function test_land_cover_item_value_matches_gori_belukar_indeks_1(): void
    {
        // A.1.1 Gori, Area Belukar, Lampiran 1: 46.58 m3/ha x Rp1.678.689 x
        // 2.58 ha = Rp201.738.801 (PDF-stated total).
        $value = $this->calc->landCoverItemValue(46.58, 1678689, 2.58);

        $this->assertEqualsWithDelta(201_738_801, $value, 1000);
    }

    public function test_land_cover_item_value_matches_pencegah_erosi_lahan_terbangun_indeks_2(): void
    {
        // A.1 Pencegah erosi, Area Lahan Terbangun, Lampiran 2: 14.49 m3/ha/th
        // x Rp250.000 x 345.29 ha = Rp1.251.072.899 (recomputed; the source
        // table for Indeks 4 only prints Jumlah, not the final total).
        $value = $this->calc->landCoverItemValue(14.49, 250000, 345.29);

        $this->assertEqualsWithDelta(1_251_072_899, $value, 500_000);
    }

    // === Aggregation ============================================================

    public function test_summarize_groups_by_category_and_sums_total(): void
    {
        $items = [
            ['service_category' => 'provisioning', 'total_value' => 100],
            ['service_category' => 'provisioning', 'total_value' => 50],
            ['service_category' => 'regulating', 'total_value' => 30],
            ['service_category' => 'supporting', 'total_value' => 20],
            ['service_category' => 'cultural', 'total_value' => 10],
        ];

        $result = $this->calc->summarize($items);

        $this->assertSame(150.0, $result['by_category']['provisioning']);
        $this->assertSame(30.0, $result['by_category']['regulating']);
        $this->assertSame(20.0, $result['by_category']['supporting']);
        $this->assertSame(10.0, $result['by_category']['cultural']);
        $this->assertSame(210.0, $result['total']);
    }

    public function test_summarize_throws_on_unknown_category(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->summarize([
            ['service_category' => 'invalid', 'total_value' => 100],
        ]);
    }

    // === Table 1 formula methods, one worked example each =====================

    public function test_food_production(): void
    {
        // VPi = FPi x Pi
        $this->assertEqualsWithDelta(500.0, $this->calc->foodProduction(10, 50), 1e-9);
    }

    public function test_raw_material(): void
    {
        // VRMi = RMPi x Pi
        $this->assertEqualsWithDelta(500.0, $this->calc->rawMaterial(10, 50), 1e-9);
    }

    public function test_climate_carbon(): void
    {
        // VCi = CSi x PCi
        $this->assertEqualsWithDelta(500.0, $this->calc->climateCarbon(10, 50), 1e-9);
    }

    public function test_erosion_control(): void
    {
        // VACi = Fi x BPi
        $this->assertEqualsWithDelta(500.0, $this->calc->erosionControl(10, 50), 1e-9);
    }

    public function test_water_supply(): void
    {
        // VWSi = WSi x W
        $this->assertEqualsWithDelta(500.0, $this->calc->waterSupply(10, 50), 1e-9);
    }

    public function test_nutrient_cycling(): void
    {
        // VNCi = LAj x PNCi
        $this->assertEqualsWithDelta(500.0, $this->calc->nutrientCycling(10, 50), 1e-9);
    }

    public function test_habitat(): void
    {
        // VHi = LAj x PHi
        $this->assertEqualsWithDelta(500.0, $this->calc->habitat(10, 50), 1e-9);
    }

    public function test_local_value(): void
    {
        // LVj = PMj x LSj
        $this->assertEqualsWithDelta(500.0, $this->calc->localValue(10, 50), 1e-9);
    }

    public function test_research_location(): void
    {
        // VRLj = BRj x FRj
        $this->assertEqualsWithDelta(500.0, $this->calc->researchLocation(10, 50), 1e-9);
    }

    public function test_recreation_consumer_surplus_per_visitor(): void
    {
        // V = beta0 + beta1*TC; choke price = -beta0/beta1 = -1000/-2 = 500.
        // CSj = (choke_price - AC) x avg_visits / 2 = (500-100) x 20 / 2 = 4000.
        $cs = $this->calc->recreationConsumerSurplusPerVisitor(
            beta0: 1000,
            beta1: -2,
            averageCost: 100,
            averageVisits: 20,
        );

        $this->assertEqualsWithDelta(4000.0, $cs, 1e-9);
    }

    public function test_recreation_consumer_surplus_throws_when_beta1_is_zero(): void
    {
        $this->expectException(ValuationException::class);
        $this->calc->recreationConsumerSurplusPerVisitor(1000, 0, 100, 20);
    }

    public function test_recreation_site_value(): void
    {
        // VWj = CSj x LWj x JKWj
        $this->assertEqualsWithDelta(500.0, $this->calc->recreationSiteValue(10, 5, 10), 1e-9);
    }
}
