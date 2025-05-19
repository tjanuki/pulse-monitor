<?php

namespace App\Http\Controllers;

use App\Models\StatusNode;
use App\Models\StatusMetric;
use App\Models\ThresholdConfiguration;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get summary statistics for quick overview
        $stats = [
            'totalNodes' => StatusNode::count(),
            'criticalNodes' => StatusNode::where('status', 'critical')->count(),
            'warningNodes' => StatusNode::where('status', 'warning')->count(),
            'normalNodes' => StatusNode::where('status', 'normal')->count(),
            'totalMetrics' => StatusMetric::count(),
            'uniqueMetricNames' => StatusMetric::select('name')->distinct()->count(),
            'configuredThresholds' => ThresholdConfiguration::count()
        ];
        
        // Get recently reported metrics
        $recentMetrics = StatusMetric::with('node')
            ->orderByDesc('recorded_at')
            ->take(10)
            ->get();
            
        // Get nodes with critical status
        $criticalNodes = StatusNode::where('status', 'critical')
            ->orderByDesc('last_seen_at')
            ->take(5)
            ->get();
            
        return view('dashboard.index', [
            'stats' => $stats,
            'recentMetrics' => $recentMetrics,
            'criticalNodes' => $criticalNodes
        ]);
    }
    
    /**
     * Display detailed information for a specific node.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function nodeDetails($id)
    {
        $node = StatusNode::findOrFail($id);
        
        return view('dashboard.node-details', [
            'node' => $node
        ]);
    }
}