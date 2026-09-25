<?php

namespace Database\Factories;

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicEvent>
 */
class AcademicEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+3 months');
        $endDate = (clone $startDate)->modify('+5 days');

        return [
            'academic_year_id' => AcademicYear::factory(),
            'semester_id' => null,
            'title' => fake()->randomElement([
                'Orientation Week for Freshers',
                'Commencement of Lectures',
                'Mid-Term Examination Week',
                'Lectures End',
                'End of Semester Final Examinations',
                'Senate Academic Board Meeting',
                'National Independence Day Holiday',
            ]),
            'event_type' => fake()->randomElement([
                'academic_deadline',
                'examination',
                'lecture_period',
                'holiday',
                'governance',
                'ceremony',
            ]),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'is_all_day' => true,
            'target_audience' => fake()->randomElement(['all', 'students', 'lecturers', 'freshers']),
            'is_holiday' => false,
            'description' => fake()->sentence(),
        ];
    }
}
