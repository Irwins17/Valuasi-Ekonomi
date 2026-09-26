<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StakeholderValidation extends Model
{
    use SoftDeletes, Auditable;

    public const STATUSES = [
        'pending' => 'Menunggu',
        'disetujui' => 'Disetujui',
        'perlu_revisi' => 'Perlu Revisi',
    ];

    protected $fillable = [
        'project_id', 'stakeholder_name', 'stakeholder_role', 'validation_date',
        'feedback', 'status', 'recorded_by',
    ];

    protected $casts = [
        'validation_date' => 'date',
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
