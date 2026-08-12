<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Exports\TcmDataExport;
use App\Http\Controllers\Controller;
use App\Imports\TcmDataImport;
use App\Models\Project;
use App\Models\TcmData;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class TcmController extends Controller
{
    public function index($projectId): Response
    {
        $project = Project::findOrFail($projectId);
        $tcmData = $project->tcmData()->paginate(10);

        $stats = [
            'total_respondents' => $project->tcmData()->count(),
            'avg_distance' => $project->tcmData()->avg('distance'),
            'avg_surplus' => $project->tcmData()->avg('consumer_surplus'),
            'total_surplus' => $project->tcmData()->sum('consumer_surplus'),
        ];

        return Inertia::render('Admin/Modules/Tcm/Index', [
            'project' => $project,
            'tcmData' => $tcmData,
            'stats' => $stats,
        ]);
    }

    public function create($projectId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Modules/Tcm/Create', ['project' => $project]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $this->validateTcm($request, [
            'respondent_id' => ['required', 'integer', 'min:1', Rule::unique('tcm_data')->where('project_id', $projectId)],
        ]);

        TcmData::create([
            'project_id' => $projectId,
            'recorded_by' => auth()->id(),
            ...$validated,
        ]);

        return redirect()->route('admin.modules.tcm.index', $projectId)
            ->with('success', 'Data TCM berhasil ditambahkan');
    }

    public function edit($projectId, $tcmId): Response
    {
        $project = Project::findOrFail($projectId);
        $tcmData = TcmData::findOrFail($tcmId);

        return Inertia::render('Admin/Modules/Tcm/Edit', [
            'project' => $project,
            'tcmData' => $tcmData,
        ]);
    }

    public function update(Request $request, $projectId, $tcmId)
    {
        $tcmData = TcmData::findOrFail($tcmId);

        $tcmData->update($this->validateTcm($request));

        return redirect()->route('admin.modules.tcm.index', $projectId)
            ->with('success', 'Data TCM berhasil diperbarui');
    }

    public function destroy($projectId, $tcmId)
    {
        $tcmData = TcmData::findOrFail($tcmId);
        $tcmData->delete();

        return redirect()->route('admin.modules.tcm.index', $projectId)
            ->with('success', 'Data TCM berhasil dihapus');
    }

    /**
     * `time_cost` stays accepted for rows entered the old way; when the
     * hourly value of time and travel time are both supplied the model
     * derives it instead, per TC = transport + tiket + (nilai waktu × waktu).
     */
    private function validateTcm(Request $request, array $extra = []): array
    {
        return $request->validate([
            ...$extra,
            'distance' => ['required', 'numeric', 'min:0'],
            'transportation_cost' => ['required', 'numeric', 'min:0'],
            'ticket_cost' => ['nullable', 'numeric', 'min:0'],
            'time_cost' => ['nullable', 'numeric', 'min:0'],
            'travel_time_hours' => ['nullable', 'numeric', 'min:0'],
            'time_value_per_hour' => ['nullable', 'numeric', 'min:0'],
            'visit_frequency' => ['required', 'integer', 'min:1'],
            'origin_location' => ['nullable', 'string', 'max:255'],
            'respondent_category' => ['nullable', 'string', 'max:255'],
            'income' => ['nullable', 'numeric', 'min:0'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'education' => ['nullable', 'string', 'max:120'],
            'substitute_site' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'respondent_id' => 'ID responden',
            'transportation_cost' => 'biaya transport',
            'visit_frequency' => 'frekuensi kunjungan',
        ]);
    }

    public function export($projectId)
    {
        $project = Project::findOrFail($projectId);

        return Excel::download(new TcmDataExport($project->id), "tcm-{$project->code}.xlsx");
    }

    public function import(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:5120'],
        ]);

        $import = new TcmDataImport($project->id, auth()->id());
        Excel::import($import, $request->file('file'));

        if ($import->failures()) {
            $messages = collect($import->failures())
                ->map(fn ($f) => "Baris {$f->row()}: ".implode(', ', $f->errors()))
                ->implode(' | ');

            return back()->with('error', "Sebagian data TCM gagal diimpor — {$messages}");
        }

        return redirect()->route('admin.modules.tcm.index', $projectId)
            ->with('success', 'Data TCM berhasil diimpor');
    }
}
