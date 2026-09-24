<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\University;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $university = University::first() ?? University::create([
            'name' => 'Apex University of Science and Technology',
            'code' => 'APEX-UNI',
        ]);

        Campus::firstOrCreate(
            ['university_id' => $university->id, 'code' => 'MAIN'],
            [
                'name' => 'Main Campus',
                'location' => 'Kampala Central, Academic Hill',
                'is_main_campus' => true,
                'status' => 'active',
            ]
        );

        Campus::firstOrCreate(
            ['university_id' => $university->id, 'code' => 'WEST'],
            [
                'name' => 'Western Campus',
                'location' => 'Mbarara Town, Western Region',
                'is_main_campus' => false,
                'status' => 'active',
            ]
        );
    }
}
