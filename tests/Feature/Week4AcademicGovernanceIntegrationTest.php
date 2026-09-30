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

class Week4AcademicGovernanceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Department $department;

    protected Programme $programme;

    protected Curriculum $curriculum;

    protected CourseUnit $course1;

    protected CourseUnit $course2;

    protected Student $studentAchiever;

    protected Student $studentStruggling;

    protected function setUp(): void
    {
        parent::setUp();

        GradingScaleTier::seedDefaults();
        AwardClassification::seedDefaults();

        $university = University::factory()->create(['name' => 'Bishop Stuart University']);
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id, 'name' => 'Faculty of Applied Sciences']);
        $this->department = Department::factory()->create([
            'faculty_id' => $faculty->id,
            'name' => 'Department of Computing',
            'code' => 'DOC',
        ]);

        $this->programme = Programme::factory()->create([
            'department_id' => $this->department->id,
            'name' => 'Bachelor of Information Technology',
            'code' => 'BIT',
            'award_type' => 'bachelors',
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

        $this->course1 = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'BIT1101',
            'name' => 'Foundations of Computer Systems',
            'credit_units' => 4.0,
        ]);

        $this->course2 = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'BIT1102',
            'name' => 'Object Oriented Programming',
            'credit_units' => 3.0,
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $this->curriculum->id,
            'course_unit_id' => $this->course1->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        CurriculumCourse::create([
            'curriculum_id' => $this->curriculum->id,
            'course_unit_id' => $this->course2->id,
            'study_year' => 1,
            'semester' => 1,
            'course_type' => 'Core',
        ]);

        $this->adminUser = User::factory()->create([
            'name' => 'Dr. Senate Secretary',
            'email' => 'senate.secretary@bsu.ac.ug',
        ]);

        // Student 1: Achiever (Clears both courses with Distinction -> First Class Honours)
        $this->studentAchiever = Student::factory()->create([
            'first_name' => 'Grace',
            'last_name' => 'Atuhaire',
            'registration_number' => '26/BSU/BIT/101',
            'student_number' => '26001101',
            'programme_id' => $this->programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        // Student 2: Struggling (Fails OOP with 35 -> Unresolved Retake & Credit Deficit)
        $this->studentStruggling = Student::factory()->create([
            'first_name' => 'Denis',
            'last_name' => 'Okello',
            'registration_number' => '26/BSU/BIT/102',
            'student_number' => '26001102',
            'programme_id' => $this->programme->id,
            'curriculum_id' => $this->curriculum->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        $regAchiever = CourseRegistration::factory()->create([
            'student_id' => $this->studentAchiever->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
            'total_credits' => 7.0,
        ]);

        $regStruggling = CourseRegistration::factory()->create([
            'student_id' => $this->studentStruggling->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
            'total_credits' => 7.0,
        ]);

        $itemA1 = CourseRegistrationItem::create(['course_registration_id' => $regAchiever->id, 'course_unit_id' => $this->course1->id, 'credit_units' => 4.0, 'status' => 'approved']);
        $itemA2 = CourseRegistrationItem::create(['course_registration_id' => $regAchiever->id, 'course_unit_id' => $this->course2->id, 'credit_units' => 3.0, 'status' => 'approved']);

        $itemS1 = CourseRegistrationItem::create(['course_registration_id' => $regStruggling->id, 'course_unit_id' => $this->course1->id, 'credit_units' => 4.0, 'status' => 'approved']);
        $itemS2 = CourseRegistrationItem::create(['course_registration_id' => $regStruggling->id, 'course_unit_id' => $this->course2->id, 'credit_units' => 3.0, 'status' => 'approved']);

        $sheet1 = CourseAssessmentSheet::create([
            'course_unit_id' => $this->course1->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'pass_mark' => 50.0,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'status' => 'published',
        ]);

        $sheet2 = CourseAssessmentSheet::create([
            'course_unit_id' => $this->course2->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'pass_mark' => 50.0,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'status' => 'published',
        ]);

        // Grace Marks (85 A, 80 A -> CGPA 5.0)
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $itemA1->id,
            'student_id' => $this->studentAchiever->id,
            'ca_score' => 35.0,
            'exam_score' => 50.0,
            'final_score' => 85.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet2->id,
            'course_registration_item_id' => $itemA2->id,
            'student_id' => $this->studentAchiever->id,
            'ca_score' => 32.0,
            'exam_score' => 48.0,
            'final_score' => 80.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
        ]);

        // Denis Marks (60 C, 35 F -> CGPA 1.71, Retake BIT1102)
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $itemS1->id,
            'student_id' => $this->studentStruggling->id,
            'ca_score' => 25.0,
            'exam_score' => 35.0,
            'final_score' => 60.0,
            'grade_letter' => 'C',
            'grade_point' => 3.0,
            'is_passed' => true,
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet2->id,
            'course_registration_item_id' => $itemS2->id,
            'student_id' => $this->studentStruggling->id,
            'ca_score' => 15.0,
            'exam_score' => 20.0,
            'final_score' => 35.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
        ]);

        $gradingEngine = app(GradingEngineService::class);
        $gradingEngine->calculateSemesterGpa($this->studentAchiever, $this->semester);
        $gradingEngine->calculateSemesterGpa($this->studentStruggling, $this->semester);
    }

    public function test_complete_week4_academic_governance_lifecycle(): void
    {
        // 1. Senate Master Broad-Sheet Workspace
        $broadSheetResponse = $this->actingAs($this->adminUser)
            ->get(route('academic.reports.broad-sheet', [
                'programme_id' => $this->programme->id,
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
                'study_year' => 1,
            ]));

        $broadSheetResponse->assertOk();
        $broadSheetResponse->assertSee('MASTER SENATE BROAD-SHEET');
        $broadSheetResponse->assertSee('Grace Atuhaire');
        $broadSheetResponse->assertSee('Denis Okello');
        $broadSheetResponse->assertSee('85.0');
        $broadSheetResponse->assertSee('35.0');
        $broadSheetResponse->assertSee('BIT1101');
        $broadSheetResponse->assertSee('BIT1102');

        // 2. Senate Broad-Sheet CSV Export
        $exportResponse = $this->actingAs($this->adminUser)
            ->get(route('academic.reports.broad-sheet.export', [
                'programme_id' => $this->programme->id,
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
                'study_year' => 1,
            ]));

        $exportResponse->assertOk();
        $exportResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $exportResponse->streamedContent();
        $this->assertStringContainsString('Grace Atuhaire', $csv);
        $this->assertStringContainsString('Denis Okello', $csv);
        $this->assertStringContainsString('Retake: BIT1102', $csv);

        // 3. Departmental & Faculty Academic Performance Analytics
        $analyticsResponse = $this->actingAs($this->adminUser)
            ->get(route('academic.reports.analytics', [
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
            ]));

        $analyticsResponse->assertOk();
        $analyticsResponse->assertSee('FACULTY &amp; DEPARTMENTAL PERFORMANCE ANALYTICS REPORT', false);
        $analyticsResponse->assertSee('Department of Computing');
        // Course 2 has 1 pass out of 2 = 50.0% failure rate (>30% threshold anomaly)
        $analyticsResponse->assertSee('BIT1102');
        $analyticsResponse->assertSee('High Failure Rate');

        // 4. Graduation Candidates Directory & Automated Audit
        $graduationDirResponse = $this->actingAs($this->adminUser)
            ->get(route('academic.graduation.index'));

        $graduationDirResponse->assertOk();
        $graduationDirResponse->assertSee('Graduation Candidates Directory');
        $graduationDirResponse->assertSee('Grace Atuhaire');
        $graduationDirResponse->assertSee('Denis Okello');
        $graduationDirResponse->assertSee('Cleared');
        $graduationDirResponse->assertSee('Deficiencies');

        // 5. Individual Student Clearance Audit
        $auditService = app(GraduationService::class);
        $achieverAudit = $auditService->auditClearance($this->studentAchiever);
        $strugglingAudit = $auditService->auditClearance($this->studentStruggling);

        $this->assertTrue($achieverAudit['is_cleared']);
        $this->assertEquals('First Class Honours', $achieverAudit['award_classification']);
        $this->assertFalse($strugglingAudit['is_cleared']);
        $this->assertNotEmpty($strugglingAudit['deficiencies']);

        $achieverAuditView = $this->actingAs($this->adminUser)
            ->get(route('academic.graduation.audit', $this->studentAchiever->id));
        $achieverAuditView->assertOk();
        $achieverAuditView->assertSee('Cleared for Graduation');
        $achieverAuditView->assertSee('First Class Honours');

        $strugglingAuditView = $this->actingAs($this->adminUser)
            ->get(route('academic.graduation.audit', $this->studentStruggling->id));
        $strugglingAuditView->assertOk();
        $strugglingAuditView->assertSee('Academic Deficiencies / Pending');
        $strugglingAuditView->assertSee('Unresolved Retake');

        // 6. Official Graduation Gazette & Honors Roll Booklet
        $gazetteResponse = $this->actingAs($this->adminUser)
            ->get(route('academic.graduation.honors-roll'));

        $gazetteResponse->assertOk();
        $gazetteResponse->assertSee('OFFICIAL GRADUATION GAZETTE &bull; HONORS ROLL', false);
        $gazetteResponse->assertSee('Bachelor of Information Technology');
        $gazetteResponse->assertSee('First Class Honours');
        $gazetteResponse->assertSee('Grace Atuhaire');
        // Denis has deficiencies and must not appear in the conferment gazette
        $gazetteResponse->assertDontSee('Denis Okello');
    }
}
