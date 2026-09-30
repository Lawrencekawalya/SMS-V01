<?php

namespace Database\Factories;

use App\Models\CourseAssessmentSheet;
use App\Models\CourseRegistrationItem;
use App\Models\Student;
use App\Models\StudentMark;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentMark>
 */
class StudentMarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_assessment_sheet_id' => CourseAssessmentSheet::factory(),
            'course_registration_item_id' => CourseRegistrationItem::factory(),
            'student_id' => Student::factory(),
            'ca_score' => 32.0,
            'exam_score' => 48.0,
            'final_score' => 80.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
            'is_retake' => false,
            'lecturer_remarks' => 'Excellent performance',
        ];
    }

    /**
     * Indicate that the mark is an unscored placeholder entry.
     */
    public function unscored(): static
    {
        return $this->state(fn (array $attributes) => [
            'ca_score' => null,
            'exam_score' => null,
            'final_score' => null,
            'grade_letter' => null,
            'grade_point' => null,
            'is_passed' => false,
            'is_retake' => false,
            'lecturer_remarks' => null,
        ]);
    }

    /**
     * Indicate that the mark is a passing score.
     */
    public function passed(float $ca = 30.0, float $exam = 42.0, string $grade = 'B', float $gp = 4.0): static
    {
        return $this->state(fn (array $attributes) => [
            'ca_score' => $ca,
            'exam_score' => $exam,
            'final_score' => $ca + $exam,
            'grade_letter' => $grade,
            'grade_point' => $gp,
            'is_passed' => true,
            'is_retake' => false,
            'lecturer_remarks' => 'Good comprehension.',
        ]);
    }

    /**
     * Indicate that the mark is a failing score requiring a retake.
     */
    public function failed(float $ca = 16.0, float $exam = 24.0): static
    {
        return $this->state(fn (array $attributes) => [
            'ca_score' => $ca,
            'exam_score' => $exam,
            'final_score' => $ca + $exam,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
            'lecturer_remarks' => 'Did not meet minimum pass threshold; retake required.',
        ]);
    }
}
