<?php

namespace App\Livewire;

use App\Models\StatusNode;
use App\Models\StatusMetric;
use Illuminate\Support\Collection;
use Livewire\Component;

class NodeMetricsCard extends Component
{
    public int $nodeId;
    public ?string $selectedGroup = null;
    public ?string $selectedMetric = null;
    public int $timeRange = 24; // hours

    public function mount($nodeId)
    {
        $this->nodeId = $nodeId;
    }

    public function getNode()
    {
        return StatusNode::findOrFail($this->nodeId);
    }
    
    public function getNodeProperty()
    {
        return $this->getNode();
    }

    public function getMetricGroups(): Collection
    {
        return StatusMetric::where('status_node_id', $this->nodeId)
            ->select('group')
            ->distinct()
            ->whereNotNull('group')
            ->pluck('group');
    }
    
    public function getMetricGroupsProperty(): Collection
    {
        return $this->getMetricGroups();
    }

    public function getMetricNames(): Collection
    {
        $query = StatusMetric::where('status_node_id', $this->nodeId)
            ->select('name')
            ->distinct();
            
        if ($this->selectedGroup) {
            $query->where('group', $this->selectedGroup);
        }
            
        return $query->pluck('name');
    }
    
    public function getMetricNamesProperty(): Collection
    {
        return $this->getMetricNames();
    }

    public function getLatestMetrics(): Collection
    {
        $query = StatusMetric::where('status_node_id', $this->nodeId)
            ->orderByDesc('recorded_at');
            
        if ($this->selectedGroup) {
            $query->where('group', $this->selectedGroup);
        }
            
        if ($this->selectedMetric) {
            $query->where('name', $this->selectedMetric);
        }
            
        return $query->take(20)->get();
    }
    
    public function getLatestMetricsProperty(): Collection
    {
        return $this->getLatestMetrics();
    }

    public function getTimeSeriesData(): array
    {
        $query = StatusMetric::where('status_node_id', $this->nodeId)
            ->where('recorded_at', '>=', now()->subHours($this->timeRange));
            
        if ($this->selectedMetric) {
            $query->where('name', $this->selectedMetric);
        } else {
            // If no specific metric is selected, we can't create a time series
            return [];
        }
            
        $metrics = $query->orderBy('recorded_at')->get();
            
        $labels = $metrics->pluck('recorded_at')->map(function ($date) {
            return $date->format('H:i');
        })->toArray();
            
        $values = $metrics->pluck('value')->toArray();
            
        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
    
    public function getTimeSeriesDataProperty(): array
    {
        return $this->getTimeSeriesData();
    }

    public function resetFilters()
    {
        $this->selectedGroup = null;
        $this->selectedMetric = null;
    }

    public function render()
    {
        return view('livewire.node-metrics-card', [
            'node' => $this->getNode(),
            'metricGroups' => $this->getMetricGroups(),
            'metricNames' => $this->getMetricNames(),
            'latestMetrics' => $this->getLatestMetrics(),
            'timeSeriesData' => $this->getTimeSeriesData(),
        ]);
    }
}