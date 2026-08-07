<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcosystemValuationIndex extends Model
{
    protected $fillable = [
        'project_id', 'index_number', 'name', 'notes', 'created_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function landCovers(): HasMany
    {
        return $this->hasMany(EcosystemLandCover::class, 'valuation_index_id')->orderBy('sort_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EcosystemServiceItem::class, 'valuation_index_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
