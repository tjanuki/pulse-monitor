<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recommendation>
 */
class RecommendationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $metricName = $this->faker->randomElement(['cpu_usage', 'memory_usage', 'disk_usage', 'network_in', 'network_out']);
        $condition = $this->faker->randomElement(['above', 'below', 'equals']);
        
        return [
            'trigger_metric' => $metricName,
            'condition' => $condition,
            'threshold_value' => $this->faker->randomFloat(2, 10, 90),
            'title' => ucfirst($metricName) . ' ' . ($condition === 'above' ? 'High' : ($condition === 'below' ? 'Low' : 'Equal')),
            'description' => $this->faker->paragraph,
            'solution' => $this->faker->paragraph,
            'additional_info' => [
                'source' => 'Auto-generated',
                'severity' => $this->faker->randomElement(['low', 'medium', 'high']),
            ],
            'is_active' => true,
        ];
    }
    
    /**
     * Indicate that the recommendation is for CPU usage.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function cpuUsage()
    {
        return $this->state(function (array $attributes) {
            return [
                'trigger_metric' => 'cpu_usage',
                'title' => 'High CPU Usage',
                'description' => 'CPU usage is above normal levels.',
                'solution' => 'Check running processes and terminate unnecessary ones.',
            ];
        });
    }
    
    /**
     * Indicate that the recommendation is for memory usage.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function memoryUsage()
    {
        return $this->state(function (array $attributes) {
            return [
                'trigger_metric' => 'memory_usage',
                'title' => 'High Memory Usage',
                'description' => 'Memory usage is above normal levels.',
                'solution' => 'Check for memory leaks and consider increasing available memory.',
            ];
        });
    }
    
    /**
     * Indicate that the recommendation is for disk usage.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function diskUsage()
    {
        return $this->state(function (array $attributes) {
            return [
                'trigger_metric' => 'disk_usage',
                'title' => 'High Disk Usage',
                'description' => 'Disk usage is above normal levels.',
                'solution' => 'Clean up unnecessary files and consider adding more storage.',
            ];
        });
    }
    
    /**
     * Indicate that the recommendation is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
