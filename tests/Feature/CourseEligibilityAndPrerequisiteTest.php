<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Campus;
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
use App\Models\University;
use App\Rules\ValidSemesterCreditLoad;
use App\Services\CourseEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseEligibilityAndPrerequisiteTest extends TestCase
{
    use RefreshDatabase;

    protected Student $fresherStudent;

    protected Student $year2Student;

    protected Semester $activeSemester;

    protected Curriculum $curriculum;

    protected CourseUnit $coreCourse1;

    protected CourseUnit $coreCourse2;

    protected CourseUnit $coreCourse3;

    protected CourseUnit $electiveCourse;

    protected CourseUnit $year2Course;

    protected CourseUnit $prereqChildCourse;

    protected CourseEligibilityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $university = University::factory()->create();
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id]);
        $department = Department::factory()->create(['faculty_id' => $faculty->id]);

        $programme = Programme::factory()->create([
            'department_id' => $department->id,
            'code' => 'BSCS',
        ]);

        $this->curriculum = Curriculum::factory()->create([
            'programme_id' => $programme->id,
            'is_active' => true,
        ]);

        $academicYear = AcademicYear::factory()->create([
            'name' => '2026/2027',
            'is_current' => true,
        ]);

        $this->activeSemester = Semester::factory()->create([
            'academic_year_id' => $academicYear->id,
            'semester_number' => 1,
            'is_active' => true,
            'registration_start_date' => now()->subDays(5),
            'registration_end_date' => now()->addDays(20),
        ]);

        // Create Course Units
        $this->coreCourse1 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1101',
            'credit_units' => 4.0,
        ]);

        $this->coreCourse2 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1102',
            'credit_units' => 4.0,
        ]);

        $this->coreCourse3 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'BIT1101',
            'credit_units' => 4.0,
        ]);

        $this->electiveCourse = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'AFN1102',
            'credit_units' => 3.0,
        ]);

        $this->year2Course = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC2101',
            'credit_units' => 4.0,
        ]);

        $this->prereqChildCourse = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1201',
            'credit_units' => 4.0,
        ]);

        // Map courses to Curriculum
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
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $this->curriculum->id,
            'course_unit_id' => $this->electiveCourse->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Elective',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $this->curriculum->id,
            'course_unit_id' => $this->year2Course->id,
            'study_year' => 2,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        // CSC1201 requires CSC1102 as prerequisite
        $this->prereqChildCourse->prerequisites()->attach($this->coreCourse2->id);

        CurriculumCourse::create([
            'curriculum_id' => $this->curriculum->id,
            'course_unit_id' => $this->prereqChildCourse->id,
            'study_year' => 1,
            'semester' => 1, // pretend mapped to sem 1 for prerequisite test
            'course_type' => 'Elective',
        ]);

        // Create Students
        $this->fresherStudent = Student::factory()->create([
            'campus_id' => $campus->id,
            'programme_id' => $programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $this->year2Student = Student::factory()->create([
            'campus_id' => $campus->id,
            'programme_id' => $programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $academicYear->id,
            'current_study_year' => 2,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $this->service = app(CourseEligibilityService::class);
    }

    public function test_fresher_is_presented_with_exact_curriculum_year1_sem1_courses(): void
    {
        $eligibility = $this->service->getEligibleCoursesForStudent($this->fresherStudent, $this->activeSemester);

        $coreCodes = $eligibility['mandatory_core_courses']->pluck('code')->toArray();
        $electiveCodes = $eligibility['available_electives']->pluck('code')->toArray();

        $this->assertContains('CSC1101', $coreCodes);
        $this->assertContains('CSC1102', $coreCodes);
        $this->assertContains('BIT1101', $coreCodes);
        $this->assertContains('AFN1102', $electiveCodes);

        $this->assertEquals(12.0, $eligibility['summary']['core_credits']);
    }

    public function test_higher_study_year_courses_are_blocked_by_stage_constraint(): void
    {
        $eligibility = $this->service->getEligibleCoursesForStudent($this->fresherStudent, $this->activeSemester);

        $blockedCourseCodes = $eligibility['blocked_courses']->pluck('course.code')->toArray();

        // Fresher (Year 1) cannot take Year 2 course (CSC2101)
        $this->assertContains('CSC2101', $blockedCourseCodes);

        $blockedInfo = $eligibility['blocked_courses']->firstWhere('course.code', 'CSC2101');
        $this->assertStringContainsString('Stage restriction', $blockedInfo['reason']);
    }

    public function test_unmet_prerequisites_block_course_eligibility_when_enforcement_is_enabled(): void
    {
        config(['academic.enforce_prerequisites' => true]);

        $eligibility = $this->service->getEligibleCoursesForStudent($this->fresherStudent, $this->activeSemester);

        // CSC1201 requires CSC1102, which fresher has not completed
        $blockedCourseCodes = $eligibility['blocked_courses']->pluck('course.code')->toArray();
        $this->assertContains('CSC1201', $blockedCourseCodes);

        $blockedInfo = $eligibility['blocked_courses']->firstWhere('course.code', 'CSC1201');
        $this->assertStringContainsString('Prerequisite requirement not met', $blockedInfo['reason']);
        $this->assertStringContainsString('CSC1102', $blockedInfo['reason']);
    }

    public function test_unmet_prerequisites_do_not_block_course_when_enforcement_is_disabled(): void
    {
        config(['academic.enforce_prerequisites' => false]);

        $eligibility = $this->service->getEligibleCoursesForStudent($this->fresherStudent, $this->activeSemester);

        // CSC1201 should NOT be in blocked courses, but available in electives pool
        $blockedCourseCodes = $eligibility['blocked_courses']->pluck('course.code')->toArray();
        $this->assertNotContains('CSC1201', $blockedCourseCodes);

        $availableElectiveCodes = $eligibility['available_electives']->pluck('code')->toArray();
        $this->assertContains('CSC1201', $availableElectiveCodes);
    }

    public function test_prerequisite_is_satisfied_when_previously_completed_in_approved_registration(): void
    {
        // Simulate previous approved semester where student passed CSC1102
        $pastSemester = Semester::factory()->create([
            'academic_year_id' => $this->activeSemester->academic_year_id,
            'semester_number' => 2,
            'is_active' => false,
        ]);

        $pastRegistration = CourseRegistration::factory()->approved()->create([
            'student_id' => $this->fresherStudent->id,
            'semester_id' => $pastSemester->id,
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $pastRegistration->id,
            'course_unit_id' => $this->coreCourse2->id, // CSC1102
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $eligibility = $this->service->getEligibleCoursesForStudent($this->fresherStudent, $this->activeSemester);

        // Now CSC1201 prerequisite is satisfied, so it appears in available electives
        $electiveCodes = $eligibility['available_electives']->pluck('code')->toArray();
        $this->assertContains('CSC1201', $electiveCodes);
    }

    public function test_validate_course_selection_rejects_courses_outside_student_curriculum(): void
    {
        $foreignCourse = CourseUnit::factory()->create([
            'code' => 'ENG9999',
            'credit_units' => 4.0,
        ]);

        $selectedIds = [
            $this->coreCourse1->id,
            $this->coreCourse2->id,
            $this->coreCourse3->id,
            $foreignCourse->id,
        ];

        $validation = $this->service->validateCourseSelection(
            $this->fresherStudent,
            $this->activeSemester,
            $selectedIds
        );

        $this->assertFalse($validation['is_valid']);
        $this->assertStringContainsString('not part of your assigned curriculum', implode(' ', $validation['errors']));
    }

    public function test_validate_course_selection_rejects_credit_underload(): void
    {
        // Only 1 course (4.0 CU) selected, below the 12.0 CU minimum floor
        $selectedIds = [$this->coreCourse1->id];

        $validation = $this->service->validateCourseSelection(
            $this->fresherStudent,
            $this->activeSemester,
            $selectedIds
        );

        $this->assertFalse($validation['is_valid']);
        $this->assertStringContainsString('below the institutional minimum limit of 12 CU', implode(' ', $validation['errors']));
    }

    public function test_validate_course_selection_rejects_credit_overload(): void
    {
        // Create extra courses to exceed 24.0 CU
        $extraCourses = CourseUnit::factory()->count(4)->create(['credit_units' => 4.0]);
        foreach ($extraCourses as $ec) {
            CurriculumCourse::create([
                'curriculum_id' => $this->curriculum->id,
                'course_unit_id' => $ec->id,
                'study_year' => 1,
                'semester' => 1,
                'course_type' => 'Elective',
            ]);
        }

        $allSelected = array_merge(
            [$this->coreCourse1->id, $this->coreCourse2->id, $this->coreCourse3->id], // 12 CU
            $extraCourses->pluck('id')->toArray() // 16 CU -> total 28 CU
        );

        $validation = $this->service->validateCourseSelection(
            $this->fresherStudent,
            $this->activeSemester,
            $allSelected
        );

        $this->assertFalse($validation['is_valid']);
        $this->assertStringContainsString('exceeds the institutional maximum limit of 24 CU', implode(' ', $validation['errors']));
    }

    public function test_validate_course_selection_rejects_omitting_mandatory_core_course(): void
    {
        // Student selects elective and 2 core courses, but omits CSC1101
        $selectedIds = [
            $this->coreCourse2->id, // 4 CU
            $this->coreCourse3->id, // 4 CU
            $this->electiveCourse->id, // 3 CU
            // CSC1101 omitted! Total 11 CU or if another is added:
        ];

        $validation = $this->service->validateCourseSelection(
            $this->fresherStudent,
            $this->activeSemester,
            $selectedIds
        );

        $this->assertFalse($validation['is_valid']);
        $this->assertStringContainsString('Mandatory core course CSC1101', implode(' ', $validation['errors']));
    }

    public function test_valid_semester_credit_load_rule(): void
    {
        $rule = new ValidSemesterCreditLoad;

        $failed = false;
        $failCallback = function ($message) use (&$failed) {
            $failed = true;
        };

        // 11.0 CU fails (underload)
        $rule->validate('total_credits', 11.0, $failCallback);
        $this->assertTrue($failed);

        // 16.0 CU passes
        $failed = false;
        $rule->validate('total_credits', 16.0, $failCallback);
        $this->assertFalse($failed);

        // 25.0 CU fails (overload)
        $failed = false;
        $rule->validate('total_credits', 25.0, $failCallback);
        $this->assertTrue($failed);
    }

    public function test_eligibility_inspector_web_route_loads(): void
    {
        config(['academic.enforce_prerequisites' => false]);

        $response = $this->get(route('academic.registrations.eligibility', [
            'student_id' => $this->fresherStudent->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Course Eligibility Inspector', false);
        $response->assertDontSee('Prerequisite Status');
        $response->assertDontSee('Restricted / Ineligible Courses');
        $response->assertSee($this->fresherStudent->registration_number);
        $response->assertSee('Mandatory Core Courses');
        $response->assertSee('Available Electives Pool');
        $response->assertSee('CSC1101');

        // When enabled, Prerequisite Status column and Restricted Courses card appear
        config(['academic.enforce_prerequisites' => true]);
        $responseEnabled = $this->get(route('academic.registrations.eligibility', [
            'student_id' => $this->fresherStudent->id,
        ]));
        $responseEnabled->assertSee('Prerequisite Status');
        $responseEnabled->assertSee('Restricted / Ineligible Courses');
    }
}
