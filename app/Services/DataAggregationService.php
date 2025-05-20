<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\StatusNode;
use App\Models\StatusMetric;
use App\Models\HistoricalMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class DataAggregationService
{
    /**
     * Aggregate metrics for a specific period type
     *
     * @param string $periodType hourly, daily, weekly, monthly
     * @param Carbon|null $date Date to aggregate for (defaults to now)
     * @return void
     */
    public function aggregateMetrics(string $periodType, ?Carbon $date = null): void
    {
        $date = $date ?? now();
        
        // Get period boundaries based on the period type
        [$periodStart, $periodEnd] = $this->getPeriodBoundaries($periodType, $date);
        
        // Get all status nodes
        $nodes = StatusNode::all();
        
        foreach ($nodes as $node) {
            // Get metrics for this node within the period
            $metrics = StatusMetric::where('status_node_id', $node->id)
                ->whereBetween('recorded_at', [$periodStart, $periodEnd])
                ->get();
                
            // Group metrics by name and group
            $groupedMetrics = $metrics->groupBy(function ($metric) {
                return $metric->name . '|' . $metric->group;
            });
            
            foreach ($groupedMetrics as $key => $metricsGroup) {
                [$metricName, $groupName] = explode('|', $key);
                
                // Skip if there are no metrics
                if ($metricsGroup->isEmpty()) {
                    continue;
                }
                
                // Calculate aggregates
                $minValue = $metricsGroup->min('value');
                $maxValue = $metricsGroup->max('value');
                $avgValue = $metricsGroup->avg('value');
                $count = $metricsGroup->count();
                
                // Create or update historical metric
                HistoricalMetric::updateOrCreate(
                    [
                        'status_node_id' => $node->id,
                        'metric_name' => $metricName,
                        'group' => $groupName ?: null,
                        'period_type' => $periodType,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                    ],
                    [
                        'min_value' => $minValue,
                        'max_value' => $maxValue,
                        'avg_value' => $avgValue,
                        'data_points_count' => $count,
                    ]
                );
            }
        }
    }
    
    /**
     * Get period boundaries based on period type
     *
     * @param string $periodType hourly, daily, weekly, monthly
     * @param Carbon $date
     * @return array
     */
    protected function getPeriodBoundaries(string $periodType, Carbon $date): array
    {
        return match ($periodType) {
            'hourly' => [
                $date->copy()->startOfHour(),
                $date->copy()->endOfHour(),
            ],
            'daily' => [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ],
            'weekly' => [
                $date->copy()->startOfWeek(),
                $date->copy()->endOfWeek(),
            ],
            'monthly' => [
                $date->copy()->startOfMonth(),
                $date->copy()->endOfMonth(),
            ],
            default => throw new \InvalidArgumentException("Invalid period type: {$periodType}"),
        };
    }
    
    /**
     * Get trend data for a specific node and metric
     *
     * @param int $nodeId
     * @param string $metricName
     * @param string $periodType hourly, daily, weekly, monthly
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return Collection
     */
    public function getTrendData(
        int $nodeId,
        string $metricName,
        string $periodType,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Collection {
        $startDate = $startDate ?? now()->subMonth();
        $endDate = $endDate ?? now();
        
        return HistoricalMetric::forNode($nodeId)
            ->forMetric($metricName)
            ->forPeriod($periodType)
            ->withinDateRange($startDate, $endDate)
            ->orderBy('period_start')
            ->get();
    }
    
    /**
     * Compare metrics between nodes
     *
     * @param array $nodeIds
     * @param string $metricName
     * @param string $periodType
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function compareNodes(
        array $nodeIds,
        string $metricName,
        string $periodType,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $startDate = $startDate ?? now()->subMonth();
        $endDate = $endDate ?? now();
        
        $result = [];
        
        foreach ($nodeIds as $nodeId) {
            $node = StatusNode::find($nodeId);
            if (!$node) continue;
            
            $trendData = $this->getTrendData($nodeId, $metricName, $periodType, $startDate, $endDate);
            
            $result[$nodeId] = [
                'node' => $node->toArray(),
                'data' => $trendData->toArray(),
                'averages' => [
                    'min' => $trendData->avg('min_value'),
                    'max' => $trendData->avg('max_value'),
                    'avg' => $trendData->avg('avg_value'),
                ],
            ];
        }
        
        return $result;
    }
    
    /**
     * Clean up old data based on retention policy
     *
     * @param int $hourlyRetentionDays
     * @param int $dailyRetentionDays
     * @param int $weeklyRetentionDays
     * @param int $monthlyRetentionDays
     * @return int Number of records deleted
     */
    public function cleanupOldData(
        int $hourlyRetentionDays = 2,
        int $dailyRetentionDays = 30,
        int $weeklyRetentionDays = 90,
        int $monthlyRetentionDays = 365
    ): int {
        $deleted = 0;
        
        // Delete hourly data
        $deleted += HistoricalMetric::where('period_type', 'hourly')
            ->where('period_start', '<', now()->subDays($hourlyRetentionDays))
            ->delete();
        
        // Delete daily data
        $deleted += HistoricalMetric::where('period_type', 'daily')
            ->where('period_start', '<', now()->subDays($dailyRetentionDays))
            ->delete();
        
        // Delete weekly data
        $deleted += HistoricalMetric::where('period_type', 'weekly')
            ->where('period_start', '<', now()->subDays($weeklyRetentionDays))
            ->delete();
        
        // Delete monthly data
        $deleted += HistoricalMetric::where('period_type', 'monthly')
            ->where('period_start', '<', now()->subDays($monthlyRetentionDays))
            ->delete();
        
        return $deleted;
    }
}