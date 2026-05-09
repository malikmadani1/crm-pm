<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->catchPhrase();

        return [
            'customer_id' => Customer::factory(),
            'manager_id' => User::factory(),
            'name' => Str::title($name),
            'code' => strtoupper(fake()->unique()->bothify('PRJ-###??')),
            'description' => fake()->paragraph(),
            'start_date' => now()->subDays(fake()->numberBetween(5, 40)),
            'due_date' => now()->addDays(fake()->numberBetween(10, 120)),
            'budget' => fake()->numberBetween(5000, 120000),
            'status' => fake()->randomElement(['in_progress', 'completed', 'paused', 'on_hold']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'progress' => fake()->numberBetween(5, 95),
            'last_activity_at' => now()->subDays(fake()->numberBetween(1, 7)),
        ];
    }
}
