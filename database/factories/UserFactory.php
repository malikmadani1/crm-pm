<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'employee_code' => strtoupper(fake()->unique()->bothify('EMP-###??')),
            'job_title' => fake()->randomElement(['Project Manager', 'Account Executive', 'Developer', 'Designer', 'Marketing Specialist']),
            'phone' => fake()->phoneNumber(),
            'timezone' => 'Asia/Damascus',
            'locale' => 'ar',
            'is_active' => true,
            'last_seen_at' => now()->subMinutes(fake()->numberBetween(5, 240)),
            'email_verified_at' => now(),
            'password' => 'password',
            'remember_token' => Str::random(10),
        ];
    }
}
