<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructurePhase2Test extends TestCase
{
    use RefreshDatabase;

    protected University $university;

    protected Campus $campus;

    protected Faculty $faculty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::factory()->create([
            'name' => 'Apex University',
            'code' => 'APEX',
        ]);

        $this->campus = Campus::factory()->create([
            'university_id' => $this->university->id,
            'name' => 'Main Campus',
            'code' => 'MAIN',
        ]);

        $this->faculty = Faculty::factory()->create([
            'campus_id' => $this->campus->id,
            'name' => 'Faculty of Science & Technology',
            'code' => 'FST',
        ]);
    }

    public function test_can_render_departments_and_programmes_indexes(): void
    {
        $department = Department::factory()->create(['faculty_id' => $this->faculty->id]);
        Programme::factory()->create(['department_id' => $department->id]);

        $this->get(route('department.list'))
            ->assertStatus(200)
            ->assertSee('Academic Departments')
            ->assertSee($department->name);

        $this->get(route('programme.list'))
            ->assertStatus(200)
            ->assertSee('Degree & Diploma Programmes')
            ->assertSee($department->name);
    }

    public function test_can_create_department_under_faculty(): void
    {
        $hod = User::factory()->create(['name' => 'Dr. Robert Kato']);

        $payload = [
            'faculty_id' => $this->faculty->id,
            'name' => 'Department of Computer Science',
            'code' => 'CS',
            'hod_user_id' => $hod->id,
            'description' => 'Computer Science Department',
            'status' => 'active',
        ];

        $response = $this->post(route('department.store'), $payload);

        $response->assertRedirect(route('department.list'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('departments', [
            'faculty_id' => $this->faculty->id,
            'code' => 'CS',
            'hod_user_id' => $hod->id,
        ]);
    }

    public function test_department_code_must_be_unique_within_faculty(): void
    {
        Department::factory()->create([
            'faculty_id' => $this->faculty->id,
            'code' => 'CS',
        ]);

        $payload = [
            'faculty_id' => $this->faculty->id,
            'name' => 'Duplicate CS Department',
            'code' => 'CS',
            'status' => 'active',
        ];

        $response = $this->post(route('department.store'), $payload);

        $response->assertSessionHasErrors('code');
    }

    public function test_can_create_programme_under_department(): void
    {
        $department = Department::factory()->create(['faculty_id' => $this->faculty->id]);

        $payload = [
            'department_id' => $department->id,
            'name' => 'Bachelor of Science in Software Engineering',
            'code' => 'BSSE',
            'award_type' => 'Bachelors',
            'duration_years' => 3,
            'required_credits_to_graduate' => 115,
            'description' => 'Software engineering degree',
            'status' => 'active',
        ];

        $response = $this->post(route('programme.store'), $payload);

        $response->assertRedirect(route('programme.list'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('programmes', [
            'department_id' => $department->id,
            'code' => 'BSSE',
            'duration_years' => 3,
            'required_credits_to_graduate' => 115,
        ]);
    }

    public function test_programme_code_must_be_unique_globally(): void
    {
        $department1 = Department::factory()->create(['faculty_id' => $this->faculty->id]);
        $department2 = Department::factory()->create(['faculty_id' => $this->faculty->id]);

        Programme::factory()->create([
            'department_id' => $department1->id,
            'code' => 'BSCS',
        ]);

        $payload = [
            'department_id' => $department2->id,
            'name' => 'Another CS Programme',
            'code' => 'BSCS',
            'award_type' => 'Bachelors',
            'duration_years' => 3,
            'required_credits_to_graduate' => 110,
            'status' => 'active',
        ];

        $response = $this->post(route('programme.store'), $payload);

        $response->assertSessionHasErrors('code');
    }

    public function test_programme_validation_rejects_invalid_duration_or_credits(): void
    {
        $department = Department::factory()->create(['faculty_id' => $this->faculty->id]);

        $payload = [
            'department_id' => $department->id,
            'name' => 'Invalid Programme',
            'code' => 'INV',
            'award_type' => 'Bachelors',
            'duration_years' => 0, // Invalid: min 1
            'required_credits_to_graduate' => -5, // Invalid: min 10
            'status' => 'active',
        ];

        $response = $this->post(route('programme.store'), $payload);

        $response->assertSessionHasErrors(['duration_years', 'required_credits_to_graduate']);
    }

    public function test_can_filter_departments_by_faculty(): void
    {
        $otherFaculty = Faculty::factory()->create([
            'campus_id' => $this->campus->id,
            'code' => 'FBUS',
        ]);

        $dept1 = Department::factory()->create([
            'faculty_id' => $this->faculty->id,
            'name' => 'CS Dept Alpha',
        ]);

        $dept2 = Department::factory()->create([
            'faculty_id' => $otherFaculty->id,
            'name' => 'Business Dept Beta',
        ]);

        $response = $this->get(route('department.list', ['faculty_id' => $this->faculty->id]));

        $response->assertStatus(200);
        $response->assertSee('CS Dept Alpha');
        $response->assertDontSee('Business Dept Beta');
    }

    public function test_cannot_delete_faculty_with_departments(): void
    {
        Department::factory()->create(['faculty_id' => $this->faculty->id]);

        $response = $this->delete(route('faculty.delete', $this->faculty));

        $response->assertRedirect(route('faculty.list'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('faculties', ['id' => $this->faculty->id]);
    }

    public function test_cannot_delete_department_with_programmes(): void
    {
        $department = Department::factory()->create(['faculty_id' => $this->faculty->id]);
        Programme::factory()->create(['department_id' => $department->id]);

        $response = $this->delete(route('department.delete', $department));

        $response->assertRedirect(route('department.list'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_can_delete_empty_programme_and_department(): void
    {
        $department = Department::factory()->create(['faculty_id' => $this->faculty->id]);
        $programme = Programme::factory()->create(['department_id' => $department->id]);

        // Delete programme first
        $response = $this->delete(route('programme.delete', $programme));
        $response->assertRedirect(route('programme.list'));
        $this->assertDatabaseMissing('programmes', ['id' => $programme->id]);

        // Now department is empty and can be deleted
        $deptResponse = $this->delete(route('department.delete', $department));
        $deptResponse->assertRedirect(route('department.list'));
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }
}
