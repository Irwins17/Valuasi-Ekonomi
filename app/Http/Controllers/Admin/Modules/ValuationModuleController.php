<?php

namespace App\Http\Controllers\Admin\Modules;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ValuationModule;
use App\Support\ValuationModuleCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ValuationModuleController extends Controller
{
    public function index($projectId): Response
    {
        $project = Project::findOrFail($projectId);
        $modules = ValuationModule::resolveForProject($project);

        return Inertia::render('Admin/Modules/Index', [
            'project' => $project,
            'modules' => $modules,
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
            'summary' => [
                'active' => collect($modules)->where('status', 'aktif')->count(),
                'draft' => collect($modules)->where('status', 'draft')->count(),
                'records' => collect($modules)->sum('record_count'),
            ],
        ]);
    }

    public function create($projectId): Response
    {
        $project = Project::findOrFail($projectId);

        return Inertia::render('Admin/Modules/Form', [
            'project' => $project,
            'module' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $this->validateModule($request, $project);

        ValuationModule::create([
            ...$validated,
            'project_id' => $project->id,
            'is_builtin' => false,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.modules.index', $project->id)
            ->with('success', 'Modul valuasi berhasil ditambahkan');
    }

    /**
     * Configuration form for one module.
     *
     * Built-ins have no row until they are configured for the first time, so
     * the catalog defaults are used to seed the form and the row is created
     * on save rather than here — opening the form should not write anything.
     */
    public function configure($projectId, $code): Response
    {
        $project = Project::findOrFail($projectId);
        $module = $this->resolveModule($project, $code);

        return Inertia::render('Admin/Modules/Form', [
            'project' => $project,
            'module' => $module,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, $projectId, $code)
    {
        $project = Project::findOrFail($projectId);
        $catalogEntry = ValuationModuleCatalog::find($code);
        $existing = ValuationModule::where('project_id', $project->id)->where('code', $code)->first();

        if (! $catalogEntry && ! $existing) {
            return redirect()->route('admin.modules.index', $project->id)
                ->with('error', 'Modul tidak ditemukan.');
        }

        $validated = $this->validateModule($request, $project, $code);

        if ($existing) {
            $existing->update([...$validated, 'updated_by' => auth()->id()]);
        } else {
            // First time a built-in is configured: materialise its override row.
            ValuationModule::create([
                ...$validated,
                'project_id' => $project->id,
                'code' => $code,
                'is_builtin' => true,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }

        return redirect()->route('admin.modules.index', $project->id)
            ->with('success', 'Konfigurasi modul berhasil disimpan');
    }

    /**
     * Removes a custom module. Built-ins cannot be deleted — resetting one
     * drops its override row so the catalog defaults apply again.
     */
    public function destroy($projectId, $code)
    {
        $project = Project::findOrFail($projectId);
        $module = ValuationModule::where('project_id', $project->id)->where('code', $code)->first();

        if (! $module) {
            return redirect()->route('admin.modules.index', $project->id)
                ->with('success', 'Modul memang sudah tidak ada.');
        }

        $wasBuiltin = $module->is_builtin || ValuationModuleCatalog::find($code) !== null;
        $module->update(['updated_by' => auth()->id()]);
        $module->delete();

        return redirect()->route('admin.modules.index', $project->id)
            ->with('success', $wasBuiltin
                ? "Konfigurasi modul {$code} dikembalikan ke pengaturan bawaan."
                : "Modul {$code} berhasil dihapus.");
    }

    private function resolveModule(Project $project, string $code): array
    {
        $modules = ValuationModule::resolveForProject($project);

        foreach ($modules as $module) {
            if ($module['code'] === $code) {
                return $module;
            }
        }

        abort(404, 'Modul tidak ditemukan.');
    }

    private function validateModule(Request $request, Project $project, ?string $code = null): array
    {
        $codeRule = ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'];

        if ($code === null) {
            // New custom module: must not collide with an existing row or a
            // catalog code, otherwise it would silently shadow a built-in.
            $codeRule[] = Rule::unique('valuation_modules')->where('project_id', $project->id);
            $codeRule[] = Rule::notIn(ValuationModuleCatalog::codes());
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => $codeRule,
            'valuation_method' => ['nullable', 'string', 'max:120'],
            'method_group' => ['nullable', Rule::in(array_keys(ValuationModuleCatalog::METHOD_GROUPS))],
            'service_category' => ['nullable', Rule::in(array_keys(ValuationModuleCatalog::SERVICE_CATEGORIES))],
            'subcategory' => ['nullable', 'string', 'max:200'],
            'formula_summary' => ['nullable', 'string', 'max:2000'],
            'input_variables' => ['nullable', 'string', 'max:2000'],
            'output_unit' => ['nullable', 'string', 'max:60'],
            'data_source' => ['nullable', 'string', 'max:200'],
            'status' => ['required', Rule::in(array_keys(ValuationModuleCatalog::STATUSES))],
            'description' => ['nullable', 'string', 'max:2000'],
            'show_on_project_detail' => ['boolean'],
        ], [
            'code.regex' => 'Kode modul hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'code.not_in' => 'Kode tersebut sudah dipakai modul bawaan. Gunakan kode lain.',
        ]);

        if ($code !== null) {
            unset($validated['code']);
        } else {
            $validated['code'] = Str::upper($validated['code']);
        }

        return $validated;
    }

    private function formOptions(): array
    {
        return [
            'methodGroups' => ValuationModuleCatalog::METHOD_GROUPS,
            'serviceCategories' => ValuationModuleCatalog::SERVICE_CATEGORIES,
            'statuses' => ValuationModuleCatalog::STATUSES,
        ];
    }
}
