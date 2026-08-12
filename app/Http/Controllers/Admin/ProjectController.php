<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectValuationSetting;
use App\Models\ValuationModule;
use App\Services\Valuation\BenefitCostPresentValues;
use App\Services\Valuation\DoubleCountingChecker;
use App\Services\Valuation\EcosystemServiceValuationCalculator;
use App\Support\Provinces;
use App\Support\ValuationModuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelPdf\Facades\Pdf;

class ProjectController extends Controller
{
    public function __construct(
        private readonly DoubleCountingChecker $doubleCounting,
        private readonly BenefitCostPresentValues $presentValues,
    ) {}

    public function index(): Response
    {
        // Excludes boundary_geojson: this list only renders code/name/
        // description/location/status/tev/bcr, and uploaded survey-area
        // polygons can run into the megabytes per row.
        $projects = Project::select([
            'id', 'code', 'name', 'description', 'location', 'status', 'tev', 'bcr',
        ])->paginate(10);

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
            'province' => ['nullable', Rule::in(Provinces::NAMES)],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'boundary_geojson' => ['nullable', 'array'],
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

        $settings = $project->valuation_settings;

        return Inertia::render('Admin/Projects/Show', [
            'project' => $project,
            // through() keeps the paginator's meta intact while giving each row
            // a pv_value that is on the same basis as the totals above it.
            'benefits' => $project->benefits()->paginate(5)
                ->through(fn ($benefit) => $this->presentValues->benefit($benefit, $settings)),
            'costs' => $project->costs()->paginate(5)
                ->through(fn ($cost) => $this->presentValues->cost($cost, $settings)),
            'ecosystemIndices' => $this->ecosystemValuationSummary($project),
            // Advisory only — nothing here blocks a save. The page shows them
            // beside the totals because an inflated TEV is invisible in the
            // number itself; the overlap is only visible in the rows.
            'doubleCountingWarnings' => $this->doubleCounting->check($project),
            'valuationSettings' => [
                'base_year' => (int) $settings->base_year,
                'discount_rate' => (float) $settings->discount_rate,
                'analysis_period' => (int) $settings->analysis_period,
                'currency' => $settings->currency,
                'start_year' => $settings->start_year,
                'end_year' => $settings->end_year,
                'eop_value_basis' => $settings->eop_value_basis,
                // Distinguishes "never configured, showing defaults" from
                // "deliberately set to these values" on the page.
                'is_configured' => $project->valuationSetting !== null,
            ],
            'eopValueBases' => ProjectValuationSetting::EOP_VALUE_BASES,
            'modules' => collect(ValuationModule::resolveForProject($project))
                ->where('show_on_project_detail', true)
                ->values()
                ->all(),
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
        ]);
    }

    /**
     * Land-cover ecosystem-service valuation (Tabel 1 method), grouped per
     * index/scenario for the "Jasa Ekosistem" section of the project page.
     */
    private function ecosystemValuationSummary(Project $project): array
    {
        $calc = new EcosystemServiceValuationCalculator;

        return $project->ecosystemValuationIndices()
            ->with(['landCovers.items', 'items' => fn ($q) => $q->whereNull('land_cover_id')])
            ->get()
            ->map(function ($index) use ($calc) {
                $landCovers = $index->landCovers->map(function ($landCover) use ($calc) {
                    $items = $landCover->items;
                    $summary = $calc->summarize($items->map(fn ($i) => [
                        'service_category' => $i->service_category,
                        'total_value' => $i->total_value,
                    ])->toArray());

                    return [
                        'id' => $landCover->id,
                        'name' => $landCover->name,
                        'area_ha' => $landCover->area_ha,
                        'notes' => $landCover->notes,
                        'items' => $items,
                        'by_category' => $summary['by_category'],
                        'total' => $summary['total'],
                    ];
                });

                $culturalItems = $index->items->whereNull('land_cover_id')->values();
                $culturalTotal = $calc->summarize($culturalItems->map(fn ($i) => [
                    'service_category' => $i->service_category,
                    'total_value' => $i->total_value,
                ])->toArray())['total'];

                return [
                    'id' => $index->id,
                    'index_number' => $index->index_number,
                    'name' => $index->name,
                    'notes' => $index->notes,
                    'land_covers' => $landCovers,
                    'cultural_items' => $culturalItems,
                    'cultural_total' => $culturalTotal,
                    'tev' => $landCovers->sum('total') + $culturalTotal,
                ];
            })
            ->values()
            ->all();
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
            'province' => ['nullable', Rule::in(Provinces::NAMES)],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'boundary_geojson' => ['nullable', 'array'],
            'status' => ['in:draft,in_progress,completed,published'],
        ]);

        $project->update([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.projects.show', $project)
            ->with('success', 'Project berhasil diperbarui');
    }

    /**
     * Soft-deletes the project (the model uses SoftDeletes, and the
     * Auditable trait records who removed it). Benefits, costs and the
     * EOP/TCM/CVM entries are deliberately left in place so the whole
     * project can be restored intact if the deletion was a mistake.
     *
     * Looks the project up including trashed rows: findOrFail() would hide
     * an already-deleted project behind the SoftDeletes scope and answer a
     * bare 404 page, which is what a repeat delete produces — a double
     * click, a stale list still showing the row, the browser back button,
     * or a second tab. Those are all "already done", not errors, so they
     * land back on the list with a message instead.
     */
    public function destroy($id)
    {
        $project = Project::withTrashed()->find($id);

        if (! $project) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Proyek tidak ditemukan — mungkin sudah dihapus permanen.');
        }

        if ($project->trashed()) {
            return redirect()->route('admin.projects.index')
                ->with('success', "Proyek {$project->code} memang sudah dihapus.");
        }

        $project->update(['updated_by' => auth()->id()]);
        $project->delete();

        return redirect()->route('admin.projects.index')
            ->with('success', "Proyek {$project->code} berhasil dihapus");
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
        $settings = $project->valuation_settings;

        $this->presentValues->benefits($project->benefits, $settings);
        $this->presentValues->costs($project->costs, $settings);

        return Pdf::view('pdf.projects.export', [
            'project' => $project,
            // A printed present value is unreadable without the rate and base
            // year it was discounted under, and a PDF outlives the screen it
            // was exported from — so the assumptions travel with it.
            'settings' => $settings,
            'activityGroups' => CostController::ACTIVITY_GROUPS,
        ])
            ->format('a4')
            ->download(Str::slug("{$project->code}-{$project->name}"));
    }
}
