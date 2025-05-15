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
                $request->input('group')
            );

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
}
