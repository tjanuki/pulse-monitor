<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\DataAggregationService;

class HistoricalMetricsController extends Controller
{
    protected DataAggregationService $dataAggregationService;

    public function __construct(DataAggregationService $dataAggregationService)
    {
        $this->dataAggregationService = $dataAggregationService;
    }

    /**
     * Get trend data for a specific node and metric
     *
     * @param Request $request
     * @param int $nodeId
     * @param string $metricName
     * @return JsonResponse
     */
    public function getTrendData(Request $request, int $nodeId, string $metricName): JsonResponse
    {
        $request->validate([
            'period_type' => 'required|string|in:hourly,daily,weekly,monthly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $periodType = $request->input('period_type');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $trendData = $this->dataAggregationService->getTrendData(
            $nodeId,
            $metricName,
            $periodType,
            $startDate,
            $endDate
        );

        return response()->json([
            'data' => $trendData,
            'meta' => [
                'metric_name' => $metricName,
                'node_id' => $nodeId,
                'period_type' => $periodType,
                'start_date' => $startDate ? $startDate->toIso8601String() : null,
                'end_date' => $endDate ? $endDate->toIso8601String() : null,
                'data_points' => $trendData->count(),
            ],
        ]);
    }

    /**
     * Compare metrics between nodes
     *
     * @param Request $request
     * @param string $metricName
     * @return JsonResponse
     */
    public function compareNodes(Request $request, string $metricName): JsonResponse
    {
        $request->validate([
            'node_ids' => 'required|array',
            'node_ids.*' => 'integer|exists:status_nodes,id',
            'period_type' => 'required|string|in:hourly,daily,weekly,monthly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $nodeIds = $request->input('node_ids');
        $periodType = $request->input('period_type');
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $comparisonData = $this->dataAggregationService->compareNodes(
            $nodeIds,
            $metricName,
            $periodType,
            $startDate,
            $endDate
        );

        return response()->json([
            'data' => $comparisonData,
            'meta' => [
                'metric_name' => $metricName,
                'period_type' => $periodType,
                'start_date' => $startDate ? $startDate->toIso8601String() : null,
                'end_date' => $endDate ? $endDate->toIso8601String() : null,
            ],
        ]);
    }

    /**
     * Trigger aggregation of metrics for a specific period type
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function triggerAggregation(Request $request): JsonResponse
    {
        $request->validate([
            'period_type' => 'required|string|in:hourly,daily,weekly,monthly',
            'date' => 'nullable|date',
        ]);

        $periodType = $request->input('period_type');
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : null;

        $this->dataAggregationService->aggregateMetrics($periodType, $date);

        return response()->json([
            'message' => "Aggregation for {$periodType} data completed successfully",
            'date' => $date ? $date->toIso8601String() : now()->toIso8601String(),
        ]);
    }
}
