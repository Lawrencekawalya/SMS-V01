<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'faculty_id' => Faculty::factory(),
            'name' => 'Department of '.fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('D??')),
            'hod_user_id' => null,
            'description' => fake()->paragraph(),
            'status' => 'active',
        ];
    }
}
