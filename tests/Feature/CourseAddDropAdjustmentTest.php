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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseAddDropAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    protected Student $student;

    protected Semester $activeSemester;

    protected CourseRegistration $registration;

    protected CourseUnit $core1;

    protected CourseUnit $core2;

    protected CourseUnit $core3;

    protected CourseUnit $elective1;

    protected CourseUnit $elective2;

    protected CourseUnit $advancedElective;

    protected function setUp(): void
    {
        parent::setUp();

        config(['academic.require_registration_approval' => true]);

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

        $this->student = Student::factory()->create([
            'campus_id' => $campus->id,
            'programme_id' => $programme->id,
            'curriculum_id' => $curriculum->id,
            'admission_academic_year_id' => $academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        // Define Course Units
        $this->core1 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1101',
            'name' => 'Structured Programming',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $this->core2 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1102',
            'name' => 'Computer Architecture',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $this->core3 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'MTH1101',
            'name' => 'Calculus I',
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

        $this->elective2 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1104',
            'name' => 'Communication Skills',
            'credit_units' => 3.0,
            'status' => 'active',
        ]);

        // Advanced elective with prerequisite on elective1
        $this->advancedElective = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1105',
            'name' => 'Advanced Web Architecture',
            'credit_units' => 3.0,
            'status' => 'active',
        ]);
        $this->advancedElective->prerequisites()->attach($this->elective1->id);

        // Map courses into curriculum
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

        CurriculumCourse::create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->elective2->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Elective',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->advancedElective->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Elective',
        ]);

        // Create an approved registration slip (Total: 4 + 4 + 4 + 4 + 3 = 19.0 CU)
        $this->registration = CourseRegistration::factory()->approved()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $this->activeSemester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'total_credits' => 19.0,
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $this->registration->id,
            'course_unit_id' => $this->core1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $this->registration->id,
            'course_unit_id' => $this->core2->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $this->registration->id,
            'course_unit_id' => $this->core3->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $this->registration->id,
            'course_unit_id' => $this->elective1->id,
            'course_type' => 'Elective',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $this->registration->id,
            'course_unit_id' => $this->elective2->id,
            'course_type' => 'Elective',
            'credit_units' => 3.0,
            'status' => 'approved',
        ]);
    }

    public function test_add_drop_workspace_renders_successfully(): void
    {
        $response = $this->get(route('registration.add-drop.edit', $this->registration));

        $response->assertStatus(200);
        $response->assertSee('Course Add / Drop Adjustment');
        $response->assertSee('Active Course Add / Drop Adjustment Window');
        $response->assertSee($this->student->full_name);
        $response->assertSee($this->student->registration_number);
        $response->assertSee('CSC1101');
        $response->assertSee('CSC1104');
        $response->assertSee('Locked Core');
        $response->assertSee('Drop');
    }

    public function test_cannot_add_drop_when_deadline_has_passed(): void
    {
        // Expire add/drop deadline
        $this->activeSemester->update([
            'add_drop_deadline' => now()->subDay(),
        ]);

        $electiveItem = $this->registration->items()->where('course_unit_id', $this->elective2->id)->first();

        // Attempt to drop course
        $dropResponse = $this->post(route('registration.add-drop.drop', [
            'registration' => $this->registration,
            'item' => $electiveItem,
        ]), [
            'drop_reason' => 'Want to adjust my timetable load.',
        ]);

        $dropResponse->assertRedirect(route('registration.add-drop.edit', $this->registration));
        $dropResponse->assertSessionHas('error');

        $this->assertDatabaseHas('course_registration_items', [
            'id' => $electiveItem->id,
            'status' => 'approved',
            'dropped_at' => null,
        ]);

        // Attempt to add course
        $addResponse = $this->post(route('registration.add-drop.add', $this->registration), [
            'course_unit_id' => $this->elective2->id,
        ]);

        $addResponse->assertRedirect(route('registration.add-drop.edit', $this->registration));
        $addResponse->assertSessionHas('error');
    }

    public function test_student_cannot_drop_mandatory_core_course(): void
    {
        $coreItem = $this->registration->items()->where('course_unit_id', $this->core1->id)->first();

        $response = $this->post(route('registration.add-drop.drop', [
            'registration' => $this->registration,
            'item' => $coreItem,
        ]), [
            'drop_reason' => 'I would prefer to drop this core course.',
        ]);

        $response->assertRedirect(route('registration.add-drop.edit', $this->registration));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('course_registration_items', [
            'id' => $coreItem->id,
            'status' => 'approved',
            'dropped_at' => null,
        ]);

        $this->assertEquals(19.0, (float) $this->registration->fresh()->total_credits);
    }

    public function test_student_can_drop_elective_course_and_recalculates_total_credits(): void
    {
        $electiveItem = $this->registration->items()->where('course_unit_id', $this->elective2->id)->first();

        $response = $this->post(route('registration.add-drop.drop', [
            'registration' => $this->registration,
            'item' => $electiveItem,
        ]), [
            'drop_reason' => 'Schedule collision with mandatory project work.',
        ]);

        $response->assertRedirect(route('registration.add-drop.edit', $this->registration));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('course_registration_items', [
            'id' => $electiveItem->id,
            'status' => 'dropped',
            'drop_reason' => 'Schedule collision with mandatory project work.',
        ]);

        $freshRegistration = $this->registration->fresh();
        // 19.0 - 3.0 = 16.0 CU
        $this->assertEquals(16.0, (float) $freshRegistration->total_credits);
        $this->assertEquals('add_drop_pending', $freshRegistration->status);
    }

    public function test_cannot_drop_courses_below_minimum_credit_floor(): void
    {
        // Adjust registration to minimum load: 12.0 CU (Core 4.0 + Core 4.0 + Elective 4.0)
        $this->registration->items()->where('course_unit_id', $this->core3->id)->delete();
        $this->registration->items()->where('course_unit_id', $this->elective2->id)->delete();
        $this->registration->recalculateTotalCredits();
        $this->assertEquals(12.0, (float) $this->registration->fresh()->total_credits);

        $electiveItem = $this->registration->items()->where('course_unit_id', $this->elective1->id)->first();

        $response = $this->post(route('registration.add-drop.drop', [
            'registration' => $this->registration,
            'item' => $electiveItem,
        ]), [
            'drop_reason' => 'Dropping to lessen semester work pressure.',
        ]);

        $response->assertRedirect(route('registration.add-drop.edit', $this->registration));
        $response->assertSessionHas('error');

        // Item remains approved and total credits remain 12.0
        $this->assertDatabaseHas('course_registration_items', [
            'id' => $electiveItem->id,
            'status' => 'approved',
            'dropped_at' => null,
        ]);
        $this->assertEquals(12.0, (float) $this->registration->fresh()->total_credits);
    }

    public function test_student_can_add_eligible_elective_course(): void
    {
        // Start with 16.0 CU (remove elective2)
        $this->registration->items()->where('course_unit_id', $this->elective2->id)->delete();
        $this->registration->recalculateTotalCredits();
        $this->assertEquals(16.0, (float) $this->registration->fresh()->total_credits);

        $response = $this->post(route('registration.add-drop.add', $this->registration), [
            'course_unit_id' => $this->elective2->id,
        ]);

        $response->assertRedirect(route('registration.add-drop.edit', $this->registration));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('course_registration_items', [
            'course_registration_id' => $this->registration->id,
            'course_unit_id' => $this->elective2->id,
            'status' => 'registered',
            'course_type' => 'Elective',
            'credit_units' => 3.0,
        ]);

        $freshRegistration = $this->registration->fresh();
        $this->assertEquals(19.0, (float) $freshRegistration->total_credits);
        $this->assertEquals('add_drop_pending', $freshRegistration->status);
    }

    public function test_cannot_add_course_exceeding_maximum_credit_ceiling(): void
    {
        // Registration is currently at 19.0 CU.
        // Create a large 6.0 CU course so 19.0 + 6.0 = 25.0 CU > 24.0 CU
        $largeCourse = CourseUnit::factory()->create([
            'credit_units' => 6.0,
            'status' => 'active',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $this->student->curriculum_id,
            'course_unit_id' => $largeCourse->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Elective',
        ]);

        $response = $this->post(route('registration.add-drop.add', $this->registration), [
            'course_unit_id' => $largeCourse->id,
        ]);

        $response->assertRedirect(route('registration.add-drop.edit', $this->registration));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('course_registration_items', [
            'course_registration_id' => $this->registration->id,
            'course_unit_id' => $largeCourse->id,
        ]);
        $this->assertEquals(19.0, (float) $this->registration->fresh()->total_credits);
    }

    public function test_cannot_add_course_with_unmet_prerequisites_when_enforcement_is_enabled(): void
    {
        config(['academic.enforce_prerequisites' => true]);

        // $this->advancedElective requires $this->elective1 to be completed in a prior approved semester.
        // It is currently enrolled in the SAME active semester, not completed in a prior semester.
        $response = $this->post(route('registration.add-drop.add', $this->registration), [
            'course_unit_id' => $this->advancedElective->id,
        ]);

        $response->assertRedirect(route('registration.add-drop.edit', $this->registration));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('course_registration_items', [
            'course_registration_id' => $this->registration->id,
            'course_unit_id' => $this->advancedElective->id,
        ]);
    }

    public function test_can_add_course_with_unmet_prerequisites_when_enforcement_is_disabled(): void
    {
        config(['academic.enforce_prerequisites' => false]);

        $response = $this->post(route('registration.add-drop.add', $this->registration), [
            'course_unit_id' => $this->advancedElective->id,
        ]);

        $response->assertRedirect(route('registration.add-drop.edit', $this->registration));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('course_registration_items', [
            'course_registration_id' => $this->registration->id,
            'course_unit_id' => $this->advancedElective->id,
            'status' => 'registered',
        ]);
    }

    public function test_re_adding_previously_dropped_course_reactivates_it(): void
    {
        // First drop elective2
        $electiveItem = $this->registration->items()->where('course_unit_id', $this->elective2->id)->first();
        $electiveItem->update([
            'status' => 'dropped',
            'dropped_at' => now(),
            'drop_reason' => 'Temporary dropped due to clash.',
        ]);
        $this->registration->recalculateTotalCredits();
        $this->assertEquals(16.0, (float) $this->registration->fresh()->total_credits);

        // Now re-add elective2
        $response = $this->post(route('registration.add-drop.add', $this->registration), [
            'course_unit_id' => $this->elective2->id,
        ]);

        $response->assertRedirect(route('registration.add-drop.edit', $this->registration));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('course_registration_items', [
            'id' => $electiveItem->id,
            'course_unit_id' => $this->elective2->id,
            'status' => 'registered',
            'dropped_at' => null,
            'drop_reason' => null,
        ]);

        $this->assertEquals(19.0, (float) $this->registration->fresh()->total_credits);
    }

    public function test_dropped_courses_display_in_audit_trail_with_strikethrough(): void
    {
        $electiveItem = $this->registration->items()->where('course_unit_id', $this->elective2->id)->first();
        $electiveItem->update([
            'status' => 'dropped',
            'dropped_at' => now(),
            'drop_reason' => 'Personal timetable conflict with elective slot.',
        ]);
        $this->registration->recalculateTotalCredits();

        $response = $this->get(route('registration.add-drop.edit', $this->registration));

        $response->assertStatus(200);
        $response->assertSee('Dropped Courses Audit Trail');
        $response->assertSee('text-decoration-line-through', false);
        $response->assertSee('CSC1104');
        $response->assertSee('Personal timetable conflict with elective slot.');
    }

    public function test_add_drop_adjustments_are_auto_approved_when_registration_approval_policy_is_disabled(): void
    {
        config(['academic.require_registration_approval' => false]);

        $electiveItem = $this->registration->items()->where('course_unit_id', $this->elective2->id)->first();

        $response = $this->post(route('registration.add-drop.drop', [
            'registration' => $this->registration,
            'item' => $electiveItem,
        ]), [
            'drop_reason' => 'Direct drop without requiring advisor sign-off.',
        ]);

        $response->assertRedirect(route('registration.add-drop.edit', $this->registration));
        $response->assertSessionHas('success');

        $freshRegistration = $this->registration->fresh();
        $this->assertEquals('approved', $freshRegistration->status);
        $this->assertNotNull($freshRegistration->approved_at);
    }
}
