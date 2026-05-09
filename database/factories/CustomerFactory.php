<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
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
            'source' => fake()->randomElement(['Website', 'Referral', 'Campaign', 'LinkedIn', 'Cold Outreach']),
            'status' => fake()->randomElement(['potential', 'active', 'not_interested']),
            'last_contacted_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'notes' => fake()->paragraph(),
        ];
    }
}
