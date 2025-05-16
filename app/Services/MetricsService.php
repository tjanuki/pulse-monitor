<?php

namespace App\Services;

use App\Models\StatusNode;
use App\Models\StatusMetric;
use App\Models\ThresholdConfiguration;
use Illuminate\Support\Facades\Log;

class MetricsService
{
    /**
     * Process and store a metric for a status node.
     *
     * @param StatusNode $statusNode
     * @param string $name
     * @param float $value
     * @param string|null $group
     * @param string|null $timestamp
     * @param array|null $metadata
     * @return StatusMetric
     */
    public function processMetric(StatusNode $statusNode, string $name, float $value, ?string $group = null, ?string $timestamp = null, ?array $metadata = null): StatusMetric
    {
        // Determine status based on thresholds
        $status = $this->determineMetricStatus($name, $value);
        
        // Store the metric
        $metric = $this->storeMetric($statusNode, $name, $value, $status, $group, $timestamp, $metadata);
        
        // Update node status
        $this->updateNodeStatus($statusNode);
        
        return $metric;
    }
    
    /**
     * Determine the status of a metric based on configured thresholds.
     *
     * @param string $metricName
     * @param float $value
     * @return string
     */
    public function determineMetricStatus(string $metricName, float $value): string
    {
        $thresholds = ThresholdConfiguration::where('metric_name', $metricName)->first();
        
        if (!$thresholds) {
            return 'normal';
        }
        
        if ($thresholds->critical_threshold !== null && 
            $value >= $thresholds->critical_threshold) {
            return 'critical';
        }
        
        if ($thresholds->warning_threshold !== null && 
            $value >= $thresholds->warning_threshold) {
            return 'warning';
        }
        
        return 'normal';
    }
    
    /**
     * Store a metric in the database.
     *
     * @param StatusNode $statusNode
     * @param string $name
     * @param float $value
     * @param string $status
     * @param string|null $group
     * @param string|null $timestamp
     * @param array|null $metadata
     * @return StatusMetric
     */
    public function storeMetric(StatusNode $statusNode, string $name, float $value, string $status, ?string $group = null, ?string $timestamp = null, ?array $metadata = null): StatusMetric
    {
        try {
            $metric = StatusMetric::create([
                'status_node_id' => $statusNode->id,
                'name' => $name,
                'group' => $group,
                'value' => $value,
                'status' => $status,
                'recorded_at' => $timestamp ? new \DateTime($timestamp) : now(),
                'metadata' => $metadata ? json_encode($metadata) : null,
            ]);
            
            // Attach the threshold configuration for reference in the controller
            $metric->threshold_config = ThresholdConfiguration::where('metric_name', $name)->first();
            
            return $metric;
        } catch (\Exception $e) {
            Log::error('Failed to store metric: ' . $e->getMessage(), [
                'node_id' => $statusNode->id,
                'metric_name' => $name,
                'value' => $value,
                'group' => $group,
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Update the status of a node based on its metrics.
     *
     * @param StatusNode $statusNode
     * @return void
     */
    public function updateNodeStatus(StatusNode $statusNode): void
    {
        $worstStatus = $this->determineNodeStatus($statusNode);
        
        // Update the node status if it's different
        if ($statusNode->status !== $worstStatus) {
            $statusNode->update(['status' => $worstStatus]);
            
            Log::info("Status node {$statusNode->name} status updated to {$worstStatus}");
        }
    }
    
    /**
     * Determine the overall status of a node based on its recent metrics.
     *
     * @param StatusNode $statusNode
     * @return string
     */
    public function determineNodeStatus(StatusNode $statusNode): string
    {
        // Default status if no metrics or all are normal
        $worstStatus = 'normal';
        
        // Get the recent metrics that have warning or critical status
        $metrics = $statusNode->metrics()
            ->whereIn('status', ['warning', 'critical'])
            ->orderByDesc('recorded_at')
            ->take(10)
            ->get();
        
        foreach ($metrics as $metric) {
            if ($metric->status === 'critical') {
                $worstStatus = 'critical';
                break; // Critical is the worst status, no need to check further
            } elseif ($metric->status === 'warning') {
                $worstStatus = 'warning';
            }
        }
        
        return $worstStatus;
    }
    
    /**
     * Set or update threshold configuration for a metric.
     *
     * @param string $metricName
     * @param float|null $warningThreshold
     * @param float|null $criticalThreshold
     * @return ThresholdConfiguration
     */
    public function setThresholds(string $metricName, ?float $warningThreshold = null, ?float $criticalThreshold = null): ThresholdConfiguration
    {
        return ThresholdConfiguration::updateOrCreate(
            ['metric_name' => $metricName],
            [
                'warning_threshold' => $warningThreshold,
                'critical_threshold' => $criticalThreshold,
            ]
        );
    }
    
    /**
     * Get all metrics for a node, optionally filtered by group and/or name.
     *
     * @param StatusNode $statusNode
     * @param string|null $group
     * @param string|null $name
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNodeMetrics(StatusNode $statusNode, ?string $group = null, ?string $name = null, int $limit = 100)
    {
        $query = $statusNode->metrics()->orderByDesc('recorded_at');
        
        if ($group) {
            $query->where('group', $group);
        }
        
        if ($name) {
            $query->where('name', $name);
        }
        
        return $query->take($limit)->get();
    }
}