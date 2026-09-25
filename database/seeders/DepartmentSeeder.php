<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fst = Faculty::where('code', 'FST')->first();
        $fbe = Faculty::where('code', 'FBE')->first();

        $hodCS = User::firstOrCreate(
            ['email' => 'hod.cs@apex.ac.ug'],
            [
                'name' => 'Dr. Robert Kato',
                'password' => bcrypt('password'),
            ]
        );

        $hodIT = User::firstOrCreate(
            ['email' => 'hod.it@apex.ac.ug'],
            [
                'name' => 'Dr. Juliet Namukasa',
                'password' => bcrypt('password'),
            ]
        );

        $hodAF = User::firstOrCreate(
            ['email' => 'hod.af@apex.ac.ug'],
            [
                'name' => 'Dr. Patrick Omondi',
                'password' => bcrypt('password'),
            ]
        );

        if ($fst) {
            Department::firstOrCreate(
                ['faculty_id' => $fst->id, 'code' => 'CS'],
                [
                    'name' => 'Department of Computer Science',
                    'hod_user_id' => $hodCS->id,
                    'description' => 'Focuses on core computer science fundamentals, artificial intelligence, algorithms, and software design.',
                    'status' => 'active',
                ]
            );

            Department::firstOrCreate(
                ['faculty_id' => $fst->id, 'code' => 'IT'],
                [
                    'name' => 'Department of Information Technology',
                    'hod_user_id' => $hodIT->id,
                    'description' => 'Emphasizes practical IT systems, networks, cybersecurity, and enterprise computing.',
                    'status' => 'active',
                ]
            );
        }

        if ($fbe) {
            Department::firstOrCreate(
                ['faculty_id' => $fbe->id, 'code' => 'AF'],
                [
                    'name' => 'Department of Accounting and Finance',
                    'hod_user_id' => $hodAF->id,
                    'description' => 'Covers financial reporting, managerial accounting, auditing, and corporate finance.',
                    'status' => 'active',
                ]
            );
        }
    }
}
