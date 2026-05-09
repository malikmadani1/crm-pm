<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerInteractionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['call', 'email', 'meeting', 'note', 'follow_up']),
            'subject' => fake()->sentence(4),
            'details' => fake()->paragraph(),
            'interaction_at' => now()->subDays(fake()->numberBetween(1, 20)),
            'metadata' => ['channel' => fake()->randomElement(['phone', 'zoom', 'gmail'])],
        ];
    }
}
