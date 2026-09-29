<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\CourseRegistration;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseRegistrationSeeder extends Seeder
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

        $advisorUser = User::first();

        // 1. Ronald Mukasa (26/BSCS/001) - Year 1, Sem 1 Fresher -> Submitted (Pending Approval)
        $ronald = Student::where('registration_number', '26/BSCS/001')->first();
        if ($ronald) {
            $reg = CourseRegistration::updateOrCreate(
                [
                    'student_id' => $ronald->id,
                    'semester_id' => $activeSemester->id,
                ],
                [
                    'academic_year_id' => $currentYear->id,
                    'study_year' => 1,
                    'semester_number' => 1,
                    'status' => 'submitted',
                    'submitted_at' => now()->subDays(1),
                    'total_credits' => 15.0,
                    'advisor_remarks' => null,
                ]
            );

            $this->seedItems($reg, [
                ['code' => 'CSC1101', 'type' => 'Core', 'credits' => 4.0, 'status' => 'registered'],
                ['code' => 'CSC1102', 'type' => 'Core', 'credits' => 4.0, 'status' => 'registered'],
                ['code' => 'BIT1101', 'type' => 'Core', 'credits' => 4.0, 'status' => 'registered'],
                ['code' => 'AFN1102', 'type' => 'Elective', 'credits' => 3.0, 'status' => 'registered'],
            ]);
            $reg->recalculateTotalCredits();
        }

        // 2. Brenda Atuhaire (26/BSCS/002) - Year 1, Sem 1 -> Draft
        $brenda = Student::where('registration_number', '26/BSCS/002')->first();
        if ($brenda) {
            $reg = CourseRegistration::updateOrCreate(
                [
                    'student_id' => $brenda->id,
                    'semester_id' => $activeSemester->id,
                ],
                [
                    'academic_year_id' => $currentYear->id,
                    'study_year' => 1,
                    'semester_number' => 1,
                    'status' => 'draft',
                    'submitted_at' => null,
                    'total_credits' => 12.0,
                    'advisor_remarks' => null,
                ]
            );

            $this->seedItems($reg, [
                ['code' => 'CSC1101', 'type' => 'Core', 'credits' => 4.0, 'status' => 'registered'],
                ['code' => 'CSC1102', 'type' => 'Core', 'credits' => 4.0, 'status' => 'registered'],
                ['code' => 'BIT1101', 'type' => 'Core', 'credits' => 4.0, 'status' => 'registered'],
            ]);
            $reg->recalculateTotalCredits();
        }

        // 3. Timothy Mugisha (25/BSCS/015) - Year 2, Sem 1 -> Approved
        $timothy = Student::where('registration_number', '25/BSCS/015')->first();
        if ($timothy) {
            $reg = CourseRegistration::updateOrCreate(
                [
                    'student_id' => $timothy->id,
                    'semester_id' => $activeSemester->id,
                ],
                [
                    'academic_year_id' => $currentYear->id,
                    'study_year' => 2,
                    'semester_number' => 1,
                    'status' => 'approved',
                    'submitted_at' => now()->subDays(4),
                    'approved_at' => now()->subDays(2),
                    'approved_by_user_id' => $advisorUser?->id,
                    'advisor_remarks' => 'All prerequisites and course units verified. Approved.',
                    'total_credits' => 12.0,
                ]
            );

            $this->seedItems($reg, [
                ['code' => 'CSC2101', 'type' => 'Core', 'credits' => 4.0, 'status' => 'approved'],
                ['code' => 'CSC2102', 'type' => 'Core', 'credits' => 4.0, 'status' => 'approved'],
                ['code' => 'BIT2101', 'type' => 'Core', 'credits' => 4.0, 'status' => 'approved'],
            ]);
            $reg->recalculateTotalCredits();
        }

        // 4. Sarah Namubiru (25/BSSE/008) - Year 2, Sem 1 -> Add/Drop Pending
        $sarah = Student::where('registration_number', '25/BSSE/008')->first();
        if ($sarah) {
            $reg = CourseRegistration::updateOrCreate(
                [
                    'student_id' => $sarah->id,
                    'semester_id' => $activeSemester->id,
                ],
                [
                    'academic_year_id' => $currentYear->id,
                    'study_year' => 2,
                    'semester_number' => 1,
                    'status' => 'add_drop_pending',
                    'submitted_at' => now()->subDays(5),
                    'approved_at' => now()->subDays(3),
                    'approved_by_user_id' => $advisorUser?->id,
                    'advisor_remarks' => 'Elective change requested during Add/Drop window.',
                    'total_credits' => 11.0,
                ]
            );

            $this->seedItems($reg, [
                ['code' => 'CSC2101', 'type' => 'Core', 'credits' => 4.0, 'status' => 'approved'],
                ['code' => 'CSC1202', 'type' => 'Core', 'credits' => 4.0, 'status' => 'approved'],
                ['code' => 'MTH1102', 'type' => 'Core', 'credits' => 3.0, 'status' => 'approved'],
                [
                    'code' => 'AFN1102',
                    'type' => 'Elective',
                    'credits' => 3.0,
                    'status' => 'dropped',
                    'dropped_at' => now()->subHours(6),
                    'drop_reason' => 'Timetable clash with CSC2101 lab session',
                ],
            ]);
            $reg->recalculateTotalCredits();
        }

        // 5. Emmanuel Twinomujuni (26/DCA/001) - Year 1, Sem 1 DCA -> Approved
        $emmanuel = Student::where('registration_number', '26/DCA/001')->first();
        if ($emmanuel) {
            $reg = CourseRegistration::updateOrCreate(
                [
                    'student_id' => $emmanuel->id,
                    'semester_id' => $activeSemester->id,
                ],
                [
                    'academic_year_id' => $currentYear->id,
                    'study_year' => 1,
                    'semester_number' => 1,
                    'status' => 'approved',
                    'submitted_at' => now()->subDays(3),
                    'approved_at' => now()->subDay(),
                    'approved_by_user_id' => $advisorUser?->id,
                    'advisor_remarks' => 'Diploma Year 1 registration signed off.',
                    'total_credits' => 15.0,
                ]
            );

            $this->seedItems($reg, [
                ['code' => 'CSC1101', 'type' => 'Core', 'credits' => 4.0, 'status' => 'approved'],
                ['code' => 'BIT1101', 'type' => 'Core', 'credits' => 4.0, 'status' => 'approved'],
                ['code' => 'BIT1102', 'type' => 'Core', 'credits' => 4.0, 'status' => 'approved'],
                ['code' => 'AFN1102', 'type' => 'Elective', 'credits' => 3.0, 'status' => 'approved'],
            ]);
            $reg->recalculateTotalCredits();
        }

        // 6. Derrick Otim (24/BIT/012) - Year 3, Sem 1 BIT -> Rejected
        $derrick = Student::where('registration_number', '24/BIT/012')->first();
        if ($derrick) {
            $reg = CourseRegistration::updateOrCreate(
                [
                    'student_id' => $derrick->id,
                    'semester_id' => $activeSemester->id,
                ],
                [
                    'academic_year_id' => $currentYear->id,
                    'study_year' => 3,
                    'semester_number' => 1,
                    'status' => 'rejected',
                    'submitted_at' => now()->subDays(2),
                    'approved_by_user_id' => $advisorUser?->id,
                    'advisor_remarks' => 'Selected credit load (8.0 CU) is below the institutional minimum of 12.0 CU. Please add an elective from the approved pool.',
                    'total_credits' => 8.0,
                ]
            );

            $this->seedItems($reg, [
                ['code' => 'BIT3101', 'type' => 'Core', 'credits' => 4.0, 'status' => 'registered'],
                ['code' => 'CSC2102', 'type' => 'Core', 'credits' => 4.0, 'status' => 'registered'],
            ]);
            $reg->recalculateTotalCredits();
        }
    }

    /**
     * Helper to seed registration line items safely.
     *
     * @param  array<int, array{code: string, type: string, credits: float, status: string, dropped_at?: mixed, drop_reason?: string|null}>  $items
     */
    private function seedItems(CourseRegistration $registration, array $items): void
    {
        foreach ($items as $item) {
            $course = CourseUnit::where('code', $item['code'])->first();
            if ($course) {
                CourseRegistrationItem::updateOrCreate(
                    [
                        'course_registration_id' => $registration->id,
                        'course_unit_id' => $course->id,
                    ],
                    [
                        'course_type' => $item['type'],
                        'credit_units' => $item['credits'],
                        'status' => $item['status'],
                        'dropped_at' => $item['dropped_at'] ?? null,
                        'drop_reason' => $item['drop_reason'] ?? null,
                    ]
                );
            }
        }
    }
}
