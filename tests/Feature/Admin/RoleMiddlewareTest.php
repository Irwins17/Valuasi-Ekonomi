<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $surveyor;
    private User $analyst;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        $roleSurveyor = Role::create(['name' => 'Surveyor', 'slug' => 'surveyor']);
        $roleAnalyst = Role::create(['name' => 'Analyst', 'slug' => 'analyst']);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@valuasi.local', 'password' => 'password123',
            'role_id' => $roleAdmin->id, 'is_active' => true,
        ]);
        $this->surveyor = User::create([
            'name' => 'Surveyor', 'email' => 'surveyor@valuasi.local', 'password' => 'password123',
            'role_id' => $roleSurveyor->id, 'is_active' => true,
        ]);
        $this->analyst = User::create([
            'name' => 'Analyst', 'email' => 'analyst@valuasi.local', 'password' => 'password123',
            'role_id' => $roleAnalyst->id, 'is_active' => true,
        ]);

        $this->project = Project::create([
            'code' => 'PRJ-001', 'name' => 'Proyek Uji', 'location' => 'Jawa',
            'status' => 'draft', 'created_by' => $this->admin->id,
        ]);
    }

    public function test_dashboard_and_project_index_viewable_by_all_roles(): void
    {
        foreach ([$this->admin, $this->surveyor, $this->analyst] as $user) {
            $this->actingAs($user)->get(route('dashboard'))->assertOk();
            $this->actingAs($user)->get(route('admin.projects.index'))->assertOk();
            $this->actingAs($user)->get(route('admin.projects.show', $this->project->id))->assertOk();
        }
    }

    public function test_module_index_pages_viewable_by_all_roles(): void
    {
        foreach ([$this->admin, $this->surveyor, $this->analyst] as $user) {
            $this->actingAs($user)->get(route('admin.modules.eop.index', $this->project->id))->assertOk();
            $this->actingAs($user)->get(route('admin.modules.tcm.index', $this->project->id))->assertOk();
            $this->actingAs($user)->get(route('admin.modules.cvm.index', $this->project->id))->assertOk();
        }
    }

    public function test_project_management_is_admin_only(): void
    {
        $this->actingAs($this->admin)->get(route('admin.projects.create'))->assertOk();
        $this->actingAs($this->surveyor)->get(route('admin.projects.create'))->assertForbidden();
        $this->actingAs($this->analyst)->get(route('admin.projects.create'))->assertForbidden();
    }

    public function test_benefits_and_costs_management_is_admin_only(): void
    {
        $this->actingAs($this->admin)->get(route('admin.benefits.create', $this->project->id))->assertOk();
        $this->actingAs($this->surveyor)->get(route('admin.benefits.create', $this->project->id))->assertForbidden();
        $this->actingAs($this->analyst)->get(route('admin.benefits.create', $this->project->id))->assertForbidden();

        $this->actingAs($this->admin)->get(route('admin.costs.create', $this->project->id))->assertOk();
        $this->actingAs($this->surveyor)->get(route('admin.costs.create', $this->project->id))->assertForbidden();
        $this->actingAs($this->analyst)->get(route('admin.costs.create', $this->project->id))->assertForbidden();
    }

    public function test_eop_tcm_cvm_data_entry_allows_admin_and_surveyor_not_analyst(): void
    {
        $this->actingAs($this->admin)->get(route('admin.modules.eop.create', $this->project->id))->assertOk();
        $this->actingAs($this->surveyor)->get(route('admin.modules.eop.create', $this->project->id))->assertOk();
        $this->actingAs($this->analyst)->get(route('admin.modules.eop.create', $this->project->id))->assertForbidden();

        $this->actingAs($this->admin)->get(route('admin.modules.tcm.create', $this->project->id))->assertOk();
        $this->actingAs($this->surveyor)->get(route('admin.modules.tcm.create', $this->project->id))->assertOk();
        $this->actingAs($this->analyst)->get(route('admin.modules.tcm.create', $this->project->id))->assertForbidden();

        $this->actingAs($this->admin)->get(route('admin.modules.cvm.create', $this->project->id))->assertOk();
        $this->actingAs($this->surveyor)->get(route('admin.modules.cvm.create', $this->project->id))->assertOk();
        $this->actingAs($this->analyst)->get(route('admin.modules.cvm.create', $this->project->id))->assertForbidden();
    }

    public function test_sensitivity_analysis_allows_admin_and_analyst_not_surveyor(): void
    {
        $this->actingAs($this->admin)->get(route('admin.sensitivity.index'))->assertOk();
        $this->actingAs($this->analyst)->get(route('admin.sensitivity.index'))->assertOk();
        $this->actingAs($this->surveyor)->get(route('admin.sensitivity.index'))->assertForbidden();
    }

    public function test_users_master_data_and_audit_log_are_admin_only(): void
    {
        foreach ([$this->surveyor, $this->analyst] as $user) {
            $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.master.prices.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.master.coefficients.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.audit.index'))->assertForbidden();
        }

        $this->actingAs($this->admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.master.prices.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.master.coefficients.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.audit.index'))->assertOk();
    }

    public function test_guest_is_redirected_to_login_not_403(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }
}
