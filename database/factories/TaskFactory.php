<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['todo', 'in_progress', 'review', 'done']);

        return [
            'project_id' => Project::factory(),
            'parent_id' => null,
            'created_by' => User::factory(),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'status' => $status,
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'start_date' => now()->subDays(fake()->numberBetween(1, 15)),
            'due_date' => now()->addDays(fake()->numberBetween(1, 20)),
            'estimated_hours' => fake()->randomFloat(1, 2, 24),
            'actual_hours' => fake()->randomFloat(1, 0, 18),
            'completion_percentage' => $status === 'done' ? 100 : fake()->numberBetween(0, 90),
            'position' => fake()->numberBetween(1, 30),
            'is_archived' => false,
            'completed_at' => $status === 'done' ? now()->subDays(fake()->numberBetween(1, 5)) : null,
        ];
    }
}
