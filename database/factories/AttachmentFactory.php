<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'attachable_type' => \App\Models\Task::class,
            'attachable_id' => 1,
            'disk' => 'public',
            'path' => 'attachments/' . fake()->uuid() . '.pdf',
            'original_name' => fake()->word() . '.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(12000, 350000),
        ];
    }
}
