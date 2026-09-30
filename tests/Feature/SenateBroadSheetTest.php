<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AwardClassification;
use App\Models\Campus;
use App\Models\CourseAssessmentSheet;
use App\Models\CourseRegistration;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use App\Models\Curriculum;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\GradingScaleTier;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\University;
use App\Models\User;
use App\Services\GradingEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SenateBroadSheetTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Department $department;

    protected Programme $programme;

    protected CourseUnit $courseUnit1;

    protected CourseUnit $courseUnit2;

    protected Student $studentAlice;

    protected Student $studentBob;

    protected function setUp(): void
    {
        parent::setUp();

        GradingScaleTier::seedDefaults();
        AwardClassification::seedDefaults();

        $university = University::factory()->create(['name' => 'Bishop Stuart University']);
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id]);

        $this->department = Department::factory()->create([
            'faculty_id' => $faculty->id,
            'name' => 'Department of Computer Science',
            'code' => 'CS',
        ]);

        $this->programme = Programme::factory()->create([
            'department_id' => $this->department->id,
            'name' => 'Bachelor of Information Technology',
            'code' => 'BIT',
            'award_type' => 'bachelors',
        ]);

        $curriculum = Curriculum::factory()->create([
            'programme_id' => $this->programme->id,
            'is_active' => true,
        ]);

        $this->academicYear = AcademicYear::factory()->create([
            'name' => '2026/2027',
            'is_current' => true,
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester 1',
            'semester_number' => 1,
            'is_active' => true,
        ]);

        $this->courseUnit1 = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'BIT1101',
            'name' => 'Computer Applications',
            'credit_units' => 4.0,
        ]);

        $this->courseUnit2 = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'BIT1102',
            'name' => 'Programming Basics',
            'credit_units' => 3.0,
        ]);

        $this->adminUser = User::factory()->create([
            'name' => 'Academic Registrar Admin',
            'email' => 'registrar@bsu.ac.ug',
        ]);

        // Student 1: Alice (Excellent)
        $this->studentAlice = Student::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Akello',
            'registration_number' => '26/BSU/BIT/001',
            'student_number' => '26001001',
            'programme_id' => $this->programme->id,
            'curriculum_id' => $curriculum->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        // Student 2: Bob (Average / Struggling)
        $this->studentBob = Student::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Bwambale',
            'registration_number' => '26/BSU/BIT/002',
            'student_number' => '26001002',
            'programme_id' => $this->programme->id,
            'curriculum_id' => $curriculum->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        // Create registrations
        $regAlice = CourseRegistration::factory()->create([
            'student_id' => $this->studentAlice->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
            'total_credits' => 7.0,
        ]);

        $itemAlice1 = CourseRegistrationItem::create([
            'course_registration_id' => $regAlice->id,
            'course_unit_id' => $this->courseUnit1->id,
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $itemAlice2 = CourseRegistrationItem::create([
            'course_registration_id' => $regAlice->id,
            'course_unit_id' => $this->courseUnit2->id,
            'credit_units' => 3.0,
            'status' => 'approved',
        ]);

        $regBob = CourseRegistration::factory()->create([
            'student_id' => $this->studentBob->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
            'total_credits' => 7.0,
        ]);

        $itemBob1 = CourseRegistrationItem::create([
            'course_registration_id' => $regBob->id,
            'course_unit_id' => $this->courseUnit1->id,
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $itemBob2 = CourseRegistrationItem::create([
            'course_registration_id' => $regBob->id,
            'course_unit_id' => $this->courseUnit2->id,
            'credit_units' => 3.0,
            'status' => 'approved',
        ]);

        // Assessment sheets
        $sheet1 = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseUnit1->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'pass_mark' => 50.0,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'status' => 'published',
        ]);

        $sheet2 = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseUnit2->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'pass_mark' => 50.0,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'status' => 'published',
        ]);

        // Marks for Alice
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $itemAlice1->id,
            'student_id' => $this->studentAlice->id,
            'ca_score' => 35.0,
            'exam_score' => 50.0,
            'final_score' => 85.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet2->id,
            'course_registration_item_id' => $itemAlice2->id,
            'student_id' => $this->studentAlice->id,
            'ca_score' => 32.0,
            'exam_score' => 45.0,
            'final_score' => 77.0,
            'grade_letter' => 'B+',
            'grade_point' => 4.5,
            'is_passed' => true,
        ]);

        // Marks for Bob
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $itemBob1->id,
            'student_id' => $this->studentBob->id,
            'ca_score' => 20.0,
            'exam_score' => 35.0,
            'final_score' => 55.0,
            'grade_letter' => 'C',
            'grade_point' => 3.0,
            'is_passed' => true,
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet2->id,
            'course_registration_item_id' => $itemBob2->id,
            'student_id' => $this->studentBob->id,
            'ca_score' => 15.0,
            'exam_score' => 25.0,
            'final_score' => 40.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
        ]);

        // Compute performance records
        $gradingEngine = app(GradingEngineService::class);
        $gradingEngine->calculateSemesterGpa($this->studentAlice, $this->semester);
        $gradingEngine->calculateSemesterGpa($this->studentBob, $this->semester);
    }

    public function test_broad_sheet_workspace_loads_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('academic.reports.broad-sheet', [
                'programme_id' => $this->programme->id,
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
                'study_year' => 1,
            ]));

        $response->assertOk();
        $response->assertViewIs('academic.reports.broad-sheet');
        $response->assertSee('Senate Master Broad-Sheet');
        $response->assertSee('Bachelor of Information Technology');
        $response->assertSee('BIT1101');
        $response->assertSee('BIT1102');
        $response->assertSee('Alice Akello');
        $response->assertSee('Bob Bwambale');
    }

    public function test_broad_sheet_renders_2d_matrix_with_accurate_student_grades(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('academic.reports.broad-sheet', [
                'programme_id' => $this->programme->id,
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
                'study_year' => 1,
            ]));

        $response->assertOk();

        // Alice scores: BIT1101 (85 A), BIT1102 (77 B+)
        $response->assertSee('85.0');
        $response->assertSee('77.0');

        // Bob scores: BIT1101 (55 C), BIT1102 (40 F)
        $response->assertSee('55.0');
        $response->assertSee('40.0');

        // Check standing indicators
        $response->assertSee('Normal Progress');
    }

    public function test_broad_sheet_calculates_course_unit_statistics_in_footer(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('academic.reports.broad-sheet', [
                'programme_id' => $this->programme->id,
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
                'study_year' => 1,
            ]));

        $response->assertOk();

        // Course 1 Average = (85 + 55) / 2 = 70.0%
        $response->assertSee('70.0%');

        // Course 2 Average = (77 + 40) / 2 = 58.5%
        $response->assertSee('58.5%');
    }

    public function test_broad_sheet_csv_export_streams_valid_tabular_data(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('academic.reports.broad-sheet.export', [
                'programme_id' => $this->programme->id,
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
                'study_year' => 1,
            ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();

        $this->assertStringContainsString('Student Reg No', $content);
        $this->assertStringContainsString('Student Name', $content);
        $this->assertStringContainsString('BIT1101', $content);
        $this->assertStringContainsString('BIT1102', $content);
        $this->assertStringContainsString('Alice Akello', $content);
        $this->assertStringContainsString('Bob Bwambale', $content);
    }
}
