<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'company_name' => fake()->company(),
            'job_title' => fake()->jobTitle(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'source' => fake()->randomElement(['Website', 'Referral', 'LinkedIn', 'Campaign']),
            'stage' => fake()->randomElement(['new_lead', 'contacted', 'qualified', 'proposal_sent', 'negotiation']),
            'status' => 'open',
            'estimated_value' => fake()->numberBetween(2000, 40000),
            'probability' => fake()->numberBetween(10, 80),
            'expected_close_date' => now()->addDays(fake()->numberBetween(7, 90)),
            'notes' => fake()->paragraph(),
        ];
    }
}
