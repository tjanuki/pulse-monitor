<?php

namespace Tests\Feature\Api;

use App\Models\ThresholdConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ThresholdConfigurationsControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_requires_authentication_for_thresholds_endpoint()
    {
        $response = $this->getJson('/api/thresholds');

        $response->assertStatus(401);
    }

    #[Test]
    public function it_lists_all_threshold_configurations()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create some test threshold configurations
        ThresholdConfiguration::factory()->count(3)->create();

        $response = $this->getJson('/api/thresholds');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'thresholds');
    }

    #[Test]
    public function it_creates_a_threshold_configuration()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/thresholds', [
            'metric_name' => 'cpu_usage',
            'warning_threshold' => 70,
            'critical_threshold' => 90,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('threshold.metric_name', 'cpu_usage')
            ->assertJsonPath('threshold.warning_threshold', 70)
            ->assertJsonPath('threshold.critical_threshold', 90);

        $this->assertDatabaseHas('threshold_configurations', [
            'metric_name' => 'cpu_usage',
            'warning_threshold' => 70,
            'critical_threshold' => 90,
        ]);
    }

    #[Test]
    public function it_shows_a_single_threshold_configuration()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a test threshold configuration
        $threshold = ThresholdConfiguration::factory()->create([
            'metric_name' => 'memory_usage',
            'warning_threshold' => 80,
            'critical_threshold' => 95,
        ]);

        $response = $this->getJson("/api/thresholds/{$threshold->id}");

        $response->assertStatus(200)
            ->assertJsonPath('threshold.id', $threshold->id)
            ->assertJsonPath('threshold.metric_name', 'memory_usage')
            ->assertJsonPath('threshold.warning_threshold', 80)
            ->assertJsonPath('threshold.critical_threshold', 95);
    }

    #[Test]
    public function it_updates_a_threshold_configuration()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a test threshold configuration
        $threshold = ThresholdConfiguration::factory()->create([
            'metric_name' => 'disk_usage',
            'warning_threshold' => 75,
            'critical_threshold' => 90,
        ]);

        $response = $this->putJson("/api/thresholds/{$threshold->id}", [
            'warning_threshold' => 80,
            'critical_threshold' => 95,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('threshold.metric_name', 'disk_usage')
            ->assertJsonPath('threshold.warning_threshold', 80)
            ->assertJsonPath('threshold.critical_threshold', 95);

        $this->assertDatabaseHas('threshold_configurations', [
            'id' => $threshold->id,
            'metric_name' => 'disk_usage',
            'warning_threshold' => 80,
            'critical_threshold' => 95,
        ]);
    }

    #[Test]
    public function it_deletes_a_threshold_configuration()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a test threshold configuration
        $threshold = ThresholdConfiguration::factory()->create();

        $response = $this->deleteJson("/api/thresholds/{$threshold->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Threshold configuration deleted successfully');

        $this->assertDatabaseMissing('threshold_configurations', [
            'id' => $threshold->id,
        ]);
    }
}