<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'code', 'name', 'description', 'location', 'province', 'latitude', 'longitude',
        'boundary_geojson', 'created_by', 'updated_by', 'status', 'started_at', 'ended_at',
        'tev', 'total_benefits', 'total_costs', 'bcr', 'notes'
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
        'tev' => 'decimal:2',
        'total_benefits' => 'decimal:2',
        'total_costs' => 'decimal:2',
        'bcr' => 'decimal:4',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'boundary_geojson' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function eopData(): HasMany
    {
        return $this->hasMany(EopData::class);
    }

    public function tcmData(): HasMany
    {
        return $this->hasMany(TcmData::class);
    }

    public function cvmData(): HasMany
    {
        return $this->hasMany(CvmData::class);
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(Benefit::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(Cost::class);
    }

    public function marketPrices(): HasMany
    {
        return $this->hasMany(MarketPrice::class);
    }

    public function ecosystemValuationIndices(): HasMany
    {
        return $this->hasMany(EcosystemValuationIndex::class)->orderBy('index_number');
    }

    public function getTotalBenefits()
    {
        return $this->benefits()->sum('value');
    }

    public function getTotalCosts()
    {
        return $this->costs()->sum('value');
    }

    public function calculateTEV()
    {
        $totalBenefits = $this->getTotalBenefits();
        $totalCosts = $this->getTotalCosts();
        $this->total_benefits = $totalBenefits;
        $this->total_costs = $totalCosts;
        $this->tev = $totalBenefits - $totalCosts;
        $this->bcr = $totalCosts > 0 ? $totalBenefits / $totalCosts : 0;
        return $this;
    }
}
