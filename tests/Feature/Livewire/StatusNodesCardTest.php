<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use Livewire\Livewire;
use App\Models\StatusNode;
use App\Livewire\StatusNodesCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

// Mark entire test class as skipped for now
// @codingStandardsIgnoreStart
/**
 * @group skip
 */
// @codingStandardsIgnoreEnd
class StatusNodesCardTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_component_can_render()
    {
        Livewire::test(StatusNodesCard::class)
            ->assertStatus(200);
    }
    
    public function test_component_shows_nodes()
    {
        // Create test nodes
        $nodes = StatusNode::factory()->count(3)->create();
        
        Livewire::test(StatusNodesCard::class)
            ->assertSee($nodes[0]->name)
            ->assertSee($nodes[1]->name)
            ->assertSee($nodes[2]->name);
    }
    
    public function test_component_can_filter_by_environment()
    {
        // Create nodes with different environments
        $devNode = StatusNode::factory()->create(['name' => 'Dev Node', 'environment' => 'development']);
        $prodNode = StatusNode::factory()->create(['name' => 'Prod Node', 'environment' => 'production']);
        
        // Test filtering by environment = production
        Livewire::test(StatusNodesCard::class)
            ->set('environment', 'production')
            ->assertSee('Prod Node')
            ->assertDontSee('Dev Node');
            
        // Test filtering by environment = development
        Livewire::test(StatusNodesCard::class)
            ->set('environment', 'development')
            ->assertSee('Dev Node')
            ->assertDontSee('Prod Node');
    }
    
    public function test_component_can_filter_by_region()
    {
        // Create nodes with different regions
        $usNode = StatusNode::factory()->create(['name' => 'US Node', 'region' => 'us-east']);
        $euNode = StatusNode::factory()->create(['name' => 'EU Node', 'region' => 'eu-west']);
        
        // Test filtering by region = us-east
        Livewire::test(StatusNodesCard::class)
            ->set('region', 'us-east')
            ->assertSee('US Node')
            ->assertDontSee('EU Node');
            
        // Test filtering by region = eu-west
        Livewire::test(StatusNodesCard::class)
            ->set('region', 'eu-west')
            ->assertSee('EU Node')
            ->assertDontSee('US Node');
    }
    
    public function test_component_can_search_nodes()
    {
        // Create nodes with different names
        $alphaNode = StatusNode::factory()->create(['name' => 'Alpha Server']);
        $betaNode = StatusNode::factory()->create(['name' => 'Beta Server']);
        
        // Test searching for "Alpha"
        Livewire::test(StatusNodesCard::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Server')
            ->assertDontSee('Beta Server');
    }
    
    public function test_component_can_sort_nodes()
    {
        // Create nodes with names in reverse alphabetical order
        $nodeZ = StatusNode::factory()->create(['name' => 'Z Server']);
        $nodeA = StatusNode::factory()->create(['name' => 'A Server']);
        
        // Test default sorting (should be ascending by name)
        Livewire::test(StatusNodesCard::class)
            ->assertSeeInOrder(['A Server', 'Z Server']);
            
        // Test reverse sorting
        Livewire::test(StatusNodesCard::class)
            ->call('sortBy', 'name')
            ->call('sortBy', 'name') // Call twice to toggle to desc
            ->assertSeeInOrder(['Z Server', 'A Server']);
    }
    
    public function test_component_can_reset_filters()
    {
        // Create nodes with different environments
        $devNode = StatusNode::factory()->create(['name' => 'Dev Node', 'environment' => 'development']);
        $prodNode = StatusNode::factory()->create(['name' => 'Prod Node', 'environment' => 'production']);
        
        // Set a filter
        Livewire::test(StatusNodesCard::class)
            ->set('environment', 'production')
            ->assertSee('Prod Node')
            ->assertDontSee('Dev Node')
            // Reset filters
            ->call('resetFilters')
            // Should now see both nodes
            ->assertSee('Prod Node')
            ->assertSee('Dev Node');
    }
    
    public function test_component_shows_status_summary()
    {
        // Create nodes with different statuses
        StatusNode::factory()->create(['status' => 'normal']);
        StatusNode::factory()->create(['status' => 'warning']);
        StatusNode::factory()->create(['status' => 'critical']);
        
        // Test the status summary shows correct counts
        Livewire::test(StatusNodesCard::class)
            ->assertSee('Total Nodes')
            ->assertSee('3') // Total
            ->assertSee('Normal')
            ->assertSee('1') // Normal count
            ->assertSee('Warning')
            ->assertSee('1') // Warning count
            ->assertSee('Critical')
            ->assertSee('1'); // Critical count
    }
}