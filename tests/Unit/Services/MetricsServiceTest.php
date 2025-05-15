<?php

namespace Tests\Unit\Services;

use App\Models\StatusNode;
use App\Models\StatusMetric;
use App\Models\ThresholdConfiguration;
use App\Services\MetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricsServiceTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * @var \App\Services\MetricsService
     */
    protected $metricsService;
    
    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->metricsService = new MetricsService();
    }
    
    /**
     * Test determining metric status with no thresholds.
     *
     * @return void
     */
    public function testDetermineMetricStatusWithNoThresholds(): void
    {
        $status = $this->metricsService->determineMetricStatus('cpu_usage', 75.0);
        
        $this->assertEquals('normal', $status);
    }
    
    /**
     * Test determining metric status with warning threshold.
     *
     * @return void
     */
    public function testDetermineMetricStatusWithWarningThreshold(): void
    {
        ThresholdConfiguration::create([
            'metric_name' => 'cpu_usage',
            'warning_threshold' => 70.0,
            'critical_threshold' => 90.0,
        ]);
        
        $normalStatus = $this->metricsService->determineMetricStatus('cpu_usage', 65.0);
        $warningStatus = $this->metricsService->determineMetricStatus('cpu_usage', 75.0);
        $criticalStatus = $this->metricsService->determineMetricStatus('cpu_usage', 95.0);
        
        $this->assertEquals('normal', $normalStatus);
        $this->assertEquals('warning', $warningStatus);
        $this->assertEquals('critical', $criticalStatus);
    }
    
    /**
     * Test storing a metric.
     *
     * @return void
     */
    public function testStoreMetric(): void
    {
        $node = StatusNode::create([
            'name' => 'test-node',
            'environment' => 'testing',
            'region' => 'us-west',
            'api_key' => 'test-api-key',
            'status' => 'normal',
        ]);
        
        $metric = $this->metricsService->storeMetric(
            $node,
            'memory_usage',
            45.2,
            'normal',
            'system'
        );
        
        $this->assertDatabaseHas('status_metrics', [
            'status_node_id' => $node->id,
            'name' => 'memory_usage',
            'group' => 'system',
            'value' => 45.2,
            'status' => 'normal',
        ]);
        
        $this->assertEquals('memory_usage', $metric->name);
        $this->assertEquals('system', $metric->group);
        $this->assertEquals(45.2, $metric->value);
        $this->assertEquals('normal', $metric->status);
    }
    
    /**
     * Test determining node status.
     *
     * @return void
     */
    public function testDetermineNodeStatus(): void
    {
        $node = StatusNode::create([
            'name' => 'test-node',
            'environment' => 'testing',
            'region' => 'us-west',
            'api_key' => 'test-api-key',
            'status' => 'normal',
        ]);
        
        // Initially no metrics, should be normal
        $status = $this->metricsService->determineNodeStatus($node);
        $this->assertEquals('normal', $status);
        
        // Add a warning metric
        StatusMetric::create([
            'status_node_id' => $node->id,
            'name' => 'memory_usage',
            'group' => 'system',
            'value' => 75.0,
            'status' => 'warning',
            'recorded_at' => now(),
        ]);
        
        $status = $this->metricsService->determineNodeStatus($node);
        $this->assertEquals('warning', $status);
        
        // Add a critical metric
        StatusMetric::create([
            'status_node_id' => $node->id,
            'name' => 'cpu_usage',
            'group' => 'system',
            'value' => 95.0,
            'status' => 'critical',
            'recorded_at' => now(),
        ]);
        
        $status = $this->metricsService->determineNodeStatus($node);
        $this->assertEquals('critical', $status);
    }
    
    /**
     * Test setting thresholds.
     *
     * @return void
     */
    public function testSetThresholds(): void
    {
        // Create new thresholds
        $thresholds = $this->metricsService->setThresholds('disk_usage', 80.0, 95.0);
        
        $this->assertEquals('disk_usage', $thresholds->metric_name);
        $this->assertEquals(80.0, $thresholds->warning_threshold);
        $this->assertEquals(95.0, $thresholds->critical_threshold);
        
        // Update existing thresholds
        $updatedThresholds = $this->metricsService->setThresholds('disk_usage', 75.0, 90.0);
        
        $this->assertEquals('disk_usage', $updatedThresholds->metric_name);
        $this->assertEquals(75.0, $updatedThresholds->warning_threshold);
        $this->assertEquals(90.0, $updatedThresholds->critical_threshold);
        $this->assertEquals($thresholds->id, $updatedThresholds->id);
    }
    
    /**
     * Test processing a metric.
     *
     * @return void
     */
    public function testProcessMetric(): void
    {
        $node = StatusNode::create([
            'name' => 'test-node',
            'environment' => 'testing',
            'region' => 'us-west',
            'api_key' => 'test-api-key',
            'status' => 'normal',
        ]);
        
        // Set thresholds for the metric
        ThresholdConfiguration::create([
            'metric_name' => 'cpu_usage',
            'warning_threshold' => 70.0,
            'critical_threshold' => 90.0,
        ]);
        
        // Process a metric with warning level
        $metric = $this->metricsService->processMetric($node, 'cpu_usage', 80.0, 'system');
        
        $this->assertEquals('warning', $metric->status);
        $this->assertEquals('warning', $node->fresh()->status);
        
        // Process a metric with critical level
        $metric = $this->metricsService->processMetric($node, 'cpu_usage', 95.0, 'system');
        
        $this->assertEquals('critical', $metric->status);
        $this->assertEquals('critical', $node->fresh()->status);
    }
}