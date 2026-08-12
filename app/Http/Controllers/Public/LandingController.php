<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Valuation\BenefitCostPresentValues;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function __construct(private readonly BenefitCostPresentValues $presentValues) {}

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
            // Present values, to match the TEV headline this chart sits under.
            // Aggregated in SQL across every published project, so the stored
            // column is used rather than a per-project recomputation; a row
            // that has never been discounted falls back to its nominal amount,
            // which is exactly what its PV would be at n = 0.
            ->select('benefits.category', DB::raw('sum(coalesce(benefits.pv_value, benefits.value)) as total'))
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

        $settings = $project->valuation_settings;

        return Inertia::render('Public/ProjectDetail', [
            'project' => $project,
            'benefits' => $this->presentValues->benefits($project->benefits()->get(), $settings),
            'costs' => $this->presentValues->costs($project->costs()->get(), $settings),
            // Published figures carry their assumptions, so a reader can tell
            // what "PV" on this page actually means.
            'valuationSettings' => [
                'base_year' => (int) $settings->base_year,
                'discount_rate' => (float) $settings->discount_rate,
                'currency' => $settings->currency,
            ],
        ]);
    }
}
