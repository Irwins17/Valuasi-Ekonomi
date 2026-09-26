<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StakeholderValidation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** Validasi Stakeholder (flowchart Langkah 9) — records stakeholder feedback on a project's valuation. */
class StakeholderValidationController extends Controller
{
    public function index($projectId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/StakeholderValidations/Index', [
            'project' => $project,
            'records' => StakeholderValidation::where('project_id', $project->id)->latest('validation_date')->paginate(10)->withQueryString(),
            'statuses' => StakeholderValidation::STATUSES,
        ]);
    }

    public function create($projectId): Response
    {
        return Inertia::render('Admin/StakeholderValidations/Form', [
            'project' => Project::findOrFail($projectId),
            'record' => null,
            'statuses' => StakeholderValidation::STATUSES,
        ]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $this->validateRecord($request);

        StakeholderValidation::create([
            ...$validated,
            'project_id' => $project->id,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.stakeholder-validations.index', $project->id)
            ->with('success', 'Validasi stakeholder berhasil ditambahkan');
    }

    public function edit($projectId, $id): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/StakeholderValidations/Form', [
            'project' => $project,
            'record' => StakeholderValidation::where('project_id', $project->id)->findOrFail($id),
            'statuses' => StakeholderValidation::STATUSES,
        ]);
    }

    public function update(Request $request, $projectId, $id)
    {
        $project = Project::findOrFail($projectId);
        $record = StakeholderValidation::where('project_id', $project->id)->findOrFail($id);
        $validated = $this->validateRecord($request);

        $record->update($validated);

        return redirect()->route('admin.stakeholder-validations.index', $project->id)
            ->with('success', 'Validasi stakeholder berhasil diperbarui');
    }

    public function destroy($projectId, $id)
    {
        $project = Project::findOrFail($projectId);
        $record = StakeholderValidation::where('project_id', $project->id)->find($id);

        if (! $record) {
            return redirect()->route('admin.stakeholder-validations.index', $project->id)
                ->with('success', 'Data memang sudah dihapus.');
        }

        $record->delete();

        return redirect()->route('admin.stakeholder-validations.index', $project->id)
            ->with('success', 'Validasi stakeholder berhasil dihapus');
    }

    private function validateRecord(Request $request): array
    {
        return $request->validate([
            'stakeholder_name' => ['required', 'string', 'max:255'],
            'stakeholder_role' => ['nullable', 'string', 'max:255'],
            'validation_date' => ['required', 'date'],
            'feedback' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(StakeholderValidation::STATUSES))],
        ]);
    }
}
