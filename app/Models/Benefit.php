<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Benefit extends Model
{
    protected $fillable = [
        'project_id', 'category', 'subcategory', 'description',
        'value', 'annual_value', 'unit', 'period_year', 'pv_value',
        'ecosystem_service_group', 'method_used', 'data_source',
        'source_module', 'source_record_id', 'data_status', 'sample_size',
        'mean_value', 'percentage_of_tev', 'calculation_notes', 'calculated_by'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'mean_value' => 'decimal:2',
        'percentage_of_tev' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }
}
