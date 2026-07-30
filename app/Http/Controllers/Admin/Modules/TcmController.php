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

        $validated = $request->validate([
            'respondent_id' => ['required', 'integer', 'min:1', Rule::unique('tcm_data')->where('project_id', $projectId)],
            'distance' => ['required', 'numeric', 'min:0'],
            'transportation_cost' => ['required', 'numeric', 'min:0'],
            'time_cost' => ['required', 'numeric', 'min:0'],
            'visit_frequency' => ['required', 'integer', 'min:1'],
            'origin_location' => ['nullable', 'string'],
            'respondent_category' => ['nullable', 'string'],
        ]);

        $tcmData = TcmData::create([
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

        $validated = $request->validate([
            'distance' => ['required', 'numeric', 'min:0'],
            'transportation_cost' => ['required', 'numeric', 'min:0'],
            'time_cost' => ['required', 'numeric', 'min:0'],
            'visit_frequency' => ['required', 'integer', 'min:1'],
            'origin_location' => ['nullable', 'string'],
            'respondent_category' => ['nullable', 'string'],
        ]);

        $tcmData->update($validated);

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
