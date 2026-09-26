<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\EcosystemServiceRecord;
use App\Models\Project;
use App\Services\Valuation\EconomicValuationCalculator;
use App\Support\DataCollectionTypes;
use App\Support\EcosystemServiceSchemas;
use App\Support\ValuationModuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Data entry for the six Tabel 1 ecosystem-service modules.
 *
 * One controller serves all of them: the `{service}` route segment selects a
 * schema from App\Support\EcosystemServiceSchemas, which supplies the field
 * list, the validation rules and the formula/preview panels. Adding a seventh
 * service means adding a schema, not another controller.
 */
class EcosystemServiceController extends Controller
{
    public function __construct(private readonly EconomicValuationCalculator $calculator) {}

    public function index($projectId, $service): Response
    {
        $project = Project::findOrFail($projectId);
        $schema = $this->schemaOrFail($service);

        $records = EcosystemServiceRecord::where('project_id', $project->id)
            ->service($service)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Modules/Ecosystem/Index', [
            'project' => $project,
            'schema' => EcosystemServiceSchemas::forFrontend($service),
            'records' => $records,
            'totals' => [
                'records' => EcosystemServiceRecord::where('project_id', $project->id)->service($service)->count(),
                'total_value' => (float) EcosystemServiceRecord::where('project_id', $project->id)->service($service)->sum('total_value'),
                'total_area' => (float) EcosystemServiceRecord::where('project_id', $project->id)->service($service)->sum('area_ha'),
            ],
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
        ]);
    }

    public function create($projectId, $service): Response
    {
        $project = Project::findOrFail($projectId);
        $this->schemaOrFail($service);

        return Inertia::render('Admin/Modules/Ecosystem/Form', [
            'project' => $project,
            'schema' => EcosystemServiceSchemas::forFrontend($service),
            'record' => null,
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
        ]);
    }

    public function store(Request $request, $projectId, $service)
    {
        $project = Project::findOrFail($projectId);
        $schema = $this->schemaOrFail($service);
        $validated = $this->validateRecord($request, $project, $service);

        EcosystemServiceRecord::create([
            ...$this->toAttributes($validated, $service),
            'project_id' => $project->id,
            'service_key' => $service,
            'service_category' => $schema['service_category'],
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.modules.ecosystem.index', [$project->id, $service])
            ->with('success', "Data {$schema['name']} berhasil ditambahkan");
    }

    public function edit($projectId, $service, $recordId): Response
    {
        $project = Project::findOrFail($projectId);
        $this->schemaOrFail($service);
        $record = $this->recordOrFail($project, $service, $recordId);

        return Inertia::render('Admin/Modules/Ecosystem/Form', [
            'project' => $project,
            'schema' => EcosystemServiceSchemas::forFrontend($service),
            'record' => $record,
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
        ]);
    }

    public function update(Request $request, $projectId, $service, $recordId)
    {
        $project = Project::findOrFail($projectId);
        $schema = $this->schemaOrFail($service);
        $record = $this->recordOrFail($project, $service, $recordId);

        $validated = $this->validateRecord($request, $project, $service, $record->id);
        $record->update($this->toAttributes($validated, $service));

        return redirect()->route('admin.modules.ecosystem.index', [$project->id, $service])
            ->with('success', "Data {$schema['name']} berhasil diperbarui");
    }

    public function destroy($projectId, $service, $recordId)
    {
        $project = Project::findOrFail($projectId);
        $schema = $this->schemaOrFail($service);
        $record = EcosystemServiceRecord::where('project_id', $project->id)
            ->service($service)
            ->find($recordId);

        if (! $record) {
            return redirect()->route('admin.modules.ecosystem.index', [$project->id, $service])
                ->with('success', 'Data memang sudah dihapus.');
        }

        $record->delete();

        return redirect()->route('admin.modules.ecosystem.index', [$project->id, $service])
            ->with('success', "Data {$schema['name']} berhasil dihapus");
    }

    private function schemaOrFail(string $service): array
    {
        $schema = EcosystemServiceSchemas::find($service);

        abort_if(! $schema, 404, 'Jenis jasa ekosistem tidak dikenal.');

        return $schema;
    }

    private function recordOrFail(Project $project, string $service, $recordId): EcosystemServiceRecord
    {
        return EcosystemServiceRecord::where('project_id', $project->id)
            ->service($service)
            ->findOrFail($recordId);
    }

    /**
     * Builds validation rules from the schema so each service validates
     * exactly the inputs its form actually renders.
     */
    private function validateRecord(Request $request, Project $project, string $service, ?int $ignoreId = null): array
    {
        $codeRule = Rule::unique('ecosystem_service_records')
            ->where(fn ($q) => $q->where('project_id', $project->id)
                ->where('service_key', $service)
                ->whereNull('deleted_at'));

        if ($ignoreId) {
            $codeRule = $codeRule->ignore($ignoreId);
        }

        $rules = [
            'record_code' => ['required', 'string', 'max:60', $codeRule],
            'data_collection_type' => ['nullable', Rule::in(array_keys(DataCollectionTypes::TYPES))],
            'collection_method' => ['nullable', Rule::in(DataCollectionTypes::allMethodCodes())],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        foreach (EcosystemServiceSchemas::resolvedFields($service) as $field) {
            $required = ! empty($field['required']);
            $rule = [$required ? 'required' : 'nullable'];

            $rule[] = match ($field['type']) {
                'number', 'currency' => 'numeric',
                'year' => 'integer',
                default => 'string',
            };

            if ($field['type'] === 'year') {
                $rule[] = 'min:1900';
                $rule[] = 'max:2200';
            } elseif (in_array($field['type'], ['number', 'currency'], true)) {
                $rule[] = 'min:0';
            } else {
                $rule[] = 'max:255';
            }

            if ($field['type'] === 'select' && ! empty($field['options'])) {
                $rule[] = Rule::in($field['options']);
            }

            $rules[$field['name']] = $rule;
        }

        return $request->validate($rules, [], [
            'record_code' => 'ID Data',
            'quantity_value' => 'kuantitas',
            'unit_price' => 'harga per unit',
        ]);
    }

    /**
     * Splits the validated payload into typed columns plus the `extra` JSON
     * bag holding whatever is specific to this one service.
     */
    private function toAttributes(array $validated, string $service): array
    {
        $extraNames = EcosystemServiceSchemas::extraFieldNames($service);
        $extra = [];

        foreach ($extraNames as $name) {
            $extra[$name] = $validated[$name] ?? null;
        }

        // The unit-reconciliation factor is a property of how the service is
        // measured, so the schema decides it; the calculator only applies it.
        $conversion = EcosystemServiceSchemas::priceConversion(
            $service,
            $validated['quantity_unit'] ?? null,
        );

        $derived = $this->calculator->ecosystemServiceRecordValues(
            $validated['quantity_value'] ?? 0,
            $validated['unit_price'] ?? 0,
            $validated['area_ha'] ?? 0,
            $conversion,
        );

        return [
            'record_code' => $validated['record_code'],
            'location' => $validated['location'] ?? null,
            'quantity_value' => $validated['quantity_value'] ?? 0,
            'quantity_unit' => $validated['quantity_unit'] ?? null,
            'unit_price' => $validated['unit_price'] ?? 0,
            'area_ha' => $validated['area_ha'] ?? null,
            'period_year' => $validated['period_year'] ?? null,
            'data_source' => $validated['data_source'] ?? null,
            'data_collection_type' => $validated['data_collection_type'] ?? null,
            'collection_method' => $validated['collection_method'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'extra' => $extra,
            ...$derived,
        ];
    }
}
