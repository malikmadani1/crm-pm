<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RoleFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Admin', 'Manager', 'Employee', 'Supervisor']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'guard_name' => 'web',
            'description' => fake()->sentence(),
            'is_system' => false,
        ];
    }
}
