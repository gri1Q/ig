<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        return [
            'provider' => $this->faker->randomElement(['netent', 'pragmatic', 'playtech']),
            'external_id' => $this->faker->unique()->word . '_' . $this->faker->randomNumber(3),
            'title' => $this->faker->words(3, true),
            'category' => $this->faker->randomElement(['slots', 'live', 'table']),
            'is_active' => $this->faker->boolean(70),
            'rtp' => $this->faker->boolean(80) ? $this->faker->randomFloat(2, 85, 99) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
