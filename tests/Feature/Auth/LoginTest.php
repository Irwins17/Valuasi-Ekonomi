<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_inertia_component(): void
    {
        $this->get(route('login'))
            ->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $role = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@valuasi.local',
            'password' => 'password123',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->post(route('login.post'), [
            'email' => 'admin-test@valuasi.local',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $role = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        User::create([
            'name' => 'Admin Test',
            'email' => 'admin-test@valuasi.local',
            'password' => 'password123',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->post(route('login.post'), [
            'email' => 'admin-test@valuasi.local',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_visiting_login_is_redirected_to_dashboard(): void
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
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }
}
