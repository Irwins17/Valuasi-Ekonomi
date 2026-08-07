<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectCrudTest extends TestCase
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

    public function test_index_renders_paginated_projects(): void
    {
        $user = $this->admin();
        Project::create([
            'code' => 'PRJ-001', 'name' => 'Proyek A', 'location' => 'Jawa Barat',
            'status' => 'draft', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.projects.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Index')
                ->has('projects.data', 1)
            );
    }

    public function test_create_page_renders(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.projects.create'))
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Projects/Create'));
    }

    public function test_store_creates_project_as_draft(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->post(route('admin.projects.store'), [
            'code' => 'PRJ-100',
            'name' => 'Proyek Baru',
            'description' => 'Deskripsi',
            'location' => 'Bali',
            'latitude' => -8.4,
            'longitude' => 115.2,
        ])->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'code' => 'PRJ-100',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.projects.store'), [])
            ->assertSessionHasErrors(['code', 'name', 'location']);
    }

    public function test_show_renders_project_with_counts_and_paginated_benefits_costs(): void
    {
        $user = $this->admin();
        $project = Project::create([
            'code' => 'PRJ-002', 'name' => 'Proyek B', 'location' => 'Sumatera',
            'status' => 'in_progress', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.projects.show', $project->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Show')
                ->where('project.id', $project->id)
                ->where('project.eop_data_count', 0)
                ->where('project.tcm_data_count', 0)
                ->where('project.cvm_data_count', 0)
                ->has('benefits.data')
                ->has('costs.data')
            );
    }

    public function test_edit_page_renders(): void
    {
        $user = $this->admin();
        $project = Project::create([
            'code' => 'PRJ-003', 'name' => 'Proyek C', 'location' => 'Kalimantan',
            'status' => 'draft', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.projects.edit', $project->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Edit')
                ->where('project.id', $project->id)
            );
    }

    public function test_update_persists_changes(): void
    {
        $user = $this->admin();
        $project = Project::create([
            'code' => 'PRJ-004', 'name' => 'Proyek D', 'location' => 'Papua',
            'status' => 'draft', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)->put(route('admin.projects.update', $project->id), [
            'name' => 'Proyek D Updated',
            'location' => 'Papua Barat',
            'status' => 'in_progress',
        ])->assertRedirect(route('admin.projects.show', $project->id));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Proyek D Updated',
            'location' => 'Papua Barat',
            'status' => 'in_progress',
            'updated_by' => $user->id,
        ]);
    }

    public function test_calculate_tev_updates_project_totals(): void
    {
        $user = $this->admin();
        $project = Project::create([
            'code' => 'PRJ-005', 'name' => 'Proyek E', 'location' => 'Aceh',
            'status' => 'draft', 'created_by' => $user->id,
        ]);
        $project->benefits()->create([
            'category' => 'direct_use', 'subcategory' => 'production',
            'description' => 'Manfaat', 'value' => 2000, 'data_source' => 'manual',
        ]);
        $project->costs()->create([
            'category' => 'direct_cost', 'subcategory' => 'investment',
            'description' => 'Biaya', 'value' => 500,
        ]);

        $this->actingAs($user)
            ->post(route('admin.projects.calculateTEV', $project->id))
            ->assertRedirect();

        $project->refresh();
        $this->assertEquals(2000, (float) $project->total_benefits);
        $this->assertEquals(500, (float) $project->total_costs);
        $this->assertEquals(1500, (float) $project->tev);
        $this->assertEquals(4, (float) $project->bcr);
    }

    public function test_destroy_soft_deletes_project(): void
    {
        $user = $this->admin();
        $project = Project::create([
            'code' => 'PRJ-006', 'name' => 'Proyek F', 'location' => 'Riau',
            'status' => 'draft', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.projects.destroy', $project->id))
            ->assertRedirect(route('admin.projects.index'));

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    /**
     * A repeat delete — double click, a stale list still showing the row,
     * the browser back button, a second tab — used to hit findOrFail() and
     * answer a bare 404 page.
     */
    public function test_destroy_on_already_deleted_project_redirects_instead_of_404(): void
    {
        $user = $this->admin();
        $project = Project::create([
            'code' => 'PRJ-007', 'name' => 'Proyek G', 'location' => 'Papua',
            'status' => 'draft', 'created_by' => $user->id,
        ]);
        $project->delete();

        $this->actingAs($user)
            ->delete(route('admin.projects.destroy', $project->id))
            ->assertRedirect(route('admin.projects.index'))
            ->assertSessionHas('success');
    }

    public function test_destroy_on_unknown_project_redirects_instead_of_404(): void
    {
        $this->actingAs($this->admin())
            ->delete(route('admin.projects.destroy', 999999))
            ->assertRedirect(route('admin.projects.index'))
            ->assertSessionHas('error');
    }

    public function test_destroy_is_forbidden_for_non_admin(): void
    {
        $surveyorRole = Role::create(['name' => 'Surveyor', 'slug' => 'surveyor']);
        $surveyor = User::create([
            'name' => 'Surveyor Test',
            'email' => 'surveyor-test@valuasi.local',
            'password' => 'password123',
            'role_id' => $surveyorRole->id,
            'is_active' => true,
        ]);
        $project = Project::create([
            'code' => 'PRJ-008', 'name' => 'Proyek H', 'location' => 'Maluku',
            'status' => 'draft', 'created_by' => $surveyor->id,
        ]);

        $this->actingAs($surveyor)
            ->delete(route('admin.projects.destroy', $project->id))
            ->assertForbidden();

        $this->assertNotSoftDeleted('projects', ['id' => $project->id]);
    }
}
