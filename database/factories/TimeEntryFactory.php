<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeEntryFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = now()->subDays(fake()->numberBetween(1, 20))->setTime(fake()->numberBetween(8, 14), 0);
        $minutes = fake()->numberBetween(30, 360);

        return [
            'project_id' => Project::factory(),
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'started_at' => $startedAt,
            'ended_at' => (clone $startedAt)->addMinutes($minutes),
            'minutes' => $minutes,
            'billable' => fake()->boolean(70),
            'description' => fake()->sentence(),
        ];
    }
}
