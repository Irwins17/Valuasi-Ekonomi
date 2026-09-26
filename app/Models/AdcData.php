<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdcData extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'adc_data';

    protected $fillable = [
        'project_id', 'record_code', 'service_category', 'damage_type', 'location',
        'protected_area', 'damage_cost_per_unit', 'event_probability', 'avoided_cost',
        'period_year', 'data_source', 'data_status', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'protected_area' => 'decimal:4',
        'damage_cost_per_unit' => 'decimal:2',
        'event_probability' => 'decimal:4',
        'avoided_cost' => 'decimal:2',
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
