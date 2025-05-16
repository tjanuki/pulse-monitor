<?php

namespace Database\Factories;

use App\Models\ThresholdConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ThresholdConfiguration>
 */
class ThresholdConfigurationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ThresholdConfiguration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'metric_name' => $this->faker->word() . '_' . $this->faker->word(),
            'warning_threshold' => $this->faker->numberBetween(50, 80),
            'critical_threshold' => $this->faker->numberBetween(81, 100),
        ];
    }
}