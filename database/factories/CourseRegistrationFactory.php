<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\CourseRegistration;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseRegistration>
 */
class CourseRegistrationFactory extends Factory
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
            'academic_year_id' => fn (array $attributes) => isset($attributes['semester_id'])
                ? Semester::find($attributes['semester_id'])?->academic_year_id ?? AcademicYear::factory()
                : AcademicYear::factory(),
            'semester_id' => Semester::factory(),
            'study_year' => 1,
            'semester_number' => 1,
            'total_credits' => 15.0,
            'status' => 'draft',
            'submitted_at' => null,
            'approved_at' => null,
            'approved_by_user_id' => null,
            'advisor_remarks' => null,
        ];
    }

    /**
     * Indicate that the registration is submitted awaiting approval.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'submitted_at' => now()->subHours(4),
        ]);
    }

    /**
     * Indicate that the registration is approved.
     */
    public function approved(?User $advisor = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'submitted_at' => now()->subDays(2),
            'approved_at' => now()->subDay(),
            'approved_by_user_id' => $advisor?->id ?? User::factory(),
            'advisor_remarks' => 'Registration verified and approved.',
        ]);
    }

    /**
     * Indicate that the registration was rejected with remarks.
     */
    public function rejected(string $remarks = 'Please select the required core courses for your stage.'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'submitted_at' => now()->subDays(2),
            'advisor_remarks' => $remarks,
        ]);
    }

    /**
     * Indicate that an add/drop review is pending.
     */
    public function addDropPending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'add_drop_pending',
            'submitted_at' => now()->subDays(3),
            'approved_at' => now()->subDays(2),
            'approved_by_user_id' => User::factory(),
        ]);
    }
}
