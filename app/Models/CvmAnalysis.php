<?php

namespace App\Models;

use App\Services\Valuation\Math\Distributions;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CvmAnalysis extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'cvm_analyses';

    protected $fillable = [
        'project_id', 'analysis_code', 'scenario', 'model', 'coefficient_source',
        'question_type', 'bid_value', 'respondent_count', 'yes_count', 'no_count',
        'alpha', 'beta_bid', 'coef_income', 'coef_education', 'coef_age',
        'mean_covariates', 'target_population', 'converged', 'log_likelihood',
        'diagnostics', 'period_year', 'data_source', 'data_collection_type', 'collection_method',
        'notes', 'created_by',
    ];

    protected $casts = [
        'bid_value' => 'decimal:2',
        'alpha' => 'float',
        'beta_bid' => 'float',
        'coef_income' => 'float',
        'coef_education' => 'float',
        'coef_age' => 'float',
        'mean_covariates' => 'array',
        'probability_yes' => 'float',
        'mean_wtp' => 'decimal:2',
        'total_wtp' => 'decimal:2',
        'log_likelihood' => 'float',
        'converged' => 'boolean',
        'diagnostics' => 'array',
        'respondent_count' => 'integer',
        'yes_count' => 'integer',
        'no_count' => 'integer',
        'target_population' => 'integer',
    ];

    /**
     * P(Ya), Mean WTP and Total WTP, all derived from the stored coefficients.
     *
     * Mean WTP = (α + γk·X̄k) / β is undefined when β is zero, and negative
     * when the sign pattern says higher bids raise acceptance — neither is a
     * usable willingness to pay. In those cases the derived values stay at
     * zero and `wtpIsValid()` explains why, rather than the page printing a
     * confident but meaningless rupiah figure.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $model) {
            $covariateTerm = collect($model->mean_covariates ?? [])
                ->map(fn ($v) => (float) $v)
                ->sum();

            $linear = $model->alpha + $covariateTerm - $model->beta_bid * (float) $model->bid_value;

            $model->probability_yes = $model->model === 'probit'
                ? Distributions::normalCdf($linear)
                : 1 / (1 + exp(-max(-30, min(30, $linear))));

            if ($model->wtpIsValid()) {
                $model->mean_wtp = ($model->alpha + $covariateTerm) / $model->beta_bid;
                $model->total_wtp = $model->mean_wtp * $model->target_population;
            } else {
                $model->mean_wtp = 0;
                $model->total_wtp = 0;
            }
        });
    }

    public function wtpIsValid(): bool
    {
        if (abs($this->beta_bid) < 1e-12) {
            return false;
        }

        $covariateTerm = collect($this->mean_covariates ?? [])->map(fn ($v) => (float) $v)->sum();

        return ($this->alpha + $covariateTerm) / $this->beta_bid > 0;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
