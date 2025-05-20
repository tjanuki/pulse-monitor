<?php

namespace Tests\Feature\Api;

use App\Models\Alert;
use App\Models\User;
use App\Models\StatusNode;
use App\Models\StatusMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AlertsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }
    
    /**
     * Test retrieving unresolved alerts
     */
    public function testGetUnresolvedAlerts(): void
    {
        // Create test data
        $node = StatusNode::factory()->create();
        $metric = StatusMetric::factory()->create([
            'status_node_id' => $node->id
        ]);
        
        // Create alerts
        Alert::create([
            'status_node_id' => $node->id,
            'status_metric_id' => $metric->id,
            'type' => 'warning',
            'message' => 'Test warning alert',
            'context' => ['test' => true],
        ]);
        
        Alert::create([
            'status_node_id' => $node->id,
            'status_metric_id' => $metric->id,
            'type' => 'critical',
            'message' => 'Test critical alert',
            'context' => ['test' => true],
        ]);
        
        // Create a resolved alert which shouldn't be returned
        Alert::create([
            'status_node_id' => $node->id,
            'status_metric_id' => $metric->id,
            'type' => 'warning',
            'message' => 'Test resolved alert',
            'context' => ['test' => true],
            'resolved_at' => now(),
        ]);
        
        // Test API endpoint
        $response = $this->getJson('/api/alerts');
        
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.critical_count', 1)
            ->assertJsonPath('meta.warning_count', 1);
    }
    
    /**
     * Test retrieving alerts for a specific node
     */
    public function testGetAlertsForNode(): void
    {
        // Create test data
        $node1 = StatusNode::factory()->create();
        $node2 = StatusNode::factory()->create();
        
        $metric1 = StatusMetric::factory()->create([
            'status_node_id' => $node1->id
        ]);
        
        $metric2 = StatusMetric::factory()->create([
            'status_node_id' => $node2->id
        ]);
        
        // Create alerts for both nodes
        Alert::create([
            'status_node_id' => $node1->id,
            'status_metric_id' => $metric1->id,
            'type' => 'warning',
            'message' => 'Test warning alert for node 1',
            'context' => ['test' => true],
        ]);
        
        Alert::create([
            'status_node_id' => $node2->id,
            'status_metric_id' => $metric2->id,
            'type' => 'critical',
            'message' => 'Test critical alert for node 2',
            'context' => ['test' => true],
        ]);
        
        // Test API endpoint for node 1
        $response = $this->getJson("/api/nodes/{$node1->id}/alerts");
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status_node_id', $node1->id);
    }
    
    /**
     * Test resolving an alert
     */
    public function testResolveAlert(): void
    {
        // Create test data
        $node = StatusNode::factory()->create();
        $metric = StatusMetric::factory()->create([
            'status_node_id' => $node->id
        ]);
        
        // Create an alert
        $alert = Alert::create([
            'status_node_id' => $node->id,
            'status_metric_id' => $metric->id,
            'type' => 'warning',
            'message' => 'Test warning alert',
            'context' => ['test' => true],
        ]);
        
        // Test API endpoint
        $response = $this->postJson("/api/alerts/{$alert->id}/resolve");
        
        $response->assertStatus(200)
            ->assertJsonPath('message', 'Alert resolved successfully')
            ->assertJsonPath('data.id', $alert->id);
        
        // Verify the alert was resolved in the database
        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'resolved_at' => now()->toDateTimeString(),
        ]);
    }
    
    /**
     * Test getting alert details
     */
    public function testGetAlertDetails(): void
    {
        // Create test data
        $node = StatusNode::factory()->create();
        $metric = StatusMetric::factory()->create([
            'status_node_id' => $node->id
        ]);
        
        // Create an alert
        $alert = Alert::create([
            'status_node_id' => $node->id,
            'status_metric_id' => $metric->id,
            'type' => 'warning',
            'message' => 'Test warning alert',
            'context' => ['test' => true],
        ]);
        
        // Test API endpoint
        $response = $this->getJson("/api/alerts/{$alert->id}");
        
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $alert->id)
            ->assertJsonPath('data.status_node_id', $node->id)
            ->assertJsonPath('data.status_metric_id', $metric->id)
            ->assertJsonPath('data.type', 'warning')
            ->assertJsonPath('data.message', 'Test warning alert');
    }
}
