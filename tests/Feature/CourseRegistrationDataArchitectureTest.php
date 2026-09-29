<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\CourseRegistration;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use App\Models\Curriculum;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\University;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseRegistrationDataArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected Student $student;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected CourseUnit $course1;

    protected CourseUnit $course2;

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

        $this->academicYear = AcademicYear::factory()->create([
            'is_current' => true,
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'semester_number' => 1,
            'is_active' => true,
        ]);

        $this->student = Student::factory()->create([
            'campus_id' => $campus->id,
            'programme_id' => $programme->id,
            'curriculum_id' => $curriculum->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $this->course1 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1101',
            'credit_units' => 4.0,
        ]);

        $this->course2 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1102',
            'credit_units' => 3.5,
        ]);
    }

    public function test_can_create_course_registration_and_items(): void
    {
        $registration = CourseRegistration::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'submitted',
            'submitted_at' => now(),
            'total_credits' => 7.5,
        ]);

        $item1 = CourseRegistrationItem::create([
            'course_registration_id' => $registration->id,
            'course_unit_id' => $this->course1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $item2 = CourseRegistrationItem::create([
            'course_registration_id' => $registration->id,
            'course_unit_id' => $this->course2->id,
            'course_type' => 'Elective',
            'credit_units' => 3.5,
            'status' => 'registered',
        ]);

        $this->assertDatabaseHas('course_registrations', [
            'id' => $registration->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        $this->assertDatabaseHas('course_registration_items', [
            'id' => $item1->id,
            'course_unit_id' => $this->course1->id,
            'course_type' => 'Core',
        ]);

        $this->assertCount(2, $registration->items);
        $this->assertCount(2, $registration->courseUnits);
        $this->assertTrue($registration->student->is($this->student));
        $this->assertTrue($this->student->courseRegistrations->contains($registration));
    }

    public function test_cannot_create_duplicate_registration_for_same_semester(): void
    {
        CourseRegistration::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'draft',
        ]);

        $this->expectException(QueryException::class);

        // Attempt duplicate registration for same student and same semester
        CourseRegistration::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'submitted',
        ]);
    }

    public function test_cannot_add_duplicate_course_unit_to_same_registration(): void
    {
        $registration = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $registration->id,
            'course_unit_id' => $this->course1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $this->expectException(QueryException::class);

        // Attempt to add the exact same course unit to the same registration slip
        CourseRegistrationItem::create([
            'course_registration_id' => $registration->id,
            'course_unit_id' => $this->course1->id,
            'course_type' => 'Elective',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);
    }

    public function test_cascade_deletes_items_when_registration_deleted(): void
    {
        $registration = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
        ]);

        $item = CourseRegistrationItem::create([
            'course_registration_id' => $registration->id,
            'course_unit_id' => $this->course1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $this->assertDatabaseHas('course_registration_items', ['id' => $item->id]);

        $registration->delete();

        $this->assertDatabaseMissing('course_registrations', ['id' => $registration->id]);
        $this->assertDatabaseMissing('course_registration_items', ['id' => $item->id]);
    }

    public function test_total_credits_recalculates_on_item_changes(): void
    {
        $registration = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'total_credits' => 0.0,
        ]);

        $item1 = CourseRegistrationItem::create([
            'course_registration_id' => $registration->id,
            'course_unit_id' => $this->course1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $item2 = CourseRegistrationItem::create([
            'course_registration_id' => $registration->id,
            'course_unit_id' => $this->course2->id,
            'course_type' => 'Core',
            'credit_units' => 3.5,
            'status' => 'registered',
        ]);

        $registration->recalculateTotalCredits();
        $this->assertEquals(7.5, $registration->fresh()->total_credits);

        // Drop item 2
        $item2->update(['status' => 'dropped', 'dropped_at' => now()]);
        $registration->recalculateTotalCredits();

        $this->assertEquals(4.0, $registration->fresh()->total_credits);
    }

    public function test_course_registration_model_scopes_and_helpers(): void
    {
        $regDraft = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester->id,
            'status' => 'draft',
        ]);

        $student2 = Student::factory()->create([
            'programme_id' => $this->student->programme_id,
            'curriculum_id' => $this->student->curriculum_id,
            'campus_id' => $this->student->campus_id,
            'admission_academic_year_id' => $this->academicYear->id,
        ]);

        $regSubmitted = CourseRegistration::factory()->submitted()->create([
            'student_id' => $student2->id,
            'semester_id' => $this->semester->id,
        ]);

        $student3 = Student::factory()->create([
            'programme_id' => $this->student->programme_id,
            'curriculum_id' => $this->student->curriculum_id,
            'campus_id' => $this->student->campus_id,
            'admission_academic_year_id' => $this->academicYear->id,
        ]);

        $regApproved = CourseRegistration::factory()->approved()->create([
            'student_id' => $student3->id,
            'semester_id' => $this->semester->id,
        ]);

        $this->assertTrue($regDraft->canBeEdited());
        $this->assertFalse($regApproved->canBeEdited());

        $pending = CourseRegistration::pendingApproval()->get();
        $this->assertTrue($pending->contains($regSubmitted));
        $this->assertFalse($pending->contains($regDraft));
        $this->assertFalse($pending->contains($regApproved));

        $approved = CourseRegistration::approved()->get();
        $this->assertTrue($approved->contains($regApproved));
        $this->assertFalse($approved->contains($regSubmitted));
    }

    public function test_course_registrations_index_view_loads_successfully(): void
    {
        CourseRegistration::factory()->submitted()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
        ]);

        $response = $this->get(route('academic.registrations.index'));

        $response->assertStatus(200);
        $response->assertSee('Semester Course Registrations');
        $response->assertSee('Total Registration Slips');
        $response->assertSee('Confirmed Enrollments');
        $response->assertDontSee('Pending Advisor Review');
        $response->assertDontSee('Changes Requested / Rejected');
        $response->assertSee('registrations-table');
        $response->assertSee($this->student->registration_number);

        // When advisor approvals are enabled, show pending and rejected cards
        config(['academic.require_registration_approval' => true]);
        $responseWithApproval = $this->get(route('academic.registrations.index'));
        $responseWithApproval->assertSee('Pending Advisor Review');
        $responseWithApproval->assertSee('Approved Enrollments');
        $responseWithApproval->assertSee('Changes Requested / Rejected');

        // When prerequisites are disabled, Eligibility Inspector button and sidebar tab are hidden
        config(['academic.enforce_prerequisites' => false]);
        $response = $this->get(route('academic.registrations.index'));
        $response->assertDontSee('Eligibility Inspector');
        $response->assertDontSee('Course Eligibility');

        // When prerequisites are enabled, they are shown
        config(['academic.enforce_prerequisites' => true]);
        $responseWithPrereq = $this->get(route('academic.registrations.index'));
        $responseWithPrereq->assertSee('Eligibility Inspector');
        $responseWithPrereq->assertSee('Course Eligibility');
    }

    public function test_course_registrations_show_view_loads_successfully(): void
    {
        $registration = CourseRegistration::factory()->submitted()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
        ]);

        CourseRegistrationItem::create([
            'course_registration_id' => $registration->id,
            'course_unit_id' => $this->course1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $response = $this->get(route('academic.registrations.show', $registration));

        $response->assertStatus(200);
        $response->assertSee('Course Registration Slip');
        $response->assertSee($this->student->full_name);
        $response->assertSee($this->course1->code);
        $response->assertSee('Registered Course Units');
    }

    public function test_course_registrations_index_filters(): void
    {
        $reg1 = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'status' => 'draft',
        ]);

        $student2 = Student::factory()->create([
            'programme_id' => $this->student->programme_id,
            'curriculum_id' => $this->student->curriculum_id,
            'campus_id' => $this->student->campus_id,
            'admission_academic_year_id' => $this->academicYear->id,
        ]);

        $reg2 = CourseRegistration::factory()->submitted()->create([
            'student_id' => $student2->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
        ]);

        $response = $this->get(route('academic.registrations.index', ['status' => 'submitted']));

        $response->assertStatus(200);
        $response->assertSee($student2->registration_number);
        $response->assertDontSee($this->student->registration_number);
    }
}
