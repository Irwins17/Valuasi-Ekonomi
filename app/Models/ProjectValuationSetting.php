<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The discounting assumptions a project's present values are computed under.
 *
 * Every figure that gets published depends on these, so they are stored per
 * project and shown on the detail page rather than being implied by the code.
 */
class ProjectValuationSetting extends Model
{
    use Auditable;

    /** Starting point for a project that has never been configured. */
    public const DEFAULT_DISCOUNT_RATE = 6.00;

    public const DEFAULT_ANALYSIS_PERIOD = 10;

    public const DEFAULT_CURRENCY = 'IDR';

    public const DEFAULT_EOP_VALUE_BASIS = 'net';

    public const EOP_VALUE_BASES = [
        'net' => 'Net EOP (setelah biaya produksi)',
        'gross' => 'Gross EOP (sebelum biaya produksi)',
    ];

    protected $fillable = [
        'project_id', 'base_year', 'discount_rate', 'analysis_period',
        'currency', 'start_year', 'end_year', 'eop_value_basis', 'updated_by',
    ];

    protected $casts = [
        'base_year' => 'integer',
        'discount_rate' => 'decimal:2',
        'analysis_period' => 'integer',
        'start_year' => 'integer',
        'end_year' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * An unsaved settings instance carrying the defaults.
     *
     * Returned for projects that have never been configured so callers can
     * treat settings as always present. The base year falls back to the
     * project's creation year, which makes the discount factor 1 for amounts
     * recorded without a year — leaving nominal totals unchanged.
     */
    public static function defaultFor(?Project $project = null): self
    {
        $baseYear = (int) ($project?->created_at?->year ?? now()->year);

        return new self([
            'project_id' => $project?->id,
            'base_year' => $baseYear,
            'discount_rate' => self::DEFAULT_DISCOUNT_RATE,
            'analysis_period' => self::DEFAULT_ANALYSIS_PERIOD,
            'currency' => self::DEFAULT_CURRENCY,
            'eop_value_basis' => self::DEFAULT_EOP_VALUE_BASIS,
        ]);
    }

    /** Discount rate as a fraction, e.g. 6.00 becomes 0.06. */
    public function discountRateFraction(): float
    {
        return (float) $this->discount_rate / 100;
    }
}
