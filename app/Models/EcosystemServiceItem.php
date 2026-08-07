<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcosystemServiceItem extends Model
{
    protected $fillable = [
        'valuation_index_id', 'land_cover_id', 'service_category', 'service_type',
        'item_name', 'productivity_value', 'productivity_unit', 'unit_price',
        'quantity', 'total_value', 'source_note', 'sort_order',
    ];

    protected $casts = [
        'productivity_value' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:4',
        'total_value' => 'decimal:2',
    ];

    public function valuationIndex(): BelongsTo
    {
        return $this->belongsTo(EcosystemValuationIndex::class, 'valuation_index_id');
    }

    public function landCover(): BelongsTo
    {
        return $this->belongsTo(EcosystemLandCover::class, 'land_cover_id');
    }
}
