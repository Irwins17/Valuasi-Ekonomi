<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EopData extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'eop_data';

    protected $fillable = [
        'project_id', 'service_category', 'commodity_name', 'product_type',
        'production_before', 'production_after', 'production_change', 'unit',
        'market_price', 'production_cost', 'total_value', 'net_value',
        'area_ha', 'period_year', 'data_source', 'data_collection_type', 'collection_method',
        'impact_type', 'recorded_by', 'notes',
    ];

    protected $casts = [
        'production_before'  => 'decimal:2',
        'production_after'   => 'decimal:2',
        'production_change'  => 'decimal:2',
        'market_price'       => 'decimal:2',
        'production_cost'    => 'decimal:2',
        'total_value'        => 'decimal:2',
        'net_value'          => 'decimal:2',
        'area_ha'            => 'decimal:4',
        'period_year'        => 'integer',
    ];

    /**
     * Derived columns — production_change, total_value and net_value — are
     * computed by EconomicValuationCalculator::eopRecordValues() and passed
     * in by whoever writes the row. The formula deliberately does not live
     * here: keeping it in the calculator is what stops a stored figure and a
     * form preview from drifting apart.
     *
     * `total_value` remains the gross ΔQ × price it has always been, so no
     * previously stored figure changes meaning; `net_value` subtracts the
     * production cost and equals the gross whenever no cost is recorded.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
