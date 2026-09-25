<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->unique()->numberBetween(1990, 2099);
        $endYear = $startYear + 1;

        return [
            'name' => "{$startYear}/{$endYear}",
            'start_date' => "{$startYear}-08-15",
            'end_date' => "{$endYear}-06-30",
            'is_current' => false,
            'description' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the academic year is current.
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
        ]);
    }
}
