<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TcmData extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'tcm_data';

    protected $fillable = [
        'project_id', 'respondent_id', 'distance', 'transportation_cost',
        'time_cost', 'total_travel_cost', 'visit_frequency', 'consumer_surplus',
        'origin_location', 'respondent_category', 'recorded_by', 'notes'
    ];

    protected $casts = [
        'distance'           => 'decimal:2',
        'transportation_cost'=> 'decimal:2',
        'time_cost'          => 'decimal:2',
        'total_travel_cost'  => 'decimal:2',
        'consumer_surplus'   => 'decimal:2',
    ];

    /**
     * Use bootTcmData() naming convention to avoid boot() conflict with traits.
     */
    protected static function bootTcmData(): void
    {
        static::saving(function ($model) {
            $model->total_travel_cost = $model->transportation_cost + $model->time_cost;
            $model->consumer_surplus  = max(0, $model->total_travel_cost * $model->visit_frequency);
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
