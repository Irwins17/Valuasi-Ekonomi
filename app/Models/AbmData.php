<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbmData extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'abm_data';

    /**
     * The three derived totals stay fillable so the writer can pass in what
     * EconomicValuationCalculator::abmRecordValues() returned — the model no
     * longer computes them itself.
     */
    protected $fillable = [
        'project_id', 'respondent_code', 'location', 'risk_type', 'exposure_condition',
        'defensive_action', 'defensive_goods', 'quantity', 'unit_price', 'time_cost',
        'medical_cost', 'sick_days', 'daily_wage', 'household_size', 'affected_population',
        'defensive_expenditure', 'lost_income', 'total_avoidance',
        'data_source', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'time_cost' => 'decimal:2',
        'medical_cost' => 'decimal:2',
        'sick_days' => 'decimal:2',
        'daily_wage' => 'decimal:2',
        'defensive_expenditure' => 'decimal:2',
        'lost_income' => 'decimal:2',
        'total_avoidance' => 'decimal:2',
        'household_size' => 'integer',
        'affected_population' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
