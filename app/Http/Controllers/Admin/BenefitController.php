<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\Project;
use App\Services\Valuation\EconomicValuationCalculator;
use App\Services\Valuation\ModuleBenefitSources;
use App\Support\ValuationModuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BenefitController extends Controller
{
    public function __construct(
        private readonly EconomicValuationCalculator $calculator,
        private readonly ModuleBenefitSources $sources,
    ) {}

    public function create($projectId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Benefits/Create', [
            'project' => $project,
            'benefit' => null,
            ...$this->formOptions($project),
        ]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $this->validateBenefit($request, $project);

        Benefit::create([
            ...$validated,
            ...$this->derivedValues($project, $validated),
            'project_id' => $project->id,
            'calculated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.projects.show', $projectId)
            ->with('success', 'Benefit berhasil ditambahkan');
    }

    public function edit($projectId, $benefitId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Benefits/Edit', [
            'project' => $project,
            'benefit' => Benefit::where('project_id', $project->id)->findOrFail($benefitId),
            ...$this->formOptions($project),
        ]);
    }

    public function update(Request $request, $projectId, $benefitId)
    {
        $project = Project::findOrFail($projectId);
        $benefit = Benefit::where('project_id', $project->id)->findOrFail($benefitId);

        $validated = $this->validateBenefit($request, $project);
        $benefit->update([...$validated, ...$this->derivedValues($project, $validated)]);

        return redirect()->route('admin.projects.show', $projectId)
            ->with('success', 'Benefit berhasil diperbarui');
    }

    public function destroy($projectId, $benefitId)
    {
        Benefit::where('project_id', $projectId)->findOrFail($benefitId)->delete();

        return redirect()->route('admin.projects.show', $projectId)
            ->with('success', 'Benefit berhasil dihapus');
    }

    /**
     * Present value for this row, discounted with the project's own
     * assumptions so it never disagrees with the project totals.
     *
     * Recomputed on every write, which is what keeps pv_value honest when the
     * amount or the year is edited. A change to the project's discount rate
     * re-runs all rows through ProjectValuationSettingController instead.
     */
    private function derivedValues(Project $project, array $validated): array
    {
        $settings = $project->valuation_settings;

        return [
            'pv_value' => $this->calculator->calculatePV(
                (float) $validated['value'],
                (int) ($validated['period_year'] ?? $settings->base_year),
                (int) $settings->base_year,
                (float) $settings->discount_rate,
            ),
        ];
    }

    private function formOptions(Project $project): array
    {
        return [
            'moduleSources' => $this->sources->forProject($project),
            'moduleLabels' => ModuleBenefitSources::MODULES,
            'serviceGroups' => ValuationModuleCatalog::SERVICE_CATEGORIES,
            'valuationSettings' => [
                'base_year' => (int) $project->valuation_settings->base_year,
                'discount_rate' => (float) $project->valuation_settings->discount_rate,
                'eop_value_basis' => $project->valuation_settings->eop_value_basis,
            ],
        ];
    }

    private function validateBenefit(Request $request, Project $project): array
    {
        $validated = $request->validate([
            'category' => ['required', 'in:direct_use,indirect_use,non_use'],
            'subcategory' => ['required'],
            'ecosystem_service_group' => ['nullable', Rule::in(array_keys(ValuationModuleCatalog::SERVICE_CATEGORIES))],
            'description' => ['required', 'string'],
            'value' => ['required', 'numeric', 'min:0'],
            'annual_value' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:60'],
            'period_year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'method_used' => ['nullable', 'string'],
            'data_source' => ['required', 'in:eop,tcm,cvm,manual,literature'],
            'source_module' => ['nullable', Rule::in(array_keys(ModuleBenefitSources::MODULES))],
            // Required for every real module, but not for "manual" — that option
            // exists precisely to say "this figure has no module record behind
            // it", so demanding one would make it unselectable.
            'source_record_id' => ['nullable', 'integer', Rule::requiredIf(
                fn () => filled($request->input('source_module'))
                    && $request->input('source_module') !== 'manual'
            )],
            'sample_size' => ['nullable', 'integer'],
            'data_status' => ['nullable', 'in:draft,verified'],
            'calculation_notes' => ['nullable', 'string'],
        ], [
            'source_record_id.required' => 'Pilih record modul yang menjadi sumber nilai.',
        ]);

        // A source reference is only worth storing if it actually points at a
        // record in this project — otherwise the traceability it promises is
        // false, and worse, it could name another project's data.
        if (($validated['source_module'] ?? 'manual') !== 'manual' && ! empty($validated['source_record_id'])) {
            $record = $this->sources->find($project, $validated['source_module'], (int) $validated['source_record_id']);

            if ($record === null) {
                // A field error rather than an error page: the picker can put
                // this next to the dropdown that caused it, and the rest of a
                // half-filled form survives. Reached whenever the reference has
                // gone stale — the record deleted in another tab, or a payload
                // naming another project's data.
                throw ValidationException::withMessages([
                    'source_record_id' => 'Record modul sumber tidak ditemukan pada proyek ini.',
                ]);
            }
        }

        if (($validated['source_module'] ?? null) === 'manual') {
            $validated['source_record_id'] = null;
        }

        return $validated;
    }
}
