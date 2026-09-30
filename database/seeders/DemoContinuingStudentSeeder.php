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

class DemoContinuingStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dit = Programme::where('code', 'DIT')->first();
        if (! $dit) {
            $this->command?->error('DIT Programme not found.');

            return;
        }

        $curriculum = Curriculum::where('programme_id', $dit->id)->where('is_active', true)->first()
            ?? Curriculum::where('programme_id', $dit->id)->first();

        $mainCampus = Campus::where('is_main_campus', true)->first() ?? Campus::first();
        $academicYear = AcademicYear::where('is_current', true)->first() ?? AcademicYear::first();

        // Semester 1 (Prior Completed Term)
        $semester1 = Semester::where('academic_year_id', $academicYear->id)
            ->where('semester_number', 1)
            ->first();

        // Semester 2 (Current Term for Registration)
        $semester2 = Semester::where('academic_year_id', $academicYear->id)
            ->where('semester_number', 2)
            ->first();

        if ($semester2) {
            // Ensure registration window is actively open for Semester 2 simulation
            $semester2->update([
                'registration_start_date' => now()->subDays(5)->startOfDay(),
                'registration_end_date' => now()->addDays(30)->endOfDay(),
                'add_drop_deadline' => now()->addDays(14)->endOfDay(),
            ]);
        }

        // 1. Create or Find User for Continuing Demo Student
        $user = User::firstOrCreate(
            ['email' => 'timothy.kigozi@student.bsu.ac.ug'],
            [
                'name' => 'Timothy Kigozi',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Create or Find Student Record (Year 1 Semester 2)
        $student = Student::firstOrCreate(
            ['registration_number' => '26/BSU/DIT/002'],
            [
                'user_id' => $user->id,
                'student_number' => '26006002',
                'first_name' => 'Timothy',
                'last_name' => 'Kigozi',
                'other_names' => null,
                'gender' => 'male',
                'date_of_birth' => '2004-06-15',
                'email' => $user->email,
                'phone' => '+256772123987',
                'campus_id' => $mainCampus->id,
                'programme_id' => $dit->id,
                'curriculum_id' => $curriculum?->id,
                'admission_academic_year_id' => $academicYear->id,
                'study_mode' => 'Day',
                'intake' => 'August',
                'current_study_year' => 1,
                'current_semester' => 2, // Continuing student in Y1S2
                'status' => 'active',
                'cumulative_gpa' => 0.0,
            ]
        );

        // Ensure study stage is exactly Y1S2
        $student->update([
            'current_study_year' => 1,
            'current_semester' => 2,
        ]);

        if (! $semester1) {
            $this->command?->warn('Semester 1 not found for prior results.');

            return;
        }

        // 3. Create Approved Prior Semester Registration for Y1S1
        $registrationY1S1 = CourseRegistration::firstOrCreate(
            [
                'student_id' => $student->id,
                'semester_id' => $semester1->id,
            ],
            [
                'academic_year_id' => $academicYear->id,
                'study_year' => 1,
                'semester_number' => 1,
                'status' => 'approved',
                'total_credits' => 27.0,
                'submitted_at' => now()->subMonths(4),
                'approved_at' => now()->subMonths(3),
                'advisor_remarks' => 'Approved full core load for Year 1 Semester 1.',
            ]
        );

        $registrationY1S1->update([
            'status' => 'approved',
            'study_year' => 1,
            'semester_number' => 1,
            'total_credits' => 27.0,
        ]);

        // 4. Register all 8 Y1S1 Course Units and Seed Published Scores
        $y1s1CoursesData = [
            ['code' => 'DIT1101', 'ca' => 32.0, 'exam' => 48.0], // Total 80.0 (A, 5.0 GP)
            ['code' => 'DIT1102', 'ca' => 30.0, 'exam' => 45.0], // Total 75.0 (B+, 4.5 GP)
            ['code' => 'DIT1103', 'ca' => 28.0, 'exam' => 44.0], // Total 72.0 (B+, 4.5 GP)
            ['code' => 'DIT1104', 'ca' => 26.0, 'exam' => 40.0], // Total 66.0 (B, 4.0 GP)
            ['code' => 'DIT1105', 'ca' => 28.0, 'exam' => 42.0], // Total 70.0 (B+, 4.5 GP)
            ['code' => 'DIT1106', 'ca' => 32.0, 'exam' => 46.0], // Total 78.0 (B+, 4.5 GP)
            ['code' => 'DIT1107', 'ca' => 30.0, 'exam' => 45.0], // Total 75.0 (B+, 4.5 GP)
            ['code' => 'DIT1108', 'ca' => 34.0, 'exam' => 50.0], // Total 84.0 (A, 5.0 GP)
        ];

        $gradingEngine = app(GradingEngineService::class);
        $instructor = User::first();

        foreach ($y1s1CoursesData as $data) {
            $course = CourseUnit::where('code', $data['code'])->first();
            if (! $course) {
                continue;
            }

            // Create or update registration item
            $regItem = CourseRegistrationItem::firstOrCreate(
                [
                    'course_registration_id' => $registrationY1S1->id,
                    'course_unit_id' => $course->id,
                ],
                [
                    'course_type' => 'Core',
                    'credit_units' => $course->credit_units,
                    'status' => 'approved',
                ]
            );

            $regItem->update(['status' => 'approved']);

            // Find or create published assessment sheet for this course in Y1S1
            $sheet = CourseAssessmentSheet::firstOrCreate(
                [
                    'course_unit_id' => $course->id,
                    'semester_id' => $semester1->id,
                ],
                [
                    'academic_year_id' => $academicYear->id,
                    'instructor_user_id' => $instructor?->id,
                    'pass_mark' => 50.0,
                    'ca_weight' => 40.0,
                    'exam_weight' => 60.0,
                    'status' => 'published',
                    'published_at' => now()->subMonths(2),
                ]
            );

            // Compute grade details
            $calc = $gradingEngine->computeMark($data['ca'], $data['exam'], $sheet);

            // Create or update student mark
            StudentMark::updateOrCreate(
                [
                    'course_assessment_sheet_id' => $sheet->id,
                    'student_id' => $student->id,
                ],
                [
                    'course_registration_item_id' => $regItem->id,
                    'ca_score' => $data['ca'],
                    'exam_score' => $data['exam'],
                    'final_score' => $calc['final_score'],
                    'grade_letter' => $calc['grade_letter'],
                    'grade_point' => $calc['grade_point'],
                    'is_passed' => $calc['is_passed'],
                    'lecturer_remarks' => 'Completed and evaluated in Year 1 Semester 1.',
                ]
            );
        }

        // 5. Freeze Official StudentSemesterPerformance for Y1S1
        $performance = $gradingEngine->calculateSemesterGpa($student, $semester1);

        // Update student cumulative GPA to reflect completed Y1S1
        $student->update([
            'cumulative_gpa' => $performance->cgpa,
            'current_study_year' => 1,
            'current_semester' => 2,
        ]);

        $this->command?->info("Demo Continuing Student seeded successfully: {$student->full_name} ({$student->registration_number})");
        $this->command?->info("Y1S1 Performance: GPA {$performance->gpa} | CGPA {$performance->cgpa} | Standing: {$performance->academic_standing}");
    }
}
