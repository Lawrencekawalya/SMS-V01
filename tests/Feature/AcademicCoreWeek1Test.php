<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\CourseUnit;
use App\Models\Curriculum;
use App\Models\CurriculumCourse;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCoreWeek1Test extends TestCase
{
    use RefreshDatabase;

    public function test_complete_institutional_hierarchy_traversal(): void
    {
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

        $department = Department::factory()->create([
            'faculty_id' => $faculty->id,
            'name' => 'Department of Computer Science',
            'code' => 'CS',
        ]);

        $programme = Programme::factory()->create([
            'department_id' => $department->id,
            'name' => 'Bachelor of Science in Software Engineering',
            'code' => 'BSSE',
            'duration_years' => 4,
            'required_credits_to_graduate' => 120,
        ]);

        // Traverse hierarchy down
        $this->assertEquals('Apex University', $programme->department->faculty->campus->university->name);
        $this->assertEquals('MAIN', $programme->department->faculty->campus->code);
        $this->assertEquals('FST', $programme->department->faculty->code);
        $this->assertEquals('CS', $programme->department->code);
        $this->assertEquals('BSSE', $programme->code);
    }

    public function test_academic_calendar_single_active_session_enforcement(): void
    {
        $year1 = AcademicYear::factory()->create([
            'name' => '2025/2026',
            'is_current' => true,
        ]);

        $year2 = AcademicYear::factory()->create([
            'name' => '2026/2027',
            'is_current' => false,
        ]);

        $sem1 = Semester::factory()->create([
            'academic_year_id' => $year1->id,
            'name' => 'Semester 1',
            'is_active' => true,
        ]);

        $sem2 = Semester::factory()->create([
            'academic_year_id' => $year2->id,
            'name' => 'Semester 1',
            'is_active' => false,
        ]);

        // Toggling year 2 to current deactivates year 1
        $this->post(route('academic.academic-years.make-current', $year2));
        $this->assertFalse($year1->fresh()->is_current);
        $this->assertTrue($year2->fresh()->is_current);
        $this->assertEquals(1, AcademicYear::where('is_current', true)->count());

        // Activating sem2 deactivates sem1 and sets parent year2 as current
        $this->post(route('academic.semesters.activate', $sem2));
        $this->assertFalse($sem1->fresh()->is_active);
        $this->assertTrue($sem2->fresh()->is_active);
        $this->assertEquals(1, Semester::where('is_active', true)->count());
        $this->assertTrue($sem2->academicYear->fresh()->is_current);
    }

    public function test_curriculum_progression_stage_filtering_for_student_study_timeline(): void
    {
        $department = Department::factory()->create();
        $programme = Programme::factory()->create(['department_id' => $department->id]);
        $curriculum = Curriculum::factory()->create(['programme_id' => $programme->id]);

        $csc1101 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1101',
            'credit_units' => 4.0,
        ]);

        $mth1102 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'MTH1102',
            'credit_units' => 3.0,
        ]);

        $csc1201 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1201',
            'credit_units' => 4.0,
        ]);

        $csc2101 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC2101',
            'credit_units' => 4.0,
        ]);

        // Map courses to curriculum stages:
        // Year 1, Sem 1: CSC1101 (4.0 CU), MTH1102 (3.0 CU) -> 7.0 CU
        CurriculumCourse::create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $csc1101->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);
        CurriculumCourse::create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $mth1102->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        // Year 1, Sem 2: CSC1201 (4.0 CU)
        CurriculumCourse::create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $csc1201->id,
            'study_year' => 1,
            'semester' => 2,
            'course_type' => 'Core',
        ]);

        // Year 2, Sem 1: CSC2101 (4.0 CU)
        CurriculumCourse::create([
            'curriculum_id' => $curriculum->id,
            'course_unit_id' => $csc2101->id,
            'study_year' => 2,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        // When a student is in Year 1, Semester 1:
        $studentStageCourses = CurriculumCourse::where('curriculum_id', $curriculum->id)
            ->forStage(1, 1)
            ->with('courseUnit')
            ->get();

        $this->assertCount(2, $studentStageCourses);
        $stageCodes = $studentStageCourses->pluck('courseUnit.code')->all();
        $this->assertContains('CSC1101', $stageCodes);
        $this->assertContains('MTH1102', $stageCodes);
        $this->assertNotContains('CSC1201', $stageCodes);
        $this->assertNotContains('CSC2101', $stageCodes);

        // Verify credit calculations
        $this->assertEquals(7.0, $curriculum->totalCreditsForStage(1, 1));
        $this->assertEquals(4.0, $curriculum->totalCreditsForStage(1, 2));
        $this->assertEquals(4.0, $curriculum->totalCreditsForStage(2, 1));
        $this->assertEquals(15.0, $curriculum->totalMappedCredits());
    }

    public function test_home_dashboard_displays_all_module_metric_counts(): void
    {
        $university = University::factory()->create();
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id]);
        $department = Department::factory()->create(['faculty_id' => $faculty->id]);
        $programme = Programme::factory()->create(['department_id' => $department->id]);
        $course = CourseUnit::factory()->create(['department_id' => $department->id]);
        $curriculum = Curriculum::factory()->create(['programme_id' => $programme->id]);

        $year = AcademicYear::factory()->create(['is_current' => true]);
        Semester::factory()->create([
            'academic_year_id' => $year->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Campuses');
        $response->assertSee('Faculties');
        $response->assertSee('Departments');
        $response->assertSee('Programmes');
        $response->assertSee('Courses');
        $response->assertSee('Curriculums');
        $response->assertSee('Active Academic Session:');
    }
}
