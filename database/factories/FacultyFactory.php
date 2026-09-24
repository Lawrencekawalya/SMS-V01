<?php

namespace Database\Factories;

use App\Models\Campus;
use App\Models\Faculty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faculty>
 */
class FacultyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campus_id' => Campus::factory(),
            'name' => 'Faculty of '.fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('F??')),
            'dean_user_id' => null,
            'description' => fake()->paragraph(),
            'status' => 'active',
        ];
    }
}
