<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RcmData extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'rcm_data';

    protected $fillable = [
        'project_id', 'record_code', 'service_category', 'asset_type', 'location',
        'quantity', 'unit', 'replacement_cost_per_unit', 'useful_life_years',
        'total_value', 'annual_value',
        'period_year', 'data_source', 'data_status', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'replacement_cost_per_unit' => 'decimal:2',
        'useful_life_years' => 'integer',
        'total_value' => 'decimal:2',
        'annual_value' => 'decimal:2',
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
