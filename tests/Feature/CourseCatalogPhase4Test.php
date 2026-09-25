<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\CourseUnit;
use App\Models\Department;
use App\Models\Faculty;
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

        $response = $this->get(route('academic.courses.index'));

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

        $response = $this->post(route('academic.courses.store'), $payload);

        $response->assertRedirect(route('academic.courses.index'));
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

        $this->post(route('academic.courses.store'), $payload);

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

        $response = $this->post(route('academic.courses.store'), [
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
        $response1 = $this->post(route('academic.courses.store'), [
            'department_id' => $this->department->id,
            'code' => 'CSC1105',
            'name' => 'Zero Credit Course',
            'credit_units' => 0,
            'status' => 'active',
        ]);
        $response1->assertSessionHasErrors(['credit_units']);

        // Excessively high credits (> 15)
        $response2 = $this->post(route('academic.courses.store'), [
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
        $response = $this->post(route('academic.courses.store'), [
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

        $response = $this->get(route('academic.courses.show', $course));

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

        $response = $this->put(route('academic.courses.update', $course), [
            'department_id' => $this->department->id,
            'code' => 'CSC3101',
            'name' => 'Software Engineering Principles',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('academic.courses.index'));
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

        $response = $this->delete(route('academic.courses.destroy', $course));

        $response->assertRedirect(route('academic.courses.index'));
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
        $this->get(route('academic.courses.index', ['department_id' => $this->department->id]))
            ->assertStatus(200)
            ->assertSee($course1->code)
            ->assertDontSee($course2->code);

        // Filter by status
        $this->get(route('academic.courses.index', ['status' => 'archived']))
            ->assertStatus(200)
            ->assertSee($course2->code)
            ->assertDontSee($course1->code);
    }
}
