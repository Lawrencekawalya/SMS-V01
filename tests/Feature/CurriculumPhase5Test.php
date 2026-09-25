<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\CourseUnit;
use App\Models\Curriculum;
use App\Models\CurriculumCourse;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurriculumPhase5Test extends TestCase
{
    use RefreshDatabase;

    protected Programme $programme;

    protected CourseUnit $course1;

    protected CourseUnit $course2;

    protected CourseUnit $course3;

    protected function setUp(): void
    {
        parent::setUp();

        $university = University::factory()->create();
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id]);
        $department = Department::factory()->create(['faculty_id' => $faculty->id]);

        $this->programme = Programme::factory()->create([
            'department_id' => $department->id,
            'name' => 'Bachelor of Science in Software Engineering',
            'code' => 'BSSE',
            'duration_years' => 4,
            'required_credits_to_graduate' => 120,
        ]);

        $this->course1 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1101',
            'name' => 'Intro to Programming',
            'credit_units' => 4.0,
        ]);

        $this->course2 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1201',
            'name' => 'Data Structures',
            'credit_units' => 4.0,
        ]);

        $this->course3 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC2101',
            'name' => 'Database Systems',
            'credit_units' => 3.5,
        ]);
    }

    public function test_can_render_curriculums_index_with_tabulator_datatable(): void
    {
        $curriculum = Curriculum::factory()->create([
            'programme_id' => $this->programme->id,
            'version_name' => '2024-2028 Structure',
        ]);

        $response = $this->get(route('academic.curriculums.index'));

        $response->assertStatus(200);
        $response->assertSee('Curriculum Framework &amp; Progression', false);
        $response->assertSee('curriculums-table');
        $response->assertSee($curriculum->version_name);
        $response->assertSee($this->programme->code);
    }

    public function test_can_create_curriculum_version_under_programme(): void
    {
        $payload = [
            'programme_id' => $this->programme->id,
            'version_name' => '2026-2030 New Curriculum',
            'start_academic_year' => 2026,
            'end_academic_year' => 2030,
            'min_graduation_credits' => 120,
            'is_active' => '1',
        ];

        $response = $this->post(route('academic.curriculums.store'), $payload);

        $curriculum = Curriculum::where('version_name', '2026-2030 New Curriculum')->first();
        $this->assertNotNull($curriculum);
        $response->assertRedirect(route('academic.curriculums.show', $curriculum));

        $this->assertDatabaseHas('curriculums', [
            'id' => $curriculum->id,
            'programme_id' => $this->programme->id,
            'version_name' => '2026-2030 New Curriculum',
            'min_graduation_credits' => 120,
            'is_active' => true,
        ]);
    }

    public function test_curriculum_version_name_must_be_unique_per_programme(): void
    {
        Curriculum::factory()->create([
            'programme_id' => $this->programme->id,
            'version_name' => '2024 Structure',
        ]);

        // Same programme, duplicate version name
        $response = $this->post(route('academic.curriculums.store'), [
            'programme_id' => $this->programme->id,
            'version_name' => '2024 Structure',
            'start_academic_year' => 2024,
            'min_graduation_credits' => 110,
        ]);

        $response->assertSessionHasErrors(['version_name']);

        // Different programme, same version name is allowed
        $otherProg = Programme::factory()->create();
        $response2 = $this->post(route('academic.curriculums.store'), [
            'programme_id' => $otherProg->id,
            'version_name' => '2024 Structure',
            'start_academic_year' => 2024,
            'min_graduation_credits' => 110,
            'is_active' => '1',
        ]);

        $response2->assertSessionHasNoErrors();
    }

    public function test_can_show_curriculum_matrix_dashboard(): void
    {
        $curriculum = Curriculum::factory()->create([
            'programme_id' => $this->programme->id,
            'version_name' => '2024-2028 Structure',
            'min_graduation_credits' => 120,
        ]);

        CurriculumCourse::factory()->create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course1->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        $response = $this->get(route('academic.curriculums.show', $curriculum));

        $response->assertStatus(200);
        $response->assertSee('Curriculum Progression Matrix');
        $response->assertSee($curriculum->version_name);
        $response->assertSee($this->programme->name);
        $response->assertSee($this->course1->code);
        $response->assertSee($this->course1->name);
        $response->assertSee('Year 1');
        $response->assertSee('Semester 1');
    }

    public function test_can_allocate_course_unit_to_study_year_and_semester(): void
    {
        $curriculum = Curriculum::factory()->create([
            'programme_id' => $this->programme->id,
        ]);

        $payload = [
            'course_unit_id' => $this->course1->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ];

        $response = $this->post(route('academic.curriculums.courses.store', $curriculum), $payload);

        $response->assertRedirect(route('academic.curriculums.show', $curriculum));
        $this->assertDatabaseHas('curriculum_courses', [
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course1->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);
    }

    public function test_cannot_assign_duplicate_course_unit_to_same_curriculum(): void
    {
        $curriculum = Curriculum::factory()->create([
            'programme_id' => $this->programme->id,
        ]);

        CurriculumCourse::factory()->create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course1->id,
            'study_year' => 1,
            'semester' => 1,
        ]);

        // Attempt to assign course1 again (even in different semester/year)
        $response = $this->post(route('academic.curriculums.courses.store', $curriculum), [
            'course_unit_id' => $this->course1->id,
            'study_year' => 2,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        $response->assertSessionHasErrors(['course_unit_id']);
    }

    public function test_stage_query_scope_returns_courses_for_given_year_and_semester(): void
    {
        $curriculum = Curriculum::factory()->create(['programme_id' => $this->programme->id]);

        $cc1 = CurriculumCourse::factory()->create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course1->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        $cc2 = CurriculumCourse::factory()->create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course2->id,
            'study_year' => 1,
            'semester' => 2,
            'course_type' => 'Core',
        ]);

        $cc3 = CurriculumCourse::factory()->create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course3->id,
            'study_year' => 2,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        // Query for Year 1 Sem 1
        $y1s1 = CurriculumCourse::where('curriculum_id', $curriculum->id)->forStage(1, 1)->get();
        $this->assertCount(1, $y1s1);
        $this->assertEquals($cc1->id, $y1s1->first()->id);

        // Query for Year 2 Sem 1
        $y2s1 = CurriculumCourse::where('curriculum_id', $curriculum->id)->forStage(2, 1)->get();
        $this->assertCount(1, $y2s1);
        $this->assertEquals($cc3->id, $y2s1->first()->id);
    }

    public function test_total_credits_for_stage_and_curriculum_calculation(): void
    {
        $curriculum = Curriculum::factory()->create(['programme_id' => $this->programme->id]);

        // Course1 = 4.0 CU (Year 1, Sem 1)
        CurriculumCourse::factory()->create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course1->id,
            'study_year' => 1,
            'semester' => 1,
        ]);

        // Course2 = 4.0 CU (Year 1, Sem 2)
        CurriculumCourse::factory()->create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course2->id,
            'study_year' => 1,
            'semester' => 2,
        ]);

        // Course3 = 3.5 CU (Year 2, Sem 1)
        CurriculumCourse::factory()->create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course3->id,
            'study_year' => 2,
            'semester' => 1,
        ]);

        $this->assertEquals(4.0, $curriculum->totalCreditsForStage(1, 1));
        $this->assertEquals(4.0, $curriculum->totalCreditsForStage(1, 2));
        $this->assertEquals(3.5, $curriculum->totalCreditsForStage(2, 1));
        $this->assertEquals(0.0, $curriculum->totalCreditsForStage(2, 2));
        $this->assertEquals(11.5, $curriculum->totalMappedCredits());
    }

    public function test_can_remove_course_from_curriculum(): void
    {
        $curriculum = Curriculum::factory()->create(['programme_id' => $this->programme->id]);

        $mapping = CurriculumCourse::factory()->create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course1->id,
            'study_year' => 1,
            'semester' => 1,
        ]);

        $response = $this->delete(route('academic.curriculums.courses.destroy', [$curriculum, $mapping]));

        $response->assertRedirect(route('academic.curriculums.show', $curriculum));
        $this->assertDatabaseMissing('curriculum_courses', [
            'id' => $mapping->id,
        ]);
    }

    public function test_can_delete_curriculum(): void
    {
        $curriculum = Curriculum::factory()->create(['programme_id' => $this->programme->id]);

        CurriculumCourse::factory()->create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course1->id,
        ]);

        $response = $this->delete(route('academic.curriculums.destroy', $curriculum));

        $response->assertRedirect(route('academic.curriculums.index'));
        $this->assertDatabaseMissing('curriculums', [
            'id' => $curriculum->id,
        ]);
        $this->assertDatabaseMissing('curriculum_courses', [
            'curriculum_id' => $curriculum->id,
        ]);
    }

    public function test_programme_show_page_displays_active_curriculum_and_course_progression_matrix(): void
    {
        $curriculum = Curriculum::factory()->create([
            'programme_id' => $this->programme->id,
            'version_name' => '2026 Revised Engineering Curriculum',
            'is_active' => true,
        ]);

        CurriculumCourse::factory()->create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $this->course1->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        $response = $this->get(route('academic.programmes.show', $this->programme));

        $response->assertStatus(200);
        $response->assertSee('Curriculum & Course Map', false);
        $response->assertSee('2026 Revised Engineering Curriculum');
        $response->assertSee('Full Matrix');
        $response->assertSee('CSC1101');
        $response->assertDontSee('Phase 5 Deliverable');
    }

    public function test_programme_show_page_displays_empty_state_when_no_curriculum(): void
    {
        $response = $this->get(route('academic.programmes.show', $this->programme));

        $response->assertStatus(200);
        $response->assertSee('Curriculum & Course Map', false);
        $response->assertSee('No Curriculum Defined for BSSE', false);
        $response->assertSee('Create Curriculum Version');
        $response->assertDontSee('Phase 5 Deliverable');
    }
}
