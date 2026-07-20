<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\Project;
use Illuminate\Http\Request;

class BenefitController extends Controller
{
    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);
        return view('admin.benefits.create', ['project' => $project]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $request->validate([
            'category' => 'required|in:direct_use,indirect_use,non_use',
            'subcategory' => 'required',
            'description' => 'required|string',
            'value' => 'required|numeric|min:0',
            'method_used' => 'nullable|string',
            'data_source' => 'required|in:eop,tcm,cvm,manual,literature',
            'sample_size' => 'nullable|integer',
            'calculation_notes' => 'nullable|string',
        ]);

        Benefit::create([
            ...$validated,
            'project_id' => $projectId,
            'calculated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.projects.show', $projectId)->with('success', 'Benefit berhasil ditambahkan');
    }

    public function edit($projectId, $benefitId)
    {
        $project = Project::findOrFail($projectId);
        $benefit = Benefit::findOrFail($benefitId);
        return view('admin.benefits.edit', ['project' => $project, 'benefit' => $benefit]);
    }

    public function update(Request $request, $projectId, $benefitId)
    {
        $benefit = Benefit::findOrFail($benefitId);
        $validated = $request->validate([
            'category' => 'required|in:direct_use,indirect_use,non_use',
            'subcategory' => 'required',
            'description' => 'required|string',
            'value' => 'required|numeric|min:0',
            'method_used' => 'nullable|string',
            'data_source' => 'required|in:eop,tcm,cvm,manual,literature',
        ]);
        $benefit->update($validated);
        return redirect()->route('admin.projects.show', $projectId)->with('success', 'Benefit berhasil diperbarui');
    }

    public function destroy($projectId, $benefitId)
    {
        Benefit::findOrFail($benefitId)->delete();
        return redirect()->route('admin.projects.show', $projectId)->with('success', 'Benefit berhasil dihapus');
    }
}
