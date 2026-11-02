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

class DemoRetakeStudentsSeeder extends Seeder
{
    /**
     * Run the database seeds for Retake demonstration students.
     */
    public function run(): void
    {
        $bscs = Programme::where('code', 'BSCS')->first() ?? Programme::first();
        if (! $bscs) {
            $this->command?->error('No programme found to attach retake demo students.');

            return;
        }

        $curriculum = Curriculum::where('programme_id', $bscs->id)->where('is_active', true)->first()
            ?? Curriculum::where('programme_id', $bscs->id)->first();

        $mainCampus = Campus::where('is_main_campus', true)->first() ?? Campus::first();

        $academicYear2025 = AcademicYear::where('name', '2025/2026')->first() ?? AcademicYear::first();
        $academicYear2026 = AcademicYear::where('name', '2026/2027')->first()
            ?? AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::latest('id')->first();

        // Semester 1 of 2025/2026 (Prior Term 1)
        $semester1 = Semester::where('academic_year_id', $academicYear2025->id)
            ->where('semester_number', 1)
            ->first();

        // Semester 2 of 2025/2026 (Prior Term 2)
        $semester2 = Semester::where('academic_year_id', $academicYear2025->id)
            ->where('semester_number', 2)
            ->first();

        // Active Semester (Current Term - e.g. 2026/2027 Semester 1)
        $activeSemester = Semester::where('is_active', true)->first()
            ?? Semester::where('academic_year_id', $academicYear2026->id)->where('semester_number', 1)->first();

        if ($activeSemester) {
            // Keep the active semester registration window open for demonstrations
            $activeSemester->update([
                'registration_start_date' => now()->subDays(14)->startOfDay(),
                'registration_end_date' => now()->addDays(60)->endOfDay(),
                'add_drop_deadline' => now()->addDays(30)->endOfDay(),
            ]);
        }

        $gradingEngine = app(GradingEngineService::class);
        $instructor = User::where('email', 'admin@bsu.ac.ug')->first() ?? User::first();

        // =========================================================================
        // DEMO STUDENT 1: Brian Asiimwe (Pending Retake Demo - Registration Stage)
        // =========================================================================
        $user1 = User::firstOrCreate(
            ['email' => 'brian.asiimwe@student.bsu.ac.ug'],
            [
                'name' => 'Brian Asiimwe',
                'password' => Hash::make('password'),
            ]
        );

        $student1 = Student::firstOrCreate(
            ['registration_number' => '25/BSU/BSCS/042'],
            [
                'user_id' => $user1->id,
                'student_number' => '25007042',
                'first_name' => 'Brian',
                'last_name' => 'Asiimwe',
                'other_names' => null,
                'gender' => 'male',
                'date_of_birth' => '2004-03-12',
                'email' => $user1->email,
                'phone' => '+256701234567',
                'campus_id' => $mainCampus->id,
                'programme_id' => $bscs->id,
                'curriculum_id' => $curriculum?->id,
                'admission_academic_year_id' => $academicYear2025->id,
                'study_mode' => 'Day',
                'intake' => 'August',
                'current_study_year' => 2,
                'current_semester' => 1,
                'status' => 'active',
                'cumulative_gpa' => 0.0,
            ]
        );

        $student1->update([
            'current_study_year' => 2,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        // Clear any existing active semester registration so Brian is ready to register
        if ($activeSemester) {
            $existingActiveReg = CourseRegistration::where('student_id', $student1->id)
                ->where('semester_id', $activeSemester->id)
                ->first();

            if ($existingActiveReg) {
                CourseRegistrationItem::where('course_registration_id', $existingActiveReg->id)->delete();
                $existingActiveReg->delete();
            }
        }

        if ($semester1) {
            // Seed Brian's Year 1 Semester 1 registration
            $regY1S1 = CourseRegistration::firstOrCreate(
                [
                    'student_id' => $student1->id,
                    'semester_id' => $semester1->id,
                ],
                [
                    'academic_year_id' => $academicYear2025->id,
                    'study_year' => 1,
                    'semester_number' => 1,
                    'status' => 'approved',
                    'total_credits' => 14.0,
                    'submitted_at' => now()->subMonths(12),
                    'approved_at' => now()->subMonths(11),
                    'advisor_remarks' => 'Approved full core load for Year 1 Semester 1.',
                ]
            );

            $brianS1Courses = [
                ['code' => 'CSC1101', 'ca' => 30.0, 'exam' => 45.0, 'type' => 'Core'], // 75.0 (B+, 4.5 GP, Passed)
                ['code' => 'BIT1101', 'ca' => 28.0, 'exam' => 42.0, 'type' => 'Core'], // 70.0 (B, 4.0 GP, Passed)
                ['code' => 'AFN1102', 'ca' => 26.0, 'exam' => 42.0, 'type' => 'Elective'], // 68.0 (B, 4.0 GP, Passed)
                ['code' => 'CSC1102', 'ca' => 15.0, 'exam' => 23.0, 'type' => 'Core'], // 38.0 (F, 0.0 GP, FAILED / RETAKE)
            ];

            foreach ($brianS1Courses as $item) {
                $course = CourseUnit::where('code', $item['code'])->first();
                if (! $course) {
                    continue;
                }

                $regItem = CourseRegistrationItem::firstOrCreate(
                    [
                        'course_registration_id' => $regY1S1->id,
                        'course_unit_id' => $course->id,
                    ],
                    [
                        'course_type' => $item['type'],
                        'credit_units' => $course->credit_units,
                        'status' => 'approved',
                    ]
                );

                $sheet = CourseAssessmentSheet::firstOrCreate(
                    [
                        'course_unit_id' => $course->id,
                        'semester_id' => $semester1->id,
                    ],
                    [
                        'academic_year_id' => $academicYear2025->id,
                        'instructor_id' => $instructor?->id,
                        'pass_mark' => 50.0,
                        'ca_weight' => 40.0,
                        'exam_weight' => 60.0,
                        'status' => 'published',
                        'published_at' => now()->subMonths(10),
                    ]
                );

                $calc = $gradingEngine->computeMark($item['ca'], $item['exam'], $sheet);

                StudentMark::updateOrCreate(
                    [
                        'course_assessment_sheet_id' => $sheet->id,
                        'student_id' => $student1->id,
                    ],
                    [
                        'course_registration_item_id' => $regItem->id,
                        'ca_score' => $item['ca'],
                        'exam_score' => $item['exam'],
                        'final_score' => $calc['final_score'],
                        'grade_letter' => $calc['grade_letter'],
                        'grade_point' => $calc['grade_point'],
                        'is_passed' => $calc['is_passed'],
                        'is_retake' => ! $calc['is_passed'],
                        'lecturer_remarks' => $calc['is_passed'] ? 'Completed Year 1 Semester 1.' : 'Requires Retake / Carryover.',
                    ]
                );
            }

            $perf1 = $gradingEngine->calculateSemesterGpa($student1, $semester1);
            $student1->update(['cumulative_gpa' => $perf1->cgpa]);

            $this->command?->info("Seeded Demo Student 1 (Pending Retake): {$student1->full_name} ({$student1->registration_number}) - Y1S1 GPA: {$perf1->gpa}");
        }

        // =========================================================================
        // DEMO STUDENT 2: Gloria Nabukenya (Completed Retake Demo - CGPA Replacement)
        // =========================================================================
        $user2 = User::firstOrCreate(
            ['email' => 'gloria.nabukenya@student.bsu.ac.ug'],
            [
                'name' => 'Gloria Nabukenya',
                'password' => Hash::make('password'),
            ]
        );

        $student2 = Student::firstOrCreate(
            ['registration_number' => '24/BSU/BSCS/018'],
            [
                'user_id' => $user2->id,
                'student_number' => '24006018',
                'first_name' => 'Gloria',
                'last_name' => 'Nabukenya',
                'other_names' => null,
                'gender' => 'female',
                'date_of_birth' => '2003-11-20',
                'email' => $user2->email,
                'phone' => '+256779876543',
                'campus_id' => $mainCampus->id,
                'programme_id' => $bscs->id,
                'curriculum_id' => $curriculum?->id,
                'admission_academic_year_id' => $academicYear2025->id,
                'study_mode' => 'Day',
                'intake' => 'August',
                'current_study_year' => 2,
                'current_semester' => 1,
                'status' => 'active',
                'cumulative_gpa' => 0.0,
            ]
        );

        $student2->update([
            'current_study_year' => 2,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        if ($semester1) {
            // Seed Gloria's Year 1 Semester 1 registration (Initial Attempt with Fail in CSC1102)
            $gloriaRegY1S1 = CourseRegistration::firstOrCreate(
                [
                    'student_id' => $student2->id,
                    'semester_id' => $semester1->id,
                ],
                [
                    'academic_year_id' => $academicYear2025->id,
                    'study_year' => 1,
                    'semester_number' => 1,
                    'status' => 'approved',
                    'total_credits' => 14.0,
                    'submitted_at' => now()->subMonths(12),
                    'approved_at' => now()->subMonths(11),
                    'advisor_remarks' => 'Approved full core load for Year 1 Semester 1.',
                ]
            );

            $gloriaS1Courses = [
                ['code' => 'CSC1101', 'ca' => 28.0, 'exam' => 44.0, 'type' => 'Core'], // 72.0 (B+, 4.5 GP, Passed)
                ['code' => 'BIT1101', 'ca' => 25.0, 'exam' => 40.0, 'type' => 'Core'], // 65.0 (B, 4.0 GP, Passed)
                ['code' => 'AFN1102', 'ca' => 28.0, 'exam' => 42.0, 'type' => 'Elective'], // 70.0 (B, 4.0 GP, Passed)
                ['code' => 'CSC1102', 'ca' => 14.0, 'exam' => 21.0, 'type' => 'Core'], // 35.0 (F, 0.0 GP, FAILED 1st Attempt)
            ];

            foreach ($gloriaS1Courses as $item) {
                $course = CourseUnit::where('code', $item['code'])->first();
                if (! $course) {
                    continue;
                }

                $regItem = CourseRegistrationItem::firstOrCreate(
                    [
                        'course_registration_id' => $gloriaRegY1S1->id,
                        'course_unit_id' => $course->id,
                    ],
                    [
                        'course_type' => $item['type'],
                        'credit_units' => $course->credit_units,
                        'status' => 'approved',
                    ]
                );

                $sheet = CourseAssessmentSheet::firstOrCreate(
                    [
                        'course_unit_id' => $course->id,
                        'semester_id' => $semester1->id,
                    ],
                    [
                        'academic_year_id' => $academicYear2025->id,
                        'instructor_id' => $instructor?->id,
                        'pass_mark' => 50.0,
                        'ca_weight' => 40.0,
                        'exam_weight' => 60.0,
                        'status' => 'published',
                        'published_at' => now()->subMonths(10),
                    ]
                );

                $calc = $gradingEngine->computeMark($item['ca'], $item['exam'], $sheet);

                StudentMark::updateOrCreate(
                    [
                        'course_assessment_sheet_id' => $sheet->id,
                        'student_id' => $student2->id,
                    ],
                    [
                        'course_registration_item_id' => $regItem->id,
                        'ca_score' => $item['ca'],
                        'exam_score' => $item['exam'],
                        'final_score' => $calc['final_score'],
                        'grade_letter' => $calc['grade_letter'],
                        'grade_point' => $calc['grade_point'],
                        'is_passed' => $calc['is_passed'],
                        'is_retake' => ! $calc['is_passed'],
                        'lecturer_remarks' => $calc['is_passed'] ? 'Completed Year 1 Semester 1.' : 'Failed. Requires Retake.',
                    ]
                );
            }

            $gradingEngine->calculateSemesterGpa($student2, $semester1);
        }

        if ($semester2) {
            // Seed Gloria's Year 1 Semester 2 registration (Took Y1S2 courses + RETAKE of CSC1102)
            $gloriaRegY1S2 = CourseRegistration::firstOrCreate(
                [
                    'student_id' => $student2->id,
                    'semester_id' => $semester2->id,
                ],
                [
                    'academic_year_id' => $academicYear2025->id,
                    'study_year' => 1,
                    'semester_number' => 2,
                    'status' => 'approved',
                    'total_credits' => 16.0,
                    'submitted_at' => now()->subMonths(6),
                    'approved_at' => now()->subMonths(5),
                    'advisor_remarks' => 'Approved semester registration with CSC1102 retake.',
                ]
            );

            $gloriaS2Courses = [
                ['code' => 'CSC1201', 'ca' => 32.0, 'exam' => 50.0, 'type' => 'Core', 'is_retake' => false], // 82.0 (A, 5.0 GP)
                ['code' => 'CSC1202', 'ca' => 30.0, 'exam' => 46.0, 'type' => 'Core', 'is_retake' => false], // 76.0 (B+, 4.5 GP)
                ['code' => 'BIT1201', 'ca' => 30.0, 'exam' => 44.0, 'type' => 'Core', 'is_retake' => false], // 74.0 (B, 4.0 GP)
                ['code' => 'CSC1102', 'ca' => 32.0, 'exam' => 46.0, 'type' => 'Retake', 'is_retake' => true], // 78.0 (B+, 4.5 GP, PASSED RETAKE)
            ];

            foreach ($gloriaS2Courses as $item) {
                $course = CourseUnit::where('code', $item['code'])->first();
                if (! $course) {
                    continue;
                }

                $regItem = CourseRegistrationItem::firstOrCreate(
                    [
                        'course_registration_id' => $gloriaRegY1S2->id,
                        'course_unit_id' => $course->id,
                    ],
                    [
                        'course_type' => $item['type'],
                        'credit_units' => $course->credit_units,
                        'status' => 'approved',
                    ]
                );

                $sheet = CourseAssessmentSheet::firstOrCreate(
                    [
                        'course_unit_id' => $course->id,
                        'semester_id' => $semester2->id,
                    ],
                    [
                        'academic_year_id' => $academicYear2025->id,
                        'instructor_id' => $instructor?->id,
                        'pass_mark' => 50.0,
                        'ca_weight' => 40.0,
                        'exam_weight' => 60.0,
                        'status' => 'published',
                        'published_at' => now()->subMonths(4),
                    ]
                );

                $calc = $gradingEngine->computeMark($item['ca'], $item['exam'], $sheet);

                StudentMark::updateOrCreate(
                    [
                        'course_assessment_sheet_id' => $sheet->id,
                        'student_id' => $student2->id,
                    ],
                    [
                        'course_registration_item_id' => $regItem->id,
                        'ca_score' => $item['ca'],
                        'exam_score' => $item['exam'],
                        'final_score' => $calc['final_score'],
                        'grade_letter' => $calc['grade_letter'],
                        'grade_point' => $calc['grade_point'],
                        'is_passed' => $calc['is_passed'],
                        'is_retake' => $item['is_retake'],
                        'lecturer_remarks' => $item['is_retake'] ? 'Successfully cleared course unit on Retake attempt.' : 'Completed Year 1 Semester 2.',
                    ]
                );
            }

            // Calculate Semester 2 performance & cumulative metrics (replaces fail attempt in CGPA)
            $perf2 = $gradingEngine->calculateSemesterGpa($student2, $semester2);
            $student2->update(['cumulative_gpa' => $perf2->cgpa]);

            $this->command?->info("Seeded Demo Student 2 (Completed Retake): {$student2->full_name} ({$student2->registration_number}) - Y1S2 Term GPA: {$perf2->gpa} | Replaced CGPA: {$perf2->cgpa}");
        }
    }
}
