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
        'ticket_cost', 'time_cost', 'travel_time_hours', 'time_value_per_hour',
        'total_travel_cost', 'visit_frequency', 'consumer_surplus',
        'origin_location', 'respondent_category', 'income', 'age', 'education',
        'substitute_site', 'recorded_by', 'notes',
    ];

    protected $casts = [
        'distance'            => 'decimal:2',
        'transportation_cost' => 'decimal:2',
        'ticket_cost'         => 'decimal:2',
        'time_cost'           => 'decimal:2',
        'travel_time_hours'   => 'decimal:2',
        'time_value_per_hour' => 'decimal:2',
        'total_travel_cost'   => 'decimal:2',
        'consumer_surplus'    => 'decimal:2',
        'income'              => 'decimal:2',
        'age'                 => 'integer',
    ];

    /**
     * TC = transport + tiket + (nilai waktu × waktu tempuh).
     *
     * `time_cost` is derived whenever both the hourly value of time and the
     * travel time are present; otherwise whatever was entered for it stands,
     * which is what keeps rows recorded before those two fields existed
     * summing to the same total as before.
     *
     * `consumer_surplus` here is annual travel expenditure (TC × frekuensi),
     * not the welfare measure — the real CS comes from the demand model in
     * TcmAnalysis, which is why the UI labels this column "pengeluaran".
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->travel_time_hours !== null && $model->time_value_per_hour !== null) {
                $model->time_cost = (float) $model->travel_time_hours * (float) $model->time_value_per_hour;
            }

            $model->total_travel_cost = (float) $model->transportation_cost
                + (float) $model->ticket_cost
                + (float) $model->time_cost;

            $model->consumer_surplus = max(0, $model->total_travel_cost * $model->visit_frequency);
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
