<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CeData extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'ce_data';

    protected $fillable = [
        'project_id', 'respondent_code', 'location', 'age', 'education', 'income',
        'scenario_title', 'choice_set', 'alternative_a', 'alternative_b', 'status_quo',
        'chosen_alternative', 'attribute_1', 'attribute_1_level', 'attribute_2',
        'attribute_2_level', 'attribute_3', 'attribute_3_level', 'cost_attribute',
        'data_source', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'income' => 'decimal:2',
        'cost_attribute' => 'decimal:2',
        'age' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
