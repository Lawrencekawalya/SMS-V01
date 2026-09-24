<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainCampus = Campus::where('code', 'MAIN')->first();

        if (! $mainCampus) {
            return;
        }

        $deanScience = User::firstOrCreate(
            ['email' => 'dean.science@apex.ac.ug'],
            [
                'name' => 'Prof. Charles Mukasa',
                'password' => bcrypt('password'),
            ]
        );

        $deanBusiness = User::firstOrCreate(
            ['email' => 'dean.business@apex.ac.ug'],
            [
                'name' => 'Dr. Sarah Nalwanga',
                'password' => bcrypt('password'),
            ]
        );

        Faculty::firstOrCreate(
            ['campus_id' => $mainCampus->id, 'code' => 'FST'],
            [
                'name' => 'Faculty of Science & Technology',
                'dean_user_id' => $deanScience->id,
                'description' => 'Hub for Computer Science, Information Technology, and Applied Sciences.',
                'status' => 'active',
            ]
        );

        Faculty::firstOrCreate(
            ['campus_id' => $mainCampus->id, 'code' => 'FBE'],
            [
                'name' => 'Faculty of Business & Economics',
                'dean_user_id' => $deanBusiness->id,
                'description' => 'Center of excellence for Economics, Management, and Accounting.',
                'status' => 'active',
            ]
        );
    }
}
