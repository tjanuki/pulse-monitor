<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThresholdConfiguration;
use App\Services\MetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ThresholdConfigurationsController extends Controller
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
     * Display a listing of the threshold configurations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $thresholds = ThresholdConfiguration::all();
        
        return response()->json([
            'thresholds' => $thresholds,
        ]);
    }
    
    /**
     * Store a newly created threshold configuration in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'metric_name' => 'required|string|max:255',
            'warning_threshold' => 'nullable|numeric',
            'critical_threshold' => 'nullable|numeric',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }
        
        try {
            $threshold = $this->metricsService->setThresholds(
                $request->input('metric_name'),
                $request->input('warning_threshold'),
                $request->input('critical_threshold')
            );
            
            return response()->json([
                'message' => 'Threshold configuration created successfully',
                'threshold' => $threshold,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create threshold configuration: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to create threshold configuration',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Display the specified threshold configuration.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $threshold = ThresholdConfiguration::find($id);
        
        if (!$threshold) {
            return response()->json([
                'error' => 'Threshold configuration not found',
            ], 404);
        }
        
        return response()->json([
            'threshold' => $threshold,
        ]);
    }
    
    /**
     * Update the specified threshold configuration in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $threshold = ThresholdConfiguration::find($id);
        
        if (!$threshold) {
            return response()->json([
                'error' => 'Threshold configuration not found',
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'warning_threshold' => 'nullable|numeric',
            'critical_threshold' => 'nullable|numeric',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }
        
        try {
            $threshold = $this->metricsService->setThresholds(
                $threshold->metric_name,
                $request->input('warning_threshold'),
                $request->input('critical_threshold')
            );
            
            return response()->json([
                'message' => 'Threshold configuration updated successfully',
                'threshold' => $threshold,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update threshold configuration: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to update threshold configuration',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Remove the specified threshold configuration from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $threshold = ThresholdConfiguration::find($id);
        
        if (!$threshold) {
            return response()->json([
                'error' => 'Threshold configuration not found',
            ], 404);
        }
        
        try {
            $threshold->delete();
            
            return response()->json([
                'message' => 'Threshold configuration deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete threshold configuration: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to delete threshold configuration',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}