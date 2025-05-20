<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'environment',
        'region',
        'api_key',
        'status',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the metrics associated with this status node.
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(StatusMetric::class);
    }
    
    /**
     * Get the latest metrics for each unique name.
     */
    public function latestMetrics(): HasMany
    {
        return $this->hasMany(StatusMetric::class)
            ->orderByDesc('recorded_at')
            ->take(10);
    }
    
    /**
     * Get the alerts for this status node.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
    
    /**
     * Get the unresolved alerts for this status node.
     */
    public function unresolvedAlerts(): HasMany
    {
        return $this->hasMany(Alert::class)->whereNull('resolved_at');
    }
    
    /**
     * Get the historical metrics for this status node.
     */
    public function historicalMetrics(): HasMany
    {
        return $this->hasMany(HistoricalMetric::class);
    }
}
