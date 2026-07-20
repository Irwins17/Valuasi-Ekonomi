<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EopData extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'eop_data';

    protected $fillable = [
        'project_id', 'commodity_name', 'production_before', 'production_after',
        'production_change', 'unit', 'market_price', 'total_value',
        'impact_type', 'recorded_by', 'notes'
    ];

    protected $casts = [
        'production_before'  => 'decimal:2',
        'production_after'   => 'decimal:2',
        'production_change'  => 'decimal:2',
        'market_price'       => 'decimal:2',
        'total_value'        => 'decimal:2',
    ];

    /**
     * Use bootEopData() naming convention so it doesn't conflict with
     * parent::boot() or the Auditable::bootAuditable() trait.
     */
    protected static function bootEopData(): void
    {
        static::saving(function ($model) {
            $model->production_change = $model->production_after - $model->production_before;
            $model->total_value       = $model->production_change * $model->market_price;
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
