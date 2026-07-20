<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $publishedProjects = Project::where('status', 'published')->count();
        $totalTEV = Project::where('status', 'published')->sum('tev');
        $totalBenefits = Project::where('status', 'published')->sum('total_benefits');
        $avgBCR = Project::where('status', 'published')->avg('bcr') ?? 0;

        // Map data
        $mapProjects = Project::where('status', 'published')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'name', 'location', 'latitude', 'longitude', 'tev')
            ->get();

        return view('public.landing', [
            'publishedProjects' => $publishedProjects,
            'totalTEV' => $totalTEV ?? 0,
            'totalBenefits' => $totalBenefits ?? 0,
            'avgBCR' => $avgBCR,
            'mapProjects' => $mapProjects,
        ]);
    }

    public function dashboard(): View
    {
        $projects = Project::where('status', 'published')
            ->with('benefits', 'costs')
            ->paginate(6);

        $benefits = DB::table('benefits')
            ->join('projects', 'benefits.project_id', '=', 'projects.id')
            ->where('projects.status', 'published')
            ->groupBy('benefits.category')
            ->select('benefits.category', DB::raw('sum(benefits.value) as total'))
            ->get();

        $methodDistribution = DB::table('benefits')
            ->join('projects', 'benefits.project_id', '=', 'projects.id')
            ->where('projects.status', 'published')
            ->groupBy('benefits.method_used')
            ->select('benefits.method_used', DB::raw('count(*) as count'))
            ->get();

        return view('public.dashboard', [
            'projects' => $projects,
            'benefits' => $benefits,
            'methodDistribution' => $methodDistribution,
        ]);
    }

    public function glossary(): View
    {
        return view('public.glossary');
    }

    public function projectDetail($id): View
    {
        $project = Project::findOrFail($id);

        if ($project->status !== 'published') {
            abort(404);
        }

        $benefits = $project->benefits()->get();
        $costs = $project->costs()->get();

        return view('public.project-detail', [
            'project' => $project,
            'benefits' => $benefits,
            'costs' => $costs,
        ]);
    }
}
