<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'auditable_type' => \App\Models\Task::class,
            'auditable_id' => 1,
            'module' => fake()->randomElement(['crm', 'projects', 'tasks', 'team']),
            'event' => fake()->randomElement(['created', 'updated', 'deleted']),
            'description' => fake()->sentence(),
            'old_values' => ['before' => fake()->word()],
            'new_values' => ['after' => fake()->word()],
            'url' => fake()->url(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => now()->subDays(fake()->numberBetween(1, 15)),
        ];
    }
}
