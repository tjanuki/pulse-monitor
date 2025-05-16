<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MetricsController extends Controller
{
    /**
     * The metrics service instance.
     *
     * @var \App\Services\MetricsService
     */
    protected $metricsService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\MetricsService  $metricsService
     * @return void
     */
    public function __construct(MetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }

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
            'timestamp' => 'nullable|date_format:Y-m-d H:i:s',
            'metadata' => 'nullable|array',
            'metadata.*.key' => 'required_with:metadata|string|max:255',
            'metadata.*.value' => 'required_with:metadata|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }

        try {
            // Process and store the metric using the service
            $metric = $this->metricsService->processMetric(
                $statusNode,
                $request->input('name'),
                (float) $request->input('value'),
                $request->input('group'),
                $request->input('timestamp'),
                $request->input('metadata')
            );

            return response()->json([
                'message' => 'Metric recorded successfully',
                'metric' => $metric,
                'status' => $metric->status,
                'threshold_info' => [
                    'has_threshold' => $metric->threshold_config !== null,
                    'warning_threshold' => $metric->threshold_config->warning_threshold ?? null,
                    'critical_threshold' => $metric->threshold_config->critical_threshold ?? null,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to record metric: ' . $e->getMessage(), [
                'node_id' => $statusNode->id,
                'node_name' => $statusNode->name,
                'metric_name' => $request->input('name'),
                'metric_value' => $request->input('value'),
                'exception' => get_class($e),
            ]);
            
            return response()->json([
                'error' => 'Failed to record metric',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Store multiple metrics in a single request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBatch(Request $request)
    {
        // The status node is attached to the request by the VerifyNodeApiKey middleware
        $statusNode = $request->status_node;

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'metrics' => 'required|array|min:1',
            'metrics.*.name' => 'required|string|max:255',
            'metrics.*.group' => 'nullable|string|max:255',
            'metrics.*.value' => 'required|numeric',
            'metrics.*.timestamp' => 'nullable|date_format:Y-m-d H:i:s',
            'metrics.*.metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }

        $results = [];
        $hasErrors = false;

        foreach ($request->input('metrics') as $metricData) {
            try {
                $metric = $this->metricsService->processMetric(
                    $statusNode,
                    $metricData['name'],
                    (float) $metricData['value'],
                    $metricData['group'] ?? null,
                    $metricData['timestamp'] ?? null,
                    $metricData['metadata'] ?? null
                );

                $results[] = [
                    'name' => $metricData['name'],
                    'status' => 'success',
                    'metric' => $metric,
                ];
            } catch (\Exception $e) {
                $hasErrors = true;
                Log::error('Failed to record metric in batch: ' . $e->getMessage(), [
                    'node_id' => $statusNode->id,
                    'metric_name' => $metricData['name'] ?? 'unknown',
                    'exception' => get_class($e),
                ]);

                $results[] = [
                    'name' => $metricData['name'] ?? 'unknown',
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => $hasErrors ? 'Some metrics failed to record' : 'All metrics recorded successfully',
            'results' => $results,
        ], $hasErrors ? 207 : 201); // 207 Multi-Status
    }
}
