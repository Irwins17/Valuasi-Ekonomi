<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcosystemLandCover extends Model
{
    protected $fillable = [
        'valuation_index_id', 'name', 'area_ha', 'notes', 'sort_order',
    ];

    protected $casts = [
        'area_ha' => 'decimal:2',
    ];

    public function valuationIndex(): BelongsTo
    {
        return $this->belongsTo(EcosystemValuationIndex::class, 'valuation_index_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EcosystemServiceItem::class, 'land_cover_id')->orderBy('sort_order');
    }
}
