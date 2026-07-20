<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\CvmData;
use App\Models\Project;
use Illuminate\Http\Request;

class CvmController extends Controller
{
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);
        $cvmData = $project->cvmData()->paginate(10);

        $stats = [
            'total_respondents' => $cvmData->count(),
            'willing_to_pay_count' => $cvmData->where('willing_to_pay', true)->count(),
            'mean_wtp' => $cvmData->avg('wtp'),
            'median_wtp' => $this->calculateMedian($cvmData->pluck('wtp')->toArray()),
            'total_wtp' => $cvmData->sum('wtp'),
        ];

        return view('admin.modules.cvm.index', [
            'project' => $project,
            'cvmData' => $cvmData,
            'stats' => $stats,
        ]);
    }

    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);

        return view('admin.modules.cvm.create', ['project' => $project]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'respondent_id' => ['required', 'integer', 'min:1', 'unique:cvm_data'],
            'wtp' => ['required_if:willing_to_pay,true', 'nullable', 'numeric', 'min:0'],
            'household_size' => ['nullable', 'integer', 'min:1'],
            'household_income' => ['nullable', 'numeric', 'min:0'],
            'willing_to_pay' => ['required', 'boolean'],
            'reason_if_unwilling' => ['required_if:willing_to_pay,false', 'nullable', 'string'],
        ]);

        $cvmData = CvmData::create([
            'project_id' => $projectId,
            'recorded_by' => auth()->id(),
            ...$validated,
        ]);

        return redirect()->route('admin.modules.cvm.index', $projectId)
            ->with('success', 'Data CVM berhasil ditambahkan');
    }

    public function edit($projectId, $cvmId)
    {
        $project = Project::findOrFail($projectId);
        $cvmData = CvmData::findOrFail($cvmId);

        return view('admin.modules.cvm.edit', [
            'project' => $project,
            'cvmData' => $cvmData,
        ]);
    }

    public function update(Request $request, $projectId, $cvmId)
    {
        $cvmData = CvmData::findOrFail($cvmId);

        $validated = $request->validate([
            'wtp' => ['required_if:willing_to_pay,true', 'nullable', 'numeric', 'min:0'],
            'household_size' => ['nullable', 'integer', 'min:1'],
            'household_income' => ['nullable', 'numeric', 'min:0'],
            'willing_to_pay' => ['required', 'boolean'],
            'reason_if_unwilling' => ['required_if:willing_to_pay,false', 'nullable', 'string'],
        ]);

        $cvmData->update($validated);

        return redirect()->route('admin.modules.cvm.index', $projectId)
            ->with('success', 'Data CVM berhasil diperbarui');
    }

    public function calculateMeanWTP($projectId)
    {
        $project = Project::findOrFail($projectId);
        $cvmData = $project->cvmData()->pluck('wtp')->toArray();
        
        sort($cvmData);
        $mean = array_sum($cvmData) / count($cvmData);
        $median = $this->calculateMedian($cvmData);

        return response()->json([
            'mean_wtp' => $mean,
            'median_wtp' => $median,
            'count' => count($cvmData),
        ]);
    }

    private function calculateMedian($array)
    {
        sort($array);
        $count = count($array);
        if ($count % 2 == 0) {
            return ($array[$count / 2 - 1] + $array[$count / 2]) / 2;
        }
        return $array[floor($count / 2)];
    }

    public function destroy($projectId, $cvmId)
    {
        $cvmData = CvmData::findOrFail($cvmId);
        $cvmData->delete();

        return redirect()->route('admin.modules.cvm.index', $projectId)
            ->with('success', 'Data CVM berhasil dihapus');
    }
}
