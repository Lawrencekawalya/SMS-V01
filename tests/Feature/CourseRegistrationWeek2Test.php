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

class CourseRegistrationWeek2Test extends TestCase
{
    use RefreshDatabase;

    protected User $advisor;

    protected Campus $campus;

    protected Faculty $faculty;

    protected Department $csDepartment;

    protected Department $businessDepartment;

    protected AcademicYear $academicYear;

    protected Semester $activeSemester;

    // Programmes
    protected Programme $bscs;

    protected Programme $bsse;

    protected Programme $dca;

    protected Programme $bba;

    // Curriculums
    protected Curriculum $bscsCurriculum;

    protected Curriculum $bsseCurriculum;

    protected Curriculum $dcaCurriculum;

    // Students
    protected Student $ronaldFresher;

    protected Student $sarahYear2;

    protected Student $emmanuelDca;

    protected function setUp(): void
    {
        parent::setUp();

        config(['academic.require_registration_approval' => true]);

        $university = University::factory()->create(['name' => 'Bishop Stuart University']);
        $this->campus = Campus::factory()->create(['university_id' => $university->id]);
        $this->faculty = Faculty::factory()->create(['campus_id' => $this->campus->id]);
        $this->csDepartment = Department::factory()->create(['faculty_id' => $this->faculty->id, 'name' => 'Department of Computer Science']);
        $this->businessDepartment = Department::factory()->create(['faculty_id' => $this->faculty->id, 'name' => 'Department of Business Studies']);

        $this->academicYear = AcademicYear::factory()->create(['is_current' => true]);

        $this->activeSemester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'semester_number' => 1,
            'is_active' => true,
            'start_date' => now()->subDays(10),
            'end_date' => now()->addMonths(3),
            'registration_start_date' => now()->subDays(10),
            'registration_end_date' => now()->addDays(10),
            'add_drop_deadline' => now()->addDays(20),
        ]);

        $this->advisor = User::factory()->create(['name' => 'Dr. Senior Academic Advisor']);

        // 1. BSCS Programme & Curriculum
        $this->bscs = Programme::factory()->create([
            'department_id' => $this->csDepartment->id,
            'code' => 'BSCS',
            'name' => 'Bachelor of Science in Computer Science',
        ]);
        $this->bscsCurriculum = Curriculum::factory()->create([
            'programme_id' => $this->bscs->id,
            'version_name' => 'BSCS-2026-V1',
            'is_active' => true,
        ]);

        // 2. BSSE Programme & Curriculum
        $this->bsse = Programme::factory()->create([
            'department_id' => $this->csDepartment->id,
            'code' => 'BSSE',
            'name' => 'Bachelor of Science in Software Engineering',
        ]);
        $this->bsseCurriculum = Curriculum::factory()->create([
            'programme_id' => $this->bsse->id,
            'version_name' => 'BSSE-2025-V1',
            'is_active' => true,
        ]);

        // 3. DCA Diploma Programme & Curriculum
        $this->dca = Programme::factory()->create([
            'department_id' => $this->csDepartment->id,
            'code' => 'DCA',
            'name' => 'Diploma in Computer Applications',
            'award_type' => 'Diploma',
        ]);
        $this->dcaCurriculum = Curriculum::factory()->create([
            'programme_id' => $this->dca->id,
            'version_name' => 'DCA-2026-V1',
            'is_active' => true,
        ]);

        // 4. BBA Programme
        $this->bba = Programme::factory()->create([
            'department_id' => $this->businessDepartment->id,
            'code' => 'BBA',
            'name' => 'Bachelor of Business Administration',
        ]);

        // Seed Course Units
        $csc1101 = CourseUnit::factory()->create(['department_id' => $this->csDepartment->id, 'code' => 'CSC1101', 'name' => 'Structured Programming', 'credit_units' => 4.0, 'status' => 'active']);
        $csc1102 = CourseUnit::factory()->create(['department_id' => $this->csDepartment->id, 'code' => 'CSC1102', 'name' => 'Computer Architecture', 'credit_units' => 4.0, 'status' => 'active']);
        $mth1101 = CourseUnit::factory()->create(['department_id' => $this->csDepartment->id, 'code' => 'MTH1101', 'name' => 'Calculus I', 'credit_units' => 4.0, 'status' => 'active']);
        $afn1102 = CourseUnit::factory()->create(['department_id' => $this->csDepartment->id, 'code' => 'AFN1102', 'name' => 'African Studies', 'credit_units' => 3.0, 'status' => 'active']);
        $csc1104 = CourseUnit::factory()->create(['department_id' => $this->csDepartment->id, 'code' => 'CSC1104', 'name' => 'Communication Skills', 'credit_units' => 3.0, 'status' => 'active']);

        // Year 2 Courses
        $csc2101 = CourseUnit::factory()->create(['department_id' => $this->csDepartment->id, 'code' => 'CSC2101', 'name' => 'Data Structures', 'credit_units' => 4.0, 'status' => 'active']);
        $csc2102 = CourseUnit::factory()->create(['department_id' => $this->csDepartment->id, 'code' => 'CSC2102', 'name' => 'Operating Systems', 'credit_units' => 4.0, 'status' => 'active']);
        $swe2101 = CourseUnit::factory()->create(['department_id' => $this->csDepartment->id, 'code' => 'SWE2101', 'name' => 'Software Design', 'credit_units' => 4.0, 'status' => 'active']);
        $swe2102 = CourseUnit::factory()->create(['department_id' => $this->csDepartment->id, 'code' => 'SWE2102', 'name' => 'Web Systems', 'credit_units' => 3.0, 'status' => 'active']);
        $swe2103 = CourseUnit::factory()->create(['department_id' => $this->csDepartment->id, 'code' => 'SWE2103', 'name' => 'Mobile App Dev', 'credit_units' => 3.0, 'status' => 'active']);

        // Prerequisite: CSC2101 requires CSC1101
        $csc2101->prerequisites()->attach($csc1101->id);

        // Map BSCS Year 1 Sem 1
        CurriculumCourse::create(['curriculum_id' => $this->bscsCurriculum->id, 'course_unit_id' => $csc1101->id, 'study_year' => 1, 'semester' => 1, 'course_type' => 'Core']);
        CurriculumCourse::create(['curriculum_id' => $this->bscsCurriculum->id, 'course_unit_id' => $csc1102->id, 'study_year' => 1, 'semester' => 1, 'course_type' => 'Core']);
        CurriculumCourse::create(['curriculum_id' => $this->bscsCurriculum->id, 'course_unit_id' => $mth1101->id, 'study_year' => 1, 'semester' => 1, 'course_type' => 'Core']);
        CurriculumCourse::create(['curriculum_id' => $this->bscsCurriculum->id, 'course_unit_id' => $afn1102->id, 'study_year' => 1, 'semester' => 1, 'course_type' => 'Elective']);
        CurriculumCourse::create(['curriculum_id' => $this->bscsCurriculum->id, 'course_unit_id' => $csc1104->id, 'study_year' => 1, 'semester' => 1, 'course_type' => 'Elective']);

        // Map BSSE Year 2 Sem 1
        CurriculumCourse::create(['curriculum_id' => $this->bsseCurriculum->id, 'course_unit_id' => $csc2101->id, 'study_year' => 2, 'semester' => 1, 'course_type' => 'Core']);
        CurriculumCourse::create(['curriculum_id' => $this->bsseCurriculum->id, 'course_unit_id' => $swe2101->id, 'study_year' => 2, 'semester' => 1, 'course_type' => 'Core']);
        CurriculumCourse::create(['curriculum_id' => $this->bsseCurriculum->id, 'course_unit_id' => $swe2102->id, 'study_year' => 2, 'semester' => 1, 'course_type' => 'Elective']);
        CurriculumCourse::create(['curriculum_id' => $this->bsseCurriculum->id, 'course_unit_id' => $swe2103->id, 'study_year' => 2, 'semester' => 1, 'course_type' => 'Elective']);

        // Map DCA Year 1 Sem 1
        CurriculumCourse::create(['curriculum_id' => $this->dcaCurriculum->id, 'course_unit_id' => $csc1101->id, 'study_year' => 1, 'semester' => 1, 'course_type' => 'Core']);
        CurriculumCourse::create(['curriculum_id' => $this->dcaCurriculum->id, 'course_unit_id' => $afn1102->id, 'study_year' => 1, 'semester' => 1, 'course_type' => 'Core']);

        // Create Students
        $this->ronaldFresher = Student::factory()->create([
            'campus_id' => $this->campus->id,
            'programme_id' => $this->bscs->id,
            'curriculum_id' => $this->bscsCurriculum->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'registration_number' => '26/BSCS/001',
            'first_name' => 'Ronald',
            'last_name' => 'Mukasa',
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $this->sarahYear2 = Student::factory()->create([
            'campus_id' => $this->campus->id,
            'programme_id' => $this->bsse->id,
            'curriculum_id' => $this->bsseCurriculum->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'registration_number' => '25/BSSE/008',
            'first_name' => 'Sarah',
            'last_name' => 'Namubiru',
            'current_study_year' => 2,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $this->emmanuelDca = Student::factory()->create([
            'campus_id' => $this->campus->id,
            'programme_id' => $this->dca->id,
            'curriculum_id' => $this->dcaCurriculum->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'registration_number' => '26/DCA/001',
            'first_name' => 'Emmanuel',
            'last_name' => 'Twinomujuni',
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);
    }

    /**
     * Journey 1: Ronald Mukasa (BSCS Fresher) Complete Lifecycle
     * Selection -> Validation -> Submission -> Advisor Approval -> Printable Slip.
     */
    public function test_journey_1_bscs_fresher_enrollment_to_approval_lifecycle(): void
    {
        $csc1101 = CourseUnit::where('code', 'CSC1101')->firstOrFail();
        $csc1102 = CourseUnit::where('code', 'CSC1102')->firstOrFail();
        $mth1101 = CourseUnit::where('code', 'MTH1101')->firstOrFail();
        $afn1102 = CourseUnit::where('code', 'AFN1102')->firstOrFail();

        // 1. Submit Course Registration (4 + 4 + 4 + 3 = 15.0 CU)
        $submitResponse = $this->post(route('registration.store'), [
            'student_id' => $this->ronaldFresher->id,
            'semester_id' => $this->activeSemester->id,
            'action_status' => 'submitted',
            'total_credits' => 15.0,
            'course_unit_ids' => [$csc1101->id, $csc1102->id, $mth1101->id, $afn1102->id],
        ]);

        $submitResponse->assertRedirect();
        $submitResponse->assertSessionHas('success');

        $registration = CourseRegistration::where('student_id', $this->ronaldFresher->id)->firstOrFail();
        $this->assertEquals('submitted', $registration->status);
        $this->assertEquals(15.0, (float) $registration->total_credits);
        $this->assertCount(4, $registration->items);

        // 2. Advisor Reviews Slip in Approvals Hub
        $approvalsList = $this->get(route('approval.list'));
        $approvalsList->assertStatus(200);
        $approvalsList->assertSee($this->ronaldFresher->full_name);

        // 3. Advisor Approves the Registration
        $this->actingAs($this->advisor);
        $approveResponse = $this->post(route('approval.approve', $registration), [
            'advisor_remarks' => 'First Year Fresher registration verified and approved.',
        ]);
        $approveResponse->assertRedirect();
        $approveResponse->assertSessionHas('success');

        $approvedSlip = $registration->fresh();
        $this->assertEquals('approved', $approvedSlip->status);
        $this->assertEquals($this->advisor->id, $approvedSlip->approved_by_user_id);

        // 4. Printable Slip Renders Cleanly with Signature Blocks
        $printResponse = $this->get(route('registration.print', $approvedSlip));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Bishop Stuart University');
        $printResponse->assertSee('Official Semester Course Registration Slip');
        $printResponse->assertSee($this->ronaldFresher->full_name);
        $printResponse->assertSee('15.0 CU');
        $printResponse->assertSee("Student's Signature & Date", false);
    }

    /**
     * Journey 2: Sarah Namubiru (BSSE Year 2) Add/Drop Course Adjustment
     * Registration -> Enters Add/Drop -> Drops Elective -> Adds Alternate -> Re-Approval.
     */
    public function test_journey_2_bsse_year2_add_drop_workflow(): void
    {
        $csc2101 = CourseUnit::where('code', 'CSC2101')->firstOrFail();
        $swe2101 = CourseUnit::where('code', 'SWE2101')->firstOrFail();
        $swe2102 = CourseUnit::where('code', 'SWE2102')->firstOrFail();
        $swe2103 = CourseUnit::where('code', 'SWE2103')->firstOrFail();

        // 1. Initial approved registration (4 + 4 + 3 = 11.0 + 3.0 = 14.0 CU)
        $registration = CourseRegistration::factory()->approved()->create([
            'student_id' => $this->sarahYear2->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->activeSemester->id,
            'study_year' => 2,
            'semester_number' => 1,
            'total_credits' => 14.0,
        ]);

        $item1 = CourseRegistrationItem::create(['course_registration_id' => $registration->id, 'course_unit_id' => $csc2101->id, 'course_type' => 'Core', 'credit_units' => 4.0, 'status' => 'approved']);
        $item2 = CourseRegistrationItem::create(['course_registration_id' => $registration->id, 'course_unit_id' => $swe2101->id, 'course_type' => 'Core', 'credit_units' => 4.0, 'status' => 'approved']);
        $item3 = CourseRegistrationItem::create(['course_registration_id' => $registration->id, 'course_unit_id' => $swe2102->id, 'course_type' => 'Elective', 'credit_units' => 3.0, 'status' => 'approved']);
        $item4 = CourseRegistrationItem::create(['course_registration_id' => $registration->id, 'course_unit_id' => $swe2103->id, 'course_type' => 'Elective', 'credit_units' => 3.0, 'status' => 'approved']);
        $registration->recalculateTotalCredits();
        $this->assertEquals(14.0, (float) $registration->fresh()->total_credits);

        // 2. Sarah accesses Add/Drop Workspace
        $workspace = $this->get(route('registration.add-drop.edit', $registration));
        $workspace->assertStatus(200);
        $workspace->assertSee('Active Course Add / Drop Adjustment Window');

        // 3. Drop elective SWE2102 (14.0 - 3.0 = 11.0 CU, wait! 11.0 < 12.0 CU floor!)
        // Dropping SWE2102 should be BLOCKED because remaining is 11.0 CU (< 12.0 CU)!
        $blockedDrop = $this->post(route('registration.add-drop.drop', [
            'registration' => $registration,
            'item' => $item3,
        ]), [
            'drop_reason' => 'Schedule collision with extracurricular.',
        ]);
        $blockedDrop->assertSessionHas('error');
        $this->assertEquals('approved', $item3->fresh()->status);

        // First add extra elective SWE2104 so total is 17.0, then drop SWE2102
        $extraCourse = CourseUnit::factory()->create([
            'department_id' => $this->csDepartment->id,
            'code' => 'SWE2104',
            'name' => 'Cloud Computing',
            'credit_units' => 3.0,
            'status' => 'active',
        ]);
        CurriculumCourse::create([
            'curriculum_id' => $this->bsseCurriculum->id,
            'course_unit_id' => $extraCourse->id,
            'study_year' => 2,
            'semester' => 1,
            'course_type' => 'Elective',
        ]);

        $addResponse = $this->post(route('registration.add-drop.add', $registration), [
            'course_unit_id' => $extraCourse->id,
        ]);
        $addResponse->assertSessionHas('success');
        $this->assertEquals(17.0, (float) $registration->fresh()->total_credits);

        // Now drop SWE2102 (17.0 - 3.0 = 14.0 CU >= 12.0 CU floor) -> Allowed!
        $dropResponse = $this->post(route('registration.add-drop.drop', [
            'registration' => $registration,
            'item' => $item3,
        ]), [
            'drop_reason' => 'Schedule collision resolved by taking Cloud Computing.',
        ]);
        $dropResponse->assertSessionHas('success');

        $freshReg = $registration->fresh();
        $this->assertEquals('add_drop_pending', $freshReg->status);
        $this->assertEquals(14.0, (float) $freshReg->total_credits);
        $this->assertEquals('dropped', $item3->fresh()->status);

        // 4. Advisor reviews change log and re-approves
        $this->actingAs($this->advisor);
        $reApprove = $this->post(route('approval.approve', $registration), [
            'advisor_remarks' => 'Add/Drop adjustment authorized.',
        ]);
        $reApprove->assertSessionHas('success');
        $this->assertEquals('approved', $registration->fresh()->status);
    }

    /**
     * Journey 3: Emmanuel Twinomujuni (DCA) Stage and Diploma Curriculum Isolation.
     */
    public function test_journey_3_dca_diploma_curriculum_and_stage_isolation(): void
    {
        $response = $this->get(route('registration.eligibility', [
            'student_id' => $this->emmanuelDca->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('DCA-2026-V1');
        $response->assertSee('CSC1101');
        $response->assertSee('AFN1102');
        // Higher year courses (e.g. CSC2101) must NOT be in mandatory core or available electives
        $response->assertDontSee('CSC2101 (Data Structures)');
    }

    /**
     * Journey 4: Edge Cases (Closed Window Lockout & Credit Overload).
     */
    public function test_journey_4_calendar_lockout_and_credit_overload_guards(): void
    {
        $csc1101 = CourseUnit::where('code', 'CSC1101')->firstOrFail();
        $csc1102 = CourseUnit::where('code', 'CSC1102')->firstOrFail();
        $mth1101 = CourseUnit::where('code', 'MTH1101')->firstOrFail();

        // 1. Credit overload > 24 CU is rejected
        $overloadResponse = $this->post(route('registration.store'), [
            'student_id' => $this->ronaldFresher->id,
            'semester_id' => $this->activeSemester->id,
            'action_status' => 'submitted',
            'total_credits' => 28.0,
            'course_unit_ids' => array_fill(0, 7, $csc1101->id), // 7 * 4 = 28 CU
        ]);
        $overloadResponse->assertSessionHasErrors(['total_credits']);

        // 2. Calendar Window Lockout
        $this->activeSemester->update([
            'registration_start_date' => now()->subDays(30),
            'registration_end_date' => now()->subDays(5), // closed in past
        ]);

        $closedResponse = $this->get(route('registration.create', [
            'student_id' => $this->ronaldFresher->id,
        ]));
        $closedResponse->assertStatus(200);
        $closedResponse->assertSee('Course Registration Window Closed');
        $closedResponse->assertSee('disabled');
    }
}
