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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseRegistrationPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Student $student;

    protected Semester $activeSemester;

    protected CourseUnit $core1;

    protected CourseUnit $core2;

    protected CourseUnit $core3;

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
            'registration_start_date' => now()->subDays(3),
            'registration_end_date' => now()->addDays(20),
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

        $this->core1 = CourseUnit::factory()->create(['code' => 'CSC1101', 'credit_units' => 4.0]);
        $this->core2 = CourseUnit::factory()->create(['code' => 'CSC1102', 'credit_units' => 4.0]);
        $this->core3 = CourseUnit::factory()->create(['code' => 'BIT1101', 'credit_units' => 4.0]);
        $this->elective1 = CourseUnit::factory()->create(['code' => 'AFN1102', 'credit_units' => 3.0]);

        CurriculumCourse::create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->core1->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->core2->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->core3->id,
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
    }

    public function test_registration_workspace_create_page_loads_with_active_session_and_student_courses(): void
    {
        $response = $this->get(route('registration.create', ['student_id' => $this->student->id]));

        $response->assertStatus(200);
        $response->assertSee('Course Registration Workspace');
        $response->assertSee('Registration Session Active');
        $response->assertSee($this->student->registration_number);
        $response->assertSee('Mandatory Core Courses');
        $response->assertSee('CSC1101');
        $response->assertSee('AFN1102');
        $response->assertSee('Confirm & Complete Registration', false);
    }

    public function test_registration_workspace_displays_advisor_approval_button_when_policy_enabled(): void
    {
        config(['academic.require_registration_approval' => true]);

        $response = $this->get(route('registration.create', ['student_id' => $this->student->id]));

        $response->assertStatus(200);
        $response->assertSee('Submit for Advisor Approval');
    }

    public function test_registration_workspace_displays_closed_alert_when_session_dates_passed(): void
    {
        $this->activeSemester->update([
            'registration_start_date' => now()->subDays(30),
            'registration_end_date' => now()->subDays(5),
        ]);

        $response = $this->get(route('registration.create', ['student_id' => $this->student->id]));

        $response->assertStatus(200);
        $response->assertSee('Course Registration Window Closed');
        $response->assertSee('Registration Session Closed');
    }

    public function test_student_can_save_registration_as_draft(): void
    {
        $response = $this->post(route('registration.store'), [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'action_status' => 'draft',
            'course_unit_ids' => [$this->core1->id, $this->core2->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('course_registrations', [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'status' => 'draft',
            'submitted_at' => null,
            'total_credits' => 8.0,
        ]);
    }

    public function test_student_can_submit_registration_for_advisor_approval_when_policy_enabled(): void
    {
        config(['academic.require_registration_approval' => true]);

        // 3 core (12 CU) + 1 elective (3 CU) = 15.0 CU
        $response = $this->post(route('registration.store'), [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'action_status' => 'submitted',
            'total_credits' => 15.0,
            'course_unit_ids' => [
                $this->core1->id,
                $this->core2->id,
                $this->core3->id,
                $this->elective1->id,
            ],
        ]);

        $registration = CourseRegistration::where('student_id', $this->student->id)->first();
        $this->assertNotNull($registration);
        $this->assertEquals('submitted', $registration->status);
        $this->assertNotNull($registration->submitted_at);
        $this->assertNull($registration->approved_at);
        $this->assertEquals(15.0, $registration->total_credits);
        $this->assertCount(4, $registration->items);

        $response->assertRedirect(route('registration.show', $registration));
        $response->assertSessionHas('success', "Course registration slip #REG-{$registration->id} has been submitted for Academic Advisor review.");
    }

    public function test_student_registration_is_auto_approved_when_advisor_approval_is_disabled(): void
    {
        config(['academic.require_registration_approval' => false]);

        $user = User::factory()->create();
        $this->actingAs($user);

        // 3 core (12 CU) + 1 elective (3 CU) = 15.0 CU
        $response = $this->post(route('registration.store'), [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'action_status' => 'submitted',
            'total_credits' => 15.0,
            'course_unit_ids' => [
                $this->core1->id,
                $this->core2->id,
                $this->core3->id,
                $this->elective1->id,
            ],
        ]);

        $registration = CourseRegistration::where('student_id', $this->student->id)->first();
        $this->assertNotNull($registration);
        $this->assertEquals('approved', $registration->status);
        $this->assertNotNull($registration->submitted_at);
        $this->assertNotNull($registration->approved_at);
        $this->assertEquals($user->id, $registration->approved_by_user_id);
        $this->assertEquals(15.0, $registration->total_credits);
        $this->assertCount(4, $registration->items);
        $this->assertEquals('approved', $registration->items->first()->status);

        $response->assertRedirect(route('registration.show', $registration));
        $response->assertSessionHas('success', "Course registration slip #REG-{$registration->id} has been registered and confirmed successfully.");
    }

    public function test_edit_draft_registration_page_loads_and_updates(): void
    {
        config(['academic.require_registration_approval' => true]);

        $registration = CourseRegistration::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->activeSemester->academic_year_id,
            'semester_id' => $this->activeSemester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'draft',
            'total_credits' => 8.0,
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $registration->id,
            'course_unit_id' => $this->core1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $registration->id,
            'course_unit_id' => $this->core2->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $response = $this->get(route('registration.edit', $registration));
        $response->assertStatus(200);
        $response->assertSee('Edit Registration Slip');

        // Update to submitted with complete course basket
        $updateResponse = $this->put(route('registration.update', $registration), [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'action_status' => 'submitted',
            'total_credits' => 15.0,
            'course_unit_ids' => [
                $this->core1->id,
                $this->core2->id,
                $this->core3->id,
                $this->elective1->id,
            ],
        ]);

        $updateResponse->assertRedirect(route('registration.show', $registration));
        $this->assertEquals('submitted', $registration->fresh()->status);
        $this->assertEquals(15.0, $registration->fresh()->total_credits);
    }

    public function test_cannot_edit_approved_registration_directly(): void
    {
        $registration = CourseRegistration::factory()->approved()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
        ]);

        $response = $this->get(route('registration.edit', $registration));
        $response->assertRedirect(route('registration.show', $registration));
        $response->assertSessionHas('warning');
    }

    public function test_official_printable_slip_renders_cleanly_with_signature_blocks(): void
    {
        $registration = CourseRegistration::factory()->approved()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'total_credits' => 15.0,
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $registration->id,
            'course_unit_id' => $this->core1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $response = $this->get(route('registration.print', $registration));

        $response->assertStatus(200);
        $response->assertSee('Bishop Stuart University', false);
        $response->assertSee('Official Semester Course Registration Slip', false);
        $response->assertSee($this->student->full_name);
        $response->assertSee($this->student->registration_number);
        $response->assertSee($this->core1->code);
        $response->assertSee("Student's Signature & Date", false);
        $response->assertSee("Advisor's Signature & Stamp", false);
        $response->assertSee("Academic Registrar's Office", false);
    }

    public function test_submitting_registration_without_explicit_total_credits_computes_it_automatically(): void
    {
        $response = $this->post(route('registration.store'), [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'action_status' => 'submitted',
            // Note: total_credits is intentionally omitted
            'course_unit_ids' => [
                $this->core1->id,
                $this->core2->id,
                $this->core3->id,
                $this->elective1->id,
            ],
        ]);

        $registration = CourseRegistration::where('student_id', $this->student->id)->first();
        $this->assertNotNull($registration);
        $this->assertEquals('approved', $registration->status);
        $this->assertEquals(15.0, $registration->total_credits);
        $response->assertRedirect(route('registration.show', $registration));
    }

    public function test_active_session_cohort_view_renders_multi_stage_students_under_single_calendar_session(): void
    {
        // Student 1 is in Year 1 Sem 1
        $regY1 = CourseRegistration::factory()->approved()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'academic_year_id' => $this->activeSemester->academic_year_id,
            'study_year' => 1,
            'semester_number' => 1,
            'total_credits' => 12.0,
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $regY1->id,
            'course_unit_id' => $this->core1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        // Student 2 is in Year 2 Sem 1
        $studentY2 = Student::factory()->create([
            'campus_id' => $this->student->campus_id,
            'programme_id' => $this->student->programme_id,
            'curriculum_id' => $this->student->curriculum_id,
            'admission_academic_year_id' => $this->student->admission_academic_year_id,
            'current_study_year' => 2,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $regY2 = CourseRegistration::factory()->approved()->create([
            'student_id' => $studentY2->id,
            'semester_id' => $this->activeSemester->id,
            'academic_year_id' => $this->activeSemester->academic_year_id,
            'study_year' => 2,
            'semester_number' => 1,
            'total_credits' => 12.0,
        ]);

        $response = $this->get(route('registration.active-session'));

        $response->assertStatus(200);
        $response->assertSee('Active University Calendar Session', false);
        $response->assertSee('Architectural Principle: Why a Single Calendar Session Drives All Study Levels', false);
        $response->assertSee($this->student->full_name);
        $response->assertSee('Year 1, Semester 1 (Y1S1)', false);
        $response->assertSee($studentY2->full_name);
        $response->assertSee('Year 2, Semester 1 (Y2S1)', false);
        $response->assertSee($this->activeSemester->name);
    }

    public function test_active_session_cohort_view_filters_by_study_year(): void
    {
        $regY1 = CourseRegistration::factory()->approved()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'academic_year_id' => $this->activeSemester->academic_year_id,
            'study_year' => 1,
            'semester_number' => 1,
            'total_credits' => 12.0,
        ]);

        $studentY2 = Student::factory()->create([
            'campus_id' => $this->student->campus_id,
            'programme_id' => $this->student->programme_id,
            'curriculum_id' => $this->student->curriculum_id,
            'admission_academic_year_id' => $this->student->admission_academic_year_id,
            'current_study_year' => 2,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $regY2 = CourseRegistration::factory()->approved()->create([
            'student_id' => $studentY2->id,
            'semester_id' => $this->activeSemester->id,
            'academic_year_id' => $this->activeSemester->academic_year_id,
            'study_year' => 2,
            'semester_number' => 1,
            'total_credits' => 12.0,
        ]);

        $response = $this->get(route('registration.active-session', ['study_year' => 1]));

        $response->assertStatus(200);
        $response->assertSee($this->student->full_name);
        $response->assertDontSee($studentY2->full_name);
    }

    public function test_advisor_approvals_navigation_links_are_hidden_when_policy_is_disabled(): void
    {
        config(['academic.require_registration_approval' => false]);

        $response = $this->get(route('registration.list'));

        $response->assertStatus(200);
        // Advisor Approvals link in sidebar should not be visible
        $response->assertDontSee('Advisor Approvals');
        $response->assertDontSee('Academic Advisor Portal');
    }

    public function test_advisor_approvals_navigation_links_are_visible_when_policy_is_enabled(): void
    {
        config(['academic.require_registration_approval' => true]);

        $response = $this->get(route('registration.list'));

        $response->assertStatus(200);
        $response->assertSee('Advisor Approvals');
        $response->assertSee('Academic Advisor Portal');
    }

    public function test_updating_registration_slip_with_linked_student_marks_succeeds_without_fk_violation(): void
    {
        $reg = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'academic_year_id' => $this->activeSemester->academic_year_id,
            'status' => 'draft',
            'study_year' => 1,
            'semester_number' => 1,
        ]);

        $item1 = CourseRegistrationItem::create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $this->core1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $sheet = CourseAssessmentSheet::create([
            'course_unit_id' => $this->core1->id,
            'semester_id' => $this->activeSemester->id,
            'academic_year_id' => $this->activeSemester->academic_year_id,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'pass_mark' => 50.0,
            'status' => 'draft',
        ]);

        $mark = StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student->id,
        ]);

        $response = $this->put(route('registration.update', $reg), [
            'student_id' => $this->student->id,
            'semester_id' => $this->activeSemester->id,
            'course_unit_ids' => [$this->core1->id, $this->core2->id, $this->core3->id],
            'action' => 'submit',
        ]);

        $response->assertRedirect(route('registration.show', $reg));
        $reg->refresh();
        $this->assertEquals(3, $reg->items()->count());

        $item1->refresh();
        $this->assertEquals('approved', $item1->status);
        $this->assertDatabaseHas('student_marks', [
            'id' => $mark->id,
            'course_registration_item_id' => $item1->id,
        ]);
    }
}
