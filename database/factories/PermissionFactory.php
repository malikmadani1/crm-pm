<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PermissionFactory extends Factory
{
    public function definition(): array
    {
        $module = fake()->randomElement(['customers', 'projects', 'tasks', 'reports']);
        $action = fake()->randomElement(['view', 'create', 'update', 'delete']);

        return [
            'name' => Str::title("{$module} {$action}"),
            'slug' => "{$module}.{$action}",
            'module' => $module,
            'description' => fake()->sentence(),
        ];
    }
}
