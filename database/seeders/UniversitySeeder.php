<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        University::firstOrCreate(
            ['code' => 'APEX-UNI'],
            [
                'name' => 'Apex University of Science and Technology',
                'email' => 'info@apex.ac.ug',
                'phone' => '+256 414 123456',
                'address' => 'Plot 45 Academic Hill, Kampala, Uganda',
                'website' => 'https://apex.ac.ug',
            ]
        );
    }
}
