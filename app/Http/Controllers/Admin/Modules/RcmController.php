<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\RcmData;
use App\Services\Valuation\EconomicValuationCalculator;
use App\Support\ValuationModuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RcmController extends Controller
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
        $rows = RcmData::where('project_id', $project->id);

        return Inertia::render('Admin/Modules/Rcm/Index', [
            'project' => $project,
            'records' => (clone $rows)->latest('id')->paginate(10)->withQueryString(),
            'totals' => [
                'records' => (clone $rows)->count(),
                'total' => (float) (clone $rows)->sum('total_value'),
                'annual' => (float) (clone $rows)->sum('annual_value'),
            ],
            'statuses' => self::STATUSES,
        ]);
    }

    public function create($projectId): Response
    {
        return Inertia::render('Admin/Modules/Rcm/Form', [
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

        RcmData::create([
            ...$validated,
            ...$this->derivedValues($validated),
            'project_id' => $project->id,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.modules.rcm.index', $project->id)
            ->with('success', 'Data Replacement Cost berhasil ditambahkan');
    }

    public function edit($projectId, $rcmId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Modules/Rcm/Form', [
            'project' => $project,
            'record' => RcmData::where('project_id', $project->id)->findOrFail($rcmId),
            'statuses' => self::STATUSES,
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
        ]);
    }

    public function update(Request $request, $projectId, $rcmId)
    {
        $project = Project::findOrFail($projectId);
        $record = RcmData::where('project_id', $project->id)->findOrFail($rcmId);
        $validated = $this->validateRecord($request, $project, $record->id);

        $record->update([...$validated, ...$this->derivedValues($validated)]);

        return redirect()->route('admin.modules.rcm.index', $project->id)
            ->with('success', 'Data Replacement Cost berhasil diperbarui');
    }

    public function destroy($projectId, $rcmId)
    {
        $project = Project::findOrFail($projectId);
        $record = RcmData::where('project_id', $project->id)->find($rcmId);

        if (! $record) {
            return redirect()->route('admin.modules.rcm.index', $project->id)
                ->with('success', 'Data memang sudah dihapus.');
        }

        $record->delete();

        return redirect()->route('admin.modules.rcm.index', $project->id)
            ->with('success', 'Data Replacement Cost berhasil dihapus');
    }

    private function derivedValues(array $validated): array
    {
        return $this->calculator->rcmRecordValues(
            $validated['quantity'] ?? 0,
            $validated['replacement_cost_per_unit'] ?? 0,
            $validated['useful_life_years'] ?? null,
        );
    }

    private function validateRecord(Request $request, Project $project, ?int $ignoreId = null): array
    {
        $codeRule = Rule::unique('rcm_data')
            ->where(fn ($q) => $q->where('project_id', $project->id)->whereNull('deleted_at'));

        if ($ignoreId) {
            $codeRule = $codeRule->ignore($ignoreId);
        }

        return $request->validate([
            'record_code' => ['required', 'string', 'max:60', $codeRule],
            'service_category' => ['required', Rule::in(array_keys(ValuationModuleCatalog::SERVICE_CATEGORIES))],
            'asset_type' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:60'],
            'replacement_cost_per_unit' => ['required', 'numeric', 'min:0'],
            'useful_life_years' => ['nullable', 'integer', 'min:1', 'max:200'],
            'period_year' => ['required', 'integer', 'min:1900', 'max:2200'],
            'data_source' => ['required', 'string', 'max:255'],
            'data_status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'record_code' => 'ID Data',
            'asset_type' => 'jenis aset yang digantikan',
            'quantity' => 'volume/luas',
            'replacement_cost_per_unit' => 'biaya penggantian per unit',
        ]);
    }
}
