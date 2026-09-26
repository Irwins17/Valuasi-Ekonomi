<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\AdcData;
use App\Models\Project;
use App\Services\Valuation\EconomicValuationCalculator;
use App\Support\ValuationModuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdcController extends Controller
{
    private const STATUSES = [
        'draft' => 'Draft',
        'verified' => 'Terverifikasi',
        'final' => 'Final',
    ];

    public function __construct(private readonly EconomicValuationCalculator $calculator) {}

    public function index($projectId): Response
    {
        $project = Project::findOrFail($projectId);
        $rows = AdcData::where('project_id', $project->id);

        return Inertia::render('Admin/Modules/Adc/Index', [
            'project' => $project,
            'records' => (clone $rows)->latest('id')->paginate(10)->withQueryString(),
            'totals' => [
                'records' => (clone $rows)->count(),
                'avoided' => (float) (clone $rows)->sum('avoided_cost'),
            ],
            'statuses' => self::STATUSES,
        ]);
    }

    public function create($projectId): Response
    {
        return Inertia::render('Admin/Modules/Adc/Form', [
            'project' => Project::findOrFail($projectId),
            'record' => null,
            'statuses' => self::STATUSES,
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
        ]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $this->validateRecord($request, $project);

        AdcData::create([
            ...$validated,
            ...$this->derivedValues($validated),
            'project_id' => $project->id,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.modules.adc.index', $project->id)
            ->with('success', 'Data Avoided Damage Cost berhasil ditambahkan');
    }

    public function edit($projectId, $adcId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Modules/Adc/Form', [
            'project' => $project,
            'record' => AdcData::where('project_id', $project->id)->findOrFail($adcId),
            'statuses' => self::STATUSES,
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
        ]);
    }

    public function update(Request $request, $projectId, $adcId)
    {
        $project = Project::findOrFail($projectId);
        $record = AdcData::where('project_id', $project->id)->findOrFail($adcId);
        $validated = $this->validateRecord($request, $project, $record->id);

        $record->update([...$validated, ...$this->derivedValues($validated)]);

        return redirect()->route('admin.modules.adc.index', $project->id)
            ->with('success', 'Data Avoided Damage Cost berhasil diperbarui');
    }

    public function destroy($projectId, $adcId)
    {
        $project = Project::findOrFail($projectId);
        $record = AdcData::where('project_id', $project->id)->find($adcId);

        if (! $record) {
            return redirect()->route('admin.modules.adc.index', $project->id)
                ->with('success', 'Data memang sudah dihapus.');
        }

        $record->delete();

        return redirect()->route('admin.modules.adc.index', $project->id)
            ->with('success', 'Data Avoided Damage Cost berhasil dihapus');
    }

    private function derivedValues(array $validated): array
    {
        return $this->calculator->adcRecordValues(
            $validated['protected_area'] ?? 0,
            $validated['damage_cost_per_unit'] ?? 0,
            $validated['event_probability'] ?? 1,
        );
    }

    private function validateRecord(Request $request, Project $project, ?int $ignoreId = null): array
    {
        $codeRule = Rule::unique('adc_data')
            ->where(fn ($q) => $q->where('project_id', $project->id)->whereNull('deleted_at'));

        if ($ignoreId) {
            $codeRule = $codeRule->ignore($ignoreId);
        }

        return $request->validate([
            'record_code' => ['required', 'string', 'max:60', $codeRule],
            'service_category' => ['required', Rule::in(array_keys(ValuationModuleCatalog::SERVICE_CATEGORIES))],
            'damage_type' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'protected_area' => ['required', 'numeric', 'min:0'],
            'damage_cost_per_unit' => ['required', 'numeric', 'min:0'],
            'event_probability' => ['required', 'numeric', 'min:0', 'max:1'],
            'period_year' => ['required', 'integer', 'min:1900', 'max:2200'],
            'data_source' => ['required', 'string', 'max:255'],
            'data_status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'record_code' => 'ID Data',
            'damage_type' => 'jenis kerusakan potensial',
            'protected_area' => 'luas area terlindungi',
            'damage_cost_per_unit' => 'biaya kerusakan per unit',
            'event_probability' => 'probabilitas kejadian',
        ]);
    }
}
