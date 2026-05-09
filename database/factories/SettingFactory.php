<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group' => 'general',
            'key' => fake()->unique()->slug(),
            'value' => fake()->word(),
            'type' => 'string',
        ];
    }
}
