<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectAssumption extends Model
{
    use SoftDeletes, Auditable;

    public const STATUSES = [
        'valid' => 'Valid',
        'perlu_revisi' => 'Perlu Revisi',
        'tidak_valid' => 'Tidak Valid',
    ];

    protected $fillable = [
        'project_id', 'assumption_type', 'assumed_value', 'justification',
        'tested_result', 'status', 'tested_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by');
    }
}
