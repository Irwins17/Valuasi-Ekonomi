<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Exports\CvmDataExport;
use App\Http\Controllers\Controller;
use App\Imports\CvmDataImport;
use App\Models\CvmData;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class CvmController extends Controller
{
    public function index($projectId): Response
    {
        $project = Project::findOrFail($projectId);
        $cvmData = $project->cvmData()->paginate(10);

        $stats = [
            'total_respondents' => $project->cvmData()->count(),
            'willing_to_pay_count' => $project->cvmData()->where('willing_to_pay', true)->count(),
            'mean_wtp' => $project->cvmData()->avg('wtp'),
            'median_wtp' => $this->calculateMedian($project->cvmData()->pluck('wtp')->toArray()),
            'total_wtp' => $project->cvmData()->sum('wtp'),
        ];

        return Inertia::render('Admin/Modules/Cvm/Index', [
            'project' => $project,
            'cvmData' => $cvmData,
            'stats' => $stats,
        ]);
    }

    public function create($projectId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Modules/Cvm/Create', ['project' => $project]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'respondent_id' => ['required', 'integer', 'min:1', Rule::unique('cvm_data')->where('project_id', $projectId)],
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

    public function edit($projectId, $cvmId): Response
    {
        $project = Project::findOrFail($projectId);
        $cvmData = CvmData::findOrFail($cvmId);

        return Inertia::render('Admin/Modules/Cvm/Edit', [
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

    private function calculateMedian($array)
    {
        if (empty($array)) {
            return 0;
        }

        sort($array);
        $count = count($array);
        if ($count % 2 == 0) {
            return ($array[$count / 2 - 1] + $array[$count / 2]) / 2;
        }
        return $array[(int) floor($count / 2)];
    }

    public function destroy($projectId, $cvmId)
    {
        $cvmData = CvmData::findOrFail($cvmId);
        $cvmData->delete();

        return redirect()->route('admin.modules.cvm.index', $projectId)
            ->with('success', 'Data CVM berhasil dihapus');
    }

    public function export($projectId)
    {
        $project = Project::findOrFail($projectId);

        return Excel::download(new CvmDataExport($project->id), "cvm-{$project->code}.xlsx");
    }

    public function import(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:5120'],
        ]);

        $import = new CvmDataImport($project->id, auth()->id());
        Excel::import($import, $request->file('file'));

        if ($import->failures()) {
            $messages = collect($import->failures())
                ->map(fn ($f) => "Baris {$f->row()}: ".implode(', ', $f->errors()))
                ->implode(' | ');

            return back()->with('error', "Sebagian data CVM gagal diimpor — {$messages}");
        }

        return redirect()->route('admin.modules.cvm.index', $projectId)
            ->with('success', 'Data CVM berhasil diimpor');
    }
}
