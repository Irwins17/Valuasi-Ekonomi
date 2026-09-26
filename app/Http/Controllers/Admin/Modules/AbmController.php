<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\AbmData;
use App\Models\Project;
use App\Services\Valuation\EconomicValuationCalculator;
use App\Support\DataCollectionTypes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AbmController extends Controller
{
    private const RISK_TYPES = ['Pencemaran air', 'Pencemaran udara', 'Pencemaran tanah', 'Kebisingan', 'Banjir/abrasi', 'Vektor penyakit', 'Lainnya'];
    private const DEFENSIVE_ACTIONS = ['Membeli air bersih/galon', 'Memasang filter air', 'Memasang air purifier', 'Membeli masker', 'Meninggikan rumah', 'Pengobatan preventif', 'Lainnya'];

    public function __construct(private readonly EconomicValuationCalculator $calculator) {}

    public function index($projectId): Response
    {
        $project = Project::findOrFail($projectId);
        $rows = AbmData::where('project_id', $project->id);

        return Inertia::render('Admin/Modules/Abm/Index', [
            'project' => $project,
            'records' => (clone $rows)->latest('id')->paginate(10)->withQueryString(),
            'totals' => [
                'records' => (clone $rows)->count(),
                'defensive' => (float) (clone $rows)->sum('defensive_expenditure'),
                'lost_income' => (float) (clone $rows)->sum('lost_income'),
                'total_avoidance' => (float) (clone $rows)->sum('total_avoidance'),
            ],
        ]);
    }

    public function create($projectId): Response
    {
        return $this->renderForm(Project::findOrFail($projectId), null);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $this->validateRecord($request, $project);

        AbmData::create([
            ...$validated,
            ...$this->derivedValues($validated),
            'project_id' => $project->id,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.modules.abm.index', $project->id)
            ->with('success', 'Data ABM berhasil ditambahkan');
    }

    public function edit($projectId, $abmId): Response
    {
        $project = Project::findOrFail($projectId);

        return $this->renderForm($project, AbmData::where('project_id', $project->id)->findOrFail($abmId));
    }

    public function update(Request $request, $projectId, $abmId)
    {
        $project = Project::findOrFail($projectId);
        $record = AbmData::where('project_id', $project->id)->findOrFail($abmId);
        $validated = $this->validateRecord($request, $project, $record->id);

        $record->update([...$validated, ...$this->derivedValues($validated)]);

        return redirect()->route('admin.modules.abm.index', $project->id)
            ->with('success', 'Data ABM berhasil diperbarui');
    }

    public function destroy($projectId, $abmId)
    {
        $project = Project::findOrFail($projectId);
        $record = AbmData::where('project_id', $project->id)->find($abmId);

        if (! $record) {
            return redirect()->route('admin.modules.abm.index', $project->id)
                ->with('success', 'Data memang sudah dihapus.');
        }

        $record->delete();

        return redirect()->route('admin.modules.abm.index', $project->id)
            ->with('success', 'Data ABM berhasil dihapus');
    }

    private function renderForm(Project $project, ?AbmData $record): Response
    {
        return Inertia::render('Admin/Modules/Abm/Form', [
            'project' => $project,
            'record' => $record,
            'riskTypes' => self::RISK_TYPES,
            'defensiveActions' => self::DEFENSIVE_ACTIONS,
        ]);
    }

    /** Defensive spend, lost income and total avoidance for one household. */
    private function derivedValues(array $validated): array
    {
        return $this->calculator->abmRecordValues(
            $validated['quantity'] ?? 0,
            $validated['unit_price'] ?? 0,
            $validated['time_cost'] ?? 0,
            $validated['medical_cost'] ?? 0,
            $validated['sick_days'] ?? 0,
            $validated['daily_wage'] ?? 0,
        );
    }

    private function validateRecord(Request $request, Project $project, ?int $ignoreId = null): array
    {
        $codeRule = Rule::unique('abm_data')
            ->where(fn ($q) => $q->where('project_id', $project->id)->whereNull('deleted_at'));

        if ($ignoreId) {
            $codeRule = $codeRule->ignore($ignoreId);
        }

        return $request->validate([
            'respondent_code' => ['required', 'string', 'max:60', $codeRule],
            'location' => ['required', 'string', 'max:255'],
            'risk_type' => ['required', 'string', 'max:120'],
            'exposure_condition' => ['nullable', 'string', 'max:255'],
            'defensive_action' => ['required', 'string', 'max:255'],
            'defensive_goods' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'time_cost' => ['nullable', 'numeric', 'min:0'],
            'medical_cost' => ['nullable', 'numeric', 'min:0'],
            'sick_days' => ['nullable', 'numeric', 'min:0'],
            'daily_wage' => ['nullable', 'numeric', 'min:0'],
            'household_size' => ['nullable', 'integer', 'min:1'],
            'affected_population' => ['nullable', 'integer', 'min:0'],
            'data_source' => ['nullable', 'string', 'max:255'],
            'data_collection_type' => ['nullable', Rule::in(array_keys(DataCollectionTypes::TYPES))],
            'collection_method' => ['nullable', Rule::in(DataCollectionTypes::allMethodCodes())],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'respondent_code' => 'ID responden',
            'defensive_action' => 'tindakan defensif',
        ]);
    }
}
