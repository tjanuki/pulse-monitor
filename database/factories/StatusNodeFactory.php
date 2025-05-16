<?php

namespace Database\Factories;

use App\Models\StatusNode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StatusNode>
 */
class StatusNodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StatusNode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $environments = ['production', 'staging', 'development', 'testing'];
        $regions = ['us-east', 'us-west', 'eu-west', 'eu-central', 'ap-southeast'];
        
        return [
            'name' => $this->faker->word() . '-' . $this->faker->word() . '-' . $this->faker->numberBetween(1, 99),
            'environment' => $this->faker->randomElement($environments),
            'region' => $this->faker->randomElement($regions),
            'api_key' => Str::random(40),
            'status' => 'normal',
            'last_seen_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}