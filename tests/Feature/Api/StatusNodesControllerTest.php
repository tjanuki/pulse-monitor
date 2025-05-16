<?php

namespace Tests\Feature\Api;

use App\Models\StatusNode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatusNodesControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication_for_nodes_endpoint()
    {
        $response = $this->getJson('/api/nodes');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_lists_all_status_nodes()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create some test nodes
        StatusNode::factory()->count(3)->create();

        $response = $this->getJson('/api/nodes');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'nodes');
    }

    #[Test]
    public function it_shows_a_single_status_node()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a test node
        $node = StatusNode::factory()->create([
            'name' => 'test-server',
            'environment' => 'production',
            'region' => 'us-east',
        ]);

        $response = $this->getJson("/api/nodes/{$node->id}");

        $response->assertStatus(200)
            ->assertJsonPath('node.id', $node->id)
            ->assertJsonPath('node.name', 'test-server')
            ->assertJsonPath('node.environment', 'production')
            ->assertJsonPath('node.region', 'us-east');
    }

    #[Test]
    public function it_updates_a_status_node()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a test node
        $node = StatusNode::factory()->create([
            'name' => 'old-server-name',
            'environment' => 'staging',
        ]);

        $response = $this->putJson("/api/nodes/{$node->id}", [
            'name' => 'new-server-name',
            'environment' => 'production',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('node.name', 'new-server-name')
            ->assertJsonPath('node.environment', 'production');

        $this->assertDatabaseHas('status_nodes', [
            'id' => $node->id,
            'name' => 'new-server-name',
            'environment' => 'production',
        ]);
    }

    #[Test]
    public function it_deletes_a_status_node()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a test node
        $node = StatusNode::factory()->create();

        $response = $this->deleteJson("/api/nodes/{$node->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Status node deleted successfully');

        $this->assertDatabaseMissing('status_nodes', [
            'id' => $node->id,
        ]);
    }

    #[Test]
    public function it_regenerates_api_key()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a test node
        $node = StatusNode::factory()->create([
            'api_key' => 'old-api-key',
        ]);

        $response = $this->postJson("/api/nodes/{$node->id}/regenerate-key");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'API key regenerated successfully')
            ->assertJsonStructure(['api_key']);

        $newApiKey = $response->json('api_key');
        
        $this->assertDatabaseHas('status_nodes', [
            'id' => $node->id,
            'api_key' => $newApiKey,
        ]);
        
        $this->assertNotEquals('old-api-key', $newApiKey);
    }
}