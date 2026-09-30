<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\CourseAssessmentSheet;
use App\Models\CourseUnit;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseAssessmentSheet>
 */
class CourseAssessmentSheetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_unit_id' => CourseUnit::factory(),
            'semester_id' => Semester::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'instructor_id' => User::factory(),
            'ca_weight' => (float) config('academic.assessment_ca_weight', 40.0),
            'exam_weight' => (float) config('academic.assessment_exam_weight', 60.0),
            'pass_mark' => (float) config('academic.assessment_pass_mark', 50.0),
            'status' => 'draft',
            'submitted_at' => null,
            'moderated_at' => null,
            'moderated_by_id' => null,
            'published_at' => null,
            'published_by_id' => null,
            'moderation_remarks' => null,
        ];
    }

    /**
     * Indicate that the mark sheet is in draft state.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'submitted_at' => null,
        ]);
    }

    /**
     * Indicate that the mark sheet has been submitted to the HoD.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted_to_hod',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Indicate that the mark sheet has been moderated by the HoD.
     */
    public function moderated(?User $moderator = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'department_moderated',
            'submitted_at' => now()->subDays(2),
            'moderated_at' => now(),
            'moderated_by_id' => $moderator?->id ?? User::factory(),
            'moderation_remarks' => 'Marks verified and endorsed for Senate approval.',
        ]);
    }

    /**
     * Indicate that the mark sheet has been officially published by Senate.
     */
    public function published(?User $publisher = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'submitted_at' => now()->subDays(5),
            'moderated_at' => now()->subDays(3),
            'moderated_by_id' => User::factory(),
            'published_at' => now(),
            'published_by_id' => $publisher?->id ?? User::factory(),
            'moderation_remarks' => 'Approved by Faculty Board & Senate.',
        ]);
    }

    /**
     * Indicate that the mark sheet was returned for revision.
     */
    public function returned(string $remarks = 'Please check CA marks discrepancies'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'returned_for_revision',
            'submitted_at' => now()->subDays(1),
            'moderated_at' => now(),
            'moderated_by_id' => User::factory(),
            'moderation_remarks' => $remarks,
        ]);
    }
}
