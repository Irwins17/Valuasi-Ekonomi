<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectAssumption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** Uji Asumsi (flowchart Langkah 9) — documents and tests a project's key valuation assumptions. */
class ProjectAssumptionController extends Controller
{
    public function index($projectId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Assumptions/Index', [
            'project' => $project,
            'records' => ProjectAssumption::where('project_id', $project->id)->latest('id')->paginate(10)->withQueryString(),
            'statuses' => ProjectAssumption::STATUSES,
        ]);
    }

    public function create($projectId): Response
    {
        return Inertia::render('Admin/Assumptions/Form', [
            'project' => Project::findOrFail($projectId),
            'record' => null,
            'statuses' => ProjectAssumption::STATUSES,
        ]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $this->validateRecord($request);

        ProjectAssumption::create([
            ...$validated,
            'project_id' => $project->id,
            'tested_by' => auth()->id(),
        ]);

        return redirect()->route('admin.assumptions.index', $project->id)
            ->with('success', 'Asumsi berhasil ditambahkan');
    }

    public function edit($projectId, $id): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Assumptions/Form', [
            'project' => $project,
            'record' => ProjectAssumption::where('project_id', $project->id)->findOrFail($id),
            'statuses' => ProjectAssumption::STATUSES,
        ]);
    }

    public function update(Request $request, $projectId, $id)
    {
        $project = Project::findOrFail($projectId);
        $record = ProjectAssumption::where('project_id', $project->id)->findOrFail($id);
        $validated = $this->validateRecord($request);

        $record->update([...$validated, 'tested_by' => auth()->id()]);

        return redirect()->route('admin.assumptions.index', $project->id)
            ->with('success', 'Asumsi berhasil diperbarui');
    }

    public function destroy($projectId, $id)
    {
        $project = Project::findOrFail($projectId);
        $record = ProjectAssumption::where('project_id', $project->id)->find($id);

        if (! $record) {
            return redirect()->route('admin.assumptions.index', $project->id)
                ->with('success', 'Data memang sudah dihapus.');
        }

        $record->delete();

        return redirect()->route('admin.assumptions.index', $project->id)
            ->with('success', 'Asumsi berhasil dihapus');
    }

    private function validateRecord(Request $request): array
    {
        return $request->validate([
            'assumption_type' => ['required', 'string', 'max:255'],
            'assumed_value' => ['required', 'string', 'max:255'],
            'justification' => ['required', 'string'],
            'tested_result' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(ProjectAssumption::STATUSES))],
        ]);
    }
}
