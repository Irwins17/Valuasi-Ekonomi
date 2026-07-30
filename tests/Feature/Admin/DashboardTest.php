<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_dashboard_with_expected_props(): void
    {
        $role = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@valuasi.local',
            'password' => 'password123',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->has('totalProjects')
                ->has('publishedProjects')
                ->has('inProgressProjects')
                ->has('totalEOPRecords')
                ->has('totalTCMRecords')
                ->has('totalCVMRecords')
                ->has('totalUsers')
                ->has('totalSurveyors')
                ->has('aggregatedTEV')
                ->has('lastProjects')
                ->has('monthlyStats')
            );
    }
}
