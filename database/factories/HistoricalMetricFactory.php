<?php

namespace Database\Factories;

use App\Models\StatusNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HistoricalMetric>
 */
class HistoricalMetricFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $periodStart = now()->subHours(rand(1, 72));
        $periodType = $this->faker->randomElement(['hourly', 'daily', 'weekly', 'monthly']);
        
        $periodEnd = match ($periodType) {
            'hourly' => $periodStart->copy()->addHour(),
            'daily' => $periodStart->copy()->addDay(),
            'weekly' => $periodStart->copy()->addWeek(),
            'monthly' => $periodStart->copy()->addMonth(),
        };
        
        return [
            'status_node_id' => StatusNode::factory(),
            'metric_name' => $this->faker->randomElement(['cpu_usage', 'memory_usage', 'disk_usage', 'network_in', 'network_out']),
            'group' => $this->faker->randomElement(['system', 'application', null]),
            'min_value' => $minValue = $this->faker->randomFloat(2, 0, 40),
            'max_value' => $maxValue = $this->faker->randomFloat(2, 60, 100),
            'avg_value' => $this->faker->randomFloat(2, $minValue, $maxValue),
            'period_type' => $periodType,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'data_points_count' => $this->faker->numberBetween(10, 100),
        ];
    }
    
    /**
     * Indicate that the metric is hourly.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function hourly()
    {
        return $this->state(function (array $attributes) {
            $periodStart = now()->subHours(rand(1, 24));
            return [
                'period_type' => 'hourly',
                'period_start' => $periodStart,
                'period_end' => $periodStart->copy()->addHour(),
            ];
        });
    }
    
    /**
     * Indicate that the metric is daily.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function daily()
    {
        return $this->state(function (array $attributes) {
            $periodStart = now()->subDays(rand(1, 30));
            return [
                'period_type' => 'daily',
                'period_start' => $periodStart,
                'period_end' => $periodStart->copy()->addDay(),
            ];
        });
    }
    
    /**
     * Indicate that the metric is for CPU usage.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function cpuUsage()
    {
        return $this->state(function (array $attributes) {
            return [
                'metric_name' => 'cpu_usage',
                'group' => 'system',
            ];
        });
    }
    
    /**
     * Indicate that the metric is for memory usage.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function memoryUsage()
    {
        return $this->state(function (array $attributes) {
            return [
                'metric_name' => 'memory_usage',
                'group' => 'system',
            ];
        });
    }
}
