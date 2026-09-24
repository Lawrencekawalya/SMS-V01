<?php

namespace Database\Factories;

use App\Models\Campus;
use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campus>
 */
class CampusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'university_id' => University::factory(),
            'name' => fake()->city().' Campus',
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'location' => fake()->address(),
            'is_main_campus' => false,
            'status' => 'active',
        ];
    }
}
