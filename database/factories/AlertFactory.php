<?php

namespace Database\Factories;

use App\Models\StatusNode;
use App\Models\StatusMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alert>
 */
class AlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status_node_id' => StatusNode::factory(),
            'status_metric_id' => StatusMetric::factory(),
            'type' => $this->faker->randomElement(['warning', 'critical', 'recovery']),
            'message' => $this->faker->sentence,
            'context' => ['source' => 'test', 'value' => $this->faker->randomFloat(2, 0, 100)],
            'resolved_at' => $this->faker->boolean(20) ? now() : null,
        ];
    }
    
    /**
     * Indicate that the alert is of warning type.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function warning()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'warning',
            ];
        });
    }
    
    /**
     * Indicate that the alert is of critical type.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function critical()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'critical',
            ];
        });
    }
    
    /**
     * Indicate that the alert is of recovery type.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function recovery()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'recovery',
            ];
        });
    }
    
    /**
     * Indicate that the alert is resolved.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function resolved()
    {
        return $this->state(function (array $attributes) {
            return [
                'resolved_at' => now(),
            ];
        });
    }
    
    /**
     * Indicate that the alert is unresolved.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unresolved()
    {
        return $this->state(function (array $attributes) {
            return [
                'resolved_at' => null,
            ];
        });
    }
}
