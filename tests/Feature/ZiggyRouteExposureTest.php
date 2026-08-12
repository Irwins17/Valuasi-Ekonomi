<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * app.blade.php emits the Ziggy route table itself rather than using the
 * @routes directive, so that the ~21 KB route() helper ships inside a cached
 * bundle instead of inline on every page.
 */
class ZiggyRouteExposureTest extends TestCase
{
    use RefreshDatabase;

    private function ziggy(string $html): array
    {
        $this->assertMatchesRegularExpression('/window\.Ziggy = \{/', $html, 'Ziggy config missing from page');
        preg_match('/window\.Ziggy = (\{.*?\});<\/script>/s', $html, $m);

        return json_decode($m[1], true)['routes'] ?? [];
    }

    /**
     * Guests must receive the admin routes too. Inertia navigates by XHR, so
     * the <script> that sets window.Ziggy only runs on the first document
     * load — narrowing the table for guests stranded anyone who landed on
     * /login with a guest-only table, and every admin route() call after
     * signing in threw "route is not in the route list".
     */
    public function test_guests_receive_the_full_route_table_so_it_survives_login(): void
    {
        $routes = $this->ziggy($this->get(route('landing'))->getContent());

        $this->assertArrayHasKey('landing', $routes);
        $this->assertArrayHasKey('admin.projects.index', $routes);
        $this->assertArrayHasKey('admin.boundary.lookup', $routes);
    }

    public function test_authenticated_users_receive_the_full_route_table(): void
    {
        $role = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-ziggy@valuasi.local',
            'password' => 'password123',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $routes = $this->ziggy($this->actingAs($user)->get(route('dashboard'))->getContent());

        $this->assertArrayHasKey('admin.projects.index', $routes);
        $this->assertGreaterThan(50, count($routes));
    }

    /**
     * The route() helper now ships inside the JS bundle instead of being
     * inlined on every page — the whole point of the change. Guard against a
     * future edit reintroducing @routes, which would put ~21 KB back into
     * every response.
     */
    public function test_the_route_helper_is_not_inlined_into_the_page(): void
    {
        $html = $this->get(route('landing'))->getContent();

        $this->assertStringNotContainsString('self).route=', $html);

        // The size guard deliberately excludes the Ziggy route table. That
        // table grows every time a legitimate route is added, so measuring
        // the whole document turned this into a budget that had to be raised
        // on each new module — which is the opposite of a guard. What the
        // test actually protects against is the ~21 KB route() *helper*
        // coming back inline, and that lives outside the table.
        $withoutRouteTable = preg_replace('/window\.Ziggy = \{.*?\};/s', '', $html);

        $this->assertLessThan(
            16 * 1024,
            strlen($withoutRouteTable),
            'Landing HTML (excluding the Ziggy route table) has grown unexpectedly large'
        );
    }

    /**
     * The route table itself is expected to grow with the app, but not without
     * limit — it ships on every first page load. This keeps an eye on it
     * separately from the helper, so a runaway table is still visible.
     */
    public function test_the_ziggy_route_table_stays_within_a_reasonable_budget(): void
    {
        $html = $this->get(route('landing'))->getContent();
        preg_match('/window\.Ziggy = (\{.*?\});<\/script>/s', $html, $m);

        $this->assertLessThan(
            24 * 1024,
            strlen($m[1] ?? ''),
            'Ziggy route table has grown large enough to be worth trimming (consider scoping it per area)'
        );
    }
}
