<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Programme>
 */
class ProgrammeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name' => 'Bachelor of '.fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('B??')),
            'award_type' => fake()->randomElement(['Certificate', 'Diploma', 'Bachelors', 'Masters', 'Doctorate']),
            'duration_years' => fake()->numberBetween(1, 5),
            'required_credits_to_graduate' => fake()->numberBetween(60, 160),
            'description' => fake()->paragraph(),
            'status' => 'active',
        ];
    }
}
