<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FollowUpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'lead_id' => null,
            'assigned_to' => User::factory(),
            'title' => fake()->sentence(4),
            'notes' => fake()->sentence(),
            'status' => fake()->randomElement(['pending', 'completed', 'cancelled']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'due_at' => now()->addDays(fake()->numberBetween(1, 14)),
            'completed_at' => null,
        ];
    }
}
