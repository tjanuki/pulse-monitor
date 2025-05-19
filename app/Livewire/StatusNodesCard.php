<?php

namespace App\Livewire;

use App\Models\StatusNode;
use Illuminate\Support\Collection;
use Livewire\Component;

class StatusNodesCard extends Component
{
    public ?string $environment = null;
    public ?string $region = null;
    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    public function mount()
    {
        // Initialize with empty filter
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getEnvironments(): Collection
    {
        return StatusNode::select('environment')
            ->distinct()
            ->whereNotNull('environment')
            ->pluck('environment');
    }
    
    // For computed property syntax
    public function getEnvironmentsProperty(): Collection
    {
        return $this->getEnvironments();
    }

    public function getRegions(): Collection
    {
        return StatusNode::select('region')
            ->distinct()
            ->whereNotNull('region')
            ->pluck('region');
    }
    
    // For computed property syntax
    public function getRegionsProperty(): Collection
    {
        return $this->getRegions();
    }

    public function getNodes(): Collection
    {
        $query = StatusNode::query();

        // Apply filters
        if ($this->environment) {
            $query->where('environment', $this->environment);
        }

        if ($this->region) {
            $query->where('region', $this->region);
        }

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->get();
    }
    
    // For computed property syntax
    public function getNodesProperty(): Collection
    {
        return $this->getNodes();
    }

    public function getStatusSummary(): array
    {
        $nodes = $this->getNodes();

        return [
            'total' => $nodes->count(),
            'normal' => $nodes->where('status', 'normal')->count(),
            'warning' => $nodes->where('status', 'warning')->count(),
            'critical' => $nodes->where('status', 'critical')->count(),
        ];
    }
    
    // For computed property syntax
    public function getStatusSummaryProperty(): array
    {
        return $this->getStatusSummary();
    }

    public function resetFilters()
    {
        $this->environment = null;
        $this->region = null;
        $this->search = '';
    }

    public function render()
    {
        return view('livewire.status-nodes-card', [
            'nodes' => $this->getNodes(),
            'environments' => $this->getEnvironments(),
            'regions' => $this->getRegions(),
            'statusSummary' => $this->getStatusSummary(),
        ]);
    }
}