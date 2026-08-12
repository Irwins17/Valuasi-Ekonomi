<?php

namespace App\Models;

use App\Support\ValuationModuleCatalog;
use App\Traits\Auditable;
use App\Models\EcosystemServiceRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Route;

class ValuationModule extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'project_id', 'code', 'name', 'valuation_method', 'method_group',
        'service_category', 'subcategory', 'formula_summary', 'input_variables',
        'output_unit', 'data_source', 'status', 'description',
        'show_on_project_detail', 'is_builtin', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'show_on_project_detail' => 'boolean',
        'is_builtin' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Every module the project should show, as plain arrays ready for Inertia.
     *
     * Built-ins come from the catalog and are overlaid with the project's
     * override row when one exists; custom modules are appended after them.
     * Record counts are read live from each module's own table, so a module
     * never advertises a stale count.
     *
     * Status resolution: an explicitly saved status always wins. A built-in
     * that has never been configured falls back to "aktif" once it holds at
     * least one record, which is what makes a freshly-filled module light up
     * without the user having to go and flip a switch.
     */
    public static function resolveForProject(Project $project): array
    {
        $overrides = static::where('project_id', $project->id)->get()->keyBy('code');
        $modules = [];

        foreach (ValuationModuleCatalog::all() as $entry) {
            $row = $overrides->get($entry['code']);
            $count = static::recordCount($project, $entry);

            $modules[] = [
                'id' => $row?->id,
                'code' => $entry['code'],
                'name' => $row?->name ?? $entry['name'],
                'description' => $row?->description ?? $entry['description'],
                'valuation_method' => $row?->valuation_method ?? $entry['valuation_method'],
                'method_group' => $row?->method_group ?? $entry['method_group'],
                'method_group_label' => ValuationModuleCatalog::METHOD_GROUPS[$row?->method_group ?? $entry['method_group']] ?? '-',
                'service_category' => $row?->service_category ?? $entry['service_category'],
                'subcategory' => $row?->subcategory ?? $entry['subcategory'],
                'formula_summary' => $row?->formula_summary ?? $entry['formula_summary'],
                'input_variables' => $row?->input_variables ?? $entry['input_variables'],
                'output_unit' => $row?->output_unit ?? $entry['output_unit'],
                'data_source' => $row?->data_source,
                'status' => $row?->status ?? ($count > 0 ? 'aktif' : 'draft'),
                'show_on_project_detail' => $row ? $row->show_on_project_detail : true,
                'record_count' => $count,
                'route_url' => static::moduleUrl($project, $entry['route'], $entry['routeParams'] ?? []),
                'is_builtin' => true,
                'is_advanced' => $entry['advanced'],
                'notes' => $entry['notes'],
            ];
        }

        foreach ($overrides as $row) {
            if ($row->is_builtin || ValuationModuleCatalog::find($row->code)) {
                continue;
            }

            $modules[] = [
                'id' => $row->id,
                'code' => $row->code,
                'name' => $row->name,
                'description' => $row->description,
                'valuation_method' => $row->valuation_method,
                'method_group' => $row->method_group,
                'method_group_label' => ValuationModuleCatalog::METHOD_GROUPS[$row->method_group] ?? '-',
                'service_category' => $row->service_category,
                'subcategory' => $row->subcategory,
                'formula_summary' => $row->formula_summary,
                'input_variables' => $row->input_variables,
                'output_unit' => $row->output_unit,
                'data_source' => $row->data_source,
                'status' => $row->status,
                'show_on_project_detail' => $row->show_on_project_detail,
                'record_count' => 0,
                'route_url' => null,
                'is_builtin' => false,
                'is_advanced' => false,
                'notes' => null,
            ];
        }

        return $modules;
    }

    /**
     * Counts a module's own records.
     *
     * The six ecosystem services share one table and are told apart by
     * `serviceKey`; every other module counts through a Project relation.
     * Modules whose tables do not exist yet declare neither and report zero.
     */
    private static function recordCount(Project $project, array $entry): int
    {
        if (! empty($entry['serviceKey'])) {
            return EcosystemServiceRecord::where('project_id', $project->id)
                ->service($entry['serviceKey'])
                ->count();
        }

        $relation = $entry['countRelation'] ?? null;

        if (! $relation || ! method_exists($project, $relation)) {
            return 0;
        }

        return $project->{$relation}()->count();
    }

    /**
     * Resolves a module's index URL, or null when its pages are not built
     * yet. Resolved server-side because Ziggy throws on unknown route names,
     * which would take the whole module list down with it.
     */
    private static function moduleUrl(Project $project, ?string $routeName, array $extraParams = []): ?string
    {
        if (! $routeName || ! Route::has($routeName)) {
            return null;
        }

        return route($routeName, [$project->id, ...$extraParams]);
    }
}
