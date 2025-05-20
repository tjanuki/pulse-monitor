<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\StatusNode;
use App\Models\StatusMetric;
use App\Services\DataAggregationService;

class NodesComparison extends Component
{
    public $selectedNodeIds = [];
    public $availableMetrics = [];
    public $selectedMetric = '';
    public $periodType = 'daily';
    public $startDate;
    public $endDate;
    public $comparisonData = [];
    public $isLoading = false;

    protected $dataAggregationService;

    public function boot(DataAggregationService $dataAggregationService)
    {
        $this->dataAggregationService = $dataAggregationService;
    }

    public function mount()
    {
        $this->startDate = now()->subWeek()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        
        // Load available metrics for comparison
        $this->loadAvailableMetrics();
    }

    public function loadAvailableMetrics()
    {
        // Get metrics that are common across multiple nodes
        $this->availableMetrics = StatusMetric::select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        if (count($this->availableMetrics) > 0 && !$this->selectedMetric) {
            $this->selectedMetric = $this->availableMetrics[0];
        }
    }

    public function updatedSelectedNodeIds()
    {
        if (count($this->selectedNodeIds) >= 2) {
            $this->compareNodes();
        } else {
            $this->comparisonData = [];
        }
    }

    public function updatedSelectedMetric()
    {
        if (count($this->selectedNodeIds) >= 2) {
            $this->compareNodes();
        }
    }

    public function updatedPeriodType()
    {
        if (count($this->selectedNodeIds) >= 2) {
            $this->compareNodes();
        }
    }

    public function updatedStartDate()
    {
        if (count($this->selectedNodeIds) >= 2) {
            $this->compareNodes();
        }
    }

    public function updatedEndDate()
    {
        if (count($this->selectedNodeIds) >= 2) {
            $this->compareNodes();
        }
    }

    public function compareNodes()
    {
        if (empty($this->selectedNodeIds) || count($this->selectedNodeIds) < 2 || empty($this->selectedMetric)) {
            $this->comparisonData = [];
            return;
        }

        $this->isLoading = true;

        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);

        $comparisonData = $this->dataAggregationService->compareNodes(
            $this->selectedNodeIds,
            $this->selectedMetric,
            $this->periodType,
            $startDate,
            $endDate
        );

        $this->comparisonData = $this->formatComparisonData($comparisonData);
        $this->isLoading = false;
    }

    protected function formatComparisonData($data)
    {
        $formattedData = [
            'labels' => [],
            'datasets' => [],
            'summary' => [],
        ];
        
        // Get all unique labels (timestamps) from all nodes
        $allLabels = [];
        foreach ($data as $nodeId => $nodeData) {
            foreach ($nodeData['data'] as $point) {
                $timestamp = Carbon::parse($point['period_start'])->format('M j, H:i');
                if (!in_array($timestamp, $allLabels)) {
                    $allLabels[] = $timestamp;
                }
            }
        }
        
        // Sort labels chronologically
        sort($allLabels);
        $formattedData['labels'] = $allLabels;
        
        // Add a dataset for each node
        $colorPalette = [
            '#3b82f6', // blue
            '#10b981', // green
            '#ef4444', // red
            '#f59e0b', // amber
            '#8b5cf6', // purple
            '#ec4899', // pink
        ];
        
        $colorIndex = 0;
        foreach ($data as $nodeId => $nodeData) {
            $nodeName = $nodeData['node']['name'];
            $environment = $nodeData['node']['environment'];
            $displayName = "{$nodeName} ({$environment})";
            
            // Map data points to the common labels
            $dataPoints = array_fill(0, count($allLabels), null);
            $dataMap = [];
            
            foreach ($nodeData['data'] as $point) {
                $timestamp = Carbon::parse($point['period_start'])->format('M j, H:i');
                $dataMap[$timestamp] = $point['avg_value'];
            }
            
            foreach ($allLabels as $index => $label) {
                $dataPoints[$index] = $dataMap[$label] ?? null;
            }
            
            // Get color for this dataset
            $color = $colorPalette[$colorIndex % count($colorPalette)];
            $colorIndex++;
            
            $formattedData['datasets'][] = [
                'label' => $displayName,
                'data' => $dataPoints,
                'borderColor' => $color,
                'backgroundColor' => str_replace(')', ', 0.1)', str_replace('rgb', 'rgba', $color)),
                'fill' => false,
                'tension' => 0.1,
            ];
            
            // Add summary statistics
            $formattedData['summary'][] = [
                'nodeId' => $nodeId,
                'nodeName' => $displayName,
                'min' => round($nodeData['averages']['min'], 2),
                'max' => round($nodeData['averages']['max'], 2),
                'avg' => round($nodeData['averages']['avg'], 2),
                'color' => $color,
            ];
        }
        
        return $formattedData;
    }

    public function render()
    {
        return view('livewire.nodes-comparison', [
            'nodes' => StatusNode::orderBy('name')->get(),
        ]);
    }
}
