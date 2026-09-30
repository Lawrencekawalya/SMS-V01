<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\CourseAssessmentSheet;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use App\Models\GradeAuditLog;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\StudentSemesterPerformance;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseAssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->first() ?? AcademicYear::first();
        if (! $currentYear) {
            return;
        }

        $activeSemester = Semester::where('academic_year_id', $currentYear->id)
            ->where('is_active', true)
            ->first() ?? Semester::where('academic_year_id', $currentYear->id)->first();

        if (! $activeSemester) {
            return;
        }

        $instructor = User::first() ?? User::factory()->create([
            'name' => 'Dr. Alex Kato',
            'email' => 'alex.kato@bsu.ac.ug',
        ]);

        $hodUser = User::where('email', '!=', $instructor->email)->first() ?? User::factory()->create([
            'name' => 'Prof. Christine Nabukenya',
            'email' => 'hod.cs@bsu.ac.ug',
        ]);

        $caWeight = (float) config('academic.assessment_ca_weight', 40.0);
        $examWeight = (float) config('academic.assessment_exam_weight', 60.0);
        $passMark = (float) config('academic.assessment_pass_mark', 50.0);

        // Course 1: CSC2101 (Data Structures & Algorithms) -> Submitted to HoD for moderation
        $csc2101 = CourseUnit::where('code', 'CSC2101')->first();
        if ($csc2101) {
            $sheet1 = CourseAssessmentSheet::updateOrCreate(
                [
                    'course_unit_id' => $csc2101->id,
                    'semester_id' => $activeSemester->id,
                ],
                [
                    'academic_year_id' => $currentYear->id,
                    'instructor_id' => $instructor->id,
                    'ca_weight' => $caWeight,
                    'exam_weight' => $examWeight,
                    'pass_mark' => $passMark,
                    'status' => 'submitted_to_hod',
                    'submitted_at' => now()->subDays(2),
                    'moderation_remarks' => 'Continuous assessment and exam marks compiled for Year 2 Computer Science cohort.',
                ]
            );

            $this->seedMarksForSheet($sheet1, [
                // Timothy Mugisha: 34 CA + 48 Exam = 82 (A, 5.0 GP)
                '25/BSCS/015' => [
                    'ca_score' => 34.0,
                    'exam_score' => 48.0,
                    'final_score' => 82.0,
                    'grade_letter' => 'A',
                    'grade_point' => 5.0,
                    'is_passed' => true,
                    'is_retake' => false,
                    'remarks' => 'Superb algorithmic comprehension',
                ],
                // Sarah Namubiru: 28 CA + 42 Exam = 70 (B, 4.0 GP)
                '25/BSSE/008' => [
                    'ca_score' => 28.0,
                    'exam_score' => 42.0,
                    'final_score' => 70.0,
                    'grade_letter' => 'B',
                    'grade_point' => 4.0,
                    'is_passed' => true,
                    'is_retake' => false,
                    'remarks' => 'Good performance in trees & graphs',
                ],
            ], $instructor);
        }

        // Course 2: BIT2101 (Database Management Systems) -> Officially Published by Senate
        $bit2101 = CourseUnit::where('code', 'BIT2101')->first();
        if ($bit2101) {
            $sheet2 = CourseAssessmentSheet::updateOrCreate(
                [
                    'course_unit_id' => $bit2101->id,
                    'semester_id' => $activeSemester->id,
                ],
                [
                    'academic_year_id' => $currentYear->id,
                    'instructor_id' => $instructor->id,
                    'ca_weight' => $caWeight,
                    'exam_weight' => $examWeight,
                    'pass_mark' => $passMark,
                    'status' => 'published',
                    'submitted_at' => now()->subDays(6),
                    'moderated_at' => now()->subDays(4),
                    'moderated_by_id' => $hodUser->id,
                    'published_at' => now()->subDays(1),
                    'published_by_id' => $hodUser->id,
                    'moderation_remarks' => 'Endorsed by Departmental Board & published by Senate.',
                ]
            );

            $this->seedMarksForSheet($sheet2, [
                // Timothy Mugisha: 36 CA + 50 Exam = 86 (A, 5.0 GP)
                '25/BSCS/015' => [
                    'ca_score' => 36.0,
                    'exam_score' => 50.0,
                    'final_score' => 86.0,
                    'grade_letter' => 'A',
                    'grade_point' => 5.0,
                    'is_passed' => true,
                    'is_retake' => false,
                    'remarks' => 'Outstanding SQL and normalization mastery',
                ],
            ], $instructor);

            // Seed an audit log on Timothy's BIT2101 mark to demonstrate audit trail
            $timothy = Student::where('registration_number', '25/BSCS/015')->first();
            if ($timothy) {
                $mark = StudentMark::where('course_assessment_sheet_id', $sheet2->id)
                    ->where('student_id', $timothy->id)
                    ->first();

                if ($mark) {
                    GradeAuditLog::updateOrCreate(
                        [
                            'student_mark_id' => $mark->id,
                            'score_type' => 'exam',
                        ],
                        [
                            'changed_by_id' => $instructor->id,
                            'old_score' => 47.0,
                            'new_score' => 50.0,
                            'reason' => 'Recount of marks in Section B database normalization question.',
                        ]
                    );
                }

                // Seed semester performance record for Timothy
                StudentSemesterPerformance::updateOrCreate(
                    [
                        'student_id' => $timothy->id,
                        'semester_id' => $activeSemester->id,
                    ],
                    [
                        'academic_year_id' => $currentYear->id,
                        'credit_units_registered' => 12.0,
                        'credit_units_earned' => 12.0,
                        'weighted_grade_points' => 54.0,
                        'gpa' => 4.50,
                        'cumulative_credit_units_registered' => 36.0,
                        'cumulative_credit_units_earned' => 36.0,
                        'cumulative_weighted_grade_points' => 158.0,
                        'cgpa' => 4.39,
                        'academic_standing' => 'Normal Progress',
                    ]
                );
            }
        }

        // Course 3: CSC1101 (Introduction to Computer Science) -> In Draft (Fresher class)
        $csc1101 = CourseUnit::where('code', 'CSC1101')->first();
        if ($csc1101) {
            $sheet3 = CourseAssessmentSheet::updateOrCreate(
                [
                    'course_unit_id' => $csc1101->id,
                    'semester_id' => $activeSemester->id,
                ],
                [
                    'academic_year_id' => $currentYear->id,
                    'instructor_id' => $instructor->id,
                    'ca_weight' => $caWeight,
                    'exam_weight' => $examWeight,
                    'pass_mark' => $passMark,
                    'status' => 'draft',
                    'submitted_at' => null,
                    'moderation_remarks' => null,
                ]
            );

            $this->seedMarksForSheet($sheet3, [
                // Ronald Mukasa: CA entered (31/40), Exam pending
                '26/BSCS/001' => [
                    'ca_score' => 31.0,
                    'exam_score' => null,
                    'final_score' => null,
                    'grade_letter' => null,
                    'grade_point' => null,
                    'is_passed' => false,
                    'is_retake' => false,
                    'remarks' => 'Assignment 1 & mid-term test completed.',
                ],
                // Brenda Atuhaire: CA entered (35/40), Exam pending
                '26/BSCS/002' => [
                    'ca_score' => 35.0,
                    'exam_score' => null,
                    'final_score' => null,
                    'grade_letter' => null,
                    'grade_point' => null,
                    'is_passed' => false,
                    'is_retake' => false,
                    'remarks' => 'Excellent coursework participation.',
                ],
            ], $instructor);
        }

        // Course 4: CSC2102 (Object Oriented Programming) -> Draft
        $csc2102 = CourseUnit::where('code', 'CSC2102')->first();
        if ($csc2102) {
            CourseAssessmentSheet::updateOrCreate(
                [
                    'course_unit_id' => $csc2102->id,
                    'semester_id' => $activeSemester->id,
                ],
                [
                    'academic_year_id' => $currentYear->id,
                    'instructor_id' => $instructor->id,
                    'ca_weight' => $caWeight,
                    'exam_weight' => $examWeight,
                    'pass_mark' => $passMark,
                    'status' => 'draft',
                    'submitted_at' => null,
                    'moderation_remarks' => null,
                ]
            );
        }
    }

    /**
     * Helper to seed marks for students registered for a course unit.
     *
     * @param  array<string, array<string, mixed>>  $studentMarksMap
     */
    protected function seedMarksForSheet(CourseAssessmentSheet $sheet, array $studentMarksMap, User $instructor): void
    {
        foreach ($studentMarksMap as $regNo => $markData) {
            $student = Student::where('registration_number', $regNo)->first();
            if (! $student) {
                continue;
            }

            // Find or create registration item
            $regItem = CourseRegistrationItem::whereHas('courseRegistration', function ($q) use ($student, $sheet) {
                $q->where('student_id', $student->id)->where('semester_id', $sheet->semester_id);
            })->where('course_unit_id', $sheet->course_unit_id)
                ->where('status', '!=', 'dropped')
                ->first();

            if (! $regItem) {
                // Ensure a registration item exists for this student and course
                $reg = $student->courseRegistrations()->where('semester_id', $sheet->semester_id)->first();
                if ($reg) {
                    $regItem = CourseRegistrationItem::create([
                        'course_registration_id' => $reg->id,
                        'course_unit_id' => $sheet->course_unit_id,
                        'course_type' => 'Core',
                        'credit_units' => $sheet->courseUnit->credit_units ?? 4.0,
                        'status' => 'approved',
                    ]);
                }
            }

            if ($regItem) {
                StudentMark::updateOrCreate(
                    [
                        'course_assessment_sheet_id' => $sheet->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'course_registration_item_id' => $regItem->id,
                        'ca_score' => $markData['ca_score'],
                        'exam_score' => $markData['exam_score'],
                        'final_score' => $markData['final_score'],
                        'grade_letter' => $markData['grade_letter'],
                        'grade_point' => $markData['grade_point'],
                        'is_passed' => $markData['is_passed'],
                        'is_retake' => $markData['is_retake'],
                        'lecturer_remarks' => $markData['remarks'],
                    ]
                );
            }
        }
    }
}
