<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\CvmAnalysis;
use App\Models\CvmData;
use App\Models\Project;
use App\Services\Valuation\ContingentValuationEstimator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CvmAnalysisController extends Controller
{
    private const MODELS = ['logit' => 'Logit', 'probit' => 'Probit'];

    private const COVARIATES = [
        'income' => 'Pendapatan RT',
        'education' => 'Pendidikan',
        'age' => 'Usia',
    ];

    public function index($projectId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Modules/Cvm/Analysis/Index', [
            'project' => $project,
            'analyses' => CvmAnalysis::where('project_id', $project->id)
                ->latest('id')->paginate(10)->withQueryString(),
            'models' => self::MODELS,
            'dichotomousCount' => $this->dichotomousCount($project),
        ]);
    }

    public function create($projectId): Response
    {
        return $this->renderForm(Project::findOrFail($projectId), null);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        CvmAnalysis::create([
            ...$this->validateAnalysis($request, $project),
            'project_id' => $project->id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.modules.cvm.analysis.index', $project->id)
            ->with('success', 'Analisis CVM berhasil disimpan');
    }

    public function edit($projectId, $analysisId): Response
    {
        $project = Project::findOrFail($projectId);

        return $this->renderForm(
            $project,
            CvmAnalysis::where('project_id', $project->id)->findOrFail($analysisId),
        );
    }

    public function update(Request $request, $projectId, $analysisId)
    {
        $project = Project::findOrFail($projectId);
        $analysis = CvmAnalysis::where('project_id', $project->id)->findOrFail($analysisId);

        $analysis->update($this->validateAnalysis($request, $project, $analysis->id));

        return redirect()->route('admin.modules.cvm.analysis.index', $project->id)
            ->with('success', 'Analisis CVM berhasil diperbarui');
    }

    public function destroy($projectId, $analysisId)
    {
        $project = Project::findOrFail($projectId);
        $analysis = CvmAnalysis::where('project_id', $project->id)->find($analysisId);

        if (! $analysis) {
            return redirect()->route('admin.modules.cvm.analysis.index', $project->id)
                ->with('success', 'Analisis memang sudah dihapus.');
        }

        $analysis->delete();

        return redirect()->route('admin.modules.cvm.analysis.index', $project->id)
            ->with('success', 'Analisis CVM berhasil dihapus');
    }

    public function estimate(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $input = $request->validate([
            'model' => ['required', Rule::in(array_keys(self::MODELS))],
            'covariates' => ['array'],
            'covariates.*' => [Rule::in(array_keys(self::COVARIATES))],
        ]);

        $result = (new ContingentValuationEstimator)->estimate(
            $project,
            $input['model'],
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

    private function dichotomousCount(Project $project): int
    {
        return CvmData::where('project_id', $project->id)
            ->where('question_method', 'dichotomous_choice')
            ->whereNotNull('bid_amount')
            ->count();
    }

    private function renderForm(Project $project, ?CvmAnalysis $analysis): Response
    {
        return Inertia::render('Admin/Modules/Cvm/Analysis/Form', [
            'project' => $project,
            'analysis' => $analysis,
            'models' => self::MODELS,
            'covariateOptions' => self::COVARIATES,
            'dichotomousCount' => $this->dichotomousCount($project),
            'minRespondents' => ContingentValuationEstimator::MIN_RESPONDENTS,
        ]);
    }

    private function validateAnalysis(Request $request, Project $project, ?int $ignoreId = null): array
    {
        $codeRule = Rule::unique('cvm_analyses')
            ->where(fn ($q) => $q->where('project_id', $project->id)->whereNull('deleted_at'));

        if ($ignoreId) {
            $codeRule = $codeRule->ignore($ignoreId);
        }

        return $request->validate([
            'analysis_code' => ['required', 'string', 'max:60', $codeRule],
            'scenario' => ['required', 'string', 'max:255'],
            'model' => ['required', Rule::in(array_keys(self::MODELS))],
            'coefficient_source' => ['required', Rule::in(['estimated', 'manual'])],
            'question_type' => ['required', 'string', 'max:120'],
            'bid_value' => ['nullable', 'numeric', 'min:0'],
            'respondent_count' => ['required', 'integer', 'min:0'],
            'yes_count' => ['required', 'integer', 'min:0'],
            'no_count' => ['required', 'integer', 'min:0'],
            'alpha' => ['required', 'numeric'],
            'beta_bid' => ['required', 'numeric'],
            'coef_income' => ['nullable', 'numeric'],
            'coef_education' => ['nullable', 'numeric'],
            'coef_age' => ['nullable', 'numeric'],
            'mean_covariates' => ['nullable', 'array'],
            'target_population' => ['required', 'integer', 'min:0'],
            'converged' => ['nullable', 'boolean'],
            'log_likelihood' => ['nullable', 'numeric'],
            'diagnostics' => ['nullable', 'array'],
            'period_year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'data_source' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'analysis_code' => 'ID Analisis',
            'beta_bid' => 'koefisien bid',
            'target_population' => 'populasi sasaran',
        ]);
    }
}
