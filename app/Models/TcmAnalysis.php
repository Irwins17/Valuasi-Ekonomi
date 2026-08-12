<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TcmAnalysis extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'tcm_analyses';

    protected $fillable = [
        'project_id', 'analysis_code', 'site_name', 'regression_model',
        'coefficient_source', 'respondent_count', 'total_visitors', 'mean_travel_cost',
        'beta_0', 'beta_1', 'coef_income', 'coef_age', 'coef_education', 'coef_substitute',
        'estimation_method', 'converged', 'log_likelihood', 'dispersion_alpha',
        'diagnostics', 'period_year', 'data_source', 'notes', 'created_by',
    ];

    protected $casts = [
        'beta_0' => 'float',
        'beta_1' => 'float',
        'coef_income' => 'float',
        'coef_age' => 'float',
        'coef_education' => 'float',
        'coef_substitute' => 'float',
        'mean_travel_cost' => 'decimal:2',
        'consumer_surplus' => 'decimal:2',
        'recreation_value' => 'decimal:2',
        'log_likelihood' => 'float',
        'dispersion_alpha' => 'float',
        'converged' => 'boolean',
        'diagnostics' => 'array',
        'respondent_count' => 'integer',
        'total_visitors' => 'integer',
        'period_year' => 'integer',
    ];

    /**
     * CS per individu = −1 / β₁, and the site's recreation value is that
     * surplus times the annual visitor count.
     *
     * The identity only holds for a downward-sloping demand curve. A β₁ that
     * is zero or positive means travel cost did not reduce visits in this
     * sample, so −1/β₁ is either undefined or negative; rather than storing a
     * nonsensical surplus the values are left at zero and `surplusIsValid()`
     * reports why, which the UI surfaces instead of a number.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $model) {
            if ($model->beta_1 < 0) {
                $model->consumer_surplus = -1 / $model->beta_1;
                $model->recreation_value = $model->consumer_surplus * $model->total_visitors;
            } else {
                $model->consumer_surplus = 0;
                $model->recreation_value = 0;
            }
        });
    }

    public function surplusIsValid(): bool
    {
        return $this->beta_1 < 0;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
