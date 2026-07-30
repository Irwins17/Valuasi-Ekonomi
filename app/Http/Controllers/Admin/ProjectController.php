<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelPdf\Facades\Pdf;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $projects = Project::with('creator', 'updater')
            ->paginate(10);

        return Inertia::render('Admin/Projects/Index', ['projects' => $projects]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Create');
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

    public function show($id): Response
    {
        $project = Project::withCount(['eopData', 'tcmData', 'cvmData'])->findOrFail($id);

        return Inertia::render('Admin/Projects/Show', [
            'project' => $project,
            'benefits' => $project->benefits()->paginate(5),
            'costs' => $project->costs()->paginate(5),
        ]);
    }

    public function edit($id): Response
    {
        $project = Project::findOrFail($id);
        return Inertia::render('Admin/Projects/Edit', ['project' => $project]);
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

        return Pdf::view('pdf.projects.export', ['project' => $project])
            ->format('a4')
            ->download(Str::slug("{$project->code}-{$project->name}"));
    }
}
