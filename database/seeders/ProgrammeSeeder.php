<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Programme;
use Illuminate\Database\Seeder;

class ProgrammeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deptCS = Department::where('code', 'CS')->first();
        $deptIT = Department::where('code', 'IT')->first();
        $deptAF = Department::where('code', 'AF')->first();

        if ($deptCS) {
            Programme::firstOrCreate(
                ['code' => 'BSCS'],
                [
                    'department_id' => $deptCS->id,
                    'name' => 'Bachelor of Science in Computer Science',
                    'award_type' => 'Bachelors',
                    'duration_years' => 3,
                    'required_credits_to_graduate' => 110,
                    'description' => 'A rigorous degree program covering algorithms, system programming, AI, and theoretical computing.',
                    'status' => 'active',
                ]
            );

            Programme::firstOrCreate(
                ['code' => 'BSSE'],
                [
                    'department_id' => $deptCS->id,
                    'name' => 'Bachelor of Science in Software Engineering',
                    'award_type' => 'Bachelors',
                    'duration_years' => 3,
                    'required_credits_to_graduate' => 115,
                    'description' => 'Specialized software engineering program focusing on software lifecycle, architecture, QA, and cloud engineering.',
                    'status' => 'active',
                ]
            );
        }

        if ($deptIT) {
            Programme::firstOrCreate(
                ['code' => 'BIT'],
                [
                    'department_id' => $deptIT->id,
                    'name' => 'Bachelor of Information Technology',
                    'award_type' => 'Bachelors',
                    'duration_years' => 3,
                    'required_credits_to_graduate' => 105,
                    'description' => 'Designed to produce IT professionals skilled in systems administration, network design, and IT service management.',
                    'status' => 'active',
                ]
            );

            Programme::firstOrCreate(
                ['code' => 'DCA'],
                [
                    'department_id' => $deptIT->id,
                    'name' => 'Diploma in Computer Applications',
                    'award_type' => 'Diploma',
                    'duration_years' => 2,
                    'required_credits_to_graduate' => 60,
                    'description' => 'A 2-year practical diploma offering foundational computer usage, web technologies, and support skills.',
                    'status' => 'active',
                ]
            );
        }

        if ($deptAF) {
            Programme::firstOrCreate(
                ['code' => 'BBA'],
                [
                    'department_id' => $deptAF->id,
                    'name' => 'Bachelor of Business Administration',
                    'award_type' => 'Bachelors',
                    'duration_years' => 3,
                    'required_credits_to_graduate' => 110,
                    'description' => 'A comprehensive business degree with options in marketing, finance, human resource management, and entrepreneurship.',
                    'status' => 'active',
                ]
            );
        }
    }
}
