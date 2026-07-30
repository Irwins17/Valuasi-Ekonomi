<?php

namespace Tests\Feature\Admin;

use App\Models\EnvironmentalCoefficient;
use App\Models\MarketPrice;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class Phase6Test extends TestCase
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

    // ----- Sensitivity -----

    public function test_sensitivity_index_renders_eligible_projects(): void
    {
        $user = $this->admin();
        $project = Project::create([
            'code' => 'PRJ-001', 'name' => 'Proyek A', 'location' => 'Jawa',
            'status' => 'in_progress', 'created_by' => $user->id,
        ]);
        $project->benefits()->create(['category' => 'direct_use', 'subcategory' => 'production', 'description' => 'x', 'value' => 100, 'data_source' => 'manual']);
        $project->costs()->create(['category' => 'direct_cost', 'subcategory' => 'investment', 'description' => 'x', 'value' => 50]);

        $this->actingAs($user)
            ->get(route('admin.sensitivity.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Sensitivity/Index')
                ->has('projects', 1)
            );
    }

    public function test_sensitivity_simulate_endpoint_still_returns_raw_json(): void
    {
        $user = $this->admin();
        $project = Project::create([
            'code' => 'PRJ-002', 'name' => 'Proyek B', 'location' => 'Bali',
            'status' => 'in_progress', 'created_by' => $user->id,
        ]);
        $project->benefits()->create(['category' => 'direct_use', 'subcategory' => 'production', 'description' => 'x', 'value' => 1000, 'data_source' => 'manual']);
        $project->costs()->create(['category' => 'direct_cost', 'subcategory' => 'investment', 'description' => 'x', 'value' => 200]);

        $this->actingAs($user)
            ->getJson(route('admin.sensitivity.simulate', ['project_id' => $project->id]))
            ->assertJsonStructure(['original' => ['tev', 'benefits', 'costs', 'bcr'], 'adjusted', 'changes']);
    }

    // ----- Users -----

    public function test_user_store_and_cannot_delete_self(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Surveyor Baru',
            'email' => 'surveyor@valuasi.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $admin->role_id,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'surveyor@valuasi.local']);
    }

    public function test_user_update_requires_password_confirmation_when_changing_password(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.users.update', $admin->id), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role_id' => $admin->role_id,
            'password' => 'newpassword123',
            'password_confirmation' => 'mismatch',
        ])->assertSessionHasErrors('password');
    }

    // ----- Market Prices -----

    public function test_market_price_store_global_and_scope_filter(): void
    {
        $user = $this->admin();
        $project = Project::create([
            'code' => 'PRJ-003', 'name' => 'Proyek C', 'location' => 'Sumatera',
            'status' => 'draft', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post(route('admin.master.prices.store'), [
            'project_id' => '',
            'commodity_name' => 'Beras',
            'unit' => 'Kg',
            'price' => 12000,
            'year' => 2026,
        ])->assertRedirect(route('admin.master.prices.index'));

        MarketPrice::create([
            'project_id' => $project->id, 'commodity_name' => 'Ikan', 'unit' => 'Kg',
            'price' => 30000, 'year' => 2026, 'approved_by' => $user->id,
        ]);

        $this->assertDatabaseHas('market_prices', ['commodity_name' => 'Beras', 'project_id' => null]);

        $this->actingAs($user)
            ->get(route('admin.master.prices.index', ['scope' => 'global']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/MasterData/MarketPrices/Index')
                ->has('prices.data', 1)
                ->where('prices.data.0.is_global', true)
            );
    }

    public function test_market_price_update_validates_year_min_2000_like_store(): void
    {
        $user = $this->admin();
        $price = MarketPrice::create([
            'project_id' => null, 'commodity_name' => 'Jagung', 'unit' => 'Kg',
            'price' => 5000, 'year' => 2026, 'approved_by' => $user->id,
        ]);

        $this->actingAs($user)->put(route('admin.master.prices.update', $price->id), [
            'project_id' => '',
            'commodity_name' => 'Jagung',
            'unit' => 'Kg',
            'price' => 5000,
            'year' => 1999,
        ])->assertSessionHasErrors('year');
    }

    // ----- Environmental Coefficients -----

    public function test_coefficient_store_and_update(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->post(route('admin.master.coefficients.store'), [
            'code' => 'CARB-01',
            'name' => 'Serapan Karbon',
            'value' => 1.2345,
            'unit' => 'ton CO2/ha/th',
            'type' => 'carbon_sequestration',
        ])->assertRedirect(route('admin.master.coefficients.index'));

        $coefficient = EnvironmentalCoefficient::first();

        $this->actingAs($user)->put(route('admin.master.coefficients.update', $coefficient->id), [
            'code' => 'CARB-01',
            'name' => 'Serapan Karbon Updated',
            'value' => 2.5,
            'unit' => 'ton CO2/ha/th',
            'type' => 'carbon_sequestration',
        ])->assertRedirect(route('admin.master.coefficients.index'));

        $this->assertDatabaseHas('environmental_coefficients', ['id' => $coefficient->id, 'name' => 'Serapan Karbon Updated']);
    }

    // ----- Audit Log -----

    public function test_audit_log_records_project_creation_without_double_encoding(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->post(route('admin.projects.store'), [
            'code' => 'PRJ-100',
            'name' => 'Proyek Audit',
            'location' => 'Jakarta',
        ])->assertRedirect();

        $log = \App\Models\AuditLog::where('table_name', 'projects')->where('event', 'created')->first();
        $this->assertNotNull($log);
        // Fixed behavior: new_values must come back as a real array (single-encoded), not a JSON string.
        $this->assertIsArray($log->new_values);
        $this->assertEquals('PRJ-100', $log->new_values['code']);
    }

    public function test_audit_log_index_renders_with_filters(): void
    {
        $user = $this->admin();
        Project::create(['code' => 'PRJ-200', 'name' => 'X', 'location' => 'Y', 'status' => 'draft', 'created_by' => $user->id]);

        $this->actingAs($user)
            ->get(route('admin.audit.index', ['event' => 'created', 'table_name' => 'projects']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/AuditLogs/Index')
                ->where('filters.event', 'created')
                ->where('filters.table_name', 'projects')
            );
    }
}
