<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\HpmData;
use App\Models\Project;
use App\Services\Valuation\HedonicPriceEstimator;
use App\Support\DataCollectionTypes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HpmController extends Controller
{
    private const PROPERTY_TYPES = ['Rumah tinggal', 'Apartemen', 'Ruko', 'Tanah kosong', 'Vila', 'Lainnya'];
    private const SCALES = ['Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'];

    public function index(Request $request, $projectId): Response
    {
        $project = Project::findOrFail($projectId);
        $envVariable = $request->query('env', 'air_quality_index');

        if (! isset(HedonicPriceEstimator::ENVIRONMENT_VARIABLES[$envVariable])) {
            $envVariable = 'air_quality_index';
        }

        return Inertia::render('Admin/Modules/Hpm/Index', [
            'project' => $project,
            'records' => HpmData::where('project_id', $project->id)
                ->latest('id')->paginate(10)->withQueryString(),
            'totals' => [
                'records' => HpmData::where('project_id', $project->id)->count(),
                'mean_price' => (float) HpmData::where('project_id', $project->id)->avg('transaction_price'),
            ],
            'estimation' => (new HedonicPriceEstimator)->estimate($project, $envVariable),
            'environmentVariables' => HedonicPriceEstimator::ENVIRONMENT_VARIABLES,
            'selectedEnv' => $envVariable,
            'minProperties' => HedonicPriceEstimator::MIN_PROPERTIES,
        ]);
    }

    public function create($projectId): Response
    {
        return $this->renderForm(Project::findOrFail($projectId), null);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        HpmData::create([
            ...$this->validateRecord($request, $project),
            'project_id' => $project->id,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.modules.hpm.index', $project->id)
            ->with('success', 'Data HPM berhasil ditambahkan');
    }

    public function edit($projectId, $hpmId): Response
    {
        $project = Project::findOrFail($projectId);

        return $this->renderForm($project, HpmData::where('project_id', $project->id)->findOrFail($hpmId));
    }

    public function update(Request $request, $projectId, $hpmId)
    {
        $project = Project::findOrFail($projectId);
        $record = HpmData::where('project_id', $project->id)->findOrFail($hpmId);

        $record->update($this->validateRecord($request, $project, $record->id));

        return redirect()->route('admin.modules.hpm.index', $project->id)
            ->with('success', 'Data HPM berhasil diperbarui');
    }

    public function destroy($projectId, $hpmId)
    {
        $project = Project::findOrFail($projectId);
        $record = HpmData::where('project_id', $project->id)->find($hpmId);

        if (! $record) {
            return redirect()->route('admin.modules.hpm.index', $project->id)
                ->with('success', 'Data memang sudah dihapus.');
        }

        $record->delete();

        return redirect()->route('admin.modules.hpm.index', $project->id)
            ->with('success', 'Data HPM berhasil dihapus');
    }

    private function renderForm(Project $project, ?HpmData $record): Response
    {
        return Inertia::render('Admin/Modules/Hpm/Form', [
            'project' => $project,
            'record' => $record,
            'propertyTypes' => self::PROPERTY_TYPES,
            'scales' => self::SCALES,
        ]);
    }

    private function validateRecord(Request $request, Project $project, ?int $ignoreId = null): array
    {
        $codeRule = Rule::unique('hpm_data')
            ->where(fn ($q) => $q->where('project_id', $project->id)->whereNull('deleted_at'));

        if ($ignoreId) {
            $codeRule = $codeRule->ignore($ignoreId);
        }

        return $request->validate([
            'property_code' => ['required', 'string', 'max:60', $codeRule],
            'transaction_price' => ['required', 'numeric', 'min:1'],
            'property_type' => ['required', 'string', 'max:120'],
            'location' => ['required', 'string', 'max:255'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:100'],
            'land_area' => ['nullable', 'numeric', 'min:0'],
            'building_area' => ['nullable', 'numeric', 'min:0'],
            'building_age' => ['nullable', 'integer', 'min:0', 'max:300'],
            'accessibility' => ['nullable', 'string', 'max:120'],
            'crime_rate' => ['nullable', 'string', 'max:120'],
            'school_quality' => ['nullable', 'string', 'max:120'],
            'air_quality_index' => ['nullable', 'numeric', 'min:0'],
            'pollutant_concentration' => ['nullable', 'string', 'max:120'],
            'noise_level' => ['nullable', 'numeric', 'min:0'],
            'distance_green_space' => ['nullable', 'numeric', 'min:0'],
            'delta_env_quality' => ['nullable', 'numeric'],
            'affected_units' => ['nullable', 'integer', 'min:0'],
            'data_source' => ['nullable', 'string', 'max:255'],
            'data_collection_type' => ['nullable', Rule::in(array_keys(DataCollectionTypes::TYPES))],
            'collection_method' => ['nullable', Rule::in(DataCollectionTypes::allMethodCodes())],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'property_code' => 'ID properti',
            'transaction_price' => 'harga transaksi',
        ]);
    }
}
