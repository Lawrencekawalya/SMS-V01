<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\Curriculum;
use App\Models\Programme;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $regNumber = fake()->unique()->numerify('26/BSCS/###');
        $studentNumber = fake()->unique()->numerify('2026#####');
        $gender = fake()->randomElement(['male', 'female']);
        $firstName = $gender === 'male' ? fake()->firstNameMale() : fake()->firstNameFemale();

        return [
            'registration_number' => $regNumber,
            'student_number' => $studentNumber,
            'first_name' => $firstName,
            'last_name' => fake()->lastName(),
            'other_names' => fake()->optional(0.4)->firstName(),
            'gender' => $gender,
            'date_of_birth' => fake()->dateTimeBetween('-26 years', '-18 years')->format('Y-m-d'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'national_id_nin' => fake()->bothify('CM#########?'),
            'campus_id' => Campus::factory(),
            'programme_id' => Programme::factory(),
            'curriculum_id' => Curriculum::factory(),
            'admission_academic_year_id' => AcademicYear::factory(),
            'study_mode' => fake()->randomElement(['Day', 'Evening', 'Weekend']),
            'intake' => fake()->randomElement(['August', 'January']),
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
            'cumulative_gpa' => fake()->randomFloat(2, 2.00, 4.80),
        ];
    }

    /**
     * Indicate that the student is a fresher (Year 1, Sem 1).
     */
    public function fresher(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_study_year' => 1,
            'current_semester' => 1,
            'cumulative_gpa' => 0.00,
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the student is in second year.
     */
    public function yearTwo(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_study_year' => 2,
            'current_semester' => 1,
            'status' => 'active',
        ]);
    }
}
