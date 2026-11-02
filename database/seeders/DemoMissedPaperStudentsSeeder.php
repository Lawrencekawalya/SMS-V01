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

class DemoMissedPaperStudentsSeeder extends Seeder
{
    /**
     * Run the database seeds for Missed Papers & Special Examination demonstration.
     */
    public function run(): void
    {
        $bscs = Programme::where('code', 'BSCS')->first() ?? Programme::first();
        if (! $bscs) {
            $this->command?->error('No programme found to attach missed paper demo students.');

            return;
        }

        $curriculum = Curriculum::where('programme_id', $bscs->id)->where('is_active', true)->first()
            ?? Curriculum::where('programme_id', $bscs->id)->first();

        $mainCampus = Campus::where('is_main_campus', true)->first() ?? Campus::first();

        $academicYear2025 = AcademicYear::where('name', '2025/2026')->first() ?? AcademicYear::first();
        $semester1 = Semester::where('academic_year_id', $academicYear2025->id)
            ->where('semester_number', 1)
            ->first();

        if (! $semester1) {
            $this->command?->error('Semester 1 not found for seeding missed paper students.');

            return;
        }

        $gradingEngine = app(GradingEngineService::class);
        $instructor = User::where('email', 'admin@bsu.ac.ug')->first() ?? User::first();

        // =========================================================================
        // DEMO STUDENT 1: Kevin Mugisha (Missed Exam with Permission - Medical -> Special Exam)
        // =========================================================================
        $user1 = User::firstOrCreate(
            ['email' => 'kevin.mugisha@student.bsu.ac.ug'],
            [
                'name' => 'Kevin Mugisha',
                'password' => Hash::make('password'),
            ]
        );

        $student1 = Student::firstOrCreate(
            ['registration_number' => '25/BSU/BSCS/088'],
            [
                'user_id' => $user1->id,
                'student_number' => '25007088',
                'first_name' => 'Kevin',
                'last_name' => 'Mugisha',
                'other_names' => null,
                'gender' => 'male',
                'date_of_birth' => '2004-05-18',
                'email' => $user1->email,
                'phone' => '+256702345678',
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

        $regKevin = CourseRegistration::firstOrCreate(
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

        $kevinCourses = [
            ['code' => 'CSC1101', 'ca' => 30.0, 'exam' => 46.0, 'status' => 'present', 'type' => 'Core'], // 76.0 (B+, 4.5 GP, Passed)
            ['code' => 'BIT1101', 'ca' => 28.0, 'exam' => 44.0, 'status' => 'present', 'type' => 'Core'], // 72.0 (B+, 4.5 GP, Passed)
            ['code' => 'AFN1102', 'ca' => 26.0, 'exam' => 42.0, 'status' => 'present', 'type' => 'Elective'], // 68.0 (B, 4.0 GP, Passed)
            ['code' => 'CSC1102', 'ca' => 32.0, 'exam' => null, 'status' => 'missed_permission', 'type' => 'Core'], // INC (Medical - Eligible for Special Exam)
        ];

        foreach ($kevinCourses as $item) {
            $course = CourseUnit::where('code', $item['code'])->first();
            if (! $course) {
                continue;
            }

            $regItem = CourseRegistrationItem::firstOrCreate(
                [
                    'course_registration_id' => $regKevin->id,
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

            if ($item['status'] === 'missed_permission') {
                $computed = $gradingEngine->computeMark($item['ca'], null, $sheet, 'missed_permission');

                StudentMark::updateOrCreate(
                    [
                        'course_assessment_sheet_id' => $sheet->id,
                        'student_id' => $student1->id,
                    ],
                    [
                        'course_registration_item_id' => $regItem->id,
                        'ca_score' => $computed['ca_score'],
                        'exam_score' => null,
                        'exam_status' => 'missed_permission',
                        'sitting_type' => 'main',
                        'final_score' => null,
                        'grade_letter' => 'INC',
                        'grade_point' => null,
                        'is_passed' => false,
                        'is_retake' => false,
                        'lecturer_remarks' => 'Admitted to hospital with malaria. Medical certificate verified. Approved by Dean for Special Examination.',
                    ]
                );
            } else {
                $computed = $gradingEngine->computeMark($item['ca'], $item['exam'], $sheet);

                StudentMark::updateOrCreate(
                    [
                        'course_assessment_sheet_id' => $sheet->id,
                        'student_id' => $student1->id,
                    ],
                    [
                        'course_registration_item_id' => $regItem->id,
                        'ca_score' => $computed['ca_score'],
                        'exam_score' => $computed['exam_score'],
                        'exam_status' => 'present',
                        'sitting_type' => 'main',
                        'final_score' => $computed['final_score'],
                        'grade_letter' => $computed['grade_letter'],
                        'grade_point' => $computed['grade_point'],
                        'is_passed' => $computed['is_passed'],
                        'is_retake' => $computed['is_retake'],
                        'lecturer_remarks' => 'Completed Year 1 Semester 1.',
                    ]
                );
            }
        }

        $perf1 = $gradingEngine->calculateSemesterGpa($student1, $semester1);
        $student1->update(['cumulative_gpa' => $perf1->cgpa]);

        $this->command?->info("Seeded Demo Student 1 (Missed with Permission / INC): {$student1->full_name} ({$student1->registration_number}) - Y1S1 GPA: {$perf1->gpa} (No penalty for INC paper)");

        // =========================================================================
        // DEMO STUDENT 2: Brenda Atuhaire (Absent Without Permission - ABS / Automatic F / Retake)
        // =========================================================================
        $user2 = User::firstOrCreate(
            ['email' => 'brenda.atuhaire@student.bsu.ac.ug'],
            [
                'name' => 'Brenda Atuhaire',
                'password' => Hash::make('password'),
            ]
        );

        $student2 = Student::firstOrCreate(
            ['registration_number' => '25/BSU/BSCS/099'],
            [
                'user_id' => $user2->id,
                'student_number' => '25007099',
                'first_name' => 'Brenda',
                'last_name' => 'Atuhaire',
                'other_names' => null,
                'gender' => 'female',
                'date_of_birth' => '2004-09-10',
                'email' => $user2->email,
                'phone' => '+256773456789',
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

        $regBrenda = CourseRegistration::firstOrCreate(
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

        $brendaCourses = [
            ['code' => 'CSC1101', 'ca' => 28.0, 'exam' => 46.0, 'status' => 'present', 'type' => 'Core'], // 74.0 (B, 4.0 GP, Passed)
            ['code' => 'BIT1101', 'ca' => 28.0, 'exam' => 42.0, 'status' => 'present', 'type' => 'Core'], // 70.0 (B, 4.0 GP, Passed)
            ['code' => 'AFN1102', 'ca' => 26.0, 'exam' => 40.0, 'status' => 'present', 'type' => 'Elective'], // 66.0 (B, 4.0 GP, Passed)
            ['code' => 'CSC1102', 'ca' => 28.0, 'exam' => 0.0, 'status' => 'absent', 'type' => 'Core'], // ABS (Automatic 0 / F / Retake)
        ];

        foreach ($brendaCourses as $item) {
            $course = CourseUnit::where('code', $item['code'])->first();
            if (! $course) {
                continue;
            }

            $regItem = CourseRegistrationItem::firstOrCreate(
                [
                    'course_registration_id' => $regBrenda->id,
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

            if ($item['status'] === 'absent') {
                $computed = $gradingEngine->computeMark($item['ca'], 0.0, $sheet, 'absent');

                StudentMark::updateOrCreate(
                    [
                        'course_assessment_sheet_id' => $sheet->id,
                        'student_id' => $student2->id,
                    ],
                    [
                        'course_registration_item_id' => $regItem->id,
                        'ca_score' => $computed['ca_score'],
                        'exam_score' => 0.0,
                        'exam_status' => 'absent',
                        'sitting_type' => 'main',
                        'final_score' => $computed['final_score'],
                        'grade_letter' => 'F',
                        'grade_point' => 0.0,
                        'is_passed' => false,
                        'is_retake' => true,
                        'lecturer_remarks' => 'Absent from examination room without permission. NCHE unexcused absence policy applied: converted to automatic Fail (Retake).',
                    ]
                );
            } else {
                $computed = $gradingEngine->computeMark($item['ca'], $item['exam'], $sheet);

                StudentMark::updateOrCreate(
                    [
                        'course_assessment_sheet_id' => $sheet->id,
                        'student_id' => $student2->id,
                    ],
                    [
                        'course_registration_item_id' => $regItem->id,
                        'ca_score' => $computed['ca_score'],
                        'exam_score' => $computed['exam_score'],
                        'exam_status' => 'present',
                        'sitting_type' => 'main',
                        'final_score' => $computed['final_score'],
                        'grade_letter' => $computed['grade_letter'],
                        'grade_point' => $computed['grade_point'],
                        'is_passed' => $computed['is_passed'],
                        'is_retake' => $computed['is_retake'],
                        'lecturer_remarks' => 'Completed Year 1 Semester 1.',
                    ]
                );
            }
        }

        $perf2 = $gradingEngine->calculateSemesterGpa($student2, $semester1);
        $student2->update(['cumulative_gpa' => $perf2->cgpa]);

        $this->command?->info("Seeded Demo Student 2 (Absent Without Permission / ABS): {$student2->full_name} ({$student2->registration_number}) - Y1S1 GPA: {$perf2->gpa} (Penalized by 0.0 GP)");
    }
}
