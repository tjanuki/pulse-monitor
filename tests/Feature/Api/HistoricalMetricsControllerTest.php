<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\StatusNode;
use App\Models\HistoricalMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HistoricalMetricsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }
    
    /**
     * Test retrieving trend data for a node metric
     */
    public function testGetTrendData(): void
    {
        // Create test data
        $node = StatusNode::factory()->create();
        $metricName = 'cpu_usage';
        
        // Create historical metrics
        for ($i = 0; $i < 5; $i++) {
            HistoricalMetric::create([
                'status_node_id' => $node->id,
                'metric_name' => $metricName,
                'min_value' => $this->faker->randomFloat(2, 0, 50),
                'max_value' => $this->faker->randomFloat(2, 50, 100),
                'avg_value' => $this->faker->randomFloat(2, 30, 70),
                'period_type' => 'hourly',
                'period_start' => now()->subHours(5 - $i),
                'period_end' => now()->subHours(4 - $i),
                'data_points_count' => $this->faker->numberBetween(10, 50)
            ]);
        }
        
        // Test API endpoint
        $response = $this->getJson("/api/nodes/{$node->id}/metrics/{$metricName}/trend?period_type=hourly");
        
        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'status_node_id', 'metric_name', 'min_value', 
                        'max_value', 'avg_value', 'period_type', 
                        'period_start', 'period_end', 'data_points_count'
                    ]
                ],
                'meta' => [
                    'metric_name', 'node_id', 'period_type', 
                    'start_date', 'end_date', 'data_points'
                ]
            ]);
    }
    
    /**
     * Test comparing nodes
     */
    public function testCompareNodes(): void
    {
        // Create test data
        $node1 = StatusNode::factory()->create();
        $node2 = StatusNode::factory()->create();
        $metricName = 'memory_usage';
        
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
                'data_points_count' => $this->faker->numberBetween(10, 50)
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
                'data_points_count' => $this->faker->numberBetween(10, 50)
            ]);
        }
        
        // Test API endpoint
        $response = $this->getJson("/api/metrics/{$metricName}/compare?node_ids[]={$node1->id}&node_ids[]={$node2->id}&period_type=daily");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    (string)$node1->id => [
                        'node', 'data', 'averages'
                    ],
                    (string)$node2->id => [
                        'node', 'data', 'averages'
                    ]
                ],
                'meta' => [
                    'metric_name', 'period_type', 'start_date', 'end_date'
                ]
            ]);
    }
    
    /**
     * Test triggering metrics aggregation
     */
    public function testTriggerAggregation(): void
    {
        // Test API endpoint
        $response = $this->postJson("/api/metrics/aggregate", [
            'period_type' => 'hourly',
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message', 'date'
            ])
            ->assertJsonPath('message', 'Aggregation for hourly data completed successfully');
    }
}
