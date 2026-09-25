<?php

namespace Database\Factories;

use App\Models\CourseUnit;
use App\Models\Curriculum;
use App\Models\CurriculumCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CurriculumCourse>
 */
class CurriculumCourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'curriculum_id' => Curriculum::factory(),
            'course_unit_id' => CourseUnit::factory(),
            'study_year' => fake()->numberBetween(1, 3),
            'semester' => fake()->randomElement([1, 2]),
            'course_type' => fake()->randomElement(['Core', 'Elective']),
        ];
    }
}
