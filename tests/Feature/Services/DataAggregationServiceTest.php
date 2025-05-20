<?php

namespace Tests\Feature\Services;

use Carbon\Carbon;
use App\Models\StatusNode;
use App\Models\StatusMetric;
use App\Models\HistoricalMetric;
use App\Services\DataAggregationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataAggregationServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected DataAggregationService $aggregationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aggregationService = app(DataAggregationService::class);
    }
    
    /**
     * Test aggregating metrics for an hourly period
     */
    public function testAggregateMetricsHourly(): void
    {
        // Create a node
        $node = StatusNode::factory()->create();
        
        // Create metrics for the last hour
        $startTime = now()->subHour();
        $endTime = now();
        
        // Create some test metrics
        for ($i = 0; $i < 10; $i++) {
            StatusMetric::create([
                'status_node_id' => $node->id,
                'name' => 'cpu_usage',
                'group' => 'system',
                'value' => $this->faker->randomFloat(2, 10, 90),
                'status' => 'ok',
                'recorded_at' => $startTime->copy()->addMinutes($i * 6),
                'metadata' => null,
            ]);
        }
        
        // Run hourly aggregation
        $this->aggregationService->aggregateMetrics('hourly', $startTime);
        
        // Verify that historical metrics were created
        $this->assertDatabaseHas('historical_metrics', [
            'status_node_id' => $node->id,
            'metric_name' => 'cpu_usage',
            'group' => 'system',
            'period_type' => 'hourly',
        ]);
        
        // Fetch the created historical metric
        $historicalMetric = HistoricalMetric::where('status_node_id', $node->id)
            ->where('metric_name', 'cpu_usage')
            ->where('period_type', 'hourly')
            ->first();
            
        $this->assertNotNull($historicalMetric);
        $this->assertEquals('hourly', $historicalMetric->period_type);
        $this->assertEquals($startTime->startOfHour()->toDateTimeString(), $historicalMetric->period_start->toDateTimeString());
        $this->assertEquals($startTime->endOfHour()->toDateTimeString(), $historicalMetric->period_end->toDateTimeString());
        $this->assertEquals($historicalMetric->data_points_count, $historicalMetric->data_points_count);
    }
    
    /**
     * Test getting trend data for a node metric
     */
    public function testGetTrendData(): void
    {
        // Create test data
        $node = StatusNode::factory()->create();
        $metricName = 'memory_usage';
        
        // Create some historical metrics
        for ($i = 0; $i < 5; $i++) {
            HistoricalMetric::create([
                'status_node_id' => $node->id,
                'metric_name' => $metricName,
                'min_value' => $this->faker->randomFloat(2, 0, 40),
                'max_value' => $this->faker->randomFloat(2, 60, 100),
                'avg_value' => $this->faker->randomFloat(2, 40, 60),
                'period_type' => 'hourly',
                'period_start' => now()->subHours(5 - $i),
                'period_end' => now()->subHours(4 - $i),
                'data_points_count' => $this->faker->numberBetween(10, 50),
            ]);
        }
        
        // Get trend data
        $startDate = now()->subHours(5);
        $endDate = now();
        $trendData = $this->aggregationService->getTrendData(
            $node->id,
            $metricName,
            'hourly',
            $startDate,
            $endDate
        );
        
        $this->assertCount(5, $trendData);
        $this->assertEquals($metricName, $trendData->first()->metric_name);
        $this->assertEquals($node->id, $trendData->first()->status_node_id);
    }
    
    /**
     * Test comparing nodes
     */
    public function testCompareNodes(): void
    {
        // Create test data
        $node1 = StatusNode::factory()->create();
        $node2 = StatusNode::factory()->create();
        $metricName = 'disk_usage';
        
        // Create historical metrics for both nodes
        for ($i = 0; $i < 3; $i++) {
            HistoricalMetric::create([
                'status_node_id' => $node1->id,
                'metric_name' => $metricName,
                'min_value' => $this->faker->randomFloat(2, 0, 50),
                'max_value' => $this->faker->randomFloat(2, 50, 100),
                'avg_value' => $this->faker->randomFloat(2, 30, 70),
                'period_type' => 'daily',
                'period_start' => now()->subDays(3 - $i),
                'period_end' => now()->subDays(2 - $i),
                'data_points_count' => $this->faker->numberBetween(10, 50),
            ]);
            
            HistoricalMetric::create([
                'status_node_id' => $node2->id,
                'metric_name' => $metricName,
                'min_value' => $this->faker->randomFloat(2, 0, 50),
                'max_value' => $this->faker->randomFloat(2, 50, 100),
                'avg_value' => $this->faker->randomFloat(2, 30, 70),
                'period_type' => 'daily',
                'period_start' => now()->subDays(3 - $i),
                'period_end' => now()->subDays(2 - $i),
                'data_points_count' => $this->faker->numberBetween(10, 50),
            ]);
        }
        
        // Get comparison data
        $startDate = now()->subDays(3);
        $endDate = now();
        $comparisonData = $this->aggregationService->compareNodes(
            [$node1->id, $node2->id],
            $metricName,
            'daily',
            $startDate,
            $endDate
        );
        
        $this->assertCount(2, $comparisonData);
        $this->assertArrayHasKey($node1->id, $comparisonData);
        $this->assertArrayHasKey($node2->id, $comparisonData);
        $this->assertArrayHasKey('data', $comparisonData[$node1->id]);
        $this->assertArrayHasKey('averages', $comparisonData[$node1->id]);
        $this->assertCount(3, $comparisonData[$node1->id]['data']);
    }
    
    /**
     * Test cleaning up old data
     */
    public function testCleanupOldData(): void
    {
        // Create test data
        $node = StatusNode::factory()->create();
        
        // Create old hourly data (3 days old)
        HistoricalMetric::create([
            'status_node_id' => $node->id,
            'metric_name' => 'test_metric',
            'min_value' => 10,
            'max_value' => 90,
            'avg_value' => 50,
            'period_type' => 'hourly',
            'period_start' => now()->subDays(3),
            'period_end' => now()->subDays(3)->addHour(),
            'data_points_count' => 10,
        ]);
        
        // Create recent hourly data (1 day old)
        HistoricalMetric::create([
            'status_node_id' => $node->id,
            'metric_name' => 'test_metric',
            'min_value' => 10,
            'max_value' => 90,
            'avg_value' => 50,
            'period_type' => 'hourly',
            'period_start' => now()->subDay(),
            'period_end' => now()->subDay()->addHour(),
            'data_points_count' => 10,
        ]);
        
        // Run cleanup with a 2-day retention policy for hourly data
        $deleted = $this->aggregationService->cleanupOldData(2, 30, 90, 365);
        
        // Only the old hourly data should be deleted
        $this->assertEquals(1, $deleted);
        
        // Verify that only the recent data remains
        $this->assertEquals(1, HistoricalMetric::count());
        $this->assertDatabaseHas('historical_metrics', [
            'period_type' => 'hourly',
            'period_start' => now()->subDay()->toDateTimeString(),
        ]);
    }
}
