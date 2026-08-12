<?php

namespace Tests\Feature;

use App\Models\EopData;
use Database\Seeders\RoleAndUserSeeder;
use Database\Seeders\SampleDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

/**
 * DatabaseSeeder runs with the WithoutModelEvents trait, so any calculation
 * that lived in a model's saving() hook was silently skipped while seeding —
 * seeded EOP rows carried zeroes in production_change / total_value even
 * though the inputs were right there.
 *
 * Now that the derived columns are supplied by the calculator at the call
 * site, seeding produces the same arithmetic as the controller. This test
 * seeds with model events suppressed, exactly as the real seeder does, and
 * checks the stored figures.
 */
class SeederValuationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_eop_rows_carry_correct_derived_values_without_model_events(): void
    {
        Model::withoutEvents(function () {
            $this->seed(RoleAndUserSeeder::class);
            $this->seed(SampleDataSeeder::class);
        });

        $rows = EopData::all();
        $this->assertGreaterThan(0, $rows->count(), 'sample data should include EOP rows');

        foreach ($rows as $row) {
            $expectedDelta = (float) $row->production_after - (float) $row->production_before;
            $expectedGross = $expectedDelta * (float) $row->market_price;

            $this->assertEqualsWithDelta(
                $expectedDelta,
                (float) $row->production_change,
                0.01,
                "production_change wrong for {$row->commodity_name}"
            );
            $this->assertEqualsWithDelta(
                $expectedGross,
                (float) $row->total_value,
                0.01,
                "total_value wrong for {$row->commodity_name}"
            );
            // No production cost is seeded, so net must equal gross.
            $this->assertEqualsWithDelta(
                $expectedGross,
                (float) $row->net_value,
                0.01,
                "net_value wrong for {$row->commodity_name}"
            );
        }
    }

    public function test_a_seeded_negative_impact_row_stores_a_negative_value(): void
    {
        Model::withoutEvents(function () {
            $this->seed(RoleAndUserSeeder::class);
            $this->seed(SampleDataSeeder::class);
        });

        // "Ikan Laut" drops from 12,000 to 10,500 tonnes at Rp25,000,000/tonne.
        $row = EopData::where('commodity_name', 'Ikan Laut')->first();

        $this->assertNotNull($row);
        $this->assertEquals(-1500, (float) $row->production_change);
        $this->assertEquals(-37500000000, (float) $row->total_value);
        $this->assertSame('negative', $row->impact_type);
    }
}
