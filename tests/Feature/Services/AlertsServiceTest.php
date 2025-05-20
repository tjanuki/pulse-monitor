<?php

namespace Tests\Feature\Services;

use App\Models\Alert;
use App\Models\StatusNode;
use App\Models\StatusMetric;
use App\Models\Recommendation;
use App\Services\AlertsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AlertsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AlertsService $alertsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alertsService = app(AlertsService::class);
        Notification::fake();
    }
    
    /**
     * Test processing a metric that triggers a warning alert
     */
    public function testProcessMetricCreatesWarningAlert(): void
    {
        // Create test data
        $node = StatusNode::factory()->create();
        $metric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
            'name' => 'cpu_usage',
            'value' => 85,
            'status' => 'warning',
        ]);
        
        // Process the metric
        $alert = $this->alertsService->processMetric($metric);
        
        // Assert an alert was created
        $this->assertNotNull($alert);
        $this->assertEquals('warning', $alert->type);
        $this->assertEquals($node->id, $alert->status_node_id);
        $this->assertEquals($metric->id, $alert->status_metric_id);
        
        // Assert the alert is in the database
        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'type' => 'warning',
            'status_node_id' => $node->id,
            'status_metric_id' => $metric->id,
        ]);
    }
    
    /**
     * Test that recovery alerts are generated when a problem is resolved
     */
    public function testHandleRecoveryCreatesRecoveryAlert(): void
    {
        // Create test data
        $node = StatusNode::factory()->create();
        
        // First, create a problematic metric with an alert
        $problemMetric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
            'name' => 'memory_usage',
            'value' => 95,
            'status' => 'critical',
        ]);
        
        $alert = Alert::create([
            'status_node_id' => $node->id,
            'status_metric_id' => $problemMetric->id,
            'type' => 'critical',
            'message' => 'High memory usage',
            'context' => ['test' => true],
        ]);
        
        // Now, create a recovery metric
        $recoveryMetric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
            'name' => 'memory_usage',
            'value' => 50,
            'status' => 'ok',
        ]);
        
        // Process the recovery metric
        $recoveryAlert = $this->alertsService->processMetric($recoveryMetric);
        
        // Assert a recovery alert was created
        $this->assertNotNull($recoveryAlert);
        $this->assertEquals('recovery', $recoveryAlert->type);
        $this->assertEquals($node->id, $recoveryAlert->status_node_id);
        $this->assertEquals($recoveryMetric->id, $recoveryAlert->status_metric_id);
        
        // Assert the original alert was resolved
        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'type' => 'critical',
        ]);
        
        $updatedAlert = Alert::find($alert->id);
        $this->assertNotNull($updatedAlert->resolved_at);
    }
    
    /**
     * Test getting recommendations for a metric
     */
    public function testGetRecommendations(): void
    {
        // Create test data
        $recommendation1 = Recommendation::create([
            'trigger_metric' => 'cpu_usage',
            'condition' => 'above',
            'threshold_value' => 80,
            'title' => 'High CPU Usage',
            'description' => 'CPU usage is above normal levels.',
            'solution' => 'Check running processes and terminate unnecessary ones.',
            'is_active' => true,
        ]);
        
        $recommendation2 = Recommendation::create([
            'trigger_metric' => 'cpu_usage',
            'condition' => 'above',
            'threshold_value' => 90,
            'title' => 'Critical CPU Usage',
            'description' => 'CPU usage is at critical levels.',
            'solution' => 'Restart the service or add resources.',
            'is_active' => true,
        ]);
        
        // Create a metric that should trigger the first recommendation
        $metric = StatusMetric::factory()->create([
            'name' => 'cpu_usage',
            'value' => 85,
            'status' => 'warning',
        ]);
        
        // Get recommendations
        $recommendations = $this->alertsService->getRecommendations($metric);
        
        // Should trigger only the first recommendation
        $this->assertCount(1, $recommendations);
        $this->assertEquals($recommendation1->id, $recommendations->first()->id);
        
        // Now test with a higher value that should trigger both
        $metric->value = 95;
        $recommendations = $this->alertsService->getRecommendations($metric);
        
        $this->assertCount(2, $recommendations);
    }
    
    /**
     * Test resolving an alert
     */
    public function testResolveAlert(): void
    {
        // Create test data
        $node = StatusNode::factory()->create();
        $metric = StatusMetric::factory()->create([
            'status_node_id' => $node->id,
        ]);
        
        $alert = Alert::create([
            'status_node_id' => $node->id,
            'status_metric_id' => $metric->id,
            'type' => 'warning',
            'message' => 'Test warning alert',
            'context' => ['test' => true],
        ]);
        
        // Assert the alert is unresolved
        $this->assertNull($alert->resolved_at);
        $this->assertFalse($alert->isResolved());
        
        // Resolve the alert
        $this->alertsService->resolveAlert($alert);
        
        // Refresh the alert from the database
        $alert->refresh();
        
        // Assert the alert is now resolved
        $this->assertNotNull($alert->resolved_at);
        $this->assertTrue($alert->isResolved());
    }
}
