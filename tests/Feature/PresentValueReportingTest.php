<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectValuationSetting;
use App\Models\Role;
use App\Models\User;
use App\Services\Valuation\EconomicValuationCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\LaravelPdf\Facades\Pdf;
use Tests\TestCase;

/**
 * Every surface that publishes a figure must publish the discounted one.
 *
 * The bug this guards against is silent by construction: a nominal amount
 * printed under a column headed "PV" looks exactly like a correct one, and is
 * only detectable by noticing that the rows do not add up to the total beside
 * them. So each test here compares what the surface reports against
 * EconomicValuationCalculator directly, and at least one asserts the two
 * differ — a project whose amounts happen to sit in the base year would pass
 * these checks even if the discounting were removed entirely.
 */
class PresentValueReportingTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_YEAR = 2026;

    private const DISCOUNT_RATE = 10.0;

    private function admin(): User
    {
        $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrator']);

        return User::create([
            'name' => 'Admin PVR', 'email' => 'admin-pvr@valuasi.local',
            'password' => 'password123', 'role_id' => $role->id, 'is_active' => true,
        ]);
    }

    private function calculator(): EconomicValuationCalculator
    {
        return app(EconomicValuationCalculator::class);
    }

    /**
     * A published project whose amounts are all dated away from the base year,
     * so nominal and PV can never coincide.
     *
     * pv_value is deliberately left unwritten: reporting must not depend on
     * the stored column being fresh.
     */
    private function project(User $user): Project
    {
        $project = Project::create([
            'code' => 'PRJ-PVR', 'name' => 'Proyek Pelaporan PV', 'location' => 'Maluku Utara',
            'status' => 'published', 'created_by' => $user->id,
        ]);

        ProjectValuationSetting::create([
            'project_id' => $project->id,
            'base_year' => self::BASE_YEAR,
            'discount_rate' => self::DISCOUNT_RATE,
            'analysis_period' => 10,
            'currency' => 'IDR',
            'eop_value_basis' => 'net',
        ]);

        $project->refresh();

        $project->benefits()->createMany([
            [
                'category' => 'direct_use', 'subcategory' => 'production',
                'description' => 'Produksi perikanan', 'value' => 1100000,
                'period_year' => 2027, 'data_source' => 'eop', 'method_used' => 'EOP',
            ],
            [
                'category' => 'non_use', 'subcategory' => 'existence_value',
                'description' => 'WTP masyarakat', 'value' => 1210000,
                'period_year' => 2028, 'data_source' => 'cvm', 'method_used' => 'CVM',
            ],
        ]);

        $project->costs()->create([
            'category' => 'direct_cost', 'subcategory' => 'investment',
            'description' => 'Restorasi', 'value' => 1331000,
            'activity_group' => 'restoration', 'year_applied' => 2029,
        ]);

        $project->calculateTEV()->save();

        return $project->refresh();
    }

    /** PV of every benefit, straight from the calculator. */
    private function expectedBenefitPvs(): array
    {
        return [
            $this->calculator()->calculatePV(1100000, 2027, self::BASE_YEAR, self::DISCOUNT_RATE),
            $this->calculator()->calculatePV(1210000, 2028, self::BASE_YEAR, self::DISCOUNT_RATE),
        ];
    }

    // ── Admin project detail ─────────────────────────────────────────────

    public function test_the_admin_detail_page_sends_a_present_value_for_every_row(): void
    {
        $user = $this->admin();
        $project = $this->project($user);
        [$firstPv, $secondPv] = $this->expectedBenefitPvs();
        $costPv = $this->calculator()->calculatePV(1331000, 2029, self::BASE_YEAR, self::DISCOUNT_RATE);

        $this->actingAs($user)->get(route('admin.projects.show', $project->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Show')
                ->where('benefits.data.0.pv_value', fn ($pv) => abs((float) $pv - $firstPv) < 0.01)
                ->where('benefits.data.1.pv_value', fn ($pv) => abs((float) $pv - $secondPv) < 0.01)
                ->where('costs.data.0.pv_value', fn ($pv) => abs((float) $pv - $costPv) < 0.01)
                // The nominal amount still travels, so the page can show both.
                ->where('benefits.data.0.value', fn ($v) => abs((float) $v - 1100000) < 0.01)
            );

        // 1.000.000 vs 1.100.000 — if discounting were dropped the assertions
        // above would fail rather than quietly still pass.
        $this->assertEqualsWithDelta(1000000.0, $firstPv, 0.01);
    }

    public function test_the_rows_on_the_admin_page_sum_to_the_totals_shown_beside_them(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $response = $this->actingAs($user)->get(route('admin.projects.show', $project->id));
        $props = $response->viewData('page')['props'];

        $rowSum = collect($props['benefits']['data'])->sum(fn ($b) => (float) $b['pv_value']);

        // The headline "Total Manfaat" is a PV; the rows under it must be too.
        $this->assertEqualsWithDelta((float) $project->total_benefits, $rowSum, 0.01);
        $this->assertEqualsWithDelta(
            (float) $project->total_costs,
            collect($props['costs']['data'])->sum(fn ($c) => (float) $c['pv_value']),
            0.01,
        );
    }

    public function test_a_row_whose_stored_present_value_is_stale_is_still_reported_correctly(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        // What a project looks like after the discount rate moved but before
        // anyone pressed "Hitung TEV" — or a row written before pv_value
        // existed at all.
        $benefit = $project->benefits()->first();
        $benefit->updateQuietly(['pv_value' => null]);

        [$firstPv] = $this->expectedBenefitPvs();

        $this->actingAs($user)->get(route('admin.projects.show', $project->id))
            ->assertInertia(fn (Assert $page) => $page
                ->where('benefits.data.0.pv_value', fn ($pv) => $pv !== null && abs((float) $pv - $firstPv) < 0.01)
            );
    }

    // ── PDF export ───────────────────────────────────────────────────────

    public function test_the_pdf_export_carries_present_values_and_the_assumptions_behind_them(): void
    {
        Pdf::fake();

        $user = $this->admin();
        $project = $this->project($user);
        [$firstPv] = $this->expectedBenefitPvs();

        $this->actingAs($user)->get(route('admin.projects.export', $project->id));

        Pdf::assertRespondedWithPdf(function ($pdf) use ($firstPv) {
            $benefit = $pdf->viewData['project']->benefits->first();
            $settings = $pdf->viewData['settings'];

            return $pdf->viewName === 'pdf.projects.export'
                && abs((float) $benefit->pv_value - $firstPv) < 0.01
                && (int) $settings->base_year === self::BASE_YEAR
                && abs((float) $settings->discount_rate - self::DISCOUNT_RATE) < 0.01;
        });
    }

    public function test_the_exported_pdf_renders_without_error(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        // Renders the Blade template for real: a missing variable in the new
        // columns would be invisible to the faked-PDF test above.
        $html = view('pdf.projects.export', [
            'project' => $project->load(['benefits', 'costs']),
            'settings' => $project->valuation_settings,
            'activityGroups' => \App\Http\Controllers\Admin\CostController::ACTIVITY_GROUPS,
        ])->render();

        $this->assertStringContainsString('Total Manfaat (PV)', $html);
        $this->assertStringContainsString('Restorasi / Konstruksi', $html);
        $this->assertStringContainsString('tahun dasar '.self::BASE_YEAR, $html);
    }

    // ── Public pages ─────────────────────────────────────────────────────

    public function test_the_public_project_page_publishes_present_values(): void
    {
        $user = $this->admin();
        $project = $this->project($user);
        [$firstPv, $secondPv] = $this->expectedBenefitPvs();

        $this->get(route('public.project', $project->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/ProjectDetail')
                ->where('benefits.0.pv_value', fn ($pv) => abs((float) $pv - $firstPv) < 0.01)
                ->where('benefits.1.pv_value', fn ($pv) => abs((float) $pv - $secondPv) < 0.01)
                ->where('valuationSettings.base_year', self::BASE_YEAR)
                ->where('valuationSettings.discount_rate', fn ($r) => abs((float) $r - self::DISCOUNT_RATE) < 1e-9)
            );
    }

    public function test_the_public_dashboard_charts_benefits_on_a_present_value_basis(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $response = $this->get(route('public.dashboard'));
        // Aggregated by the query builder, so these arrive as plain objects.
        $categories = collect($response->viewData('page')['props']['benefits'])->keyBy('category');

        $expected = $this->expectedBenefitPvs();

        $this->assertEqualsWithDelta($expected[0], (float) $categories['direct_use']->total, 0.01);
        $this->assertEqualsWithDelta($expected[1], (float) $categories['non_use']->total, 0.01);

        // The whole chart adds up to the project's published TEV benefit side.
        $this->assertEqualsWithDelta((float) $project->total_benefits, array_sum($expected), 0.01);
    }
}
