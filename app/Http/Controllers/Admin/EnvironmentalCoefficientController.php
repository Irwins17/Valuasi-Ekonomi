<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnvironmentalCoefficient;
use Illuminate\Http\Request;

class EnvironmentalCoefficientController extends Controller
{
    public function index()
    {
        $coefficients = EnvironmentalCoefficient::latest()->paginate(15);
        return view('admin.master-data.coefficients.index', ['coefficients' => $coefficients]);
    }

    public function create()
    {
        return view('admin.master-data.coefficients.create');
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

    public function edit($id)
    {
        $coefficient = EnvironmentalCoefficient::findOrFail($id);
        return view('admin.master-data.coefficients.edit', ['coefficient' => $coefficient]);
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
