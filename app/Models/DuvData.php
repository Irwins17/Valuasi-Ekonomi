<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DuvData extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'duv_data';

    /**
     * `gross_value` and `net_value` are derived, but stay fillable so the
     * writer can pass in what EconomicValuationCalculator::duvRecordValues()
     * returned — the model no longer computes them itself.
     */
    protected $fillable = [
        'project_id', 'record_code', 'service_category', 'goods_type', 'location',
        'quantity', 'unit', 'market_price', 'production_cost',
        'gross_value', 'net_value',
        'period_year', 'data_source', 'data_status', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'market_price' => 'decimal:2',
        'production_cost' => 'decimal:2',
        'gross_value' => 'decimal:2',
        'net_value' => 'decimal:2',
        'period_year' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
