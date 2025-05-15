<?php

namespace Tests\Unit\Console\Commands;

use App\Models\StatusNode;
use App\Services\MetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricCollectorCommandsTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test behavior when node doesn't exist.
     *
     * @return void
     */
    public function testCommandFailsWithNonExistentNode(): void
    {
        // Execute the command with a non-existent node ID
        $this->artisan('metrics:collect-cpu', ['node_id' => 999])
            ->assertExitCode(1);
    }
    
    /**
     * Test the collection of all metrics via the metrics:collect-all command.
     *
     * @return void
     */
    public function testCollectAllCommand(): void
    {
        // Create a status node to collect metrics for
        $node = StatusNode::create([
            'name' => 'test-node',
            'environment' => 'testing',
            'region' => 'us-west',
            'api_key' => 'test-api-key',
            'status' => 'normal',
        ]);
        
        // Test that the command runs, we're not testing actual metrics collection here
        $this->artisan('metrics:collect-all')
            ->assertExitCode(0);
    }

    /**
     * Helper method to create a test MetricsService that doesn't rely
     * on actual system metrics collection.
     *
     * @return \App\Services\MetricsService
     */
    protected function createTestMetricsService()
    {
        $metricsService = $this->getMockBuilder(MetricsService::class)
            ->onlyMethods(['processMetric'])
            ->getMock();
        
        $metricsService->expects($this->any())
            ->method('processMetric')
            ->willReturn((object)[
                'name' => 'test_metric',
                'value' => 50.0,
                'group' => 'test',
                'status' => 'normal'
            ]);
            
        return $metricsService;
    }
}