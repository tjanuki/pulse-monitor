<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\StatusNode;
use Illuminate\Support\Facades\Http;
use App\Services\DataAggregationService;

class TrendVisualization extends Component
{
    public $nodeId;
    public $metricName;
    public $periodType = 'hourly';
    public $startDate;
    public $endDate;
    public $chartData = [];
    public $availableMetrics = [];
    public $isLoading = false;

    protected $dataAggregationService;

    public function boot(DataAggregationService $dataAggregationService)
    {
        $this->dataAggregationService = $dataAggregationService;
    }

    public function mount($nodeId = null)
    {
        $this->nodeId = $nodeId;
        $this->startDate = now()->subDay()->format('Y-m-d H:i');
        $this->endDate = now()->format('Y-m-d H:i');

        if ($this->nodeId) {
            $this->loadAvailableMetrics();
        }
    }

    public function loadAvailableMetrics()
    {
        if (!$this->nodeId) return;

        $metrics = \App\Models\StatusMetric::where('status_node_id', $this->nodeId)
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        $this->availableMetrics = $metrics;

        if (count($metrics) > 0 && !$this->metricName) {
            $this->metricName = $metrics[0];
        }
    }

    public function updatedNodeId()
    {
        $this->loadAvailableMetrics();
        $this->chartData = [];
    }

    public function updatedMetricName()
    {
        $this->loadTrendData();
    }

    public function updatedPeriodType()
    {
        $this->loadTrendData();
    }

    public function updatedStartDate()
    {
        $this->loadTrendData();
    }

    public function updatedEndDate()
    {
        $this->loadTrendData();
    }

    public function loadTrendData()
    {
        if (!$this->nodeId || !$this->metricName) {
            $this->chartData = [];
            return;
        }

        $this->isLoading = true;

        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);

        $trendData = $this->dataAggregationService->getTrendData(
            $this->nodeId,
            $this->metricName,
            $this->periodType,
            $startDate,
            $endDate
        );

        $this->chartData = $this->formatChartData($trendData);
        $this->isLoading = false;
    }

    protected function formatChartData($trendData)
    {
        $labels = [];
        $minValues = [];
        $maxValues = [];
        $avgValues = [];

        foreach ($trendData as $item) {
            $labels[] = Carbon::parse($item->period_start)->format('M j, H:i');
            $minValues[] = $item->min_value;
            $maxValues[] = $item->max_value;
            $avgValues[] = $item->avg_value;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Minimum',
                    'data' => $minValues,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Average',
                    'data' => $avgValues,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
                [
                    'label' => 'Maximum',
                    'data' => $maxValues,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
            ],
        ];
    }

    public function render()
    {
        $nodes = StatusNode::orderBy('name')->get();
        
        return view('livewire.trend-visualization', [
            'nodes' => $nodes,
        ]);
    }
}
