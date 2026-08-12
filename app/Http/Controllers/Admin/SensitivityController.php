<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectValuationSetting;
use App\Services\Valuation\EconomicValuationCalculator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Sensitivity analysis over a project's valuation.
 *
 * Every figure here comes from EconomicValuationCalculator, the same one the
 * project page uses. That matters more than it sounds: this controller used to
 * carry its own BCR formula and its own single-period discounting
 * (value / (1 + r) regardless of year), so the same project could report one
 * BCR on its detail page and a different one here.
 *
 * The three inputs shift the scenario, not the arithmetic:
 *  - price_adjustment moves benefit amounts,
 *  - inflation_rate moves cost amounts,
 *  - discount_rate replaces the project's own rate for the adjusted run,
 *    which is the whole point of testing sensitivity to it.
 */
class SensitivityController extends Controller
{
    public function __construct(private readonly EconomicValuationCalculator $calculator) {}

    public function index(): Response
    {
        $projects = Project::where('status', '!=', 'draft')
            ->whereHas('benefits')
            ->whereHas('costs')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Sensitivity/Index', ['projects' => $projects]);
    }

    public function simulate(Request $request)
    {
        $input = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'inflation_rate' => ['nullable', 'numeric', 'min:-100', 'max:1000'],
            'price_adjustment' => ['nullable', 'numeric', 'min:-100', 'max:1000'],
            'discount_rate' => ['nullable', 'numeric', 'gt:-100', 'max:100'],
        ]);

        $project = Project::with(['benefits', 'costs', 'valuationSetting'])->findOrFail($input['project_id']);
        $settings = $project->valuation_settings;

        $inflation = (float) ($input['inflation_rate'] ?? 0) / 100;
        $priceAdjustment = (float) ($input['price_adjustment'] ?? 0) / 100;

        $benefits = $project->benefits->map(fn ($b) => ['value' => (float) $b->value, 'year' => $b->period_year])->all();
        $costs = $project->costs->map(fn ($c) => ['value' => (float) $c->value, 'year' => $c->year_applied])->all();

        $original = $this->scenario($benefits, $costs, $settings);

        // The simulated rate replaces the project's own; leaving it out means
        // "keep the project's assumption and vary only the amounts".
        $adjustedSettings = $this->settingsWithRate(
            $settings,
            array_key_exists('discount_rate', $input) && $input['discount_rate'] !== null
                ? (float) $input['discount_rate']
                : (float) $settings->discount_rate,
        );

        $adjusted = $this->scenario(
            $this->scaleAmounts($benefits, $priceAdjustment),
            $this->scaleAmounts($costs, $inflation),
            $adjustedSettings,
        );

        return response()->json([
            'original' => $original,
            'adjusted' => $adjusted,
            'changes' => [
                'tev_pct' => $this->percentChange($original['tev'], $adjusted['tev']),
                'bcr_pct' => $this->percentChange($original['bcr'], $adjusted['bcr']),
            ],
            'assumptions' => [
                'base_year' => (int) $settings->base_year,
                'original_discount_rate' => (float) $settings->discount_rate,
                'adjusted_discount_rate' => (float) $adjustedSettings->discount_rate,
                'currency' => $settings->currency,
            ],
        ]);
    }

    /**
     * One scenario's headline figures, all discounted to the base year.
     *
     * @param  array<int, array{value: float, year: int|null}>  $benefits
     * @param  array<int, array{value: float, year: int|null}>  $costs
     */
    private function scenario(array $benefits, array $costs, ProjectValuationSetting $settings): array
    {
        $pvBenefits = $this->calculator->calculateDiscountedTEV($benefits, $settings);
        $pvCosts = $this->calculator->sumPresentValue($costs, $settings);
        $bcr = $this->calculator->calculateBCR($pvBenefits, $pvCosts);

        return [
            'tev' => round($this->calculator->calculateNPV($pvBenefits, $pvCosts), 2),
            'benefits' => round($pvBenefits, 2),
            'costs' => round($pvCosts, 2),
            // Null travels through to the client rather than becoming 0, which
            // would read as "no benefit" instead of "no costs to divide by".
            'bcr' => $bcr === null ? null : round($bcr, 4),
        ];
    }

    /** @param  array<int, array{value: float, year: int|null}>  $items */
    private function scaleAmounts(array $items, float $factor): array
    {
        return array_map(
            fn ($item) => [...$item, 'value' => $item['value'] * (1 + $factor)],
            $items,
        );
    }

    /**
     * An unsaved copy of the project's assumptions with a different rate, so
     * the simulation never touches what is stored.
     */
    private function settingsWithRate(ProjectValuationSetting $settings, float $rate): ProjectValuationSetting
    {
        return new ProjectValuationSetting([
            'project_id' => $settings->project_id,
            'base_year' => $settings->base_year,
            'discount_rate' => $rate,
            'analysis_period' => $settings->analysis_period,
            'currency' => $settings->currency,
            'eop_value_basis' => $settings->eop_value_basis,
        ]);
    }

    /** Percentage movement between two figures, or null when undefined. */
    private function percentChange(?float $from, ?float $to): ?float
    {
        if ($from === null || $to === null || $from == 0.0) {
            return null;
        }

        return round(($to - $from) / abs($from) * 100, 2);
    }
}
