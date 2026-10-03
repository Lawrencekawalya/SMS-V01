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
use App\Models\CurriculumCourse;
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
use App\Services\GraduationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraduationClearanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Programme $programme;

    protected Curriculum $curriculum;

    protected CourseUnit $courseCore1;

    protected CourseUnit $courseCore2;

    protected Student $studentCleared;

    protected Student $studentDeficient;

    protected function setUp(): void
    {
        parent::setUp();

        GradingScaleTier::seedDefaults();
        AwardClassification::seedDefaults();

        $university = University::factory()->create(['name' => 'Bishop Stuart University']);
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id]);
        $department = Department::factory()->create(['faculty_id' => $faculty->id]);

        $this->programme = Programme::factory()->create([
            'department_id' => $department->id,
            'name' => 'Diploma in Information Technology',
            'code' => 'DIT',
            'award_type' => 'diploma',
            'required_credits_to_graduate' => 7,
        ]);

        $this->curriculum = Curriculum::factory()->create([
            'programme_id' => $this->programme->id,
            'min_graduation_credits' => 7,
            'is_active' => true,
        ]);

        $this->academicYear = AcademicYear::factory()->create(['name' => '2026/2027', 'is_current' => true]);
        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester 1',
            'semester_number' => 1,
            'is_active' => true,
        ]);

        $this->courseCore1 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'DIT1101',
            'name' => 'Intro to IT',
            'credit_units' => 4.0,
        ]);

        $this->courseCore2 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'DIT1102',
            'name' => 'Structured Programming',
            'credit_units' => 3.0,
        ]);

        // Map core courses to curriculum
        CurriculumCourse::create([
            'curriculum_id' => $this->curriculum->id,
            'course_unit_id' => $this->courseCore1->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $this->curriculum->id,
            'course_unit_id' => $this->courseCore2->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        $this->adminUser = User::factory()->create(['name' => 'Academic Registrar Admin']);

        // Assessment sheets
        $sheet1 = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseCore1->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'pass_mark' => 50.0,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'status' => 'published',
        ]);

        $sheet2 = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseCore2->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'pass_mark' => 50.0,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'status' => 'published',
        ]);

        // Student 1: Cleared (Passed both core courses, 7 CU earned, CGPA 4.71 -> Class I Distinction)
        $this->studentCleared = Student::factory()->create([
            'first_name' => 'Kigozi',
            'last_name' => 'Timothy',
            'registration_number' => '26/BSU/DIT/001',
            'student_number' => '26001001',
            'programme_id' => $this->programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $regCleared = CourseRegistration::factory()->create([
            'student_id' => $this->studentCleared->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
            'total_credits' => 7.0,
        ]);

        $itemC1 = CourseRegistrationItem::create(['course_registration_id' => $regCleared->id, 'course_unit_id' => $this->courseCore1->id, 'credit_units' => 4.0, 'status' => 'approved']);
        $itemC2 = CourseRegistrationItem::create(['course_registration_id' => $regCleared->id, 'course_unit_id' => $this->courseCore2->id, 'credit_units' => 3.0, 'status' => 'approved']);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $itemC1->id,
            'student_id' => $this->studentCleared->id,
            'ca_score' => 38.0,
            'exam_score' => 50.0,
            'final_score' => 88.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet2->id,
            'course_registration_item_id' => $itemC2->id,
            'student_id' => $this->studentCleared->id,
            'ca_score' => 32.0,
            'exam_score' => 45.0,
            'final_score' => 77.0,
            'grade_letter' => 'B+',
            'grade_point' => 4.5,
            'is_passed' => true,
        ]);

        // Student 2: Deficient (Passed DIT1101, but failed DIT1102 with F -> Unresolved Retake & Credit Shortfall)
        $this->studentDeficient = Student::factory()->create([
            'first_name' => 'Mbabazi',
            'last_name' => 'Patience',
            'registration_number' => '26/BSU/DIT/002',
            'student_number' => '26001002',
            'programme_id' => $this->programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $regDeficient = CourseRegistration::factory()->create([
            'student_id' => $this->studentDeficient->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
            'total_credits' => 7.0,
        ]);

        $itemD1 = CourseRegistrationItem::create(['course_registration_id' => $regDeficient->id, 'course_unit_id' => $this->courseCore1->id, 'credit_units' => 4.0, 'status' => 'approved']);
        $itemD2 = CourseRegistrationItem::create(['course_registration_id' => $regDeficient->id, 'course_unit_id' => $this->courseCore2->id, 'credit_units' => 3.0, 'status' => 'approved']);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $itemD1->id,
            'student_id' => $this->studentDeficient->id,
            'ca_score' => 25.0,
            'exam_score' => 35.0,
            'final_score' => 60.0,
            'grade_letter' => 'C',
            'grade_point' => 3.0,
            'is_passed' => true,
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet2->id,
            'course_registration_item_id' => $itemD2->id,
            'student_id' => $this->studentDeficient->id,
            'ca_score' => 15.0,
            'exam_score' => 20.0,
            'final_score' => 35.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
        ]);

        $gradingEngine = app(GradingEngineService::class);
        $gradingEngine->calculateSemesterGpa($this->studentCleared, $this->semester);
        $gradingEngine->calculateSemesterGpa($this->studentDeficient, $this->semester);
    }

    public function test_graduation_service_clears_eligible_candidate(): void
    {
        $service = app(GraduationService::class);
        $audit = $service->auditClearance($this->studentCleared);

        $this->assertTrue($audit['is_cleared']);
        $this->assertEquals('Cleared for Graduation', $audit['status']);
        $this->assertEquals(7.0, $audit['earned_credits']);
        $this->assertEquals(0.0, $audit['credit_deficit']);
        $this->assertEmpty($audit['deficiencies']);
        $this->assertEquals('Class I (Distinction)', $audit['award_classification']);
    }

    public function test_graduation_service_flags_deficiencies_for_ineligible_candidate(): void
    {
        $service = app(GraduationService::class);
        $audit = $service->auditClearance($this->studentDeficient);

        $this->assertFalse($audit['is_cleared']);
        $this->assertEquals('Academic Deficiencies / Pending', $audit['status']);
        $this->assertEquals(4.0, $audit['earned_credits']);
        $this->assertEquals(3.0, $audit['credit_deficit']);
        $this->assertNotEmpty($audit['deficiencies']);

        // Check for specific deficiencies
        $deficienciesStr = implode(' ', $audit['deficiencies']);
        $this->assertStringContainsString('DIT1102', $deficienciesStr);
        $this->assertStringContainsString('Credit Shortfall', $deficienciesStr);
    }

    public function test_graduation_candidates_directory_loads_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('graduation.list'));

        $response->assertOk();
        $response->assertViewIs('academic.graduation.index');
        $response->assertSee('Graduation Candidates Directory');
        $response->assertSee('Timothy');
        $response->assertSee('Patience');
        $response->assertSee('Cleared');
        $response->assertSee('Deficiencies');
    }

    public function test_student_clearance_audit_page_renders_checklist_and_metrics(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('graduation.audit', $this->studentCleared->id));

        $response->assertOk();
        $response->assertViewIs('academic.graduation.audit');
        $response->assertSee('OFFICIAL GRADUATION CLEARANCE AUDIT SHEET');
        $response->assertSee('Timothy');
        $response->assertSee('DIT1101');
        $response->assertSee('DIT1102');
        $response->assertSee('Cleared for Graduation');
        $response->assertSee('Class I (Distinction)');
    }

    public function test_honors_roll_gazette_groups_cleared_graduands(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('graduation.honors-roll'));

        $response->assertOk();
        $response->assertViewIs('academic.graduation.honors-roll');
        $response->assertSee('OFFICIAL GRADUATION GAZETTE');
        $response->assertSee('Diploma in Information Technology');
        $response->assertSee('Class I (Distinction)');
        $response->assertSee('Timothy');
        // Patience should NOT appear in the honors roll because of deficiencies
        $response->assertDontSee('Patience');
    }
}
