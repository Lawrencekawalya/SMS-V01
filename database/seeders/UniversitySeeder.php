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
            ['code' => 'BSU'],
            [
                'name' => 'Bishop Stuart University',
                'email' => 'info@bsu.ac.ug',
                'phone' => '+256 707 200703',
                'address' => 'Buremba-Kakoba Road, P.O. Box 09, Mbarara, Uganda',
                'website' => 'https://www.bsu.ac.ug',
            ]
        );
    }
}
