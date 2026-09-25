<?php

namespace Database\Factories;

use App\Models\Curriculum;
use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curriculum>
 */
class CurriculumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2023, 2026);

        return [
            'programme_id' => Programme::factory(),
            'version_name' => "Structure {$startYear}-".($startYear + 4),
            'start_academic_year' => $startYear,
            'end_academic_year' => $startYear + 4,
            'min_graduation_credits' => fake()->randomElement([90, 105, 110, 120]),
            'is_active' => true,
        ];
    }
}
