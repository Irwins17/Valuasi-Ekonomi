<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HpmData extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'hpm_data';

    protected $fillable = [
        'project_id', 'property_code', 'transaction_price', 'property_type', 'location',
        'bedrooms', 'land_area', 'building_area', 'building_age',
        'accessibility', 'crime_rate', 'school_quality',
        'air_quality_index', 'pollutant_concentration', 'noise_level', 'distance_green_space',
        'delta_env_quality', 'affected_units', 'data_source', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'transaction_price' => 'decimal:2',
        'land_area' => 'decimal:2',
        'building_area' => 'decimal:2',
        'air_quality_index' => 'decimal:2',
        'noise_level' => 'decimal:2',
        'distance_green_space' => 'decimal:2',
        'delta_env_quality' => 'decimal:4',
        'bedrooms' => 'integer',
        'building_age' => 'integer',
        'affected_units' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
