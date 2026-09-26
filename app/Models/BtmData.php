<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BtmData extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'btm_data';

    protected $fillable = [
        'project_id', 'record_code', 'service_category',
        'source_study_title', 'source_study_location', 'source_study_year', 'source_value',
        'adjustment_factor', 'target_quantity', 'transferred_value', 'validity_notes',
        'period_year', 'data_source', 'data_status', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'source_study_year' => 'integer',
        'source_value' => 'decimal:2',
        'adjustment_factor' => 'decimal:4',
        'target_quantity' => 'decimal:4',
        'transferred_value' => 'decimal:2',
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
