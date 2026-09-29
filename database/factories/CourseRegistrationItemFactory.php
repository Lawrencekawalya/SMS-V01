<?php

namespace Database\Factories;

use App\Models\CourseRegistration;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseRegistrationItem>
 */
class CourseRegistrationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_registration_id' => CourseRegistration::factory(),
            'course_unit_id' => CourseUnit::factory(),
            'course_type' => 'Core',
            'credit_units' => 3.0,
            'status' => 'registered',
            'dropped_at' => null,
            'drop_reason' => null,
        ];
    }

    /**
     * Indicate that the course item is a mandatory core course.
     */
    public function core(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_type' => 'Core',
        ]);
    }

    /**
     * Indicate that the course item is an elective course.
     */
    public function elective(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_type' => 'Elective',
        ]);
    }

    /**
     * Indicate that the course item was approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the course item was dropped.
     */
    public function dropped(string $reason = 'Schedule conflict with job requirements'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dropped',
            'dropped_at' => now(),
            'drop_reason' => $reason,
        ]);
    }
}
