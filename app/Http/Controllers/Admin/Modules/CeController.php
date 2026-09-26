<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\CeData;
use App\Models\Project;
use App\Support\DataCollectionTypes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Choice experiment responses.
 *
 * MWTPk = −βk/βp requires a conditional logit fitted across the whole design.
 * This app does not estimate that model, so the coefficients are supplied from
 * whichever software fitted it and the module computes MWTP from them — the
 * page states this plainly rather than implying the βs were derived here.
 */
class CeController extends Controller
{
    private const EDUCATION_LEVELS = ['Tidak sekolah', 'SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S2', 'S3'];

    public function index($projectId): Response
    {
        $project = Project::findOrFail($projectId);
        $rows = CeData::where('project_id', $project->id);

        return Inertia::render('Admin/Modules/Ce/Index', [
            'project' => $project,
            'records' => (clone $rows)->latest('id')->paginate(10)->withQueryString(),
            'totals' => [
                'records' => (clone $rows)->count(),
                'respondents' => (clone $rows)->distinct('respondent_code')->count('respondent_code'),
                'choice_sets' => (clone $rows)->distinct('choice_set')->count('choice_set'),
            ],
            'choiceShares' => (clone $rows)
                ->selectRaw('chosen_alternative, COUNT(*) as total')
                ->groupBy('chosen_alternative')
                ->pluck('total', 'chosen_alternative'),
        ]);
    }

    public function create($projectId): Response
    {
        return $this->renderForm(Project::findOrFail($projectId), null);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        CeData::create([
            ...$this->validateRecord($request, $project),
            'project_id' => $project->id,
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.modules.ce.index', $project->id)
            ->with('success', 'Data Choice Experiment berhasil ditambahkan');
    }

    public function edit($projectId, $ceId): Response
    {
        $project = Project::findOrFail($projectId);

        return $this->renderForm($project, CeData::where('project_id', $project->id)->findOrFail($ceId));
    }

    public function update(Request $request, $projectId, $ceId)
    {
        $project = Project::findOrFail($projectId);
        $record = CeData::where('project_id', $project->id)->findOrFail($ceId);

        $record->update($this->validateRecord($request, $project, $record->id));

        return redirect()->route('admin.modules.ce.index', $project->id)
            ->with('success', 'Data Choice Experiment berhasil diperbarui');
    }

    public function destroy($projectId, $ceId)
    {
        $project = Project::findOrFail($projectId);
        $record = CeData::where('project_id', $project->id)->find($ceId);

        if (! $record) {
            return redirect()->route('admin.modules.ce.index', $project->id)
                ->with('success', 'Data memang sudah dihapus.');
        }

        $record->delete();

        return redirect()->route('admin.modules.ce.index', $project->id)
            ->with('success', 'Data Choice Experiment berhasil dihapus');
    }

    private function renderForm(Project $project, ?CeData $record): Response
    {
        return Inertia::render('Admin/Modules/Ce/Form', [
            'project' => $project,
            'record' => $record,
            'educationLevels' => self::EDUCATION_LEVELS,
        ]);
    }

    private function validateRecord(Request $request, Project $project, ?int $ignoreId = null): array
    {
        // A respondent answers several choice sets, so uniqueness is the pair.
        $uniqueRule = Rule::unique('ce_data')
            ->where(fn ($q) => $q->where('project_id', $project->id)
                ->where('choice_set', $request->input('choice_set'))
                ->whereNull('deleted_at'));

        if ($ignoreId) {
            $uniqueRule = $uniqueRule->ignore($ignoreId);
        }

        return $request->validate([
            'respondent_code' => ['required', 'string', 'max:60', $uniqueRule],
            'location' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'education' => ['nullable', 'string', 'max:120'],
            'income' => ['nullable', 'numeric', 'min:0'],
            'scenario_title' => ['required', 'string', 'max:255'],
            'choice_set' => ['required', 'string', 'max:120'],
            'alternative_a' => ['nullable', 'string', 'max:255'],
            'alternative_b' => ['nullable', 'string', 'max:255'],
            'status_quo' => ['nullable', 'string', 'max:255'],
            'chosen_alternative' => ['required', 'in:a,b,status_quo'],
            'attribute_1' => ['nullable', 'string', 'max:120'],
            'attribute_1_level' => ['nullable', 'string', 'max:120'],
            'attribute_2' => ['nullable', 'string', 'max:120'],
            'attribute_2_level' => ['nullable', 'string', 'max:120'],
            'attribute_3' => ['nullable', 'string', 'max:120'],
            'attribute_3_level' => ['nullable', 'string', 'max:120'],
            'cost_attribute' => ['nullable', 'numeric', 'min:0'],
            'data_source' => ['nullable', 'string', 'max:255'],
            'data_collection_type' => ['nullable', Rule::in(array_keys(DataCollectionTypes::TYPES))],
            'collection_method' => ['nullable', Rule::in(DataCollectionTypes::allMethodCodes())],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'respondent_code.unique' => 'Responden ini sudah punya jawaban untuk choice set tersebut.',
        ], [
            'respondent_code' => 'ID responden',
            'chosen_alternative' => 'alternatif dipilih',
        ]);
    }
}
