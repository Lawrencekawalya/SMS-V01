<?php

namespace Database\Factories;

use App\Models\CourseUnit;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseUnit>
 */
class CourseUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prefixes = ['CSC', 'BIT', 'MTH', 'ENG', 'PHY', 'ACC', 'ECO', 'FIN', 'MKT'];
        $prefix = fake()->randomElement($prefixes);
        $number = fake()->unique()->numberBetween(1101, 4299);

        return [
            'department_id' => Department::factory(),
            'code' => "{$prefix}{$number}",
            'name' => fake()->catchPhrase(),
            'credit_units' => fake()->randomElement([3.0, 4.0, 5.0]),
            'description' => fake()->paragraph(),
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the course unit is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
