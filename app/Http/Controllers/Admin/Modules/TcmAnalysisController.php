<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TcmAnalysis;
use App\Models\TcmData;
use App\Services\Valuation\TravelCostEstimator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TcmAnalysisController extends Controller
{
    private const MODELS = [
        'poisson' => 'Poisson',
        'negative_binomial' => 'Negative Binomial',
    ];

    private const COVARIATES = [
        'income' => 'Pendapatan',
        'age' => 'Usia',
        'education' => 'Pendidikan',
        'substitute' => 'Situs pengganti',
    ];

    public function index($projectId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Modules/Tcm/Analysis/Index', [
            'project' => $project,
            'analyses' => TcmAnalysis::where('project_id', $project->id)
                ->latest('id')->paginate(10)->withQueryString(),
            'respondentCount' => TcmData::where('project_id', $project->id)->count(),
            'models' => self::MODELS,
        ]);
    }

    public function create($projectId): Response
    {
        return $this->renderForm(Project::findOrFail($projectId), null);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $this->validateAnalysis($request, $project);

        TcmAnalysis::create([
            ...$validated,
            'project_id' => $project->id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.modules.tcm.analysis.index', $project->id)
            ->with('success', 'Analisis TCM berhasil disimpan');
    }

    public function edit($projectId, $analysisId): Response
    {
        $project = Project::findOrFail($projectId);

        return $this->renderForm(
            $project,
            TcmAnalysis::where('project_id', $project->id)->findOrFail($analysisId),
        );
    }

    public function update(Request $request, $projectId, $analysisId)
    {
        $project = Project::findOrFail($projectId);
        $analysis = TcmAnalysis::where('project_id', $project->id)->findOrFail($analysisId);

        $analysis->update($this->validateAnalysis($request, $project, $analysis->id));

        return redirect()->route('admin.modules.tcm.analysis.index', $project->id)
            ->with('success', 'Analisis TCM berhasil diperbarui');
    }

    public function destroy($projectId, $analysisId)
    {
        $project = Project::findOrFail($projectId);
        $analysis = TcmAnalysis::where('project_id', $project->id)->find($analysisId);

        if (! $analysis) {
            return redirect()->route('admin.modules.tcm.analysis.index', $project->id)
                ->with('success', 'Analisis memang sudah dihapus.');
        }

        $analysis->delete();

        return redirect()->route('admin.modules.tcm.analysis.index', $project->id)
            ->with('success', 'Analisis TCM berhasil dihapus');
    }

    /**
     * Fits the demand model to this project's respondents and hands the
     * coefficients back to the open form, without saving anything — the
     * analyst still reviews and submits.
     */
    public function estimate(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $input = $request->validate([
            'regression_model' => ['required', Rule::in(array_keys(self::MODELS))],
            'covariates' => ['array'],
            'covariates.*' => [Rule::in(array_keys(self::COVARIATES))],
        ]);

        $result = (new TravelCostEstimator)->estimate(
            $project,
            $input['regression_model'],
            $input['covariates'] ?? [],
        );

        if (! $result['ok']) {
            return back()->with('error', $result['message'])->withInput();
        }

        return back()->with([
            'success' => 'Estimasi selesai. Periksa koefisien sebelum menyimpan.',
            'estimation' => $result,
        ]);
    }

    private function renderForm(Project $project, ?TcmAnalysis $analysis): Response
    {
        return Inertia::render('Admin/Modules/Tcm/Analysis/Form', [
            'project' => $project,
            'analysis' => $analysis,
            'models' => self::MODELS,
            'covariateOptions' => self::COVARIATES,
            'respondentCount' => TcmData::where('project_id', $project->id)->count(),
            'minRespondents' => TravelCostEstimator::MIN_RESPONDENTS,
        ]);
    }

    private function validateAnalysis(Request $request, Project $project, ?int $ignoreId = null): array
    {
        $codeRule = Rule::unique('tcm_analyses')
            ->where(fn ($q) => $q->where('project_id', $project->id)->whereNull('deleted_at'));

        if ($ignoreId) {
            $codeRule = $codeRule->ignore($ignoreId);
        }

        return $request->validate([
            'analysis_code' => ['required', 'string', 'max:60', $codeRule],
            'site_name' => ['required', 'string', 'max:255'],
            'regression_model' => ['required', Rule::in(array_keys(self::MODELS))],
            'coefficient_source' => ['required', Rule::in(['estimated', 'manual'])],
            'respondent_count' => ['required', 'integer', 'min:0'],
            'total_visitors' => ['required', 'integer', 'min:0'],
            'mean_travel_cost' => ['required', 'numeric', 'min:0'],
            'beta_0' => ['required', 'numeric'],
            'beta_1' => ['required', 'numeric'],
            'coef_income' => ['nullable', 'numeric'],
            'coef_age' => ['nullable', 'numeric'],
            'coef_education' => ['nullable', 'numeric'],
            'coef_substitute' => ['nullable', 'numeric'],
            'estimation_method' => ['nullable', 'string', 'max:120'],
            'converged' => ['nullable', 'boolean'],
            'log_likelihood' => ['nullable', 'numeric'],
            'dispersion_alpha' => ['nullable', 'numeric'],
            'diagnostics' => ['nullable', 'array'],
            'period_year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'data_source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'analysis_code' => 'ID Analisis',
            'site_name' => 'lokasi wisata',
            'beta_1' => 'koefisien biaya perjalanan',
        ]);
    }
}
