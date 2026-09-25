<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Semester>
 */
class SemesterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'semester_number' => 1,
            'name' => 'Semester 1',
            'start_date' => '2026-08-15',
            'end_date' => '2026-12-20',
            'registration_start_date' => '2026-08-01',
            'registration_end_date' => '2026-09-01',
            'add_drop_deadline' => '2026-09-15',
            'is_active' => false,
        ];
    }

    /**
     * Indicate that the semester is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
