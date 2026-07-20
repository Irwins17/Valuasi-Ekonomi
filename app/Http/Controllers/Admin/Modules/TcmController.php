<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\Project;
use App\Models\TcmData;
use Illuminate\Http\Request;

class TcmController extends Controller
{
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);
        $tcmData = $project->tcmData()->paginate(10);

        $stats = [
            'total_respondents' => $tcmData->count(),
            'avg_distance' => $tcmData->avg('distance'),
            'avg_surplus' => $tcmData->avg('consumer_surplus'),
            'total_surplus' => $tcmData->sum('consumer_surplus'),
        ];

        return view('admin.modules.tcm.index', [
            'project' => $project,
            'tcmData' => $tcmData,
            'stats' => $stats,
        ]);
    }

    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);

        return view('admin.modules.tcm.create', ['project' => $project]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'respondent_id' => ['required', 'integer', 'min:1', 'unique:tcm_data'],
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

    public function edit($projectId, $tcmId)
    {
        $project = Project::findOrFail($projectId);
        $tcmData = TcmData::findOrFail($tcmId);

        return view('admin.modules.tcm.edit', [
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

    public function calculateMeanSurplus($projectId)
    {
        $project = Project::findOrFail($projectId);
        $meanSurplus = $project->tcmData()->avg('consumer_surplus');
        $totalSurplus = $project->tcmData()->sum('consumer_surplus');

        return response()->json([
            'mean_surplus' => $meanSurplus,
            'total_surplus' => $totalSurplus,
        ]);
    }

    public function destroy($projectId, $tcmId)
    {
        $tcmData = TcmData::findOrFail($tcmId);
        $tcmData->delete();

        return redirect()->route('admin.modules.tcm.index', $projectId)
            ->with('success', 'Data TCM berhasil dihapus');
    }
}
