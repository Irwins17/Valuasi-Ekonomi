<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvironmentalCoefficient extends Model
{
    protected $fillable = [
        'name', 'code', 'description', 'value', 'unit', 'type',
        'source', 'year', 'approved_by'
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'year' => 'integer',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
