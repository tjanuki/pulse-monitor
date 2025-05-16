<?php

namespace Tests\Feature\Api;

use App\Models\StatusNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MetricsControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_api_key_for_metrics_endpoint()
    {
        $response = $this->postJson('/api/metrics', [
            'name' => 'cpu_usage',
            'value' => 45.5,
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'API key is required']);
    }

    #[Test]
    public function it_rejects_invalid_api_key()
    {
        $response = $this->withHeaders([
            'X-API-Key' => 'invalid-key',
        ])->postJson('/api/metrics', [
            'name' => 'cpu_usage',
            'value' => 45.5,
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid API key']);
    }

    #[Test]
    public function it_stores_metric_with_valid_api_key()
    {
        $node = StatusNode::factory()->create([
            'api_key' => 'valid-test-key',
            'name' => 'test-server',
            'environment' => 'testing',
            'region' => 'us-west',
        ]);

        $response = $this->withHeaders([
            'X-API-Key' => 'valid-test-key',
        ])->postJson('/api/metrics', [
            'name' => 'cpu_usage',
            'value' => 45.5,
            'group' => 'system',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Metric recorded successfully')
            ->assertJsonPath('metric.name', 'cpu_usage')
            ->assertJsonPath('metric.value', 45.5)
            ->assertJsonPath('metric.group', 'system')
            ->assertJsonPath('metric.status_node_id', $node->id);
    }

    #[Test]
    public function it_validates_required_fields()
    {
        $node = StatusNode::factory()->create([
            'api_key' => 'valid-test-key',
        ]);

        $response = $this->withHeaders([
            'X-API-Key' => 'valid-test-key',
        ])->postJson('/api/metrics', [
            // Missing required fields
        ]);

        $response->assertStatus(422)
            ->assertJson(['error' => 'Validation failed'])
            ->assertJsonPath('messages.name', function($value) {
                return !empty($value);
            })
            ->assertJsonPath('messages.value', function($value) {
                return !empty($value);
            });
    }

    #[Test]
    public function it_stores_batch_metrics()
    {
        $node = StatusNode::factory()->create([
            'api_key' => 'valid-test-key',
        ]);

        $response = $this->withHeaders([
            'X-API-Key' => 'valid-test-key',
        ])->postJson('/api/metrics/batch', [
            'metrics' => [
                [
                    'name' => 'cpu_usage',
                    'value' => 45.5,
                    'group' => 'system',
                ],
                [
                    'name' => 'memory_usage',
                    'value' => 70.2,
                    'group' => 'system',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'All metrics recorded successfully')
            ->assertJsonCount(2, 'results');

        $this->assertDatabaseHas('status_metrics', [
            'status_node_id' => $node->id,
            'name' => 'cpu_usage',
            'value' => 45.5,
            'group' => 'system',
        ]);

        $this->assertDatabaseHas('status_metrics', [
            'status_node_id' => $node->id,
            'name' => 'memory_usage',
            'value' => 70.2,
            'group' => 'system',
        ]);
    }
}