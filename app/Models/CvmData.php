<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CvmData extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'cvm_data';

    protected $fillable = [
        'project_id', 'respondent_id', 'wtp', 'wtp_category', 'household_size',
        'household_income', 'income_category', 'respondent_location', 'education_level',
        'willing_to_pay', 'reason_if_unwilling', 'recorded_by', 'notes'
    ];

    protected $casts = [
        'wtp' => 'decimal:2',
        'household_income' => 'decimal:2',
        'willing_to_pay' => 'boolean',
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
