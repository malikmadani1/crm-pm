<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Backend', 'Frontend', 'Urgent', 'Design', 'Marketing', 'QA', 'CRM', 'PM']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'color' => fake()->randomElement(['slate', 'sky', 'amber', 'emerald', 'rose']),
        ];
    }
}
