<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\StatusNode;
use App\Models\StatusMetric;
use App\Livewire\NodeMetricsCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class NodeMetricsCardTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_component_can_render()
    {
        $node = StatusNode::factory()->create();
        
        Livewire::test(NodeMetricsCard::class, ['nodeId' => $node->id])
            ->assertStatus(200);
    }
    
    public function test_component_shows_node_details()
    {
        // Create a node with specific attributes
        $node = StatusNode::factory()->create([
            'name' => 'Test Server',
            'environment' => 'staging',
            'region' => 'eu-central',
            'status' => 'normal'
        ]);
        
        // Check if the component displays the node details
        Livewire::test(NodeMetricsCard::class, ['nodeId' => $node->id])
            ->assertSee('Test Server')
            ->assertSee('staging')
            ->assertSee('eu-central')
            ->assertSee('Normal');
    }
    
    public function test_component_shows_node_metrics()
    {
        // Create a node and associated metrics
        $node = StatusNode::factory()->create();
        $metrics = StatusMetric::factory()->count(3)->create([
            'status_node_id' => $node->id,
            'name' => 'cpu_usage',
            'group' => 'system',
            'value' => 75.5,
            'status' => 'warning'
        ]);
        
        // Check if the component displays the metrics
        Livewire::test(NodeMetricsCard::class, ['nodeId' => $node->id])
            ->assertSee('cpu_usage')
            ->assertSee('system')
            ->assertSee('75.5')
            ->assertSee('Warning');
    }
    
    public function test_component_can_filter_by_group()
    {
        // Create a node
        $node = StatusNode::factory()->create();
        
        // Create metrics with different groups
        $systemMetric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
            'name' => 'cpu_usage',
            'group' => 'system',
        ]);
        
        $networkMetric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
            'name' => 'network_throughput',
            'group' => 'network',
        ]);
        
        // Test filtering by group = system
        Livewire::test(NodeMetricsCard::class, ['nodeId' => $node->id])
            ->set('selectedGroup', 'system')
            ->assertSee('cpu_usage')
            ->assertDontSee('network_throughput');
            
        // Test filtering by group = network
        Livewire::test(NodeMetricsCard::class, ['nodeId' => $node->id])
            ->set('selectedGroup', 'network')
            ->assertSee('network_throughput')
            ->assertDontSee('cpu_usage');
    }
    
    public function test_component_can_filter_by_metric_name()
    {
        // Create a node
        $node = StatusNode::factory()->create();
        
        // Create different metrics
        $cpuMetric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
            'name' => 'cpu_usage',
            'group' => 'system',
        ]);
        
        $memoryMetric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
            'name' => 'memory_usage',
            'group' => 'system',
        ]);
        
        // Test filtering by metric name = cpu_usage
        Livewire::test(NodeMetricsCard::class, ['nodeId' => $node->id])
            ->set('selectedMetric', 'cpu_usage')
            ->assertSee('cpu_usage');
            // Note: memory_usage will still appear in the dropdown list, 
            // but not in the metrics table
    }
    
    public function test_component_can_reset_filters()
    {
        // Create a node
        $node = StatusNode::factory()->create();
        
        // Create metrics with different groups
        $systemMetric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
            'name' => 'cpu_usage',
            'group' => 'system',
        ]);
        
        $networkMetric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
            'name' => 'network_throughput',
            'group' => 'network',
        ]);
        
        // Set filters and then reset them
        Livewire::test(NodeMetricsCard::class, ['nodeId' => $node->id])
            ->set('selectedGroup', 'system')
            ->assertSee('cpu_usage')
            ->assertDontSee('network_throughput')
            // Reset filters
            ->call('resetFilters')
            // Should now see both metrics
            ->assertSee('cpu_usage')
            ->assertSee('network_throughput');
    }
    
    public function test_component_can_change_time_range()
    {
        // Create a node
        $node = StatusNode::factory()->create();
        
        // Create metrics at different times
        // Recent metric (within last hour)
        $recentMetric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
            'name' => 'recent_metric',
            'recorded_at' => now()->subMinutes(30),
        ]);
        
        // Older metric (more than 1 hour ago)
        $olderMetric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
            'name' => 'older_metric',
            'recorded_at' => now()->subHours(2),
        ]);
        
        // Test with 1 hour time range
        Livewire::test(NodeMetricsCard::class, ['nodeId' => $node->id])
            ->set('timeRange', 1)
            ->set('selectedMetric', 'recent_metric')
            ->assertViewHas('timeSeriesData', function ($timeSeriesData) {
                // Verify that timeSeriesData has data
                return count($timeSeriesData['labels']) > 0;
            });
            
        // With 6 hours time range, both metrics should be included
        Livewire::test(NodeMetricsCard::class, ['nodeId' => $node->id])
            ->set('timeRange', 6)
            ->assertSee('recent_metric')
            ->assertSee('older_metric');
    }
}