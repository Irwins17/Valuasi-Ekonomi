<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\EopData;
use App\Models\MarketPrice;
use App\Models\Project;
use App\Services\Valuation\EconomicValuationCalculator;
use App\Support\DataCollectionTypes;
use App\Support\ValuationModuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EopController extends Controller
{
    public function __construct(private readonly EconomicValuationCalculator $calculator) {}

    public function index($projectId): Response
    {
        $project = Project::findOrFail($projectId);
        $eopData = $project->eopData()->paginate(10);

        return Inertia::render('Admin/Modules/Eop/Index', [
            'project' => $project,
            'eopData' => $eopData,
        ]);
    }

    public function create($projectId): Response
    {
        $project = Project::findOrFail($projectId);
        // Referensi harga untuk proyek ini: harga khusus proyek + harga umum/global
        $marketPrices = MarketPrice::where('year', now()->year)
            ->forProject($project->id)
            ->get()
            ->groupBy('commodity_name');

        return Inertia::render('Admin/Modules/Eop/Create', [
            'project' => $project,
            'marketPrices' => $marketPrices,
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
        ]);
    }

    public function store(Request $request, $projectId)
    {
        Project::findOrFail($projectId);

        $validated = $this->validateEop($request);

        $eopData = EopData::create([
            'project_id' => $projectId,
            'recorded_by' => auth()->id(),
            ...$validated,
            ...$this->derivedValues($validated),
        ]);

        // Create benefit if impact is positive. Uses the net value so a
        // recorded production cost is carried through to the benefit; with no
        // cost entered net equals the gross, matching the previous behaviour.
        if ($eopData->impact_type === 'positive') {
            Benefit::create([
                'project_id' => $projectId,
                'category' => 'direct_use',
                'subcategory' => 'production',
                'description' => 'Produksi ' . $eopData->commodity_name,
                'value' => abs($eopData->net_value),
                'method_used' => 'EOP',
                'data_source' => 'eop',
                'calculated_by' => auth()->id(),
            ]);
        }

        return redirect()->route('admin.modules.eop.index', $projectId)
            ->with('success', 'Data EOP berhasil ditambahkan');
    }

    public function edit($projectId, $eopId): Response
    {
        $project = Project::findOrFail($projectId);
        $eopData = EopData::findOrFail($eopId);
        $marketPrices = MarketPrice::where('year', now()->year)
            ->forProject($project->id)
            ->get();

        return Inertia::render('Admin/Modules/Eop/Edit', [
            'project' => $project,
            'eopData' => $eopData,
            'marketPrices' => $marketPrices,
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
        ]);
    }

    public function update(Request $request, $projectId, $eopId)
    {
        $eopData = EopData::findOrFail($eopId);

        $validated = $this->validateEop($request);

        // Derive from the row as it will be after the update: a payload that
        // leaves production_cost out must keep the cost already recorded,
        // not silently recompute the net value as if it were zero.
        $inputs = array_merge(
            $eopData->only(['production_before', 'production_after', 'market_price', 'production_cost']),
            $validated,
        );

        $eopData->update([...$validated, ...$this->derivedValues($inputs)]);

        return redirect()->route('admin.modules.eop.index', $projectId)
            ->with('success', 'Data EOP berhasil diperbarui');
    }

    public function destroy($projectId, $eopId)
    {
        $eopData = EopData::findOrFail($eopId);
        $eopData->delete();

        return redirect()->route('admin.modules.eop.index', $projectId)
            ->with('success', 'Data EOP berhasil dihapus');
    }

    /**
     * The stored derived columns for an EOP row, from the single
     * implementation of the formula.
     *
     * `impact_direction` is dropped here because it is a read of the data
     * (does ΔQ go up or down), not a column — `impact_type` remains the
     * user's own classification.
     */
    private function derivedValues(array $inputs): array
    {
        $values = $this->calculator->eopRecordValues(
            $inputs['production_before'] ?? 0,
            $inputs['production_after'] ?? 0,
            $inputs['market_price'] ?? 0,
            $inputs['production_cost'] ?? 0,
        );

        unset($values['impact_direction']);

        return $values;
    }

    private function validateEop(Request $request): array
    {
        return $request->validate([
            'commodity_name' => ['required', 'string', 'max:255'],
            // Optional rather than required: EOP defaults to Provisioning at
            // the column level, so a payload that omits it (an older client,
            // an import) still stores a valid row instead of being rejected.
            // Validated whenever it *is* sent, so the category is never free
            // text and is not hardcoded to Provisioning either.
            'service_category' => ['sometimes', Rule::in(array_keys(ValuationModuleCatalog::SERVICE_CATEGORIES))],
            'product_type' => ['nullable', 'string', 'max:255'],
            'production_before' => ['required', 'numeric', 'min:0'],
            'production_after' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:60'],
            'market_price' => ['required', 'numeric', 'min:0'],
            'production_cost' => ['nullable', 'numeric', 'min:0'],
            'area_ha' => ['nullable', 'numeric', 'min:0'],
            'period_year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'data_source' => ['nullable', 'string', 'max:255'],
            'data_collection_type' => ['nullable', Rule::in(array_keys(DataCollectionTypes::TYPES))],
            'collection_method' => ['nullable', Rule::in(DataCollectionTypes::allMethodCodes())],
            'impact_type' => ['required', 'in:positive,negative'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'commodity_name' => 'nama komoditas',
            'production_before' => 'produksi sebelum',
            'production_after' => 'produksi sesudah',
            'market_price' => 'harga pasar',
        ]);
    }
}
