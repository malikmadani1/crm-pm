<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\DealStage;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'customer_id' => Customer::factory(),
            'owner_id' => User::factory(),
            'stage_id' => DealStage::factory(),
            'title' => fake()->sentence(4),
            'value' => fake()->numberBetween(3000, 60000),
            'probability' => fake()->numberBetween(10, 95),
            'expected_close_date' => now()->addDays(fake()->numberBetween(7, 120)),
            'status' => fake()->randomElement(['open', 'won', 'lost']),
            'closed_at' => null,
            'notes' => fake()->paragraph(),
        ];
    }
}
