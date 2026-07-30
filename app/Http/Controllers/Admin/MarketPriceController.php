<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketPrice;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketPriceController extends Controller
{
    public function index(Request $request): Response
    {
        // scope: 'all' (default) | 'global' (umum saja) | id proyek tertentu
        $scope = $request->query('scope', 'all');

        $prices = MarketPrice::with('project')
            ->when($scope === 'global', fn ($q) => $q->global())
            ->when(is_numeric($scope), fn ($q) => $q->where('project_id', (int) $scope))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/MasterData/MarketPrices/Index', [
            'prices' => $prices,
            'projects' => Project::orderBy('name')->get(['id', 'name', 'code']),
            'scope' => $scope,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/MasterData/MarketPrices/Create', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'commodity_name' => 'required|string',
            'unit' => 'required|string',
            'price' => 'required|numeric|min:0',
            'year' => 'required|integer|min:2000',
            'source' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // string kosong dari <select> "Umum (Global)" -> simpan sebagai NULL
        $validated['project_id'] = $validated['project_id'] ?: null;

        MarketPrice::create([...$validated, 'approved_by' => auth()->id()]);

        return redirect()->route('admin.master.prices.index')
            ->with('success', 'Harga pasar berhasil ditambahkan');
    }

    public function edit($id): Response
    {
        $price = MarketPrice::findOrFail($id);

        return Inertia::render('Admin/MasterData/MarketPrices/Edit', [
            'price' => $price,
            'projects' => Project::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $price = MarketPrice::findOrFail($id);

        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'commodity_name' => 'required|string',
            'unit' => 'required|string',
            'price' => 'required|numeric|min:0',
            'year' => 'required|integer|min:2000',
            'source' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['project_id'] = $validated['project_id'] ?: null;

        $price->update($validated);

        return redirect()->route('admin.master.prices.index')
            ->with('success', 'Harga pasar berhasil diperbarui');
    }

    public function destroy($id)
    {
        MarketPrice::findOrFail($id)->delete();

        return redirect()->route('admin.master.prices.index')
            ->with('success', 'Harga pasar berhasil dihapus');
    }
}
