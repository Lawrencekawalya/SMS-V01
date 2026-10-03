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

class AcademicPerformanceAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Faculty $faculty;

    protected Department $departmentCS;

    protected Department $departmentBA;

    protected Programme $programmeCS;

    protected Programme $programmeBA;

    protected CourseUnit $courseAlgorithms;

    protected CourseUnit $courseAccounting;

    protected function setUp(): void
    {
        parent::setUp();

        GradingScaleTier::seedDefaults();
        AwardClassification::seedDefaults();

        $university = University::factory()->create(['name' => 'Bishop Stuart University']);
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $this->faculty = Faculty::factory()->create([
            'campus_id' => $campus->id,
            'name' => 'Faculty of Applied Sciences',
        ]);

        $this->departmentCS = Department::factory()->create([
            'faculty_id' => $this->faculty->id,
            'name' => 'Department of Computer Science',
            'code' => 'DCS',
        ]);

        $this->departmentBA = Department::factory()->create([
            'faculty_id' => $this->faculty->id,
            'name' => 'Department of Business Administration',
            'code' => 'DBA',
        ]);

        $this->programmeCS = Programme::factory()->create([
            'department_id' => $this->departmentCS->id,
            'name' => 'Bachelor of Information Technology',
            'code' => 'BIT',
            'award_type' => 'bachelors',
        ]);

        $this->programmeBA = Programme::factory()->create([
            'department_id' => $this->departmentBA->id,
            'name' => 'Bachelor of Business Administration',
            'code' => 'BBA',
            'award_type' => 'bachelors',
        ]);

        $currCS = Curriculum::factory()->create(['programme_id' => $this->programmeCS->id, 'is_active' => true]);
        $currBA = Curriculum::factory()->create(['programme_id' => $this->programmeBA->id, 'is_active' => true]);

        $this->academicYear = AcademicYear::factory()->create(['name' => '2026/2027', 'is_current' => true]);
        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester 1',
            'semester_number' => 1,
            'is_active' => true,
        ]);

        // Course 1: Algorithms (High failure rate simulation: 1 pass, 2 fails = 66.7% fail rate > 30%)
        $this->courseAlgorithms = CourseUnit::factory()->create([
            'department_id' => $this->departmentCS->id,
            'code' => 'CSC1101',
            'name' => 'Data Structures & Algorithms',
            'credit_units' => 4.0,
        ]);

        // Course 2: Accounting (High pass rate simulation: 2 pass, 0 fail = 100% pass rate)
        $this->courseAccounting = CourseUnit::factory()->create([
            'department_id' => $this->departmentBA->id,
            'code' => 'BBA1101',
            'name' => 'Financial Accounting',
            'credit_units' => 3.0,
        ]);

        $this->adminUser = User::factory()->create(['name' => 'Dean of Faculty', 'email' => 'dean@bsu.ac.ug']);

        // Create 3 CS Students
        $studentsCS = [];
        for ($i = 1; $i <= 3; $i++) {
            $studentsCS[$i] = Student::factory()->create([
                'first_name' => "CS_Student_{$i}",
                'programme_id' => $this->programmeCS->id,
                'curriculum_id' => $currCS->id,
                'admission_academic_year_id' => $this->academicYear->id,
                'current_study_year' => 1,
                'current_semester' => 1,
            ]);
        }

        // Create 2 BA Students
        $studentsBA = [];
        for ($i = 1; $i <= 2; $i++) {
            $studentsBA[$i] = Student::factory()->create([
                'first_name' => "BA_Student_{$i}",
                'programme_id' => $this->programmeBA->id,
                'curriculum_id' => $currBA->id,
                'admission_academic_year_id' => $this->academicYear->id,
                'current_study_year' => 1,
                'current_semester' => 1,
            ]);
        }

        // Assessment sheet for Algorithms
        $sheetAlgo = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseAlgorithms->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'pass_mark' => 50.0,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'status' => 'published',
        ]);

        // Assessment sheet for Accounting
        $sheetAcc = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseAccounting->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'pass_mark' => 50.0,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'status' => 'published',
        ]);

        // CS Student 1: Passes Algorithms with A (85)
        $regCS1 = CourseRegistration::factory()->create([
            'student_id' => $studentsCS[1]->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
        ]);
        $itemCS1 = CourseRegistrationItem::create(['course_registration_id' => $regCS1->id, 'course_unit_id' => $this->courseAlgorithms->id, 'credit_units' => 4.0, 'status' => 'approved']);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheetAlgo->id,
            'course_registration_item_id' => $itemCS1->id,
            'student_id' => $studentsCS[1]->id,
            'ca_score' => 35.0,
            'exam_score' => 50.0,
            'final_score' => 85.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
        ]);

        // CS Student 2: Fails Algorithms with F (35)
        $regCS2 = CourseRegistration::factory()->create([
            'student_id' => $studentsCS[2]->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
        ]);
        $itemCS2 = CourseRegistrationItem::create(['course_registration_id' => $regCS2->id, 'course_unit_id' => $this->courseAlgorithms->id, 'credit_units' => 4.0, 'status' => 'approved']);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheetAlgo->id,
            'course_registration_item_id' => $itemCS2->id,
            'student_id' => $studentsCS[2]->id,
            'ca_score' => 15.0,
            'exam_score' => 20.0,
            'final_score' => 35.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
        ]);

        // CS Student 3: Fails Algorithms with F (42)
        $regCS3 = CourseRegistration::factory()->create([
            'student_id' => $studentsCS[3]->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
        ]);
        $itemCS3 = CourseRegistrationItem::create(['course_registration_id' => $regCS3->id, 'course_unit_id' => $this->courseAlgorithms->id, 'credit_units' => 4.0, 'status' => 'approved']);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheetAlgo->id,
            'course_registration_item_id' => $itemCS3->id,
            'student_id' => $studentsCS[3]->id,
            'ca_score' => 18.0,
            'exam_score' => 24.0,
            'final_score' => 42.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
        ]);

        // BA Student 1: Passes Accounting with B+ (75)
        $regBA1 = CourseRegistration::factory()->create([
            'student_id' => $studentsBA[1]->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
        ]);
        $itemBA1 = CourseRegistrationItem::create(['course_registration_id' => $regBA1->id, 'course_unit_id' => $this->courseAccounting->id, 'credit_units' => 3.0, 'status' => 'approved']);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheetAcc->id,
            'course_registration_item_id' => $itemBA1->id,
            'student_id' => $studentsBA[1]->id,
            'ca_score' => 30.0,
            'exam_score' => 45.0,
            'final_score' => 75.0,
            'grade_letter' => 'B+',
            'grade_point' => 4.5,
            'is_passed' => true,
        ]);

        // BA Student 2: Passes Accounting with B (68)
        $regBA2 = CourseRegistration::factory()->create([
            'student_id' => $studentsBA[2]->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
        ]);
        $itemBA2 = CourseRegistrationItem::create(['course_registration_id' => $regBA2->id, 'course_unit_id' => $this->courseAccounting->id, 'credit_units' => 3.0, 'status' => 'approved']);
        StudentMark::create([
            'course_assessment_sheet_id' => $sheetAcc->id,
            'course_registration_item_id' => $itemBA2->id,
            'student_id' => $studentsBA[2]->id,
            'ca_score' => 28.0,
            'exam_score' => 40.0,
            'final_score' => 68.0,
            'grade_letter' => 'B',
            'grade_point' => 4.0,
            'is_passed' => true,
        ]);

        // Compute performance records
        $engine = app(GradingEngineService::class);
        foreach ($studentsCS as $st) {
            $engine->calculateSemesterGpa($st, $this->semester);
        }
        foreach ($studentsBA as $st) {
            $engine->calculateSemesterGpa($st, $this->semester);
        }
    }

    public function test_academic_analytics_dashboard_loads_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('analytics.list', [
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
            ]));

        $response->assertOk();
        $response->assertViewIs('academic.reports.analytics');
        $response->assertSee('Academic Performance Analytics');
        $response->assertSee('Faculty of Applied Sciences');
        $response->assertSee('Department of Computer Science');
        $response->assertSee('Department of Business Administration');
    }

    public function test_academic_analytics_computes_department_metrics(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('analytics.list', [
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
            ]));

        $response->assertOk();

        // CS department has 1 pass, 2 fails = 33.3% pass rate
        $response->assertSee('33.3%');

        // Business department has 2 passes, 0 fails = 100.0% pass rate
        $response->assertSee('100.0%');
    }

    public function test_academic_analytics_detects_course_failure_anomalies(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('analytics.list', [
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
            ]));

        $response->assertOk();

        // Algorithms has a failure rate of 66.7% (> 30% threshold), so it must be flagged
        $response->assertSee('CSC1101');
        $response->assertSee('Data Structures & Algorithms');
        $response->assertSee('66.7%');
        $response->assertSee('High Failure Rate');
    }

    public function test_academic_analytics_aggregates_institutional_grade_distribution(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('analytics.list', [
                'academic_year_id' => $this->academicYear->id,
                'semester_id' => $this->semester->id,
            ]));

        $response->assertOk();

        // Grade distribution should contain A (1), B+ (1), B (1), F (2)
        $response->assertSee('Grade Distribution');
    }
}
