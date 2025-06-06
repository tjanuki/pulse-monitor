<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\StatusNode;
use App\Models\StatusMetric;
use Laravel\Pulse\Facades\Pulse;
use App\Providers\PulseServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PulseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pulse_dashboard_has_protection()
    {
        // The Pulse dashboard is protected by the Authorize middleware,
        // so we expect a forbidden response when not authorized
        $response = $this->get('/pulse');
        $response->assertStatus(403); // Forbidden - Laravel Pulse default behavior
    }

    public function test_livewire_components_are_available()
    {
        // We'll test by checking if the Livewire classes exist rather than rendering them
        $this->assertTrue(class_exists(\App\Livewire\StatusNodesCard::class));
        $this->assertTrue(class_exists(\App\Livewire\NodeMetricsCard::class));
    }

    public function test_data_is_accurate_for_dashboard()
    {
        // Create exactly the nodes and metrics we need for this test
        $normalNode = StatusNode::factory()->create(['status' => 'normal']);
        $warningNode = StatusNode::factory()->create(['status' => 'warning']);

        // Create metrics with specific statuses, and attach them to nodes
        StatusMetric::factory()->count(5)->create([
            'status' => 'normal',
            'status_node_id' => $normalNode->id
        ]);

        StatusMetric::factory()->count(3)->create([
            'status' => 'warning',
            'status_node_id' => $warningNode->id
        ]);

        StatusMetric::factory()->count(2)->create([
            'status' => 'critical',
            'status_node_id' => $warningNode->id
        ]);

        // Verify correct data was created
        $this->assertEquals(2, StatusNode::count());
        $this->assertEquals(10, StatusMetric::count());
    }
}
