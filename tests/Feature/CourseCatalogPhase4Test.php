<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\CourseAssessmentSheet;
use App\Models\CourseUnit;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\StudentMark;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCatalogPhase4Test extends TestCase
{
    use RefreshDatabase;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $university = University::factory()->create([
            'name' => 'Apex University',
            'code' => 'APEX',
        ]);

        $campus = Campus::factory()->create([
            'university_id' => $university->id,
            'name' => 'Main Campus',
            'code' => 'MAIN',
        ]);

        $faculty = Faculty::factory()->create([
            'campus_id' => $campus->id,
            'name' => 'Faculty of Science & Technology',
            'code' => 'FST',
        ]);

        $this->department = Department::factory()->create([
            'faculty_id' => $faculty->id,
            'name' => 'Department of Computer Science',
            'code' => 'CS',
        ]);
    }

    public function test_can_render_course_catalog_index_with_tabulator_datatable(): void
    {
        $course = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC1101',
            'name' => 'Introduction to Computer Science',
            'credit_units' => 4.0,
        ]);

        $response = $this->get(route('course.list'));

        $response->assertStatus(200);
        $response->assertSee('Master Course Catalog');
        $response->assertSee('courses-table');
        $response->assertSee($course->code);
        $response->assertSee($course->name);
        $response->assertSee('4.0 CU');
    }

    public function test_can_create_a_course_unit_under_a_department(): void
    {
        $payload = [
            'department_id' => $this->department->id,
            'code' => 'CSC1201',
            'name' => 'Object Oriented Programming with Java',
            'credit_units' => 4.0,
            'description' => 'Comprehensive OOP syllabus.',
            'status' => 'active',
        ];

        $response = $this->post(route('course.store'), $payload);

        $response->assertRedirect(route('course.list'));
        $this->assertDatabaseHas('course_units', [
            'code' => 'CSC1201',
            'name' => 'Object Oriented Programming with Java',
            'department_id' => $this->department->id,
        ]);
    }

    public function test_course_code_is_normalized_to_uppercase(): void
    {
        $payload = [
            'department_id' => $this->department->id,
            'code' => 'bit1101',
            'name' => 'Foundations of IT',
            'credit_units' => 3.0,
            'status' => 'active',
        ];

        $this->post(route('course.store'), $payload);

        $this->assertDatabaseHas('course_units', [
            'code' => 'BIT1101',
        ]);
    }

    public function test_course_code_must_be_unique(): void
    {
        CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC1101',
        ]);

        $response = $this->post(route('course.store'), [
            'department_id' => $this->department->id,
            'code' => 'CSC1101',
            'name' => 'Duplicate Course',
            'credit_units' => 3.0,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_credit_units_must_be_within_valid_range(): void
    {
        // Negative / zero credit
        $response1 = $this->post(route('course.store'), [
            'department_id' => $this->department->id,
            'code' => 'CSC1105',
            'name' => 'Zero Credit Course',
            'credit_units' => 0,
            'status' => 'active',
        ]);
        $response1->assertSessionHasErrors(['credit_units']);

        // Excessively high credits (> 15)
        $response2 = $this->post(route('course.store'), [
            'department_id' => $this->department->id,
            'code' => 'CSC1106',
            'name' => 'Excessive Credit Course',
            'credit_units' => 25.0,
            'status' => 'active',
        ]);
        $response2->assertSessionHasErrors(['credit_units']);
    }

    public function test_department_id_is_required_and_must_exist(): void
    {
        $response = $this->post(route('course.store'), [
            'department_id' => 99999, // Non-existent
            'code' => 'CSC9999',
            'name' => 'Orphan Course',
            'credit_units' => 3.0,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['department_id']);
    }

    public function test_can_show_course_unit_profile(): void
    {
        $course = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC2101',
            'name' => 'Database Management Systems',
            'description' => 'Relational database theory and SQL.',
        ]);

        $response = $this->get(route('course.show', $course));

        $response->assertStatus(200);
        $response->assertSee($course->code);
        $response->assertSee($course->name);
        $response->assertSee('Relational database theory and SQL.');
        $response->assertSee($this->department->name);
    }

    public function test_can_update_course_unit(): void
    {
        $course = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC3101',
            'name' => 'Old Course Name',
            'credit_units' => 3.0,
        ]);

        $response = $this->put(route('course.update', $course), [
            'department_id' => $this->department->id,
            'code' => 'CSC3101',
            'name' => 'Software Engineering Principles',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('course.list'));
        $this->assertDatabaseHas('course_units', [
            'id' => $course->id,
            'name' => 'Software Engineering Principles',
            'credit_units' => 4.0,
        ]);
    }

    public function test_can_delete_course_unit(): void
    {
        $course = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC9999',
        ]);

        $response = $this->delete(route('course.delete', $course));

        $response->assertRedirect(route('course.list'));
        $this->assertDatabaseMissing('course_units', [
            'id' => $course->id,
        ]);
    }

    public function test_filter_courses_by_department_and_status(): void
    {
        $otherDept = Department::factory()->create(['name' => 'Finance Dept', 'code' => 'FIN']);

        $course1 = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC1001',
            'status' => 'active',
        ]);

        $course2 = CourseUnit::factory()->create([
            'department_id' => $otherDept->id,
            'code' => 'FIN1001',
            'status' => 'archived',
        ]);

        // Filter by department
        $this->get(route('course.list', ['department_id' => $this->department->id]))
            ->assertStatus(200)
            ->assertSee($course1->code)
            ->assertDontSee($course2->code);

        // Filter by status
        $this->get(route('course.list', ['status' => 'archived']))
            ->assertStatus(200)
            ->assertSee($course2->code)
            ->assertDontSee($course1->code);
    }

    public function test_cannot_update_course_unit_when_assessment_marks_are_attached(): void
    {
        $course = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC2201',
            'name' => 'Data Structures and Algorithms',
            'credit_units' => 4.0,
        ]);

        $sheet = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $course->id,
        ]);

        StudentMark::factory()->create([
            'course_assessment_sheet_id' => $sheet->id,
            'final_score' => 75.0,
        ]);

        $this->assertTrue($course->hasAssessmentMarks());

        $response = $this->put(route('course.update', $course), [
            'department_id' => $this->department->id,
            'code' => 'CSC2201',
            'name' => 'Modified Algorithms Title',
            'credit_units' => 5.0,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('course.edit', $course));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('course_units', [
            'id' => $course->id,
            'name' => 'Data Structures and Algorithms',
            'credit_units' => 4.0,
        ]);
        $this->assertDatabaseMissing('course_units', [
            'name' => 'Modified Algorithms Title',
        ]);
    }

    public function test_cannot_delete_course_unit_when_assessment_marks_are_attached(): void
    {
        $course = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC2202',
            'name' => 'Discrete Mathematics',
        ]);

        $sheet = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $course->id,
        ]);

        StudentMark::factory()->create([
            'course_assessment_sheet_id' => $sheet->id,
        ]);

        $response = $this->delete(route('course.delete', $course));

        $response->assertRedirect(route('course.list'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('course_units', [
            'id' => $course->id,
        ]);
    }

    public function test_edit_view_shows_locked_notice_and_disables_inputs_when_marks_exist(): void
    {
        $course = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC3105',
            'name' => 'Artificial Intelligence',
        ]);

        $sheet = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $course->id,
        ]);

        StudentMark::factory()->create([
            'course_assessment_sheet_id' => $sheet->id,
        ]);

        $response = $this->get(route('course.edit', $course));

        $response->assertStatus(200);
        $response->assertSee('Course Unit is Locked');
        $response->assertSee('Assessment Marks Recorded');
        $response->assertSee('Create New Course Unit Instead');
        $response->assertSee('Locked &mdash; Cannot Update', false);
    }

    public function test_show_view_displays_locked_immutable_badge_and_hides_delete_button_when_marks_exist(): void
    {
        $course = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC3201',
            'name' => 'Computer Networks',
        ]);

        $sheet = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $course->id,
        ]);

        StudentMark::factory()->create([
            'course_assessment_sheet_id' => $sheet->id,
        ]);

        $response = $this->get(route('course.show', $course));

        $response->assertStatus(200);
        $response->assertSee('Marks Recorded &bull; Immutable', false);
        $response->assertSee('View Locked Specification');
        $response->assertSee('Create New Unit for Curriculum');
        $response->assertDontSee('Delete Course Unit');
    }
}
