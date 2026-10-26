<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\CourseUnit;
use App\Models\Curriculum;
use App\Models\CurriculumCourse;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemesterCreditLimitsPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Student $student;

    protected Semester $activeSemester;

    protected Curriculum $curriculum;

    protected CourseUnit $coreCourse1;

    protected CourseUnit $coreCourse2;

    protected function setUp(): void
    {
        parent::setUp();

        $university = University::factory()->create();
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id]);
        $department = Department::factory()->create(['faculty_id' => $faculty->id]);

        $programme = Programme::factory()->create([
            'department_id' => $department->id,
            'name' => 'Bachelor of Science in Software Engineering',
            'code' => 'BSSE',
            'duration_years' => 3,
        ]);

        $this->curriculum = Curriculum::factory()->create([
            'programme_id' => $programme->id,
            'version_name' => 'Curriculum 2026/2027 v1.0',
            'start_academic_year' => '2026/2027',
            'is_active' => true,
        ]);

        $this->student = Student::factory()->create([
            'programme_id' => $programme->id,
            'curriculum_id' => $this->curriculum->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $academicYear = AcademicYear::factory()->create([
            'start_date' => now()->subMonths(1),
            'end_date' => now()->addMonths(9),
        ]);

        $this->activeSemester = Semester::factory()->create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Semester 1 2026/2027',
            'semester_number' => 1,
            'is_active' => true,
            'registration_start_date' => now()->subDays(10),
            'registration_end_date' => now()->addDays(20),
            'add_drop_deadline' => now()->addDays(15),
        ]);

        $this->coreCourse1 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'BSSE1101',
            'name' => 'Introduction to Software Engineering',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $this->coreCourse2 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'BSSE1102',
            'name' => 'Discrete Mathematics',
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

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_curriculum_show_hides_credit_limit_inputs_when_policy_is_disabled(): void
    {
        config(['academic.enforce_credit_limits' => false]);

        $response = $this->get(route('curriculum.show', $this->curriculum));

        $response->assertStatus(200);
        $response->assertDontSee('title="Save Credit Limits"', false);
    }

    public function test_curriculum_show_displays_credit_limit_inputs_when_policy_is_enabled(): void
    {
        config(['academic.enforce_credit_limits' => true]);

        $response = $this->get(route('curriculum.show', $this->curriculum));

        $response->assertStatus(200);
        $response->assertSee('title="Save Credit Limits"', false);
    }

    public function test_registration_workspace_hides_bounds_meter_and_checklist_when_policy_is_disabled(): void
    {
        config(['academic.enforce_credit_limits' => false]);

        $response = $this->get(route('registration.create', [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
        ]));

        $response->assertStatus(200);
        $response->assertDontSee('Visual Capacity Progress Bar');
        $response->assertDontSee('Minimum Floor (&ge;', false);
        $response->assertDontSee('Maximum Ceiling (&le;', false);
    }

    public function test_registration_workspace_displays_bounds_meter_and_checklist_when_policy_is_enabled(): void
    {
        config(['academic.enforce_credit_limits' => true]);

        $response = $this->get(route('registration.create', [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('meter-progress-bar');
        $response->assertSee('Minimum Floor (&ge;', false);
        $response->assertSee('Maximum Ceiling (&le;', false);
    }

    public function test_registration_eligibility_check_hides_bounds_card_when_policy_is_disabled(): void
    {
        config(['academic.enforce_credit_limits' => false]);

        $response = $this->get(route('registration.eligibility', [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
        ]));

        $response->assertStatus(200);
        $response->assertDontSee('Semester Credit Load Bounds');
    }

    public function test_registration_eligibility_check_displays_bounds_card_when_policy_is_enabled(): void
    {
        config(['academic.enforce_credit_limits' => true]);

        $response = $this->get(route('registration.eligibility', [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Semester Credit Load Bounds');
    }

    public function test_submitting_course_registration_with_underload_succeeds_when_policy_is_disabled(): void
    {
        config(['academic.enforce_credit_limits' => false]);

        // Student registers 8.0 CU (2 core courses), which is below standard 12.0 CU floor
        $response = $this->post(route('registration.store'), [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'action_status' => 'submitted',
            'total_credits' => 8.0,
            'course_unit_ids' => [$this->coreCourse1->id, $this->coreCourse2->id],
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('course_registrations', [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'total_credits' => 8.0,
        ]);
    }

    public function test_submitting_course_registration_with_underload_is_rejected_when_policy_is_enabled(): void
    {
        config(['academic.enforce_credit_limits' => true]);

        // Student registers 8.0 CU, below 12.0 CU floor
        $response = $this->post(route('registration.store'), [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'action_status' => 'submitted',
            'total_credits' => 8.0,
            'course_unit_ids' => [$this->coreCourse1->id, $this->coreCourse2->id],
        ]);

        $response->assertSessionHasErrors(['total_credits']);
    }
}
