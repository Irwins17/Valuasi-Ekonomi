<?php

namespace App\Models;

use App\Services\Valuation\EconomicValuationCalculator;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'code', 'name', 'description', 'location', 'province', 'latitude', 'longitude',
        'boundary_geojson', 'created_by', 'updated_by', 'status', 'started_at', 'ended_at',
        'tev', 'total_benefits', 'total_costs', 'bcr', 'notes'
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
        'tev' => 'decimal:2',
        'total_benefits' => 'decimal:2',
        'total_costs' => 'decimal:2',
        'bcr' => 'decimal:4',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'boundary_geojson' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function eopData(): HasMany
    {
        return $this->hasMany(EopData::class);
    }

    public function duvData(): HasMany
    {
        return $this->hasMany(DuvData::class);
    }

    public function hpmData(): HasMany
    {
        return $this->hasMany(HpmData::class);
    }

    public function abmData(): HasMany
    {
        return $this->hasMany(AbmData::class);
    }

    public function ceData(): HasMany
    {
        return $this->hasMany(CeData::class);
    }

    public function ecosystemServiceRecords(): HasMany
    {
        return $this->hasMany(EcosystemServiceRecord::class);
    }

    public function tcmData(): HasMany
    {
        return $this->hasMany(TcmData::class);
    }

    public function cvmData(): HasMany
    {
        return $this->hasMany(CvmData::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(Benefit::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(Cost::class);
    }

    public function marketPrices(): HasMany
    {
        return $this->hasMany(MarketPrice::class);
    }

    public function ecosystemValuationIndices(): HasMany
    {
        return $this->hasMany(EcosystemValuationIndex::class)->orderBy('index_number');
    }

    public function valuationSetting(): HasOne
    {
        return $this->hasOne(ProjectValuationSetting::class);
    }

    /**
     * The project's discounting assumptions, always present.
     *
     * Projects created before this feature have no row, so a default instance
     * stands in. Callers never have to null-check, and an unconfigured
     * project discounts nothing — its base year is its creation year and
     * amounts without a year sit at n = 0.
     */
    public function getValuationSettingsAttribute(): ProjectValuationSetting
    {
        return $this->valuationSetting ?? ProjectValuationSetting::defaultFor($this);
    }

    /** Present value of all benefits, discounted to the base year. */
    public function getTotalBenefits()
    {
        return app(EconomicValuationCalculator::class)->calculateDiscountedTEV(
            $this->discountableBenefits(),
            $this->valuation_settings,
        );
    }

    /** Present value of all costs, discounted to the base year. */
    public function getTotalCosts()
    {
        return app(EconomicValuationCalculator::class)->sumPresentValue(
            $this->discountableCosts(),
            $this->valuation_settings,
        );
    }

    /**
     * Recomputes the project's headline figures on a present-value basis.
     *
     * TEV here is the net present value: discounted benefits less discounted
     * costs. Rows recorded without a year discount by a factor of 1, so a
     * project that has never set a year or a rate keeps exactly the totals it
     * had before present values existed.
     *
     * `bcr` is left null when there are no costs — the ratio is undefined
     * then, and storing 0 would read as "no benefit at all".
     */
    public function calculateTEV()
    {
        $calculator = app(EconomicValuationCalculator::class);
        $settings = $this->valuation_settings;

        $pvBenefits = $calculator->calculateDiscountedTEV($this->discountableBenefits(), $settings);
        $pvCosts = $calculator->sumPresentValue($this->discountableCosts(), $settings);

        $this->total_benefits = $pvBenefits;
        $this->total_costs = $pvCosts;
        $this->tev = $calculator->calculateNPV($pvBenefits, $pvCosts);
        $this->bcr = $calculator->calculateBCR($pvBenefits, $pvCosts);

        $this->storeRowLevelPresentValues($calculator, $settings);

        return $this;
    }

    /** @return array<int, array{value: float, year: int|null}> */
    private function discountableBenefits(): array
    {
        return $this->benefits()->get(['id', 'value', 'period_year'])
            ->map(fn ($b) => ['value' => (float) $b->value, 'year' => $b->period_year])
            ->all();
    }

    /** @return array<int, array{value: float, year: int|null}> */
    private function discountableCosts(): array
    {
        return $this->costs()->get(['id', 'value', 'year_applied'])
            ->map(fn ($c) => ['value' => (float) $c->value, 'year' => $c->year_applied])
            ->all();
    }

    /**
     * Writes each row's own present value back, so a published total can be
     * traced to the discounted figures it was built from rather than only to
     * the nominal amounts.
     */
    private function storeRowLevelPresentValues(
        EconomicValuationCalculator $calculator,
        ProjectValuationSetting $settings,
    ): void {
        foreach ($this->benefits()->get(['id', 'value', 'period_year']) as $benefit) {
            $benefit->updateQuietly(['pv_value' => $calculator->calculatePV(
                (float) $benefit->value,
                (int) ($benefit->period_year ?? $settings->base_year),
                (int) $settings->base_year,
                (float) $settings->discount_rate,
            )]);
        }

        foreach ($this->costs()->get(['id', 'value', 'year_applied']) as $cost) {
            $cost->updateQuietly(['pv_value' => $calculator->calculatePV(
                (float) $cost->value,
                (int) ($cost->year_applied ?? $settings->base_year),
                (int) $settings->base_year,
                (float) $settings->discount_rate,
            )]);
        }
    }
}
