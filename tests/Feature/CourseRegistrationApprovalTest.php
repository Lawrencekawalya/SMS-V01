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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseRegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $advisor;

    protected Student $student;

    protected Semester $activeSemester;

    protected CourseRegistration $submittedRegistration;

    protected CourseRegistration $addDropRegistration;

    protected CourseUnit $core1;

    protected CourseUnit $elective1;

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

        $curriculum = Curriculum::factory()->create([
            'programme_id' => $programme->id,
            'is_active' => true,
        ]);

        $academicYear = AcademicYear::factory()->create([
            'is_current' => true,
        ]);

        $this->activeSemester = Semester::factory()->create([
            'academic_year_id' => $academicYear->id,
            'semester_number' => 1,
            'is_active' => true,
            'start_date' => now()->subDays(10),
            'end_date' => now()->addMonths(3),
            'registration_start_date' => now()->subDays(10),
            'registration_end_date' => now()->addDays(5),
            'add_drop_deadline' => now()->addDays(14),
        ]);

        $this->advisor = User::factory()->create([
            'name' => 'Dr. Jane Advisor',
            'email' => 'advisor@bsu.ac.ug',
        ]);

        $this->student = Student::factory()->create([
            'campus_id' => $campus->id,
            'programme_id' => $programme->id,
            'curriculum_id' => $curriculum->id,
            'admission_academic_year_id' => $academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $this->core1 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1101',
            'name' => 'Structured Programming',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $this->elective1 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1103',
            'name' => 'Internet Technologies',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->core1->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->elective1->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Elective',
        ]);

        // 1. Submitted normal registration
        $this->submittedRegistration = CourseRegistration::factory()->submitted()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $this->activeSemester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'total_credits' => 16.0,
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $this->submittedRegistration->id,
            'course_unit_id' => $this->core1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $this->submittedRegistration->id,
            'course_unit_id' => $this->elective1->id,
            'course_type' => 'Elective',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        // 2. Another student with add/drop pending
        $student2 = Student::factory()->create([
            'campus_id' => $campus->id,
            'programme_id' => $programme->id,
            'curriculum_id' => $curriculum->id,
            'admission_academic_year_id' => $academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $this->addDropRegistration = CourseRegistration::factory()->addDropPending()->create([
            'student_id' => $student2->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $this->activeSemester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'total_credits' => 16.0,
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $this->addDropRegistration->id,
            'course_unit_id' => $this->core1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);
    }

    public function test_advisor_dashboard_accurately_lists_slips_and_kpi_boxes(): void
    {
        $response = $this->get(route('approval.list'));

        $response->assertStatus(200);
        $response->assertSee('Course Registration Approvals');
        $response->assertSee('Pending Review');
        $response->assertSee('Approved This Term');
        $response->assertSee('Add / Drop Pending');
        $response->assertSee('Rejected Slips');
        $response->assertSee($this->student->full_name);
        $response->assertSee($this->student->registration_number);
        $response->assertSee('Approve Selected');
    }

    public function test_advisor_inspection_view_renders_student_standing_and_course_breakdown(): void
    {
        $response = $this->get(route('approval.show', $this->submittedRegistration));

        $response->assertStatus(200);
        $response->assertSee('Student Academic Standing');
        $response->assertSee($this->student->full_name);
        $response->assertSee('Mandatory Core Courses');
        $response->assertSee('CSC1101');
        $response->assertSee('Enrolled Elective Courses');
        $response->assertSee('CSC1103');
        $response->assertSee('Approve Registration');
        $response->assertSee('Request Changes / Reject');
    }

    public function test_advisor_can_approve_valid_registration_slip(): void
    {
        $this->actingAs($this->advisor);

        $response = $this->post(route('approval.approve', $this->submittedRegistration), [
            'advisor_remarks' => 'Curriculum checks complete. All requirements satisfied.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $fresh = $this->submittedRegistration->fresh();
        $this->assertEquals('approved', $fresh->status);
        $this->assertNotNull($fresh->approved_at);
        $this->assertEquals($this->advisor->id, $fresh->approved_by_user_id);
        $this->assertEquals('Curriculum checks complete. All requirements satisfied.', $fresh->advisor_remarks);

        // Assert all line items transitioned to approved
        $items = $fresh->items()->get();
        foreach ($items as $item) {
            $this->assertEquals('approved', $item->status);
        }
    }

    public function test_advisor_rejection_requires_feedback_remarks(): void
    {
        $this->actingAs($this->advisor);

        // 1. Missing remarks fails validation
        $responseEmpty = $this->post(route('approval.reject', $this->submittedRegistration), [
            'advisor_remarks' => '',
        ]);
        $responseEmpty->assertSessionHasErrors(['advisor_remarks']);

        // 2. Too short remarks (< 10 chars) fails validation
        $responseShort = $this->post(route('approval.reject', $this->submittedRegistration), [
            'advisor_remarks' => 'Short',
        ]);
        $responseShort->assertSessionHasErrors(['advisor_remarks']);

        // Slip remains submitted
        $this->assertEquals('submitted', $this->submittedRegistration->fresh()->status);

        // 3. Valid feedback marks slip as rejected
        $responseValid = $this->post(route('approval.reject', $this->submittedRegistration), [
            'advisor_remarks' => 'Credit underload: Missing second elective course required for Semester 1.',
        ]);
        $responseValid->assertRedirect();
        $responseValid->assertSessionHas('warning');

        $fresh = $this->submittedRegistration->fresh();
        $this->assertEquals('rejected', $fresh->status);
        $this->assertNull($fresh->approved_at);
        $this->assertEquals($this->advisor->id, $fresh->approved_by_user_id);
        $this->assertEquals('Credit underload: Missing second elective course required for Semester 1.', $fresh->advisor_remarks);
    }

    public function test_batch_approval_approves_multiple_pending_slips(): void
    {
        $this->actingAs($this->advisor);

        $response = $this->post(route('approval.batch-approve'), [
            'registration_ids' => [
                $this->submittedRegistration->id,
                $this->addDropRegistration->id,
            ],
        ]);

        $response->assertRedirect(route('approval.list'));
        $response->assertSessionHas('success');

        $this->assertEquals('approved', $this->submittedRegistration->fresh()->status);
        $this->assertEquals('approved', $this->addDropRegistration->fresh()->status);
        $this->assertNotNull($this->submittedRegistration->fresh()->approved_at);
        $this->assertNotNull($this->addDropRegistration->fresh()->approved_at);
    }

    public function test_filtering_by_status_and_study_year(): void
    {
        // 1. Filter by status: add_drop_pending
        $responseAddDrop = $this->get(route('approval.list', ['status' => 'add_drop_pending']));
        $responseAddDrop->assertStatus(200);
        $responseAddDrop->assertSee('#REG-'.str_pad($this->addDropRegistration->id, 5, '0', STR_PAD_LEFT));
        $responseAddDrop->assertDontSee('#REG-'.str_pad($this->submittedRegistration->id, 5, '0', STR_PAD_LEFT));

        // 2. Filter by status: submitted
        $responseSubmitted = $this->get(route('approval.list', ['status' => 'submitted']));
        $responseSubmitted->assertStatus(200);
        $responseSubmitted->assertSee('#REG-'.str_pad($this->submittedRegistration->id, 5, '0', STR_PAD_LEFT));
        $responseSubmitted->assertDontSee('#REG-'.str_pad($this->addDropRegistration->id, 5, '0', STR_PAD_LEFT));
    }
}
