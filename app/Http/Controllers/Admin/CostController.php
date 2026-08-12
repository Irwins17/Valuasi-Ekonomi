<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cost;
use App\Models\Project;
use App\Services\Valuation\EconomicValuationCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CostController extends Controller
{
    /**
     * Activity groups a project cost can belong to.
     *
     * These describe project/intervention spending — the costs a BCR is
     * measured against. Production costs already netted off inside a module
     * (EOP's Ci, for instance) do not belong here; recording them again would
     * charge the project twice for the same outlay.
     */
    public const ACTIVITY_GROUPS = [
        'survey' => 'Survey / Persiapan',
        'restoration' => 'Restorasi / Konstruksi',
        'monitoring' => 'Monitoring',
        'maintenance' => 'Pemeliharaan',
        'admin' => 'Admin / Operasional',
        'other' => 'Lainnya',
    ];

    public function __construct(private readonly EconomicValuationCalculator $calculator) {}

    public function create($projectId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Costs/Create', [
            'project' => $project,
            'cost' => null,
            ...$this->formOptions($project),
        ]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $this->validateCost($request);

        Cost::create([
            ...$validated,
            ...$this->derivedValues($project, $validated),
            'project_id' => $project->id,
            'calculated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.projects.show', $projectId)
            ->with('success', 'Cost berhasil ditambahkan');
    }

    public function edit($projectId, $costId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Costs/Edit', [
            'project' => $project,
            'cost' => Cost::where('project_id', $project->id)->findOrFail($costId),
            ...$this->formOptions($project),
        ]);
    }

    public function update(Request $request, $projectId, $costId)
    {
        $project = Project::findOrFail($projectId);
        $cost = Cost::where('project_id', $project->id)->findOrFail($costId);

        $validated = $this->validateCost($request);
        $cost->update([...$validated, ...$this->derivedValues($project, $validated)]);

        return redirect()->route('admin.projects.show', $projectId)
            ->with('success', 'Cost berhasil diperbarui');
    }

    public function destroy($projectId, $costId)
    {
        Cost::where('project_id', $projectId)->findOrFail($costId)->delete();

        return redirect()->route('admin.projects.show', $projectId)
            ->with('success', 'Cost berhasil dihapus');
    }

    /** Present value of this cost, on the project's own assumptions. */
    private function derivedValues(Project $project, array $validated): array
    {
        $settings = $project->valuation_settings;

        return [
            'pv_value' => $this->calculator->calculatePV(
                (float) $validated['value'],
                (int) ($validated['year_applied'] ?? $settings->base_year),
                (int) $settings->base_year,
                (float) $settings->discount_rate,
            ),
        ];
    }

    private function formOptions(Project $project): array
    {
        return [
            'activityGroups' => self::ACTIVITY_GROUPS,
            'valuationSettings' => [
                'base_year' => (int) $project->valuation_settings->base_year,
                'discount_rate' => (float) $project->valuation_settings->discount_rate,
            ],
        ];
    }

    private function validateCost(Request $request): array
    {
        return $request->validate([
            'category' => ['required', 'in:direct_cost,indirect_cost'],
            'subcategory' => ['required'],
            'activity_group' => ['nullable', Rule::in(array_keys(self::ACTIVITY_GROUPS))],
            'description' => ['required', 'string'],
            'value' => ['required', 'numeric', 'min:0'],
            'payment_type' => ['nullable', 'string'],
            'calculation_method' => ['nullable', 'string', 'max:255'],
            'responsible_party' => ['nullable', 'string', 'max:255'],
            'year_applied' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'data_status' => ['nullable', 'in:draft,verified'],
            'calculation_notes' => ['nullable', 'string'],
        ]);
    }
}
