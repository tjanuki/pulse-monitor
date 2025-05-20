<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\HistoricalMetricFactory;

class HistoricalMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_node_id',
        'metric_name',
        'group',
        'min_value',
        'max_value',
        'avg_value',
        'period_type',
        'period_start',
        'period_end',
        'data_points_count',
    ];

    protected $casts = [
        'min_value' => 'float',
        'max_value' => 'float',
        'avg_value' => 'float',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'data_points_count' => 'integer',
    ];

    public function statusNode(): BelongsTo
    {
        return $this->belongsTo(StatusNode::class);
    }

    public function scopeForNode($query, $nodeId)
    {
        return $query->where('status_node_id', $nodeId);
    }

    public function scopeForMetric($query, $metricName)
    {
        return $query->where('metric_name', $metricName);
    }

    public function scopeForPeriod($query, $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('period_start', [$startDate, $endDate]);
    }
    
    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return HistoricalMetricFactory::new();
    }
}
