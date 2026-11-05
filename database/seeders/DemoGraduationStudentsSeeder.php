<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\CourseAssessmentSheet;
use App\Models\CourseRegistration;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use App\Models\Curriculum;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\User;
use App\Services\GradingEngineService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoGraduationStudentsSeeder extends Seeder
{
    /**
     * Seed 3 prospective graduation candidates with distinct honours classifications:
     * 1. Sarah Namubiru  - First Class Honours (CGPA 4.93)
     * 2. David Mukasa    - Second Class Honours (Upper Division) (CGPA 4.07)
     * 3. Faith Kemigisha - Second Class Honours (Lower Division) (CGPA 3.18)
     */
    public function run(): void
    {
        $bscs = Programme::where('code', 'BSCS')->first();
        if (! $bscs) {
            $this->command?->error('BSCS programme not found.');

            return;
        }

        $curriculum = Curriculum::where('programme_id', $bscs->id)->first();
        if ($curriculum) {
            $curriculum->update(['min_graduation_credits' => 57]);
        }

        // Academic Years & Semesters
        $ay1 = AcademicYear::firstOrCreate(['name' => '2025/2026'], [
            'start_date' => '2025-08-18',
            'end_date' => '2026-06-30',
            'is_current' => false,
        ]);

        $ay2 = AcademicYear::firstOrCreate(['name' => '2026/2027'], [
            'start_date' => '2026-08-17',
            'end_date' => '2027-06-25',
            'is_current' => false,
        ]);

        $ay3 = AcademicYear::firstOrCreate(['name' => '2027/2028'], [
            'start_date' => '2027-08-01',
            'end_date' => '2028-07-31',
            'is_current' => true,
        ]);

        $semesters = [
            1 => Semester::firstOrCreate(['academic_year_id' => $ay1->id, 'semester_number' => 1], ['name' => 'Semester 1', 'start_date' => '2025-08-18', 'end_date' => '2025-12-19', 'is_active' => false]),
            2 => Semester::firstOrCreate(['academic_year_id' => $ay1->id, 'semester_number' => 2], ['name' => 'Semester 2', 'start_date' => '2026-01-12', 'end_date' => '2026-06-05', 'is_active' => false]),
            3 => Semester::firstOrCreate(['academic_year_id' => $ay2->id, 'semester_number' => 1], ['name' => 'Semester 1', 'start_date' => '2026-08-17', 'end_date' => '2026-12-18', 'is_active' => false]),
            4 => Semester::firstOrCreate(['academic_year_id' => $ay2->id, 'semester_number' => 2], ['name' => 'Semester 2', 'start_date' => '2027-01-11', 'end_date' => '2027-06-04', 'is_active' => false]),
            5 => Semester::firstOrCreate(['academic_year_id' => $ay3->id, 'semester_number' => 1], ['name' => 'Semester 1', 'start_date' => '2027-08-01', 'end_date' => '2027-12-20', 'is_active' => false]),
            6 => Semester::firstOrCreate(['academic_year_id' => $ay3->id, 'semester_number' => 2], ['name' => 'Semester 2', 'start_date' => '2028-02-01', 'end_date' => '2028-06-30', 'is_active' => true]),
        ];

        // 15 Core Courses mapped across the 6 study semesters (Total = 57 CU)
        $semesterCourses = [
            1 => ['CSC1101', 'CSC1102', 'BIT1101'], // 4 + 4 + 3 = 11 CU
            2 => ['CSC1201', 'CSC1202', 'BIT1201'], // 4 + 4 + 4 = 12 CU
            3 => ['CSC2101', 'CSC2102', 'BIT2101'], // 4 + 4 + 4 = 12 CU
            4 => ['BIT2201'],                       // 3 CU
            5 => ['CSC3101', 'BIT3101', 'CSC3102', 'CSC3103'], // 4 + 3 + 4 + 4 = 15 CU
            6 => ['CSC3201'],                       // 4 CU
        ];

        $candidatesData = [
            [
                'name' => 'Sarah Namubiru',
                'email' => 'sarah.namubiru@student.bsu.ac.ug',
                'reg_no' => '23/BSU/BSCS/001',
                'student_no' => '23001001',
                'gender' => 'Female',
                'target_award' => 'First Class Honours',
                // Mostly A (80-92%), one B+ (77%)
                'score_generator' => function (string $code): array {
                    if ($code === 'CSC1102') {
                        return ['ca' => 31.0, 'exam' => 46.0]; // 77.0% -> B+ (4.5 GP)
                    }

                    return ['ca' => 35.0, 'exam' => 52.0]; // 87.0% -> A (5.0 GP)
                },
            ],
            [
                'name' => 'David Mukasa',
                'email' => 'david.mukasa@student.bsu.ac.ug',
                'reg_no' => '23/BSU/BSCS/002',
                'student_no' => '23001002',
                'gender' => 'Male',
                'target_award' => 'Second Class Honours (Upper Division)',
                // Mostly B+ (75-78%) and B (70-74%)
                'score_generator' => function (string $code): array {
                    if (in_array($code, ['CSC1101', 'CSC2101', 'CSC3101'], true)) {
                        return ['ca' => 32.0, 'exam' => 45.0]; // 77.0% -> B+ (4.5 GP)
                    }

                    return ['ca' => 29.0, 'exam' => 43.0]; // 72.0% -> B (4.0 GP)
                },
            ],
            [
                'name' => 'Faith Kemigisha',
                'email' => 'faith.kemigisha@student.bsu.ac.ug',
                'reg_no' => '23/BSU/BSCS/003',
                'student_no' => '23001003',
                'gender' => 'Female',
                'target_award' => 'Second Class Honours (Lower Division)',
                // Mostly C+ (65-68%) and C (60-64%)
                'score_generator' => function (string $code): array {
                    if (in_array($code, ['CSC1101', 'CSC2101', 'CSC3101'], true)) {
                        return ['ca' => 26.0, 'exam' => 41.0]; // 67.0% -> C+ (3.5 GP)
                    }

                    return ['ca' => 25.0, 'exam' => 37.0]; // 62.0% -> C (3.0 GP)
                },
            ],
        ];

        $gradingEngine = app(GradingEngineService::class);
        $instructor = User::first() ?? User::factory()->create(['name' => 'Dr. Course Examiner']);

        foreach ($candidatesData as $data) {
            $nameParts = explode(' ', $data['name']);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Student';

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $campus = Campus::first();

            $student = Student::updateOrCreate(
                ['registration_number' => $data['reg_no']],
                [
                    'user_id' => $user->id,
                    'student_number' => $data['student_no'],
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $data['email'],
                    'phone' => '+256700'.rand(100000, 999999),
                    'date_of_birth' => '2002-05-15',
                    'gender' => strtolower($data['gender']),
                    'campus_id' => $campus?->id ?? 1,
                    'programme_id' => $bscs->id,
                    'curriculum_id' => $curriculum?->id,
                    'admission_academic_year_id' => $ay1->id,
                    'study_mode' => 'Day',
                    'intake' => 'August',
                    'current_study_year' => 3,
                    'current_semester' => 2,
                    'status' => 'active',
                ]
            );

            // Seed registrations and marks across the 6 study semesters
            foreach ($semesterCourses as $semIndex => $courseCodes) {
                $sem = $semesters[$semIndex];
                $studyYear = (int) ceil($semIndex / 2);
                $semNumber = ($semIndex % 2 === 1) ? 1 : 2;

                $reg = CourseRegistration::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'semester_id' => $sem->id,
                    ],
                    [
                        'academic_year_id' => $sem->academic_year_id,
                        'study_year' => $studyYear,
                        'semester_number' => $semNumber,
                        'status' => 'approved',
                        'submitted_at' => $sem->start_date ?? now(),
                        'approved_at' => $sem->start_date ?? now(),
                    ]
                );

                foreach ($courseCodes as $code) {
                    $cu = CourseUnit::where('code', $code)->first();
                    if (! $cu) {
                        continue;
                    }

                    $regItem = CourseRegistrationItem::updateOrCreate(
                        [
                            'course_registration_id' => $reg->id,
                            'course_unit_id' => $cu->id,
                        ],
                        [
                            'credit_units' => $cu->credit_units,
                            'status' => 'registered',
                        ]
                    );

                    $sheet = CourseAssessmentSheet::firstOrCreate(
                        [
                            'course_unit_id' => $cu->id,
                            'semester_id' => $sem->id,
                        ],
                        [
                            'academic_year_id' => $sem->academic_year_id,
                            'instructor_id' => $instructor->id,
                            'ca_weight' => 40.0,
                            'exam_weight' => 60.0,
                            'pass_mark' => 50.0,
                            'status' => 'published',
                            'published_at' => $sem->end_date ?? now(),
                            'published_by_id' => $instructor->id,
                        ]
                    );

                    $scores = $data['score_generator']($code);
                    $computed = $gradingEngine->computeMark($scores['ca'], $scores['exam'], $sheet, 'present');

                    StudentMark::updateOrCreate(
                        [
                            'course_assessment_sheet_id' => $sheet->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'course_registration_item_id' => $regItem->id,
                            'ca_score' => $computed['ca_score'],
                            'exam_score' => $computed['exam_score'],
                            'final_score' => $computed['final_score'],
                            'grade_letter' => $computed['grade_letter'],
                            'grade_point' => $computed['grade_point'],
                            'exam_status' => 'present',
                            'sitting_type' => 'main',
                            'is_passed' => true,
                            'is_retake' => false,
                        ]
                    );
                }

                $reg->recalculateTotalCredits();

                // Recalculate semester GPA and cumulative metrics up to this term
                $gradingEngine->calculateSemesterGpa($student, $sem);
            }

            $student->refresh();
            $this->command?->info("Seeded Graduand: {$student->full_name} ({$student->registration_number}) - CGPA: {$student->cumulative_gpa} [{$data['target_award']}]");
        }
    }
}
