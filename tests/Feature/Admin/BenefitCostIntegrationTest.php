<?php

namespace Tests\Feature\Admin;

use App\Models\CvmAnalysis;
use App\Models\EcosystemServiceRecord;
use App\Models\EopData;
use App\Models\Project;
use App\Models\ProjectValuationSetting;
use App\Models\Role;
use App\Models\TcmAnalysis;
use App\Models\User;
use App\Services\Valuation\EconomicValuationCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * The seam between the valuation modules and the benefit/cost tables that feed
 * TEV and BCR.
 *
 * Three things are worth protecting here and none of them are visible in a
 * single unit test:
 *  - a benefit pulled from a module keeps the link to the record it came from,
 *    so the figure stays traceable rather than becoming an anonymous number;
 *  - every present value on the site comes out of EconomicValuationCalculator,
 *    so the project page, a stored pv_value and the sensitivity screen cannot
 *    disagree about the same project;
 *  - an overlap between EOP and a provisioning service is surfaced, because an
 *    inflated TEV is indistinguishable from a correct one by inspection.
 */
class BenefitCostIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_YEAR = 2026;

    private const DISCOUNT_RATE = 10.0;

    private function admin(): User
    {
        $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrator']);

        return User::create([
            'name' => 'Admin BC',
            'email' => 'admin-bc@valuasi.local',
            'password' => 'password123',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    /** A project whose discounting assumptions are pinned, so PV is predictable. */
    private function project(User $user, string $code = 'PRJ-BC'): Project
    {
        $project = Project::create([
            'code' => $code, 'name' => "Proyek {$code}", 'location' => 'Maluku Utara',
            'status' => 'in_progress', 'created_by' => $user->id,
        ]);

        ProjectValuationSetting::create([
            'project_id' => $project->id,
            'base_year' => self::BASE_YEAR,
            'discount_rate' => self::DISCOUNT_RATE,
            'analysis_period' => 10,
            'currency' => 'IDR',
            'eop_value_basis' => 'net',
        ]);

        return $project->refresh();
    }

    private function calculator(): EconomicValuationCalculator
    {
        return app(EconomicValuationCalculator::class);
    }

    /** ΔQ = 50, gross = 1.000.000, net = 800.000. */
    private function eopRecord(Project $project, string $commodity = 'Ikan', int $year = 2027): EopData
    {
        $values = $this->calculator()->eopRecordValues(100, 150, 20000, 200000);

        return EopData::create([
            'project_id' => $project->id,
            'recorded_by' => $project->created_by,
            'service_category' => 'provisioning',
            'commodity_name' => $commodity,
            'product_type' => 'Hasil tangkapan',
            'production_before' => 100,
            'production_after' => 150,
            'production_change' => $values['production_change'],
            'unit' => 'kg',
            'market_price' => 20000,
            'production_cost' => 200000,
            'total_value' => $values['total_value'],
            'net_value' => $values['net_value'],
            'period_year' => $year,
            'data_source' => 'Survei',
            'impact_type' => 'positive',
        ]);
    }

    // ── Benefits pulled from a module ────────────────────────────────────

    public function test_a_benefit_pulled_from_eop_keeps_its_source_and_gets_a_present_value(): void
    {
        $user = $this->admin();
        $project = $this->project($user);
        $eop = $this->eopRecord($project);

        // net_value is the offered amount, since the project values EOP net.
        $this->assertEquals(800000.0, (float) $eop->net_value);

        $this->actingAs($user)->post(route('admin.benefits.store', $project->id), [
            'category' => 'direct_use',
            'subcategory' => 'production',
            'ecosystem_service_group' => 'provisioning',
            'description' => 'Produksi Ikan',
            'value' => 800000,
            'annual_value' => 800000,
            'unit' => 'kg',
            'period_year' => 2027,
            'method_used' => 'EOP',
            'data_source' => 'eop',
            'source_module' => 'eop',
            'source_record_id' => $eop->id,
            'data_status' => 'verified',
        ])->assertRedirect(route('admin.projects.show', $project->id));

        $benefit = $project->benefits()->firstOrFail();

        $this->assertSame('eop', $benefit->source_module);
        $this->assertEquals($eop->id, $benefit->source_record_id);
        $this->assertEquals(800000.0, (float) $benefit->annual_value);
        $this->assertSame('provisioning', $benefit->ecosystem_service_group);

        // PV = 800.000 / 1,10 — the calculator's answer, not a literal.
        $expected = $this->calculator()->calculatePV(800000, 2027, self::BASE_YEAR, self::DISCOUNT_RATE);
        $this->assertEqualsWithDelta($expected, (float) $benefit->pv_value, 0.01);
        $this->assertEqualsWithDelta(727272.73, (float) $benefit->pv_value, 0.01);
    }

    public function test_a_benefit_pulled_from_a_tcm_analysis_gets_a_present_value(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        // CS = −1 / −0,0005 = 2.000; nilai rekreasi = 2.000 × 10.000.
        $tcm = TcmAnalysis::create([
            'project_id' => $project->id,
            'analysis_code' => 'TCM-1',
            'site_name' => 'Pantai Selatan',
            'regression_model' => 'poisson',
            'coefficient_source' => 'manual',
            'respondent_count' => 60,
            'total_visitors' => 10000,
            'beta_0' => 3.2,
            'beta_1' => -0.0005,
            'period_year' => 2028,
        ]);

        $this->assertEquals(20000000.0, (float) $tcm->recreation_value);

        $this->actingAs($user)->post(route('admin.benefits.store', $project->id), [
            'category' => 'direct_use',
            'subcategory' => 'recreation',
            'ecosystem_service_group' => 'cultural',
            'description' => 'Nilai rekreasi — Pantai Selatan',
            'value' => 20000000,
            'annual_value' => 20000000,
            'unit' => 'Rp/tahun',
            'period_year' => 2028,
            'method_used' => 'TCM',
            'data_source' => 'tcm',
            'source_module' => 'tcm',
            'source_record_id' => $tcm->id,
            'data_status' => 'verified',
        ])->assertRedirect();

        $benefit = $project->benefits()->firstOrFail();

        $this->assertSame('tcm', $benefit->source_module);
        // Two years out: 20.000.000 / 1,21.
        $expected = $this->calculator()->calculatePV(20000000, 2028, self::BASE_YEAR, self::DISCOUNT_RATE);
        $this->assertEqualsWithDelta($expected, (float) $benefit->pv_value, 0.01);
        $this->assertEqualsWithDelta(16528925.62, (float) $benefit->pv_value, 0.01);
    }

    public function test_a_benefit_pulled_from_a_cvm_analysis_gets_a_present_value(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        // Mean WTP = α / β = 2 / 0,0001 = 20.000; total = × 1.000 responden.
        $cvm = CvmAnalysis::create([
            'project_id' => $project->id,
            'analysis_code' => 'CVM-1',
            'scenario' => 'Rehabilitasi mangrove',
            'model' => 'logit',
            'coefficient_source' => 'manual',
            'bid_value' => 15000,
            'respondent_count' => 120,
            'alpha' => 2,
            'beta_bid' => 0.0001,
            'target_population' => 1000,
            'period_year' => 2026,
        ]);

        $this->assertEquals(20000000.0, (float) $cvm->total_wtp);

        $this->actingAs($user)->post(route('admin.benefits.store', $project->id), [
            'category' => 'non_use',
            'subcategory' => 'existence_value',
            'ecosystem_service_group' => 'cultural',
            'description' => 'WTP masyarakat — Rehabilitasi mangrove',
            'value' => 20000000,
            'period_year' => 2026,
            'method_used' => 'CVM',
            'data_source' => 'cvm',
            'source_module' => 'cvm',
            'source_record_id' => $cvm->id,
            'data_status' => 'draft',
        ])->assertRedirect();

        $benefit = $project->benefits()->firstOrFail();

        $this->assertSame('cvm', $benefit->source_module);
        $this->assertSame('draft', $benefit->data_status);
        // Recorded in the base year, so n = 0 and the amount is untouched.
        $this->assertEqualsWithDelta(20000000.0, (float) $benefit->pv_value, 0.01);
    }

    public function test_a_manual_benefit_needs_no_source_record(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.benefits.store', $project->id), [
            'category' => 'direct_use',
            'subcategory' => 'production',
            'description' => 'Estimasi literatur',
            'value' => 1210000,
            'period_year' => 2028,
            'data_source' => 'literature',
            'source_module' => 'manual',
            'data_status' => 'verified',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $benefit = $project->benefits()->firstOrFail();

        $this->assertNull($benefit->source_record_id);
        $this->assertEqualsWithDelta(1000000.0, (float) $benefit->pv_value, 0.01);
    }

    public function test_a_module_record_from_another_project_is_refused(): void
    {
        $user = $this->admin();
        $project = $this->project($user, 'PRJ-BC-A');
        $other = $this->project($user, 'PRJ-BC-B');
        $foreignEop = $this->eopRecord($other);

        $this->actingAs($user)->post(route('admin.benefits.store', $project->id), [
            'category' => 'direct_use',
            'subcategory' => 'production',
            'description' => 'Produksi Ikan',
            'value' => 800000,
            'period_year' => 2027,
            'data_source' => 'eop',
            'source_module' => 'eop',
            'source_record_id' => $foreignEop->id,
        ])->assertSessionHasErrors('source_record_id');

        $this->assertDatabaseCount('benefits', 0);
    }

    public function test_editing_a_benefit_recomputes_its_present_value(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $benefit = $project->benefits()->create([
            'category' => 'direct_use', 'subcategory' => 'production',
            'description' => 'Manfaat', 'value' => 1100, 'period_year' => 2027,
            'data_source' => 'manual', 'pv_value' => 1000,
        ]);

        // Same amount, one year further out — the stored PV must follow.
        $this->actingAs($user)->put(route('admin.benefits.update', [$project->id, $benefit->id]), [
            'category' => 'direct_use',
            'subcategory' => 'production',
            'description' => 'Manfaat',
            'value' => 1100,
            'period_year' => 2028,
            'data_source' => 'manual',
            'source_module' => 'manual',
        ])->assertRedirect();

        $expected = $this->calculator()->calculatePV(1100, 2028, self::BASE_YEAR, self::DISCOUNT_RATE);
        $this->assertEqualsWithDelta($expected, (float) $benefit->fresh()->pv_value, 0.01);
        $this->assertEqualsWithDelta(909.09, (float) $benefit->fresh()->pv_value, 0.01);
    }

    // ── Costs ────────────────────────────────────────────────────────────

    public function test_a_cost_stores_its_activity_group_and_a_consistent_present_value(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.costs.store', $project->id), [
            'category' => 'direct_cost',
            'subcategory' => 'investment',
            'activity_group' => 'restoration',
            'description' => 'Penanaman mangrove',
            'value' => 1210000,
            'year_applied' => 2028,
            'payment_type' => 'Investasi Awal',
            'calculation_method' => 'Harga satuan × luas',
            'responsible_party' => 'Dinas Lingkungan Hidup',
            'data_status' => 'verified',
        ])->assertRedirect(route('admin.projects.show', $project->id));

        $cost = $project->costs()->firstOrFail();

        $this->assertSame('restoration', $cost->activity_group);
        $this->assertSame('Harga satuan × luas', $cost->calculation_method);
        $this->assertSame('Dinas Lingkungan Hidup', $cost->responsible_party);

        $expected = $this->calculator()->calculatePV(1210000, 2028, self::BASE_YEAR, self::DISCOUNT_RATE);
        $this->assertEqualsWithDelta($expected, (float) $cost->pv_value, 0.01);
        $this->assertEqualsWithDelta(1000000.0, (float) $cost->pv_value, 0.01);
    }

    public function test_updating_a_cost_activity_group_keeps_the_present_value_consistent(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $cost = $project->costs()->create([
            'category' => 'direct_cost', 'subcategory' => 'investment',
            'description' => 'Monitoring', 'value' => 1210000,
            'year_applied' => 2028, 'pv_value' => 1000000,
        ]);

        $this->actingAs($user)->put(route('admin.costs.update', [$project->id, $cost->id]), [
            'category' => 'direct_cost',
            'subcategory' => 'operation_maintenance',
            'activity_group' => 'monitoring',
            'description' => 'Monitoring tahunan',
            'value' => 1210000,
            'year_applied' => 2027,
            'data_status' => 'draft',
        ])->assertRedirect();

        $cost->refresh();

        $this->assertSame('monitoring', $cost->activity_group);
        $this->assertSame('draft', $cost->data_status);

        // Moved a year closer, so the discount factor drops from 1,21 to 1,10.
        $expected = $this->calculator()->calculatePV(1210000, 2027, self::BASE_YEAR, self::DISCOUNT_RATE);
        $this->assertEqualsWithDelta($expected, (float) $cost->pv_value, 0.01);
        $this->assertEqualsWithDelta(1100000.0, (float) $cost->pv_value, 0.01);
    }

    public function test_an_unknown_activity_group_is_rejected(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.costs.store', $project->id), [
            'category' => 'direct_cost',
            'subcategory' => 'investment',
            'activity_group' => 'kegiatan-lain',
            'description' => 'Biaya',
            'value' => 1000,
        ])->assertSessionHasErrors('activity_group');
    }

    // ── Double counting ──────────────────────────────────────────────────

    public function test_eop_and_food_production_on_the_same_commodity_raise_a_danger_warning(): void
    {
        $user = $this->admin();
        $project = $this->project($user);
        $eop = $this->eopRecord($project);

        $food = EcosystemServiceRecord::create([
            'project_id' => $project->id,
            'service_key' => 'FOOD',
            'service_category' => 'provisioning',
            'record_code' => 'FP-1',
            'location' => 'Tambak',
            'quantity_value' => 1250,
            'quantity_unit' => 'kg',
            'unit_price' => 25000,
            'price_conversion' => 1,
            'area_ha' => 10,
            'value_per_ha' => 31250000,
            'total_value' => 312500000,
            'period_year' => 2027,
        ]);

        $project->benefits()->create([
            'category' => 'direct_use', 'subcategory' => 'production',
            'ecosystem_service_group' => 'provisioning',
            'description' => 'Produksi Ikan', 'value' => 800000, 'period_year' => 2027,
            'data_source' => 'eop', 'method_used' => 'EOP',
            'source_module' => 'eop', 'source_record_id' => $eop->id,
        ]);
        $project->benefits()->create([
            'category' => 'direct_use', 'subcategory' => 'production',
            'ecosystem_service_group' => 'provisioning',
            'description' => 'Food Production — Ikan Tambak', 'value' => 312500000,
            'period_year' => 2027, 'data_source' => 'manual',
            'source_module' => 'ecosystem_service', 'source_record_id' => $food->id,
        ]);

        $this->actingAs($user)->get(route('admin.projects.show', $project->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Show')
                ->has('doubleCountingWarnings', 1)
                ->where('doubleCountingWarnings.0.code', 'eop_vs_provisioning_service')
                ->where('doubleCountingWarnings.0.severity', 'danger')
                ->where('doubleCountingWarnings.0.benefit_ids', fn ($ids) => count($ids) === 2)
            );
    }

    public function test_a_project_without_overlapping_benefits_reports_no_warnings(): void
    {
        $user = $this->admin();
        $project = $this->project($user);
        $eop = $this->eopRecord($project);

        $project->benefits()->create([
            'category' => 'direct_use', 'subcategory' => 'production',
            'description' => 'Produksi Ikan', 'value' => 800000, 'period_year' => 2027,
            'data_source' => 'eop', 'method_used' => 'EOP',
            'source_module' => 'eop', 'source_record_id' => $eop->id,
        ]);

        $this->actingAs($user)->get(route('admin.projects.show', $project->id))
            ->assertInertia(fn (Assert $page) => $page->has('doubleCountingWarnings', 0));
    }

    public function test_one_module_record_used_by_two_benefits_is_flagged(): void
    {
        $user = $this->admin();
        $project = $this->project($user);
        $eop = $this->eopRecord($project);

        foreach (['Produksi Ikan tahap 1', 'Produksi Ikan tahap 2'] as $description) {
            $project->benefits()->create([
                'category' => 'direct_use', 'subcategory' => 'production',
                'description' => $description, 'value' => 800000, 'period_year' => 2027,
                'data_source' => 'eop', 'method_used' => 'EOP',
                'source_module' => 'eop', 'source_record_id' => $eop->id,
            ]);
        }

        $this->actingAs($user)->get(route('admin.projects.show', $project->id))
            ->assertInertia(fn (Assert $page) => $page
                ->has('doubleCountingWarnings', 1)
                ->where('doubleCountingWarnings.0.code', 'duplicate_source_record')
                ->where('doubleCountingWarnings.0.severity', 'danger')
            );
    }

    // ── Sensitivity ──────────────────────────────────────────────────────

    public function test_the_unadjusted_sensitivity_scenario_matches_the_project_totals(): void
    {
        $user = $this->admin();
        $project = $this->seededProject($user);

        $project->calculateTEV()->save();
        $project->refresh();

        $response = $this->actingAs($user)->getJson(route('admin.sensitivity.simulate', [
            'project_id' => $project->id,
        ]))->assertOk();

        $original = $response->json('original');

        $this->assertEqualsWithDelta((float) $project->total_benefits, $original['benefits'], 0.01);
        $this->assertEqualsWithDelta((float) $project->total_costs, $original['costs'], 0.01);
        $this->assertEqualsWithDelta((float) $project->tev, $original['tev'], 0.01);
        $this->assertEqualsWithDelta((float) $project->bcr, $original['bcr'], 0.0001);
    }

    public function test_the_adjusted_sensitivity_scenario_matches_the_calculator(): void
    {
        $user = $this->admin();
        $project = $this->seededProject($user);

        $response = $this->actingAs($user)->getJson(route('admin.sensitivity.simulate', [
            'project_id' => $project->id,
            'price_adjustment' => -20,
            'inflation_rate' => 15,
            'discount_rate' => 8,
        ]))->assertOk();

        // The same arithmetic the controller must be doing, recomputed here
        // straight from the calculator rather than restated as literals.
        $calculator = $this->calculator();
        $settings = new ProjectValuationSetting([
            'base_year' => self::BASE_YEAR,
            'discount_rate' => 8,
            'analysis_period' => 10,
            'currency' => 'IDR',
            'eop_value_basis' => 'net',
        ]);

        $benefits = $project->benefits->map(fn ($b) => ['value' => (float) $b->value * 0.8, 'year' => $b->period_year])->all();
        $costs = $project->costs->map(fn ($c) => ['value' => (float) $c->value * 1.15, 'year' => $c->year_applied])->all();

        $pvBenefits = $calculator->calculateDiscountedTEV($benefits, $settings);
        $pvCosts = $calculator->sumPresentValue($costs, $settings);

        $adjusted = $response->json('adjusted');

        $this->assertEqualsWithDelta($pvBenefits, $adjusted['benefits'], 0.01);
        $this->assertEqualsWithDelta($pvCosts, $adjusted['costs'], 0.01);
        $this->assertEqualsWithDelta($calculator->calculateNPV($pvBenefits, $pvCosts), $adjusted['tev'], 0.01);
        $this->assertEqualsWithDelta($calculator->calculateBCR($pvBenefits, $pvCosts), $adjusted['bcr'], 0.0001);

        $this->assertSame(8.0, (float) $response->json('assumptions.adjusted_discount_rate'));
        $this->assertSame(self::DISCOUNT_RATE, (float) $response->json('assumptions.original_discount_rate'));
    }

    public function test_sensitivity_leaves_a_ratio_undefined_when_a_project_has_no_costs(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $project->benefits()->create([
            'category' => 'direct_use', 'subcategory' => 'production',
            'description' => 'Manfaat', 'value' => 1000, 'period_year' => 2027,
            'data_source' => 'manual',
        ]);

        $response = $this->actingAs($user)->getJson(route('admin.sensitivity.simulate', [
            'project_id' => $project->id,
        ]))->assertOk();

        // Null, not 0 — "no costs to divide by" is not "no benefit".
        $this->assertNull($response->json('original.bcr'));
        $this->assertNull($response->json('changes.bcr_pct'));
    }

    /** A project carrying benefits and costs across several years. */
    private function seededProject(User $user): Project
    {
        $project = $this->project($user, 'PRJ-BC-SENS');

        $project->benefits()->createMany([
            [
                'category' => 'direct_use', 'subcategory' => 'production',
                'description' => 'Produksi perikanan', 'value' => 5000000,
                'period_year' => 2027, 'data_source' => 'eop',
            ],
            [
                'category' => 'non_use', 'subcategory' => 'existence_value',
                'description' => 'WTP masyarakat', 'value' => 3000000,
                'period_year' => 2029, 'data_source' => 'cvm',
            ],
            [
                'category' => 'indirect_use', 'subcategory' => 'water_regulation',
                'description' => 'Tata air', 'value' => 1500000,
                'data_source' => 'manual',
            ],
        ]);

        $project->costs()->createMany([
            [
                'category' => 'direct_cost', 'subcategory' => 'investment',
                'description' => 'Restorasi', 'value' => 2000000,
                'activity_group' => 'restoration', 'year_applied' => 2026,
            ],
            [
                'category' => 'direct_cost', 'subcategory' => 'operation_maintenance',
                'description' => 'Monitoring', 'value' => 400000,
                'activity_group' => 'monitoring', 'year_applied' => 2030,
            ],
        ]);

        return $project->load(['benefits', 'costs']);
    }
}
