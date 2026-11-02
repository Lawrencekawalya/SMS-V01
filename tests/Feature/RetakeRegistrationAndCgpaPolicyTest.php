<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\CourseAssessmentSheet;
use App\Models\CourseRegistration;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use App\Models\Curriculum;
use App\Models\CurriculumCourse;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\University;
use App\Models\User;
use App\Services\CourseEligibilityService;
use App\Services\GradingEngineService;
use Database\Seeders\AcademicCalendarSeeder;
use Database\Seeders\CampusSeeder;
use Database\Seeders\CourseUnitSeeder;
use Database\Seeders\CurriculumSeeder;
use Database\Seeders\DemoRetakeStudentsSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\FacultySeeder;
use Database\Seeders\GradingPolicySeeder;
use Database\Seeders\ProgrammeSeeder;
use Database\Seeders\UniversitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetakeRegistrationAndCgpaPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected Student $student;

    protected Semester $semester1;

    protected Semester $semester2;

    protected AcademicYear $academicYear;

    protected Curriculum $curriculum;

    protected CourseUnit $coreCourse1;

    protected CourseUnit $coreCourse2;

    protected CourseUnit $coreCourse3;

    protected function setUp(): void
    {
        parent::setUp();

        $university = University::factory()->create(['name' => 'Bishop Stuart University']);
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id]);
        $department = Department::factory()->create(['faculty_id' => $faculty->id]);
        $programme = Programme::factory()->create(['department_id' => $department->id]);

        $this->academicYear = AcademicYear::factory()->create([
            'start_date' => now()->subMonths(6)->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
        ]);

        $this->semester1 = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester 1',
            'semester_number' => 1,
            'start_date' => now()->subMonths(5)->toDateString(),
            'end_date' => now()->subMonths(2)->toDateString(),
            'is_active' => false,
        ]);

        $this->semester2 = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester 2',
            'semester_number' => 2,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'registration_start_date' => now()->subDays(10)->toDateString(),
            'registration_end_date' => now()->addDays(14)->toDateString(),
            'is_active' => true,
        ]);

        $this->curriculum = Curriculum::factory()->create([
            'programme_id' => $programme->id,
            'min_graduation_credits' => 100,
        ]);

        $this->coreCourse1 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1101',
            'name' => 'Structured Programming in C',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $this->coreCourse2 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1102',
            'name' => 'Computer Architecture',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $this->coreCourse3 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1201',
            'name' => 'Object Oriented Programming',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $this->curriculum->id,
            'course_unit_id' => $this->coreCourse1->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $this->curriculum->id,
            'course_unit_id' => $this->coreCourse2->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $this->curriculum->id,
            'course_unit_id' => $this->coreCourse3->id,
            'study_year' => 1,
            'semester' => 2,
            'course_type' => 'Core',
        ]);

        $user = User::factory()->create();
        $this->student = Student::factory()->create([
            'user_id' => $user->id,
            'campus_id' => $campus->id,
            'programme_id' => $programme->id,
            'curriculum_id' => $this->curriculum->id,
            'current_study_year' => 1,
            'current_semester' => 2,
            'status' => 'active',
        ]);
    }

    public function test_failed_course_appears_in_retake_section_during_course_registration(): void
    {
        // Student enrolled in CSC1101 in Semester 1, sat for exam, and scored F (requires retake)
        $reg1 = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'approved',
        ]);

        $item1 = CourseRegistrationItem::create([
            'course_registration_id' => $reg1->id,
            'course_unit_id' => $this->coreCourse1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $sheet1 = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->coreCourse1->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'published',
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student->id,
            'ca_score' => 15.0,
            'exam_score' => 25.0,
            'final_score' => 40.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);

        // When viewing registration page for Semester 2:
        $response = $this->get(route('registration.create', [
            'student_id' => $this->student->id,
            'semester_id' => $this->semester2->id,
        ]));

        $response->assertOk();
        $response->assertSee('Retake / Carryover Courses');
        $response->assertSee('CSC1101');
        $response->assertSee('Structured Programming in C');
        $response->assertSee('Grade F (40%)');
        $response->assertSee('4.0 CU');
    }

    public function test_can_register_for_retake_course_and_it_is_saved_with_retake_type(): void
    {
        $reg1 = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'approved',
        ]);

        $item1 = CourseRegistrationItem::create([
            'course_registration_id' => $reg1->id,
            'course_unit_id' => $this->coreCourse1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        // Past failed mark for CSC1101
        $sheet1 = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->coreCourse1->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'published',
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student->id,
            'ca_score' => 10.0,
            'exam_score' => 30.0,
            'final_score' => 40.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);

        // Register for current term's core course (CSC1201) + the Retake course (CSC1101)
        $payload = [
            'student_id' => $this->student->id,
            'semester_id' => $this->semester2->id,
            'course_unit_ids' => [
                $this->coreCourse3->id, // Regular term course
                $this->coreCourse1->id, // Retake course
            ],
            'total_credits' => 8.0,
            'status' => 'submitted',
        ];

        $response = $this->post(route('registration.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('course_registrations', [
            'student_id' => $this->student->id,
            'semester_id' => $this->semester2->id,
            'total_credits' => 8.0,
        ]);

        // Regular course gets Core, failed course gets Retake!
        $this->assertDatabaseHas('course_registration_items', [
            'course_unit_id' => $this->coreCourse3->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
        ]);

        $this->assertDatabaseHas('course_registration_items', [
            'course_unit_id' => $this->coreCourse1->id,
            'course_type' => 'Retake',
            'credit_units' => 4.0,
        ]);
    }

    public function test_cgpa_calculation_replaces_fail_grade_under_nche_standard_policy(): void
    {
        config(['academic.retake_cgpa_policy' => 'replace_fail']);

        $gradingEngine = app(GradingEngineService::class);

        $reg1 = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'approved',
        ]);

        $item1 = CourseRegistrationItem::create([
            'course_registration_id' => $reg1->id,
            'course_unit_id' => $this->coreCourse1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $item2 = CourseRegistrationItem::create([
            'course_registration_id' => $reg1->id,
            'course_unit_id' => $this->coreCourse2->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        // Semester 1:
        // Course 1 (4 CU): F (0.0 GP) -> 0.0 WGP
        // Course 2 (4 CU): A (5.0 GP) -> 20.0 WGP
        // Sem 1 Term GPA: 20.0 / 8.0 = 2.50
        $sheet1 = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->coreCourse1->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student->id,
            'final_score' => 40.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);

        $sheet2 = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->coreCourse2->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet2->id,
            'course_registration_item_id' => $item2->id,
            'student_id' => $this->student->id,
            'final_score' => 85.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
            'is_retake' => false,
        ]);

        $perf1 = $gradingEngine->calculateSemesterGpa($this->student, $this->semester1);
        $this->assertEquals(2.50, $perf1->gpa);
        $this->assertEquals(2.50, $perf1->cgpa);
        $this->assertEquals(8.0, $perf1->cumulative_credit_units_registered);

        // Semester 2:
        $reg2 = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester2->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'approved',
        ]);

        $item1Retake = CourseRegistrationItem::create([
            'course_registration_id' => $reg2->id,
            'course_unit_id' => $this->coreCourse1->id,
            'course_type' => 'Retake',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $item3 = CourseRegistrationItem::create([
            'course_registration_id' => $reg2->id,
            'course_unit_id' => $this->coreCourse3->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        // Semester 2: Student retakes Course 1 and gets B (4.0 GP, 16.0 WGP)
        // And takes Course 3 (4 CU) and gets A (5.0 GP, 20.0 WGP)
        // Sem 2 Term GPA: (16 + 20) / 8 = 36.0 / 8 = 4.50
        $sheet1Retake = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->coreCourse1->id,
            'semester_id' => $this->semester2->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1Retake->id,
            'course_registration_item_id' => $item1Retake->id,
            'student_id' => $this->student->id,
            'final_score' => 72.0,
            'grade_letter' => 'B',
            'grade_point' => 4.0,
            'is_passed' => true,
            'is_retake' => false,
        ]);

        $sheet3 = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->coreCourse3->id,
            'semester_id' => $this->semester2->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet3->id,
            'course_registration_item_id' => $item3->id,
            'student_id' => $this->student->id,
            'final_score' => 88.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
            'is_retake' => false,
        ]);

        $perf2 = $gradingEngine->calculateSemesterGpa($this->student, $this->semester2);

        // Term 2 GPA remains 4.50
        $this->assertEquals(4.50, $perf2->gpa);

        // Under 'replace_fail' policy:
        // Distinct courses:
        // - Course 1: Best attempt is B (4 CU, 4.0 GP = 16.0 WGP)
        // - Course 2: Attempt is A (4 CU, 5.0 GP = 20.0 WGP)
        // - Course 3: Attempt is A (4 CU, 5.0 GP = 20.0 WGP)
        // Total Cumulative Registered Credits = 4 + 4 + 4 = 12.0 CU (NOT 16.0 CU!)
        // Total Cumulative Points = 16.0 + 20.0 + 20.0 = 56.0
        // Cumulative CGPA = 56.0 / 12.0 = 4.67!
        $this->assertEquals(12.0, $perf2->cumulative_credit_units_registered);
        $this->assertEquals(12.0, $perf2->cumulative_credit_units_earned);
        $this->assertEquals(56.0, $perf2->cumulative_weighted_grade_points);
        $this->assertEquals(4.67, $perf2->cgpa);
        $this->assertEquals('Normal Progress', $perf2->academic_standing);
    }

    public function test_cgpa_calculation_accumulates_all_attempts_when_policy_is_set_to_accumulate_all(): void
    {
        config(['academic.retake_cgpa_policy' => 'accumulate_all']);

        $gradingEngine = app(GradingEngineService::class);

        $reg1 = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'approved',
        ]);

        $item1 = CourseRegistrationItem::create([
            'course_registration_id' => $reg1->id,
            'course_unit_id' => $this->coreCourse1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $item2 = CourseRegistrationItem::create([
            'course_registration_id' => $reg1->id,
            'course_unit_id' => $this->coreCourse2->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        // Semester 1:
        // Course 1 (4 CU): F (0.0 GP) -> 0.0 WGP
        // Course 2 (4 CU): A (5.0 GP) -> 20.0 WGP
        $sheet1 = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->coreCourse1->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student->id,
            'final_score' => 40.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);

        $sheet2 = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->coreCourse2->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet2->id,
            'course_registration_item_id' => $item2->id,
            'student_id' => $this->student->id,
            'final_score' => 85.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
            'is_retake' => false,
        ]);

        // Semester 2:
        $reg2 = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester2->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'approved',
        ]);

        $item1Retake = CourseRegistrationItem::create([
            'course_registration_id' => $reg2->id,
            'course_unit_id' => $this->coreCourse1->id,
            'course_type' => 'Retake',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $item3 = CourseRegistrationItem::create([
            'course_registration_id' => $reg2->id,
            'course_unit_id' => $this->coreCourse3->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        // Semester 2: Student retakes Course 1 and gets B (4.0 GP, 16.0 WGP)
        // And takes Course 3 (4 CU) and gets A (5.0 GP, 20.0 WGP)
        $sheet1Retake = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->coreCourse1->id,
            'semester_id' => $this->semester2->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1Retake->id,
            'course_registration_item_id' => $item1Retake->id,
            'student_id' => $this->student->id,
            'final_score' => 72.0,
            'grade_letter' => 'B',
            'grade_point' => 4.0,
            'is_passed' => true,
            'is_retake' => false,
        ]);

        $sheet3 = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->coreCourse3->id,
            'semester_id' => $this->semester2->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet3->id,
            'course_registration_item_id' => $item3->id,
            'student_id' => $this->student->id,
            'final_score' => 88.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
            'is_retake' => false,
        ]);

        $perf2 = $gradingEngine->calculateSemesterGpa($this->student, $this->semester2);

        // Under 'accumulate_all' policy:
        // Total Cumulative Registered Credits = 4 + 4 + 4 + 4 = 16.0 CU
        // Total Cumulative Points = 0.0 + 20.0 + 16.0 + 20.0 = 56.0
        // Cumulative CGPA = 56.0 / 16.0 = 3.50
        $this->assertEquals(16.0, $perf2->cumulative_credit_units_registered);
        $this->assertEquals(56.0, $perf2->cumulative_weighted_grade_points);
        $this->assertEquals(3.50, $perf2->cgpa);
    }

    public function test_demo_retake_students_seeder_runs_and_seeds_expected_demo_profiles(): void
    {
        $this->seed(UniversitySeeder::class);
        $this->seed(CampusSeeder::class);
        $this->seed(FacultySeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(ProgrammeSeeder::class);
        $this->seed(AcademicCalendarSeeder::class);
        $this->seed(CourseUnitSeeder::class);
        $this->seed(CurriculumSeeder::class);
        $this->seed(GradingPolicySeeder::class);
        $this->seed(DemoRetakeStudentsSeeder::class);

        // Student 1: Brian Asiimwe (Pending Retake)
        $brian = Student::where('registration_number', '25/BSU/BSCS/042')->first();
        $this->assertNotNull($brian);
        $this->assertEquals('Brian', $brian->first_name);

        $eligibilityService = app(CourseEligibilityService::class);
        $activeSemester = Semester::where('is_active', true)->first();
        $basket = $eligibilityService->getEligibleCoursesForStudent($brian, $activeSemester);

        $this->assertTrue($basket['summary']['is_registration_open']);
        $this->assertCount(1, $basket['retake_courses']);
        $this->assertEquals('CSC1102', $basket['retake_courses']->first()->code);
        $this->assertEquals('F', $basket['retake_courses']->first()->last_grade_letter);

        // Student 2: Gloria Nabukenya (Completed Retake with CGPA replacement)
        $gloria = Student::where('registration_number', '24/BSU/BSCS/018')->first();
        $this->assertNotNull($gloria);
        $this->assertEquals('Gloria', $gloria->first_name);

        $gradingEngine = app(GradingEngineService::class);
        $cumulative = $gradingEngine->computeCumulativeMetrics($gloria);

        // Replaced fail: registered credits are 26.0 (not 30.0), CGPA is 4.25
        $this->assertEquals(26.0, $cumulative['cumulative_credits_registered']);
        $this->assertEquals(4.25, $cumulative['cgpa']);
    }
}
