<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ThresholdConfiguration;
use Illuminate\Support\Facades\Validator;

class AlertThresholdsConfig extends Component
{
    public $thresholds = [];
    public $editingThresholdId = null;
    public $showCreateForm = false;

    // Form properties
    public $metric_name = '';
    public $warning_threshold = '';
    public $critical_threshold = '';

    public function mount()
    {
        $this->loadThresholds();
    }

    public function loadThresholds()
    {
        $this->thresholds = ThresholdConfiguration::orderBy('metric_name')->get()->toArray();
    }

    public function startEditing($thresholdId)
    {
        $this->editingThresholdId = $thresholdId;
        $threshold = collect($this->thresholds)->firstWhere('id', $thresholdId);
        
        if ($threshold) {
            $this->metric_name = $threshold['metric_name'];
            $this->warning_threshold = $threshold['warning_threshold'];
            $this->critical_threshold = $threshold['critical_threshold'];
        }
    }

    public function cancelEditing()
    {
        $this->resetForm();
        $this->editingThresholdId = null;
        $this->showCreateForm = false;
    }

    public function showCreateThresholdForm()
    {
        $this->resetForm();
        $this->showCreateForm = true;
    }

    public function saveThreshold()
    {
        if ($this->editingThresholdId) {
            $this->updateThreshold();
        } else {
            $this->createThreshold();
        }
    }

    public function createThreshold()
    {
        $validated = $this->validateThreshold();
        
        ThresholdConfiguration::create($validated);
        
        $this->resetForm();
        $this->showCreateForm = false;
        $this->loadThresholds();
        
        session()->flash('message', 'Threshold configuration created successfully.');
    }

    public function updateThreshold()
    {
        $validated = $this->validateThreshold();
        
        $threshold = ThresholdConfiguration::find($this->editingThresholdId);
        $threshold->update($validated);
        
        $this->resetForm();
        $this->editingThresholdId = null;
        $this->loadThresholds();
        
        session()->flash('message', 'Threshold configuration updated successfully.');
    }

    public function deleteThreshold($thresholdId)
    {
        $threshold = ThresholdConfiguration::find($thresholdId);
        $threshold->delete();
        
        $this->loadThresholds();
        
        session()->flash('message', 'Threshold configuration deleted successfully.');
    }

    protected function validateThreshold()
    {
        return Validator::make([
            'metric_name' => $this->metric_name,
            'warning_threshold' => $this->warning_threshold,
            'critical_threshold' => $this->critical_threshold,
        ], [
            'metric_name' => 'required|string|max:255',
            'warning_threshold' => 'required|numeric',
            'critical_threshold' => 'required|numeric',
        ])->validate();
    }

    protected function resetForm()
    {
        $this->metric_name = '';
        $this->warning_threshold = '';
        $this->critical_threshold = '';
    }

    public function render()
    {
        return view('livewire.alert-thresholds-config');
    }
}
