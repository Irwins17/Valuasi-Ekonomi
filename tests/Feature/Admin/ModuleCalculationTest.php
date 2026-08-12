<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Derived columns are no longer computed in model boot hooks — the writer
 * asks EconomicValuationCalculator for them and passes them in. That removes
 * the safety net a saving() hook provided, so these tests go through the real
 * HTTP layer and assert the stored figures, which is where a forgotten call
 * would otherwise show up only as a silent zero.
 */
class ModuleCalculationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::create(['name' => 'Administrator', 'slug' => 'admin']);

        return User::create([
            'name' => 'Admin Calc',
            'email' => 'admin-calc@valuasi.local',
            'password' => 'password123',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    private function project(User $user): Project
    {
        return Project::create([
            'code' => 'PRJ-CALC', 'name' => 'Proyek Kalkulasi', 'location' => 'Jawa',
            'status' => 'draft', 'created_by' => $user->id,
        ]);
    }

    public function test_duv_store_persists_gross_and_net(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.modules.duv.store', $project->id), [
            'record_code' => 'DUV-1',
            'service_category' => 'provisioning',
            'goods_type' => 'Ikan',
            'location' => 'Tambak',
            'quantity' => 1250,
            'unit' => 'kg',
            'market_price' => 25000,
            'production_cost' => 5000000,
            'period_year' => 2026,
            'data_source' => 'Survei',
            'data_status' => 'draft',
        ])->assertRedirect(route('admin.modules.duv.index', $project->id));

        $row = $project->duvData()->first();
        // gross = 1250 * 25000 = 31_250_000; net = gross - 5_000_000
        $this->assertEquals(31250000, (float) $row->gross_value);
        $this->assertEquals(26250000, (float) $row->net_value);
    }

    public function test_duv_update_recomputes_the_derived_values(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.modules.duv.store', $project->id), [
            'record_code' => 'DUV-2', 'service_category' => 'provisioning',
            'goods_type' => 'Ikan', 'location' => 'Tambak', 'quantity' => 10,
            'unit' => 'kg', 'market_price' => 1000, 'period_year' => 2026,
            'data_source' => 'Survei', 'data_status' => 'draft',
        ])->assertRedirect();

        $row = $project->duvData()->first();
        $this->assertEquals(10000, (float) $row->gross_value);

        $this->actingAs($user)->put(route('admin.modules.duv.update', [$project->id, $row->id]), [
            'record_code' => 'DUV-2', 'service_category' => 'provisioning',
            'goods_type' => 'Ikan', 'location' => 'Tambak', 'quantity' => 20,
            'unit' => 'kg', 'market_price' => 1000, 'production_cost' => 2500,
            'period_year' => 2026, 'data_source' => 'Survei', 'data_status' => 'final',
        ])->assertRedirect();

        $row->refresh();
        $this->assertEquals(20000, (float) $row->gross_value);
        $this->assertEquals(17500, (float) $row->net_value);
    }

    public function test_abm_store_persists_the_three_avoidance_totals(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.modules.abm.store', $project->id), [
            'respondent_code' => 'ABM-1',
            'location' => 'Desa X',
            'risk_type' => 'Pencemaran air',
            'defensive_action' => 'Membeli air bersih/galon',
            'quantity' => 24,
            'unit_price' => 20000,
            'time_cost' => 300000,
            'medical_cost' => 750000,
            'sick_days' => 5,
            'daily_wage' => 100000,
        ])->assertRedirect(route('admin.modules.abm.index', $project->id));

        $row = $project->abmData()->first();
        // defensive = 24*20000 + 300000 = 780_000
        $this->assertEquals(780000, (float) $row->defensive_expenditure);
        // lost income = 5 * 100000
        $this->assertEquals(500000, (float) $row->lost_income);
        // total = 780_000 + 750_000 + 500_000
        $this->assertEquals(2030000, (float) $row->total_avoidance);
    }

    public function test_water_supply_applies_the_cubic_metre_to_litre_conversion(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.modules.ecosystem.store', [$project->id, 'WATER']), [
            'record_code' => 'WS-1',
            'ecosystem_type' => 'Hutan lindung',
            'location' => 'DAS Hulu',
            'quantity_value' => 850,
            'quantity_unit' => 'm³/tahun',
            'unit_price' => 25,
            'area_ha' => 10.5,
            'research_method' => 'Studi hidrologi',
            'period_year' => 2026,
            'data_source' => 'Laporan penelitian',
        ])->assertRedirect(route('admin.modules.ecosystem.index', [$project->id, 'WATER']));

        $row = $project->ecosystemServiceRecords()->first();
        // 850 m3 at Rp25/litre => 850 * 25 * 1000 per hectare
        $this->assertEquals(1000, (float) $row->price_conversion);
        $this->assertEquals(21250000, (float) $row->value_per_ha);
        $this->assertEquals(223125000, (float) $row->total_value);
    }

    public function test_water_supply_in_litres_needs_no_conversion(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.modules.ecosystem.store', [$project->id, 'WATER']), [
            'record_code' => 'WS-2',
            'ecosystem_type' => 'Hutan lindung',
            'location' => 'DAS Hulu',
            'quantity_value' => 850,
            'quantity_unit' => 'Liter/tahun',
            'unit_price' => 25,
            'area_ha' => 10.5,
            'research_method' => 'Studi hidrologi',
            'period_year' => 2026,
            'data_source' => 'Laporan penelitian',
        ])->assertRedirect();

        $row = $project->ecosystemServiceRecords()->first();
        $this->assertEquals(1, (float) $row->price_conversion);
        $this->assertEquals(21250, (float) $row->value_per_ha);
    }

    public function test_erosion_control_multiplies_frequency_by_handling_cost(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.modules.ecosystem.store', [$project->id, 'EROSION']), [
            'record_code' => 'EC-1',
            'location' => 'Pantai Selatan',
            'protector_ecosystem' => 'Mangrove',
            'eroded_area_ha' => 25,
            'quantity_value' => 2,
            'unit_price' => 12500000,
            'area_ha' => 50,
            'period_year' => 2026,
            'data_source' => 'Laporan teknis',
        ])->assertRedirect();

        $row = $project->ecosystemServiceRecords()->first();
        // VACi = 2 * 12_500_000 per hectare, over 50 affected hectares
        $this->assertEquals(1, (float) $row->price_conversion);
        $this->assertEquals(25000000, (float) $row->value_per_ha);
        $this->assertEquals(1250000000, (float) $row->total_value);
        $this->assertSame('Mangrove', $row->extra['protector_ecosystem']);
    }
}
