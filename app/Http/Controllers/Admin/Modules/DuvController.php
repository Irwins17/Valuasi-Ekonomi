<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\DuvData;
use App\Models\Project;
use App\Services\Valuation\EconomicValuationCalculator;
use App\Support\DataCollectionTypes;
use App\Support\ValuationModuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DuvController extends Controller
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
        $rows = DuvData::where('project_id', $project->id);

        return Inertia::render('Admin/Modules/Duv/Index', [
            'project' => $project,
            'records' => (clone $rows)->latest('id')->paginate(10)->withQueryString(),
            'totals' => [
                'records' => (clone $rows)->count(),
                'gross' => (float) (clone $rows)->sum('gross_value'),
                'cost' => (float) (clone $rows)->sum('production_cost'),
                'net' => (float) (clone $rows)->sum('net_value'),
            ],
            'statuses' => self::STATUSES,
        ]);
    }

    public function create($projectId): Response
    {
        return Inertia::render('Admin/Modules/Duv/Form', [
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

        DuvData::create([
            ...$validated,
            ...$this->derivedValues($validated),
            'project_id' => $project->id,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.modules.duv.index', $project->id)
            ->with('success', 'Data DUV berhasil ditambahkan');
    }

    public function edit($projectId, $duvId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Modules/Duv/Form', [
            'project' => $project,
            'record' => DuvData::where('project_id', $project->id)->findOrFail($duvId),
            'statuses' => self::STATUSES,
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
        ]);
    }

    public function update(Request $request, $projectId, $duvId)
    {
        $project = Project::findOrFail($projectId);
        $record = DuvData::where('project_id', $project->id)->findOrFail($duvId);
        $validated = $this->validateRecord($request, $project, $record->id);

        $record->update([...$validated, ...$this->derivedValues($validated)]);

        return redirect()->route('admin.modules.duv.index', $project->id)
            ->with('success', 'Data DUV berhasil diperbarui');
    }

    public function destroy($projectId, $duvId)
    {
        $project = Project::findOrFail($projectId);
        $record = DuvData::where('project_id', $project->id)->find($duvId);

        if (! $record) {
            return redirect()->route('admin.modules.duv.index', $project->id)
                ->with('success', 'Data memang sudah dihapus.');
        }

        $record->delete();

        return redirect()->route('admin.modules.duv.index', $project->id)
            ->with('success', 'Data DUV berhasil dihapus');
    }

    /** Gross and net for one DUV row, from the single implementation. */
    private function derivedValues(array $validated): array
    {
        return $this->calculator->duvRecordValues(
            $validated['quantity'] ?? 0,
            $validated['market_price'] ?? 0,
            $validated['production_cost'] ?? 0,
        );
    }

    private function validateRecord(Request $request, Project $project, ?int $ignoreId = null): array
    {
        $codeRule = Rule::unique('duv_data')
            ->where(fn ($q) => $q->where('project_id', $project->id)->whereNull('deleted_at'));

        if ($ignoreId) {
            $codeRule = $codeRule->ignore($ignoreId);
        }

        return $request->validate([
            'record_code' => ['required', 'string', 'max:60', $codeRule],
            'service_category' => ['required', Rule::in(array_keys(ValuationModuleCatalog::SERVICE_CATEGORIES))],
            'goods_type' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:60'],
            'market_price' => ['required', 'numeric', 'min:0'],
            'production_cost' => ['nullable', 'numeric', 'min:0'],
            'period_year' => ['required', 'integer', 'min:1900', 'max:2200'],
            'data_source' => ['required', 'string', 'max:255'],
            'data_collection_type' => ['nullable', Rule::in(array_keys(DataCollectionTypes::TYPES))],
            'collection_method' => ['nullable', Rule::in(DataCollectionTypes::allMethodCodes())],
            'data_status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'record_code' => 'ID Data',
            'goods_type' => 'jenis barang/jasa',
            'quantity' => 'kuantitas',
            'market_price' => 'harga pasar',
        ]);
    }
}
