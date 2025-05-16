<?php

namespace Database\Factories;

use App\Models\StatusMetric;
use App\Models\StatusNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StatusMetric>
 */
class StatusMetricFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StatusMetric::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $metricNames = ['cpu_usage', 'memory_usage', 'disk_usage', 'network_io', 'response_time', 'error_rate'];
        $groups = ['system', 'application', 'database', 'network', null];
        $statuses = ['normal', 'warning', 'critical'];
        
        return [
            'status_node_id' => StatusNode::factory(),
            'name' => $this->faker->randomElement($metricNames),
            'group' => $this->faker->randomElement($groups),
            'value' => $this->faker->randomFloat(2, 0, 100),
            'status' => $this->faker->randomElement($statuses),
            'recorded_at' => $this->faker->dateTimeThisMonth(),
            'metadata' => null,
        ];
    }
}