<?php

namespace App\Http\Controllers\Api;

use App\Models\Alert;
use Illuminate\Http\Request;
use App\Services\AlertsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AlertsController extends Controller
{
    protected AlertsService $alertsService;

    public function __construct(AlertsService $alertsService)
    {
        $this->alertsService = $alertsService;
    }

    /**
     * Get all unresolved alerts
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $alerts = $this->alertsService->getUnresolvedAlerts();
        
        return response()->json([
            'data' => $alerts,
            'meta' => [
                'total' => $alerts->count(),
                'critical_count' => $alerts->where('type', 'critical')->count(),
                'warning_count' => $alerts->where('type', 'warning')->count(),
            ],
        ]);
    }

    /**
     * Get alerts for a specific node
     *
     * @param int $nodeId
     * @return JsonResponse
     */
    public function forNode(int $nodeId): JsonResponse
    {
        $alerts = $this->alertsService->getUnresolvedAlertsForNode($nodeId);
        
        return response()->json([
            'data' => $alerts,
            'meta' => [
                'total' => $alerts->count(),
                'critical_count' => $alerts->where('type', 'critical')->count(),
                'warning_count' => $alerts->where('type', 'warning')->count(),
            ],
        ]);
    }

    /**
     * Resolve an alert
     *
     * @param int $alertId
     * @return JsonResponse
     */
    public function resolve(int $alertId): JsonResponse
    {
        $alert = Alert::findOrFail($alertId);
        
        if ($alert->isResolved()) {
            return response()->json([
                'message' => 'Alert is already resolved',
                'data' => $alert,
            ]);
        }
        
        $this->alertsService->resolveAlert($alert);
        
        return response()->json([
            'message' => 'Alert resolved successfully',
            'data' => $alert->fresh(),
        ]);
    }

    /**
     * Get alert details
     *
     * @param int $alertId
     * @return JsonResponse
     */
    public function show(int $alertId): JsonResponse
    {
        $alert = Alert::with(['statusNode', 'statusMetric'])->findOrFail($alertId);
        
        return response()->json([
            'data' => $alert,
        ]);
    }
}
