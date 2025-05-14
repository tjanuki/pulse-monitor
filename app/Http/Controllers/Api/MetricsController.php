<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StatusMetric;
use App\Models\ThresholdConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MetricsController extends Controller
{
    /**
     * Store a newly received metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // The status node is attached to the request by the VerifyNodeApiKey middleware
        $statusNode = $request->status_node;

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'group' => 'nullable|string|max:255',
            'value' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }

        // Determine status based on threshold configurations
        $metricStatus = $this->determineMetricStatus(
            $request->input('name'),
            $request->input('value')
        );

        try {
            // Create the new metric
            $metric = StatusMetric::create([
                'status_node_id' => $statusNode->id,
                'name' => $request->input('name'),
                'group' => $request->input('group'),
                'value' => $request->input('value'),
                'status' => $metricStatus,
                'recorded_at' => now(),
            ]);

            // Update node status if needed
            $this->updateNodeStatus($statusNode);

            return response()->json([
                'message' => 'Metric recorded successfully',
                'metric' => $metric,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to record metric: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to record metric',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Determine the status of a metric based on configured thresholds.
     *
     * @param  string  $metricName
     * @param  float  $value
     * @return string
     */
    protected function determineMetricStatus(string $metricName, float $value): string
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
     * Update the status of a node based on its metrics.
     *
     * @param  \App\Models\StatusNode  $statusNode
     * @return void
     */
    protected function updateNodeStatus($statusNode): void
    {
        // Get the worst status from the node's metrics (critical > warning > normal)
        $worstStatus = 'normal';
        
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

        // Update the node status if it's different
        if ($statusNode->status !== $worstStatus) {
            $statusNode->update(['status' => $worstStatus]);
        }
    }
}
