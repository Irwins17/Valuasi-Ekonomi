<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\RawJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * An uploaded survey-area polygon is the one prop on these pages that can run
 * to tens of thousands of nested coordinate arrays. Inertia's prop resolver
 * walks every nested array looking for lazy props, so a boundary handed over
 * as a plain array costs seconds of request time for nodes that can never be
 * a lazy prop. These tests pin the two things that keep that in check: the
 * boundary is wrapped (invisibly, on the wire) and pages that draw no map do
 * not ship it at all.
 */
class ProjectBoundaryPayloadTest extends TestCase
{
    use RefreshDatabase;

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

    private function boundary(): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => ['NAMA' => 'Area Uji'],
                'geometry' => [
                    'type' => 'Polygon',
                    // Deliberately no whole numbers: those survive the JSON column as ints
                    // and would make an equality assertion fail for the wrong reason.
                    'coordinates' => [[[98.9, 2.6], [99.1, 2.6], [99.1, 2.7], [98.9, 2.7], [98.9, 2.6]]],
                ],
            ]],
        ];
    }

    private function project(User $user): Project
    {
        return Project::create([
            'code' => 'PRJ-BND', 'name' => 'Proyek Batas', 'location' => 'Sumatera Utara',
            'status' => 'published', 'created_by' => $user->id,
            'boundary_geojson' => $this->boundary(),
        ]);
    }

    public function test_edit_page_ships_the_boundary_unchanged(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)
            ->get(route('admin.projects.edit', $project))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Edit')
                ->where('project.boundary_geojson', $this->boundary())
            );
    }

    public function test_public_detail_page_ships_the_boundary_unchanged(): void
    {
        $project = $this->project($this->admin());

        $this->get(route('public.project', $project))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/ProjectDetail')
                ->where('project.boundary_geojson', $this->boundary())
            );
    }

    public function test_detail_page_omits_the_boundary_it_never_draws(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $this->actingAs($user)
            ->get(route('admin.projects.show', $project))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Show')
                ->has('project.name')
                ->missing('project.boundary_geojson')
            );
    }

    public function test_wrapped_boundary_is_hidden_from_the_prop_resolver(): void
    {
        $project = $this->project($this->admin());

        $wrapped = $project->toInertiaArray()['boundary_geojson'];

        // Not an array — this is what stops the resolver recursing per coordinate.
        $this->assertInstanceOf(RawJson::class, $wrapped);
        $this->assertFalse(is_array($wrapped));

        // ...and identical once encoded, so the page component sees no difference.
        $this->assertSame(json_encode($this->boundary()), json_encode($wrapped));
    }

    public function test_a_project_without_a_boundary_still_reports_null(): void
    {
        $user = $this->admin();
        $project = Project::create([
            'code' => 'PRJ-NOBND', 'name' => 'Tanpa Batas', 'location' => 'Bali',
            'status' => 'draft', 'created_by' => $user->id,
        ]);

        $this->assertNull($project->toInertiaArray()['boundary_geojson']);

        $this->actingAs($user)
            ->get(route('admin.projects.edit', $project))
            ->assertInertia(fn (Assert $page) => $page->where('project.boundary_geojson', null));
    }
}
