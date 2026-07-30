<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\Role;
use App\Models\TcmData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ModuleDataTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::create(['name' => 'Administrator', 'slug' => 'admin']);

        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@valuasi.local',
            'password' => 'password123',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    private function project(User $user): Project
    {
        return Project::create([
            'code' => 'PRJ-001', 'name' => 'Proyek Uji', 'location' => 'Jawa',
            'status' => 'draft', 'created_by' => $user->id,
        ]);
    }

    // ----- Benefits -----

    public function test_benefit_store_and_update(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.benefits.store', $project->id), [
            'category' => 'direct_use',
            'subcategory' => 'production',
            'description' => 'Manfaat produksi',
            'value' => 1000,
            'method_used' => 'EOP',
            'data_source' => 'eop',
        ])->assertRedirect(route('admin.projects.show', $project->id));

        $benefit = $project->benefits()->first();
        $this->assertNotNull($benefit);

        // update rules now include sample_size + calculation_notes (normalized to match store)
        $this->actingAs($user)->put(route('admin.benefits.update', [$project->id, $benefit->id]), [
            'category' => 'indirect_use',
            'subcategory' => 'tourism',
            'description' => 'Manfaat wisata',
            'value' => 2000,
            'method_used' => 'TCM',
            'data_source' => 'tcm',
            'sample_size' => 50,
            'calculation_notes' => 'catatan',
        ])->assertRedirect(route('admin.projects.show', $project->id));

        $this->assertDatabaseHas('benefits', ['id' => $benefit->id, 'value' => 2000, 'sample_size' => 50]);
    }

    // ----- Costs -----

    public function test_cost_store_and_update(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.costs.store', $project->id), [
            'category' => 'direct_cost',
            'subcategory' => 'investment',
            'description' => 'Biaya investasi',
            'value' => 500,
        ])->assertRedirect(route('admin.projects.show', $project->id));

        $cost = $project->costs()->first();

        $this->actingAs($user)->put(route('admin.costs.update', [$project->id, $cost->id]), [
            'category' => 'indirect_cost',
            'subcategory' => 'externality',
            'description' => 'Biaya eksternalitas',
            'value' => 700,
            'year_applied' => 2026,
            'calculation_notes' => 'catatan biaya',
        ])->assertRedirect(route('admin.projects.show', $project->id));

        $this->assertDatabaseHas('costs', ['id' => $cost->id, 'value' => 700, 'year_applied' => 2026]);
    }

    // ----- EOP -----

    public function test_eop_store_auto_computes_and_creates_benefit_when_positive(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.modules.eop.store', $project->id), [
            'commodity_name' => 'Padi',
            'production_before' => 100,
            'production_after' => 150,
            'unit' => 'Ton',
            'market_price' => 5000,
            'impact_type' => 'positive',
        ])->assertRedirect(route('admin.modules.eop.index', $project->id));

        $eop = $project->eopData()->first();
        $this->assertEquals(50, (float) $eop->production_change);
        $this->assertEquals(250000, (float) $eop->total_value);

        // Positive impact auto-creates a Benefit row (existing behavior, untouched per plan)
        $this->assertDatabaseHas('benefits', [
            'project_id' => $project->id,
            'description' => 'Produksi Padi',
            'value' => 250000,
        ]);
    }

    public function test_eop_negative_impact_does_not_create_cost(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.modules.eop.store', $project->id), [
            'commodity_name' => 'Ikan',
            'production_before' => 100,
            'production_after' => 60,
            'unit' => 'Ton',
            'market_price' => 3000,
            'impact_type' => 'negative',
        ])->assertRedirect();

        $this->assertDatabaseCount('costs', 0);
    }

    // ----- TCM -----

    public function test_tcm_store_auto_computes_totals(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.modules.tcm.store', $project->id), [
            'respondent_id' => 1,
            'distance' => 10,
            'transportation_cost' => 50000,
            'time_cost' => 10000,
            'visit_frequency' => 2,
        ])->assertRedirect(route('admin.modules.tcm.index', $project->id));

        $tcm = $project->tcmData()->first();
        $this->assertEquals(60000, (float) $tcm->total_travel_cost);
        $this->assertEquals(120000, (float) $tcm->consumer_surplus);
    }

    public function test_tcm_respondent_id_is_scoped_per_project_not_global(): void
    {
        $user = $this->admin();
        $projectA = $this->project($user);
        $projectB = Project::create([
            'code' => 'PRJ-002', 'name' => 'Proyek Lain', 'location' => 'Bali',
            'status' => 'draft', 'created_by' => $user->id,
        ]);

        TcmData::create([
            'project_id' => $projectA->id, 'respondent_id' => 1, 'distance' => 5,
            'transportation_cost' => 10000, 'time_cost' => 5000, 'visit_frequency' => 1,
            'recorded_by' => $user->id,
        ]);

        // Same respondent_id (1) on a DIFFERENT project must be allowed post-fix.
        $this->actingAs($user)->post(route('admin.modules.tcm.store', $projectB->id), [
            'respondent_id' => 1,
            'distance' => 8,
            'transportation_cost' => 20000,
            'time_cost' => 5000,
            'visit_frequency' => 1,
        ])->assertRedirect(route('admin.modules.tcm.index', $projectB->id));

        $this->assertDatabaseCount('tcm_data', 2);

        // But the SAME project must still reject a duplicate respondent_id.
        $this->actingAs($user)->post(route('admin.modules.tcm.store', $projectA->id), [
            'respondent_id' => 1,
            'distance' => 3,
            'transportation_cost' => 5000,
            'time_cost' => 2000,
            'visit_frequency' => 1,
        ])->assertSessionHasErrors('respondent_id');
    }

    public function test_tcm_index_stats_reflect_full_dataset_not_just_current_page(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        // Create 12 records so the paginator (10/page) only shows 10 on page 1.
        for ($i = 1; $i <= 12; $i++) {
            TcmData::create([
                'project_id' => $project->id, 'respondent_id' => $i, 'distance' => 10,
                'transportation_cost' => 10000, 'time_cost' => 10000, 'visit_frequency' => 1,
                'recorded_by' => $user->id,
            ]);
        }

        $this->actingAs($user)
            ->get(route('admin.modules.tcm.index', $project->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Modules/Tcm/Index')
                ->where('stats.total_respondents', 12)
            );
    }

    // ----- CVM -----

    public function test_cvm_store_willing_to_pay_requires_wtp(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.modules.cvm.store', $project->id), [
            'respondent_id' => 1,
            'willing_to_pay' => true,
            'wtp' => 25000,
        ])->assertRedirect(route('admin.modules.cvm.index', $project->id));

        $this->assertDatabaseHas('cvm_data', ['project_id' => $project->id, 'respondent_id' => 1, 'wtp' => 25000]);
    }

    public function test_cvm_store_unwilling_requires_reason(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)->post(route('admin.modules.cvm.store', $project->id), [
            'respondent_id' => 2,
            'willing_to_pay' => false,
        ])->assertSessionHasErrors('reason_if_unwilling');
    }

    public function test_cvm_respondent_id_is_scoped_per_project(): void
    {
        $user = $this->admin();
        $projectA = $this->project($user);
        $projectB = Project::create([
            'code' => 'PRJ-003', 'name' => 'Proyek Ketiga', 'location' => 'Sumatera',
            'status' => 'draft', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post(route('admin.modules.cvm.store', $projectA->id), [
            'respondent_id' => 5, 'willing_to_pay' => true, 'wtp' => 10000,
        ])->assertRedirect();

        $this->actingAs($user)->post(route('admin.modules.cvm.store', $projectB->id), [
            'respondent_id' => 5, 'willing_to_pay' => true, 'wtp' => 20000,
        ])->assertRedirect();

        $this->assertDatabaseCount('cvm_data', 2);
    }
}
