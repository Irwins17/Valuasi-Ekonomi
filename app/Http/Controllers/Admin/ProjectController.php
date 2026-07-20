<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\Cost;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::with('creator', 'updater')
            ->paginate(10);

        return view('admin.projects.index', ['projects' => $projects]);
    }

    public function create(): View
    {
        return view('admin.projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'unique:projects'],
            'name' => ['required'],
            'description' => ['nullable'],
            'location' => ['required'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $project = Project::create([
            ...$validated,
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        return redirect()->route('admin.projects.show', $project)
            ->with('success', 'Project berhasil dibuat');
    }

    public function show($id): View
    {
        $project = Project::with(['benefits', 'costs', 'eopData', 'tcmData', 'cvmData'])->findOrFail($id);

        return view('admin.projects.show', [
            'project' => $project,
            'benefits' => $project->benefits()->paginate(5),
            'costs' => $project->costs()->paginate(5),
        ]);
    }

    public function edit($id): View
    {
        $project = Project::findOrFail($id);
        return view('admin.projects.edit', ['project' => $project]);
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required'],
            'description' => ['nullable'],
            'location' => ['required'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'status' => ['in:draft,in_progress,completed,published'],
        ]);

        $project->update([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.projects.show', $project)
            ->with('success', 'Project berhasil diperbarui');
    }

    public function calculateTEV($id)
    {
        $project = Project::findOrFail($id);
        $project->calculateTEV()->save();

        return back()->with('success', 'TEV berhasil dihitung');
    }

    public function export($id)
    {
        $project = Project::with(['benefits', 'costs'])->findOrFail($id);

        $html = view('admin.projects.pdf', ['project' => $project])->render();

        return response($html)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $project->name . '.pdf"');
    }
}
