<?php

namespace Database\Factories;

use App\Models\Idea;
use App\Models\Steps;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Steps>
 */
class StepsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idea_id' => Idea::factory(),
            'description' => fake()->sentence(),
            'completed' => false,
        ];
    }
}
