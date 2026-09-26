<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\BtmData;
use App\Models\Project;
use App\Services\Valuation\EconomicValuationCalculator;
use App\Support\ValuationModuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BtmController extends Controller
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
        $rows = BtmData::where('project_id', $project->id);

        return Inertia::render('Admin/Modules/Btm/Index', [
            'project' => $project,
            'records' => (clone $rows)->latest('id')->paginate(10)->withQueryString(),
            'totals' => [
                'records' => (clone $rows)->count(),
                'transferred' => (float) (clone $rows)->sum('transferred_value'),
            ],
            'statuses' => self::STATUSES,
        ]);
    }

    public function create($projectId): Response
    {
        return Inertia::render('Admin/Modules/Btm/Form', [
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

        BtmData::create([
            ...$validated,
            ...$this->derivedValues($validated),
            'project_id' => $project->id,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.modules.btm.index', $project->id)
            ->with('success', 'Data Benefit Transfer berhasil ditambahkan');
    }

    public function edit($projectId, $btmId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Modules/Btm/Form', [
            'project' => $project,
            'record' => BtmData::where('project_id', $project->id)->findOrFail($btmId),
            'statuses' => self::STATUSES,
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
        ]);
    }

    public function update(Request $request, $projectId, $btmId)
    {
        $project = Project::findOrFail($projectId);
        $record = BtmData::where('project_id', $project->id)->findOrFail($btmId);
        $validated = $this->validateRecord($request, $project, $record->id);

        $record->update([...$validated, ...$this->derivedValues($validated)]);

        return redirect()->route('admin.modules.btm.index', $project->id)
            ->with('success', 'Data Benefit Transfer berhasil diperbarui');
    }

    public function destroy($projectId, $btmId)
    {
        $project = Project::findOrFail($projectId);
        $record = BtmData::where('project_id', $project->id)->find($btmId);

        if (! $record) {
            return redirect()->route('admin.modules.btm.index', $project->id)
                ->with('success', 'Data memang sudah dihapus.');
        }

        $record->delete();

        return redirect()->route('admin.modules.btm.index', $project->id)
            ->with('success', 'Data Benefit Transfer berhasil dihapus');
    }

    private function derivedValues(array $validated): array
    {
        return $this->calculator->btmRecordValues(
            $validated['source_value'] ?? 0,
            $validated['adjustment_factor'] ?? 1,
            $validated['target_quantity'] ?? 1,
        );
    }

    private function validateRecord(Request $request, Project $project, ?int $ignoreId = null): array
    {
        $codeRule = Rule::unique('btm_data')
            ->where(fn ($q) => $q->where('project_id', $project->id)->whereNull('deleted_at'));

        if ($ignoreId) {
            $codeRule = $codeRule->ignore($ignoreId);
        }

        return $request->validate([
            'record_code' => ['required', 'string', 'max:60', $codeRule],
            'service_category' => ['required', Rule::in(array_keys(ValuationModuleCatalog::SERVICE_CATEGORIES))],
            'source_study_title' => ['required', 'string', 'max:255'],
            'source_study_location' => ['nullable', 'string', 'max:255'],
            'source_study_year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'source_value' => ['required', 'numeric', 'min:0'],
            'adjustment_factor' => ['required', 'numeric', 'min:0'],
            'target_quantity' => ['required', 'numeric', 'min:0'],
            'validity_notes' => ['nullable', 'string', 'max:500'],
            'period_year' => ['required', 'integer', 'min:1900', 'max:2200'],
            'data_source' => ['required', 'string', 'max:255'],
            'data_status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'record_code' => 'ID Data',
            'source_study_title' => 'judul studi sumber',
            'source_value' => 'nilai studi sumber',
            'adjustment_factor' => 'faktor penyesuaian',
            'target_quantity' => 'kuantitas target',
        ]);
    }
}
