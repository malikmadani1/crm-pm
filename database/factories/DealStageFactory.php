<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DealStageFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'color' => fake()->randomElement(['slate', 'sky', 'indigo', 'amber', 'orange', 'emerald', 'rose']),
            'position' => fake()->numberBetween(1, 9),
            'is_won' => false,
            'is_lost' => false,
        ];
    }
}
