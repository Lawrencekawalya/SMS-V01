<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\Programme;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainCampus = Campus::where('is_main', true)->first() ?? Campus::first();
        $currentYear = AcademicYear::where('is_current', true)->first() ?? AcademicYear::first();
        $previousYear = AcademicYear::where('is_current', false)->first() ?? $currentYear;

        $programmes = [
            'BSCS' => Programme::where('code', 'BSCS')->with('curriculums')->first(),
            'BSSE' => Programme::where('code', 'BSSE')->with('curriculums')->first(),
            'BIT' => Programme::where('code', 'BIT')->with('curriculums')->first(),
            'DCA' => Programme::where('code', 'DCA')->with('curriculums')->first(),
            'BBA' => Programme::where('code', 'BBA')->with('curriculums')->first(),
        ];

        if (! $mainCampus || ! $currentYear) {
            return;
        }

        $sampleStudents = [
            // BSCS Freshers (Year 1, Sem 1)
            [
                'reg' => '26/BSCS/001',
                'std_no' => '202600101',
                'first_name' => 'Ronald',
                'last_name' => 'Mukasa',
                'other_names' => 'Brian',
                'gender' => 'male',
                'email' => 'r.mukasa@student.bsu.ac.ug',
                'phone' => '+256701123456',
                'prog_code' => 'BSCS',
                'adm_year_id' => $currentYear->id,
                'year' => 1,
                'sem' => 1,
                'status' => 'active',
                'gpa' => 0.00,
                'mode' => 'Day',
            ],
            [
                'reg' => '26/BSCS/002',
                'std_no' => '202600102',
                'first_name' => 'Brenda',
                'last_name' => 'Atuhaire',
                'other_names' => null,
                'gender' => 'female',
                'email' => 'b.atuhaire@student.bsu.ac.ug',
                'phone' => '+256772234567',
                'prog_code' => 'BSCS',
                'adm_year_id' => $currentYear->id,
                'year' => 1,
                'sem' => 1,
                'status' => 'active',
                'gpa' => 0.00,
                'mode' => 'Day',
            ],
            // BSCS Continuing (Year 2, Sem 1)
            [
                'reg' => '25/BSCS/015',
                'std_no' => '202500115',
                'first_name' => 'Timothy',
                'last_name' => 'Mugisha',
                'other_names' => 'David',
                'gender' => 'male',
                'email' => 't.mugisha@student.bsu.ac.ug',
                'phone' => '+256784345678',
                'prog_code' => 'BSCS',
                'adm_year_id' => $previousYear->id,
                'year' => 2,
                'sem' => 1,
                'status' => 'active',
                'gpa' => 3.82,
                'mode' => 'Day',
            ],
            // BSSE Freshers & Continuing
            [
                'reg' => '26/BSSE/001',
                'std_no' => '202600201',
                'first_name' => 'Derrick',
                'last_name' => 'Katamba',
                'other_names' => null,
                'gender' => 'male',
                'email' => 'd.katamba@student.bsu.ac.ug',
                'phone' => '+256703456789',
                'prog_code' => 'BSSE',
                'adm_year_id' => $currentYear->id,
                'year' => 1,
                'sem' => 1,
                'status' => 'active',
                'gpa' => 0.00,
                'mode' => 'Day',
            ],
            [
                'reg' => '25/BSSE/008',
                'std_no' => '202500208',
                'first_name' => 'Sarah',
                'last_name' => 'Namubiru',
                'other_names' => 'Grace',
                'gender' => 'female',
                'email' => 's.namubiru@student.bsu.ac.ug',
                'phone' => '+256755567890',
                'prog_code' => 'BSSE',
                'adm_year_id' => $previousYear->id,
                'year' => 2,
                'sem' => 1,
                'status' => 'active',
                'gpa' => 4.15,
                'mode' => 'Day',
            ],
            // BIT Freshers & Continuing
            [
                'reg' => '26/BIT/001',
                'std_no' => '202600301',
                'first_name' => 'Joshua',
                'last_name' => 'Kigozi',
                'other_names' => null,
                'gender' => 'male',
                'email' => 'j.kigozi@student.bsu.ac.ug',
                'phone' => '+256776678901',
                'prog_code' => 'BIT',
                'adm_year_id' => $currentYear->id,
                'year' => 1,
                'sem' => 1,
                'status' => 'active',
                'gpa' => 0.00,
                'mode' => 'Evening',
            ],
            [
                'reg' => '25/BIT/020',
                'std_no' => '202500320',
                'first_name' => 'Grace',
                'last_name' => 'Ainembabazi',
                'other_names' => null,
                'gender' => 'female',
                'email' => 'g.ainembabazi@student.bsu.ac.ug',
                'phone' => '+256708789012',
                'prog_code' => 'BIT',
                'adm_year_id' => $previousYear->id,
                'year' => 2,
                'sem' => 1,
                'status' => 'active',
                'gpa' => 3.55,
                'mode' => 'Day',
            ],
            // DCA Diploma Students (Year 1 & Year 2)
            [
                'reg' => '26/DCA/001',
                'std_no' => '202600401',
                'first_name' => 'Emmanuel',
                'last_name' => 'Twinomujuni',
                'other_names' => null,
                'gender' => 'male',
                'email' => 'e.twinomujuni@student.bsu.ac.ug',
                'phone' => '+256789890123',
                'prog_code' => 'DCA',
                'adm_year_id' => $currentYear->id,
                'year' => 1,
                'sem' => 1,
                'status' => 'active',
                'gpa' => 0.00,
                'mode' => 'Day',
            ],
            [
                'reg' => '25/DCA/005',
                'std_no' => '202500405',
                'first_name' => 'Patricia',
                'last_name' => 'Kyomugisha',
                'other_names' => 'Mercy',
                'gender' => 'female',
                'email' => 'p.kyomugisha@student.bsu.ac.ug',
                'phone' => '+256700901234',
                'prog_code' => 'DCA',
                'adm_year_id' => $previousYear->id,
                'year' => 2,
                'sem' => 1,
                'status' => 'active',
                'gpa' => 3.90,
                'mode' => 'Weekend',
            ],
            // BBA Business Student
            [
                'reg' => '26/BBA/001',
                'std_no' => '202600501',
                'first_name' => 'Kevin',
                'last_name' => 'Tumwesigye',
                'other_names' => null,
                'gender' => 'male',
                'email' => 'k.tumwesigye@student.bsu.ac.ug',
                'phone' => '+256754012345',
                'prog_code' => 'BBA',
                'adm_year_id' => $currentYear->id,
                'year' => 1,
                'sem' => 1,
                'status' => 'active',
                'gpa' => 0.00,
                'mode' => 'Day',
            ],
        ];

        foreach ($sampleStudents as $s) {
            $prog = $programmes[$s['prog_code']] ?? null;
            if (! $prog) {
                continue;
            }

            $curriculum = $prog->activeCurriculum ?? $prog->curriculums->first();
            if (! $curriculum) {
                continue;
            }

            Student::updateOrCreate(
                ['registration_number' => $s['reg']],
                [
                    'student_number' => $s['std_no'],
                    'first_name' => $s['first_name'],
                    'last_name' => $s['last_name'],
                    'other_names' => $s['other_names'],
                    'gender' => $s['gender'],
                    'email' => $s['email'],
                    'phone' => $s['phone'],
                    'campus_id' => $mainCampus->id,
                    'programme_id' => $prog->id,
                    'curriculum_id' => $curriculum->id,
                    'admission_academic_year_id' => $s['adm_year_id'],
                    'study_mode' => $s['mode'],
                    'intake' => 'August',
                    'current_study_year' => $s['year'],
                    'current_semester' => $s['sem'],
                    'status' => $s['status'],
                    'cumulative_gpa' => $s['gpa'],
                ]
            );
        }
    }
}
