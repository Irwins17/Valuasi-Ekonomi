<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cost;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CostController extends Controller
{
    public function create($projectId): Response
    {
        $project = Project::findOrFail($projectId);
        return Inertia::render('Admin/Costs/Create', ['project' => $project]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $request->validate([
            'category' => 'required|in:direct_cost,indirect_cost',
            'subcategory' => 'required',
            'description' => 'required|string',
            'value' => 'required|numeric|min:0',
            'payment_type' => 'nullable|string',
            'year_applied' => 'nullable|integer',
            'calculation_notes' => 'nullable|string',
        ]);

        Cost::create([
            ...$validated,
            'project_id' => $projectId,
            'calculated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.projects.show', $projectId)->with('success', 'Cost berhasil ditambahkan');
    }

    public function edit($projectId, $costId): Response
    {
        $project = Project::findOrFail($projectId);
        $cost = Cost::findOrFail($costId);
        return Inertia::render('Admin/Costs/Edit', ['project' => $project, 'cost' => $cost]);
    }

    public function update(Request $request, $projectId, $costId)
    {
        $cost = Cost::findOrFail($costId);
        $validated = $request->validate([
            'category' => 'required|in:direct_cost,indirect_cost',
            'subcategory' => 'required',
            'description' => 'required|string',
            'value' => 'required|numeric|min:0',
            'payment_type' => 'nullable|string',
            'year_applied' => 'nullable|integer',
            'calculation_notes' => 'nullable|string',
        ]);
        $cost->update($validated);
        return redirect()->route('admin.projects.show', $projectId)->with('success', 'Cost berhasil diperbarui');
    }

    public function destroy($projectId, $costId)
    {
        Cost::findOrFail($costId)->delete();
        return redirect()->route('admin.projects.show', $projectId)->with('success', 'Cost berhasil dihapus');
    }
}
