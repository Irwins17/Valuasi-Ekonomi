<?php

namespace Tests\Feature\Admin;

use App\Models\AdministrativeBoundary;
use App\Models\Role;
use App\Models\User;
use App\Support\BoundaryGeometryStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoundaryLookupTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/boundary-lookup-'.uniqid();
        config(['boundaries.geometry_path' => $this->dir]);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->dir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($this->dir);
        }

        parent::tearDown();
    }

    private function admin(): User
    {
        $role = Role::create(['name' => 'Administrator', 'slug' => 'admin']);

        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin-boundary@valuasi.local',
            'password' => 'password123',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    private function kendal(): AdministrativeBoundary
    {
        return AdministrativeBoundary::create([
            'level' => AdministrativeBoundary::LEVEL_REGENCY,
            'code' => '3324',
            'name' => 'Kendal',
            'parent_code' => '33',
            'province_name' => 'Jawa Tengah',
            'min_lat' => -7.2, 'min_lng' => 109.9,
            'max_lat' => -6.8, 'max_lng' => 110.4,
        ]);
    }

    public function test_it_returns_the_polygon_from_the_geometry_store(): void
    {
        $this->kendal();
        BoundaryGeometryStore::put(2, '3324', [
            'type' => 'Polygon',
            'coordinates' => [[[109.9, -7.2], [110.4, -7.2], [110.4, -6.8], [109.9, -7.2]]],
        ]);

        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.boundary.lookup', ['level' => 2, 'code' => '3324']))
            ->assertOk()
            ->assertJson([
                'found' => true,
                'displayName' => 'Kendal',
                'boundary' => [
                    'type' => 'FeatureCollection',
                    'features' => [[
                        'type' => 'Feature',
                        'properties' => ['name' => 'Kendal'],
                        'geometry' => ['type' => 'Polygon'],
                    ]],
                ],
            ]);

        $this->assertCount(4, $response->json('boundary.features.0.geometry.coordinates.0'));

        // Centre is the bounding-box midpoint, used to zoom the map. Compared
        // with a delta because JSON gives back -7 as an int, not -7.0.
        $this->assertEqualsWithDelta(-7.0, $response->json('center.lat'), 1e-9);
        $this->assertEqualsWithDelta(110.15, $response->json('center.lng'), 1e-9);
    }

    public function test_it_reports_not_found_when_the_code_is_unknown(): void
    {
        $this->actingAs($this->admin())
            ->getJson(route('admin.boundary.lookup', ['level' => 2, 'code' => '9999']))
            ->assertOk()
            ->assertExactJson(['found' => false]);
    }

    /**
     * Metadata can outlive its geometry file when boundaries:import is run
     * for only some levels. The picker treats this the same as a miss, so the
     * endpoint must not 500.
     */
    public function test_it_reports_not_found_when_the_geometry_file_is_missing(): void
    {
        $this->kendal();

        $this->actingAs($this->admin())
            ->getJson(route('admin.boundary.lookup', ['level' => 2, 'code' => '3324']))
            ->assertOk()
            ->assertExactJson(['found' => false]);
    }

    public function test_it_rejects_an_unsupported_level(): void
    {
        $this->actingAs($this->admin())
            ->getJson(route('admin.boundary.lookup', ['level' => 9, 'code' => '3324']))
            ->assertStatus(422);
    }
}
