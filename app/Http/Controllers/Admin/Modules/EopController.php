<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\EopData;
use App\Models\MarketPrice;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EopController extends Controller
{
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
        ]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'commodity_name' => ['required', 'string'],
            'production_before' => ['required', 'numeric'],
            'production_after' => ['required', 'numeric'],
            'unit' => ['required', 'string'],
            'market_price' => ['required', 'numeric'],
            'impact_type' => ['required', 'in:positive,negative'],
        ]);

        $eopData = EopData::create([
            'project_id' => $projectId,
            'recorded_by' => auth()->id(),
            ...$validated,
        ]);

        // Create benefit if impact is positive
        if ($eopData->impact_type === 'positive') {
            Benefit::create([
                'project_id' => $projectId,
                'category' => 'direct_use',
                'subcategory' => 'production',
                'description' => 'Produksi ' . $eopData->commodity_name,
                'value' => abs($eopData->total_value),
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
        ]);
    }

    public function update(Request $request, $projectId, $eopId)
    {
        $eopData = EopData::findOrFail($eopId);

        $validated = $request->validate([
            'commodity_name' => ['required', 'string'],
            'production_before' => ['required', 'numeric'],
            'production_after' => ['required', 'numeric'],
            'unit' => ['required', 'string'],
            'market_price' => ['required', 'numeric'],
            'impact_type' => ['required', 'in:positive,negative'],
        ]);

        $eopData->update($validated);

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
}
