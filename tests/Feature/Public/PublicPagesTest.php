<?php

namespace Tests\Feature\Public;

use App\Models\Benefit;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    private function publishedProject(array $overrides = []): Project
    {
        $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrator']);
        $user = User::firstOrCreate(
            ['email' => 'creator@valuasi.local'],
            ['name' => 'Creator', 'password' => 'password123', 'role_id' => $role->id, 'is_active' => true]
        );

        return Project::create(array_merge([
            'code' => 'PRJ-001',
            'name' => 'Proyek Uji Coba',
            'description' => 'Deskripsi proyek uji coba.',
            'location' => 'Jawa Barat',
            'latitude' => -6.9,
            'longitude' => 107.6,
            'status' => 'published',
            'created_by' => $user->id,
            'tev' => 1_000_000_000,
            'total_benefits' => 1_500_000_000,
            'total_costs' => 500_000_000,
            'bcr' => 3,
        ], $overrides));
    }

    public function test_landing_page_renders_with_expected_props(): void
    {
        $this->publishedProject();

        $this->get(route('landing'))->assertInertia(fn (Assert $page) => $page
            ->component('Public/Landing')
            ->has('publishedProjects')
            ->has('totalTEV')
            ->has('totalBenefits')
            ->has('avgBCR')
            ->has('mapProjects', 1)
        );
    }

    public function test_glossary_page_renders(): void
    {
        $this->get(route('public.glossary'))->assertInertia(fn (Assert $page) => $page->component('Public/Glossary'));
    }

    public function test_public_dashboard_renders_with_paginated_projects(): void
    {
        $project = $this->publishedProject();
        Benefit::create([
            'project_id' => $project->id,
            'category' => 'direct_use',
            'subcategory' => 'production',
            'description' => 'Manfaat produksi',
            'value' => 1_500_000_000,
            'method_used' => 'EOP',
            'data_source' => 'eop',
        ]);

        $this->get(route('public.dashboard'))->assertInertia(fn (Assert $page) => $page
            ->component('Public/Dashboard')
            ->has('projects.data', 1)
            ->has('benefits')
            ->has('methodDistribution')
        );
    }

    public function test_published_project_detail_is_visible(): void
    {
        $project = $this->publishedProject();

        $this->get(route('public.project', $project->id))->assertInertia(fn (Assert $page) => $page
            ->component('Public/ProjectDetail')
            ->where('project.id', $project->id)
        );
    }

    public function test_unpublished_project_detail_returns_404(): void
    {
        $project = $this->publishedProject(['code' => 'PRJ-002', 'status' => 'draft']);

        $this->get(route('public.project', $project->id))->assertNotFound();
    }
}
