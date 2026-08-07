<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function index(): Response
    {
        $publishedProjects = Project::where('status', 'published')->count();
        $totalTEV = Project::where('status', 'published')->sum('tev');
        $totalBenefits = Project::where('status', 'published')->sum('total_benefits');
        $avgBCR = Project::where('status', 'published')->avg('bcr') ?? 0;

        // Map data
        $mapProjects = Project::where('status', 'published')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'name', 'location', 'province', 'latitude', 'longitude', 'tev')
            ->get();

        return Inertia::render('Public/Landing', [
            'publishedProjects' => $publishedProjects,
            'totalTEV' => $totalTEV ?? 0,
            'totalBenefits' => $totalBenefits ?? 0,
            'avgBCR' => $avgBCR,
            'mapProjects' => $mapProjects,
        ]);
    }

    public function dashboard(): Response
    {
        // Excludes boundary_geojson: this card list only renders code/name/
        // description/location/tev/bcr, and uploaded survey-area polygons
        // can run into the megabytes per row.
        $projects = Project::where('status', 'published')
            ->with('benefits', 'costs')
            ->select(['id', 'code', 'name', 'description', 'location', 'tev', 'bcr', 'status', 'created_at'])
            ->paginate(6);

        // These go through the query builder rather than Eloquent, so the
        // SoftDeletes global scope does not apply — a deleted project's
        // benefits would otherwise keep showing up in the public charts.
        $benefits = DB::table('benefits')
            ->join('projects', 'benefits.project_id', '=', 'projects.id')
            ->where('projects.status', 'published')
            ->whereNull('projects.deleted_at')
            ->groupBy('benefits.category')
            ->select('benefits.category', DB::raw('sum(benefits.value) as total'))
            ->get();

        $methodDistribution = DB::table('benefits')
            ->join('projects', 'benefits.project_id', '=', 'projects.id')
            ->where('projects.status', 'published')
            ->whereNull('projects.deleted_at')
            ->groupBy('benefits.method_used')
            ->select('benefits.method_used', DB::raw('count(*) as count'))
            ->get();

        $mapProjects = Project::where('status', 'published')
            ->select('id', 'name', 'location', 'province', 'tev')
            ->get();

        return Inertia::render('Public/Dashboard', [
            'projects' => $projects,
            'benefits' => $benefits,
            'methodDistribution' => $methodDistribution,
            'mapProjects' => $mapProjects,
        ]);
    }

    public function glossary(): Response
    {
        return Inertia::render('Public/Glossary');
    }

    public function projectDetail($id): Response
    {
        $project = Project::findOrFail($id);

        if ($project->status !== 'published') {
            abort(404);
        }

        $benefits = $project->benefits()->get();
        $costs = $project->costs()->get();

        return Inertia::render('Public/ProjectDetail', [
            'project' => $project,
            'benefits' => $benefits,
            'costs' => $costs,
        ]);
    }
}
