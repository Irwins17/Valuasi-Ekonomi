<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\ProjectValuationSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectValuationSettingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::create(['name' => 'Administrator', 'slug' => 'admin']);

        return User::create([
            'name' => 'Admin PV', 'email' => 'admin-pv@valuasi.local',
            'password' => 'password123', 'role_id' => $role->id, 'is_active' => true,
        ]);
    }

    private function project(User $user): Project
    {
        return Project::create([
            'code' => 'PRJ-PV', 'name' => 'Proyek PV', 'location' => 'Jawa',
            'status' => 'draft', 'created_by' => $user->id,
        ]);
    }

    public function test_a_project_without_settings_reports_usable_defaults(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $settings = $project->valuation_settings;

        $this->assertInstanceOf(ProjectValuationSetting::class, $settings);
        $this->assertEquals(6.00, (float) $settings->discount_rate);
        $this->assertEquals(10, $settings->analysis_period);
        $this->assertSame('IDR', $settings->currency);
        $this->assertSame('net', $settings->eop_value_basis);
        $this->assertFalse($settings->exists, 'the default stand-in must not be persisted');
    }

    public function test_the_detail_page_exposes_the_valuation_settings(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->get(route('admin.projects.show', $project->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Show')
                // Compared numerically: JSON serialises 6.0 as `6`, which a
                // strict check would read as an int and reject.
                ->where('valuationSettings.discount_rate', fn ($rate) => abs((float) $rate - 6.0) < 1e-9)
                ->where('valuationSettings.base_year', fn ($year) => (int) $year > 0)
                ->where('valuationSettings.is_configured', false)
                ->has('eopValueBases')
            );
    }

    public function test_settings_can_be_saved_and_are_marked_configured(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->put(route('admin.projects.settings.update', $project->id), [
            'base_year' => 2026,
            'discount_rate' => 8.5,
            'analysis_period' => 20,
            'currency' => 'IDR',
            'start_year' => 2026,
            'end_year' => 2045,
            'eop_value_basis' => 'gross',
        ])->assertRedirect();

        $this->assertDatabaseHas('project_valuation_settings', [
            'project_id' => $project->id,
            'discount_rate' => 8.50,
            'analysis_period' => 20,
            'eop_value_basis' => 'gross',
        ]);

        $this->actingAs($user)->get(route('admin.projects.show', $project->id))
            ->assertInertia(fn (Assert $page) => $page->where('valuationSettings.is_configured', true));
    }

    public function test_saving_settings_recomputes_the_project_totals(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        // 1,100 due next year; at 10% that is exactly 1,000 today.
        $project->benefits()->create([
            'category' => 'direct_use', 'subcategory' => 'production',
            'description' => 'Manfaat', 'value' => 1100, 'period_year' => 2027,
            'data_source' => 'manual',
        ]);

        $this->actingAs($user)->put(route('admin.projects.settings.update', $project->id), [
            'base_year' => 2026, 'discount_rate' => 10, 'analysis_period' => 10,
            'currency' => 'IDR', 'eop_value_basis' => 'net',
        ])->assertRedirect();

        $project->refresh();
        $this->assertEqualsWithDelta(1000.0, (float) $project->total_benefits, 0.01);
        $this->assertEqualsWithDelta(1000.0, (float) $project->tev, 0.01);
        // No costs recorded, so the ratio is undefined rather than zero.
        $this->assertNull($project->bcr);
    }

    public function test_present_values_are_stored_per_row_for_traceability(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $benefit = $project->benefits()->create([
            'category' => 'direct_use', 'subcategory' => 'production',
            'description' => 'Manfaat', 'value' => 1210, 'period_year' => 2028,
            'data_source' => 'manual',
        ]);
        $cost = $project->costs()->create([
            'category' => 'direct_cost', 'subcategory' => 'investment',
            'description' => 'Biaya', 'value' => 1100, 'year_applied' => 2027,
        ]);

        $this->actingAs($user)->put(route('admin.projects.settings.update', $project->id), [
            'base_year' => 2026, 'discount_rate' => 10, 'analysis_period' => 10,
            'currency' => 'IDR', 'eop_value_basis' => 'net',
        ])->assertRedirect();

        $this->assertEqualsWithDelta(1000.0, (float) $benefit->fresh()->pv_value, 0.01);
        $this->assertEqualsWithDelta(1000.0, (float) $cost->fresh()->pv_value, 0.01);

        $project->refresh();
        $this->assertEqualsWithDelta(0.0, (float) $project->tev, 0.01);
        $this->assertEqualsWithDelta(1.0, (float) $project->bcr, 0.0001);
    }

    public function test_undated_amounts_keep_their_face_value_so_old_projects_are_unchanged(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $project->benefits()->create([
            'category' => 'direct_use', 'subcategory' => 'production',
            'description' => 'Manfaat', 'value' => 2000, 'data_source' => 'manual',
        ]);
        $project->costs()->create([
            'category' => 'direct_cost', 'subcategory' => 'investment',
            'description' => 'Biaya', 'value' => 500,
        ]);

        // A steep rate must change nothing while no row carries a year.
        $this->actingAs($user)->put(route('admin.projects.settings.update', $project->id), [
            'base_year' => 2026, 'discount_rate' => 25, 'analysis_period' => 10,
            'currency' => 'IDR', 'eop_value_basis' => 'net',
        ])->assertRedirect();

        $project->refresh();
        $this->assertEquals(2000, (float) $project->total_benefits);
        $this->assertEquals(500, (float) $project->total_costs);
        $this->assertEquals(1500, (float) $project->tev);
        $this->assertEquals(4, (float) $project->bcr);
    }

    public function test_an_impossible_discount_rate_is_rejected(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->put(route('admin.projects.settings.update', $project->id), [
            'base_year' => 2026, 'discount_rate' => -100, 'analysis_period' => 10,
            'currency' => 'IDR', 'eop_value_basis' => 'net',
        ])->assertSessionHasErrors('discount_rate');
    }

    public function test_an_end_year_before_the_start_year_is_rejected(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->put(route('admin.projects.settings.update', $project->id), [
            'base_year' => 2026, 'discount_rate' => 6, 'analysis_period' => 10,
            'currency' => 'IDR', 'start_year' => 2030, 'end_year' => 2027,
            'eop_value_basis' => 'net',
        ])->assertSessionHasErrors('end_year');
    }

    public function test_non_admins_cannot_change_the_assumptions(): void
    {
        $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        $surveyorRole = Role::create(['name' => 'Surveyor', 'slug' => 'surveyor']);

        $admin = User::create([
            'name' => 'A', 'email' => 'a-pv@valuasi.local', 'password' => 'password123',
            'role_id' => $adminRole->id, 'is_active' => true,
        ]);
        $surveyor = User::create([
            'name' => 'S', 'email' => 's-pv@valuasi.local', 'password' => 'password123',
            'role_id' => $surveyorRole->id, 'is_active' => true,
        ]);
        $project = $this->project($admin);

        $this->actingAs($surveyor)->put(route('admin.projects.settings.update', $project->id), [
            'base_year' => 2026, 'discount_rate' => 99, 'analysis_period' => 10,
            'currency' => 'IDR', 'eop_value_basis' => 'net',
        ])->assertForbidden();

        $this->assertDatabaseCount('project_valuation_settings', 0);
    }
}
