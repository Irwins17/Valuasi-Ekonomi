<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cost extends Model
{
    protected $fillable = [
        'project_id', 'category', 'subcategory', 'description',
        'value', 'payment_type', 'year_applied', 'pv_value', 'percentage_of_total',
        'activity_group', 'calculation_method', 'responsible_party', 'data_status',
        'calculation_notes', 'calculated_by'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'percentage_of_total' => 'decimal:2',
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
