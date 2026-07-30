<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketPrice extends Model
{
    protected $fillable = [
        'project_id', 'commodity_name', 'unit', 'price', 'year', 'source', 'approved_by', 'notes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'year' => 'integer',
    ];

    /**
     * Needed so `is_global` reaches the React frontend's JSON props
     * (Blade could call the accessor directly; JSON serialization can't).
     */
    protected $appends = ['is_global'];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Proyek pemilik harga ini. Null berarti harga umum/global.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getIsGlobalAttribute(): bool
    {
        return is_null($this->project_id);
    }

    /**
     * Hanya harga umum/global (tidak terikat proyek manapun).
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('project_id');
    }

    /**
     * Harga yang berlaku saat menggarap sebuah proyek:
     * harga spesifik proyek tersebut + harga umum/global sebagai fallback.
     */
    public function scopeForProject(Builder $query, int $projectId): Builder
    {
        return $query->where(function (Builder $q) use ($projectId) {
            $q->where('project_id', $projectId)->orWhereNull('project_id');
        });
    }
}
