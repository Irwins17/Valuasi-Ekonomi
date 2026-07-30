<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\Role;
use App\Models\TcmData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Storage::disk('local')->delete(['tmp-tcm-import.xlsx', 'tmp-cvm-import.xlsx']);

        parent::tearDown();
    }

    private function admin(): User
    {
        $role = Role::create(['name' => 'Administrator', 'slug' => 'admin']);

        return User::create([
            'name' => 'Admin Test', 'email' => 'admin-test@valuasi.local',
            'password' => 'password123', 'role_id' => $role->id, 'is_active' => true,
        ]);
    }

    private function project(User $user): Project
    {
        return Project::create([
            'code' => 'PRJ-001', 'name' => 'Proyek Uji', 'location' => 'Jawa',
            'status' => 'draft', 'created_by' => $user->id,
        ]);
    }

    private function makeXlsxUpload(array $headings, array $rows, string $filename): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headings, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $path = Storage::disk('local')->path("tmp-{$filename}");
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, $filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_tcm_export_returns_xlsx_download(): void
    {
        $user = $this->admin();
        $project = $this->project($user);
        TcmData::create([
            'project_id' => $project->id, 'respondent_id' => 1, 'distance' => 5,
            'transportation_cost' => 10000, 'time_cost' => 2000, 'visit_frequency' => 2,
            'recorded_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.modules.tcm.export', $project->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_tcm_import_creates_records_and_rejects_duplicate_respondent_in_same_project(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        TcmData::create([
            'project_id' => $project->id, 'respondent_id' => 1, 'distance' => 1,
            'transportation_cost' => 1000, 'time_cost' => 500, 'visit_frequency' => 1,
            'recorded_by' => $user->id,
        ]);

        $file = $this->makeXlsxUpload(
            ['respondent_id', 'distance', 'transportation_cost', 'time_cost', 'visit_frequency'],
            [
                [1, 5, 10000, 2000, 3], // duplicate respondent_id within this project -> should fail validation
                [2, 8, 15000, 3000, 1], // valid -> should import
            ],
            'tcm-import.xlsx'
        );

        // The controller uses back()->with('error', ...) when some rows fail validation.
        $this->actingAs($user)
            ->post(route('admin.modules.tcm.import', $project->id), ['file' => $file])
            ->assertSessionHas('error');

        // Row 2 (respondent_id=2) imported successfully.
        $this->assertDatabaseHas('tcm_data', ['project_id' => $project->id, 'respondent_id' => 2]);
        // Still only the original + the one valid new row -- the duplicate row was skipped, not overwritten.
        $this->assertDatabaseCount('tcm_data', 2);
    }

    public function test_cvm_export_returns_xlsx_download(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $response = $this->actingAs($user)->get(route('admin.modules.cvm.export', $project->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_cvm_import_creates_records(): void
    {
        $user = $this->admin();
        $project = $this->project($user);

        $file = $this->makeXlsxUpload(
            ['respondent_id', 'willing_to_pay', 'wtp'],
            [
                [10, 1, 25000],
                [11, 1, 30000],
            ],
            'cvm-import.xlsx'
        );

        $this->actingAs($user)
            ->post(route('admin.modules.cvm.import', $project->id), ['file' => $file])
            ->assertRedirect(route('admin.modules.cvm.index', $project->id));

        $this->assertDatabaseHas('cvm_data', ['project_id' => $project->id, 'respondent_id' => 10]);
        $this->assertDatabaseHas('cvm_data', ['project_id' => $project->id, 'respondent_id' => 11]);
    }
}
