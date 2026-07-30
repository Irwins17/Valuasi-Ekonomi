<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\LaravelPdf\Facades\Pdf;
use Tests\TestCase;

class ProjectExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_renders_correct_pdf_view_with_project_data(): void
    {
        Pdf::fake();

        $role = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        $user = User::create([
            'name' => 'Admin Test', 'email' => 'admin-test@valuasi.local',
            'password' => 'password123', 'role_id' => $role->id, 'is_active' => true,
        ]);
        $project = Project::create([
            'code' => 'PRJ-001', 'name' => 'Proyek Ekspor', 'location' => 'Jawa',
            'status' => 'published', 'created_by' => $user->id,
        ]);

        $this->actingAs($user)->get(route('admin.projects.export', $project->id));

        Pdf::assertRespondedWithPdf(function ($pdf) use ($project) {
            return $pdf->viewName === 'pdf.projects.export'
                && ($pdf->viewData['project']->id ?? null) === $project->id;
        });
    }
}
