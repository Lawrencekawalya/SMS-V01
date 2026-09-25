<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\Curriculum;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\Student;
use App\Models\University;
use Database\Seeders\StudentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected Campus $campus;

    protected Programme $programme;

    protected Curriculum $curriculum;

    protected AcademicYear $year;

    protected function setUp(): void
    {
        parent::setUp();

        $university = University::factory()->create();
        $this->campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $this->campus->id]);
        $department = Department::factory()->create(['faculty_id' => $faculty->id]);

        $this->programme = Programme::factory()->create([
            'department_id' => $department->id,
            'name' => 'Bachelor of Science in Computer Science',
            'code' => 'BSCS',
            'duration_years' => 3,
        ]);

        $this->curriculum = Curriculum::factory()->create([
            'programme_id' => $this->programme->id,
            'version_name' => '2026-2029 Standard CS Curriculum',
            'is_active' => true,
        ]);

        $this->year = AcademicYear::factory()->create([
            'name' => '2026/2027',
            'is_current' => true,
        ]);
    }

    public function test_can_create_student_linked_to_academic_model(): void
    {
        $student = Student::factory()->create([
            'campus_id' => $this->campus->id,
            'programme_id' => $this->programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $this->year->id,
            'registration_number' => '26/BSCS/001',
            'student_number' => '202600101',
            'first_name' => 'Ronald',
            'last_name' => 'Mukasa',
            'other_names' => null,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('students', [
            'registration_number' => '26/BSCS/001',
            'programme_id' => $this->programme->id,
            'curriculum_id' => $this->curriculum->id,
        ]);

        $this->assertEquals('Ronald Mukasa', $student->full_name);
        $this->assertEquals('Year 1, Sem 1', $student->academic_stage);
        $this->assertTrue($student->isEligibleForRegistration());
        $this->assertEquals('text-bg-success', $student->status_badge_class);
    }

    public function test_student_model_relationships(): void
    {
        $student = Student::factory()->create([
            'campus_id' => $this->campus->id,
            'programme_id' => $this->programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $this->year->id,
        ]);

        $this->assertInstanceOf(Campus::class, $student->campus);
        $this->assertInstanceOf(Programme::class, $student->programme);
        $this->assertInstanceOf(Curriculum::class, $student->curriculum);
        $this->assertInstanceOf(AcademicYear::class, $student->admissionAcademicYear);

        // Inverse relationships
        $this->assertTrue($this->programme->students->contains($student));
        $this->assertTrue($this->curriculum->students->contains($student));
    }

    public function test_student_scopes(): void
    {
        $activeFresher = Student::factory()->fresher()->create([
            'campus_id' => $this->campus->id,
            'programme_id' => $this->programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $this->year->id,
            'status' => 'active',
        ]);

        $suspendedYearTwo = Student::factory()->yearTwo()->create([
            'campus_id' => $this->campus->id,
            'programme_id' => $this->programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $this->year->id,
            'status' => 'suspended',
        ]);

        $this->assertTrue(Student::active()->get()->contains($activeFresher));
        $this->assertFalse(Student::active()->get()->contains($suspendedYearTwo));

        $freshers = Student::inStage(1, 1)->get();
        $this->assertTrue($freshers->contains($activeFresher));
        $this->assertFalse($freshers->contains($suspendedYearTwo));
    }

    public function test_student_seeder_populates_students_cleanly(): void
    {
        $this->seed(StudentSeeder::class);

        $this->assertDatabaseHas('students', [
            'registration_number' => '26/BSCS/001',
            'first_name' => 'Ronald',
        ]);
        $this->assertDatabaseHas('students', [
            'registration_number' => '26/BSCS/002',
            'first_name' => 'Brenda',
        ]);
    }

    public function test_student_index_page_displays_table_and_statistics(): void
    {
        $student = Student::factory()->create([
            'campus_id' => $this->campus->id,
            'programme_id' => $this->programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $this->year->id,
            'registration_number' => '26/BSCS/001',
            'first_name' => 'Ronald',
            'last_name' => 'Mukasa',
        ]);

        $response = $this->get(route('academic.students.index'));

        $response->assertStatus(200);
        $response->assertSee('Students Directory');
        $response->assertSee('students-table');
        $response->assertSee('26/BSCS/001');
        $response->assertSee('Ronald Mukasa');
    }

    public function test_student_show_page_displays_profile_and_academic_placement(): void
    {
        $student = Student::factory()->create([
            'campus_id' => $this->campus->id,
            'programme_id' => $this->programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $this->year->id,
            'registration_number' => '26/BSCS/001',
            'first_name' => 'Ronald',
            'last_name' => 'Mukasa',
        ]);

        $response = $this->get(route('academic.students.show', $student));

        $response->assertStatus(200);
        $response->assertSee('Ronald Mukasa');
        $response->assertSee('26/BSCS/001');
        $response->assertSee($this->programme->name);
        $response->assertSee($this->curriculum->version_name);
    }
}
