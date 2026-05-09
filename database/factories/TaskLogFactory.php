<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['task_created', 'task_updated', 'status_changed', 'comment_added']),
            'description' => fake()->sentence(),
            'old_values' => null,
            'new_values' => ['example' => true],
            'created_at' => now()->subDays(fake()->numberBetween(1, 10)),
        ];
    }
}
