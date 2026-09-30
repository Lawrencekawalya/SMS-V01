<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentSemesterPerformance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentSemesterPerformance>
 */
class StudentSemesterPerformanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'semester_id' => Semester::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'credit_units_registered' => 18.0,
            'credit_units_earned' => 18.0,
            'weighted_grade_points' => 75.0,
            'gpa' => 4.17,
            'cumulative_credit_units_registered' => 18.0,
            'cumulative_credit_units_earned' => 18.0,
            'cumulative_weighted_grade_points' => 75.0,
            'cgpa' => 4.17,
            'academic_standing' => 'Normal Progress',
        ];
    }

    /**
     * Indicate that the student is placed on academic probation.
     */
    public function probation(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_units_registered' => 18.0,
            'credit_units_earned' => 9.0,
            'weighted_grade_points' => 32.0,
            'gpa' => 1.78,
            'cumulative_credit_units_registered' => 18.0,
            'cumulative_credit_units_earned' => 9.0,
            'cumulative_weighted_grade_points' => 32.0,
            'cgpa' => 1.78,
            'academic_standing' => 'Probation',
        ]);
    }
}
