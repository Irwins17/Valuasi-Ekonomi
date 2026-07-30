<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnvironmentalCoefficient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EnvironmentalCoefficientController extends Controller
{
    public function index(): Response
    {
        $coefficients = EnvironmentalCoefficient::latest()->paginate(15);
        return Inertia::render('Admin/MasterData/Coefficients/Index', ['coefficients' => $coefficients]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/MasterData/Coefficients/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:environmental_coefficients',
            'description' => 'nullable|string',
            'value' => 'required|numeric',
            'unit' => 'required|string',
            'type' => 'required|string',
            'source' => 'nullable|string',
            'year' => 'nullable|integer',
        ]);

        EnvironmentalCoefficient::create([...$validated, 'approved_by' => auth()->id()]);
        return redirect()->route('admin.master.coefficients.index')->with('success', 'Koefisien berhasil ditambahkan');
    }

    public function edit($id): Response
    {
        $coefficient = EnvironmentalCoefficient::findOrFail($id);
        return Inertia::render('Admin/MasterData/Coefficients/Edit', ['coefficient' => $coefficient]);
    }

    public function update(Request $request, $id)
    {
        $coefficient = EnvironmentalCoefficient::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:environmental_coefficients,code,' . $id,
            'description' => 'nullable|string',
            'value' => 'required|numeric',
            'unit' => 'required|string',
            'type' => 'required|string',
            'source' => 'nullable|string',
            'year' => 'nullable|integer',
        ]);
        $coefficient->update($validated);
        return redirect()->route('admin.master.coefficients.index')->with('success', 'Koefisien berhasil diperbarui');
    }

    public function destroy($id)
    {
        EnvironmentalCoefficient::findOrFail($id)->delete();
        return redirect()->route('admin.master.coefficients.index')->with('success', 'Koefisien berhasil dihapus');
    }
}
