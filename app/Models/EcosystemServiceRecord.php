<?php

namespace App\Models;

use App\Support\EcosystemServiceSchemas;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcosystemServiceRecord extends Model
{
    use SoftDeletes, Auditable;

    /**
     * `value_per_ha` and `total_value` stay fillable so the writer can pass
     * in what EconomicValuationCalculator::ecosystemServiceRecordValues()
     * returned — the model no longer computes them itself.
     */
    protected $fillable = [
        'project_id', 'service_key', 'service_category', 'record_code', 'location',
        'quantity_value', 'quantity_unit', 'unit_price', 'price_conversion',
        'area_ha', 'output_unit', 'value_per_ha', 'total_value',
        'period_year', 'data_source', 'data_collection_type', 'collection_method',
        'extra', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'quantity_value' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'price_conversion' => 'decimal:6',
        'area_ha' => 'decimal:4',
        'value_per_ha' => 'decimal:2',
        'total_value' => 'decimal:2',
        'period_year' => 'integer',
        'extra' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeService($query, string $serviceKey)
    {
        return $query->where('service_key', $serviceKey);
    }

    /** Quantity scaled by area — "Total Produksi" / "Debit Air" in the preview. */
    public function getQuantityAreaAttribute(): float
    {
        return (float) $this->quantity_value * (float) ($this->area_ha ?? 0);
    }

    public function getSchemaAttribute(): ?array
    {
        return EcosystemServiceSchemas::find($this->service_key);
    }
}
