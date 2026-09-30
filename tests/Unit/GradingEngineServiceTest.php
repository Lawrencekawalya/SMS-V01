<?php

namespace Tests\Unit;

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
use App\Models\StudentSemesterPerformance;
use App\Models\University;
use App\Models\User;
use App\Services\GradingEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class GradingEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GradingEngineService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new GradingEngineService;
        GradingScaleTier::seedDefaults();
        AwardClassification::seedDefaults();
    }

    public function test_compute_mark_with_high_distinction_score(): void
    {
        $result = $this->service->computeMark(36.0, 52.0); // 88.0%

        $this->assertEquals(36.0, $result['ca_score']);
        $this->assertEquals(52.0, $result['exam_score']);
        $this->assertEquals(88.0, $result['final_score']);
        $this->assertEquals('A', $result['grade_letter']);
        $this->assertEquals(5.0, $result['grade_point']);
        $this->assertTrue($result['is_passed']);
        $this->assertFalse($result['is_retake']);
    }

    public function test_nche_scale_boundary_conditions_and_edge_scores(): void
    {
        $testCases = [
            ['score' => 100.0, 'letter' => 'A',  'gp' => 5.0, 'passed' => true],
            ['score' => 80.0,  'letter' => 'A',  'gp' => 5.0, 'passed' => true],
            ['score' => 79.99, 'letter' => 'B+', 'gp' => 4.5, 'passed' => true],
            ['score' => 75.0,  'letter' => 'B+', 'gp' => 4.5, 'passed' => true],
            ['score' => 74.99, 'letter' => 'B',  'gp' => 4.0, 'passed' => true],
            ['score' => 70.0,  'letter' => 'B',  'gp' => 4.0, 'passed' => true],
            ['score' => 69.99, 'letter' => 'C+', 'gp' => 3.5, 'passed' => true],
            ['score' => 65.0,  'letter' => 'C+', 'gp' => 3.5, 'passed' => true],
            ['score' => 64.99, 'letter' => 'C',  'gp' => 3.0, 'passed' => true],
            ['score' => 60.0,  'letter' => 'C',  'gp' => 3.0, 'passed' => true],
            ['score' => 59.99, 'letter' => 'D+', 'gp' => 2.5, 'passed' => true],
            ['score' => 55.0,  'letter' => 'D+', 'gp' => 2.5, 'passed' => true],
            ['score' => 54.99, 'letter' => 'D',  'gp' => 2.0, 'passed' => true],
            ['score' => 50.0,  'letter' => 'D',  'gp' => 2.0, 'passed' => true],
            ['score' => 49.99, 'letter' => 'F',  'gp' => 0.0, 'passed' => false],
            ['score' => 25.0,  'letter' => 'F',  'gp' => 0.0, 'passed' => false],
            ['score' => 0.0,   'letter' => 'F',  'gp' => 0.0, 'passed' => false],
        ];

        foreach ($testCases as $case) {
            $grade = $this->service->resolveGrade($case['score']);
            $this->assertEquals(
                $case['letter'],
                $grade['grade_letter'],
                "Failed resolving grade letter for score {$case['score']}"
            );
            $this->assertEquals(
                $case['gp'],
                $grade['grade_point'],
                "Failed resolving grade point for score {$case['score']}"
            );

            $markResult = $this->service->computeMark(0.0, $case['score']);
            $this->assertEquals($case['passed'], $markResult['is_passed']);
            $this->assertEquals(! $case['passed'], $markResult['is_retake']);
        }
    }

    public function test_compute_mark_respects_custom_sheet_pass_mark_threshold(): void
    {
        $sheet = CourseAssessmentSheet::factory()->create([
            'pass_mark' => 60.0, // Stricter 60% pass mark for special clinical/law course
        ]);

        // Student scores 55% (Standard D+ with 2.5 GP, but fails this course's 60% threshold)
        $result = $this->service->computeMark(25.0, 30.0, $sheet);

        $this->assertEquals(55.0, $result['final_score']);
        $this->assertEquals('D+', $result['grade_letter']);
        $this->assertEquals(2.5, $result['grade_point']);
        $this->assertFalse($result['is_passed']);
        $this->assertTrue($result['is_retake']);
    }

    public function test_calculates_semester_gpa_with_multiple_graded_courses(): void
    {
        $university = University::factory()->create();
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id]);
        $dept = Department::factory()->create(['faculty_id' => $faculty->id]);
        $programme = Programme::factory()->create(['department_id' => $dept->id, 'award_type' => 'Bachelors']);
        $curriculum = Curriculum::factory()->create(['programme_id' => $programme->id]);
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['academic_year_id' => $year->id]);

        $student = Student::factory()->create([
            'campus_id' => $campus->id,
            'programme_id' => $programme->id,
            'curriculum_id' => $curriculum->id,
        ]);

        $reg = CourseRegistration::factory()->create([
            'student_id' => $student->id,
            'semester_id' => $semester->id,
            'academic_year_id' => $year->id,
        ]);

        // Course 1: 4.0 CU, Grade A (5.0 GP) -> 20.0 weighted points
        $course1 = CourseUnit::factory()->create(['department_id' => $dept->id, 'credit_units' => 4.0]);
        $item1 = CourseRegistrationItem::factory()->create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $course1->id,
            'credit_units' => 4.0,
        ]);
        $sheet1 = CourseAssessmentSheet::factory()->create(['course_unit_id' => $course1->id, 'semester_id' => $semester->id]);
        StudentMark::factory()->create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $student->id,
            'ca_score' => 35.0,
            'exam_score' => 48.0,
            'final_score' => 83.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
        ]);

        // Course 2: 4.0 CU, Grade B+ (4.5 GP) -> 18.0 weighted points
        $course2 = CourseUnit::factory()->create(['department_id' => $dept->id, 'credit_units' => 4.0]);
        $item2 = CourseRegistrationItem::factory()->create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $course2->id,
            'credit_units' => 4.0,
        ]);
        $sheet2 = CourseAssessmentSheet::factory()->create(['course_unit_id' => $course2->id, 'semester_id' => $semester->id]);
        StudentMark::factory()->create([
            'course_assessment_sheet_id' => $sheet2->id,
            'course_registration_item_id' => $item2->id,
            'student_id' => $student->id,
            'ca_score' => 32.0,
            'exam_score' => 44.0,
            'final_score' => 76.0,
            'grade_letter' => 'B+',
            'grade_point' => 4.5,
            'is_passed' => true,
        ]);

        // Course 3: 3.0 CU, Grade B (4.0 GP) -> 12.0 weighted points
        $course3 = CourseUnit::factory()->create(['department_id' => $dept->id, 'credit_units' => 3.0]);
        $item3 = CourseRegistrationItem::factory()->create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $course3->id,
            'credit_units' => 3.0,
        ]);
        $sheet3 = CourseAssessmentSheet::factory()->create(['course_unit_id' => $course3->id, 'semester_id' => $semester->id]);
        StudentMark::factory()->create([
            'course_assessment_sheet_id' => $sheet3->id,
            'course_registration_item_id' => $item3->id,
            'student_id' => $student->id,
            'ca_score' => 30.0,
            'exam_score' => 41.0,
            'final_score' => 71.0,
            'grade_letter' => 'B',
            'grade_point' => 4.0,
            'is_passed' => true,
        ]);

        // Expected: Total registered = 11.0 CU, Earned = 11.0 CU
        // Weighted Points = 20.0 + 18.0 + 12.0 = 50.0
        // GPA = 50.0 / 11.0 = 4.55
        $perf = $this->service->calculateSemesterGpa($student, $semester);

        $this->assertEquals(11.0, $perf->credit_units_registered);
        $this->assertEquals(11.0, $perf->credit_units_earned);
        $this->assertEquals(50.0, $perf->weighted_grade_points);
        $this->assertEquals(4.55, $perf->gpa);
        $this->assertEquals(4.55, $perf->cgpa);
        $this->assertEquals('Normal Progress', $perf->academic_standing);
        $this->assertEquals(4.55, $student->fresh()->cumulative_gpa);
    }

    public function test_failing_grade_affects_gpa_and_does_not_count_toward_earned_credit_units(): void
    {
        $university = University::factory()->create();
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id]);
        $dept = Department::factory()->create(['faculty_id' => $faculty->id]);
        $programme = Programme::factory()->create(['department_id' => $dept->id]);
        $curriculum = Curriculum::factory()->create(['programme_id' => $programme->id]);
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['academic_year_id' => $year->id]);

        $student = Student::factory()->create([
            'campus_id' => $campus->id,
            'programme_id' => $programme->id,
            'curriculum_id' => $curriculum->id,
        ]);

        $reg = CourseRegistration::factory()->create([
            'student_id' => $student->id,
            'semester_id' => $semester->id,
        ]);

        // Course 1: 4.0 CU, Grade B (4.0 GP) -> 16.0 points
        $c1 = CourseUnit::factory()->create(['department_id' => $dept->id, 'credit_units' => 4.0]);
        $i1 = CourseRegistrationItem::factory()->create(['course_registration_id' => $reg->id, 'course_unit_id' => $c1->id, 'credit_units' => 4.0]);
        $s1 = CourseAssessmentSheet::factory()->create(['course_unit_id' => $c1->id, 'semester_id' => $semester->id]);
        StudentMark::factory()->create([
            'course_assessment_sheet_id' => $s1->id,
            'course_registration_item_id' => $i1->id,
            'student_id' => $student->id,
            'final_score' => 70.0,
            'grade_point' => 4.0,
            'is_passed' => true,
        ]);

        // Course 2: 4.0 CU, Grade F (0.0 GP, Failed) -> 0.0 points
        $c2 = CourseUnit::factory()->create(['department_id' => $dept->id, 'credit_units' => 4.0]);
        $i2 = CourseRegistrationItem::factory()->create(['course_registration_id' => $reg->id, 'course_unit_id' => $c2->id, 'credit_units' => 4.0]);
        $s2 = CourseAssessmentSheet::factory()->create(['course_unit_id' => $c2->id, 'semester_id' => $semester->id]);
        StudentMark::factory()->create([
            'course_assessment_sheet_id' => $s2->id,
            'course_registration_item_id' => $i2->id,
            'student_id' => $student->id,
            'final_score' => 42.0,
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);

        // Registered: 8.0 CU. Earned: 4.0 CU.
        // Points: 16.0. GPA = 16.0 / 8.0 = 2.00
        $perf = $this->service->calculateSemesterGpa($student, $semester);

        $this->assertEquals(8.0, $perf->credit_units_registered);
        $this->assertEquals(4.0, $perf->credit_units_earned);
        $this->assertEquals(16.0, $perf->weighted_grade_points);
        $this->assertEquals(2.00, $perf->gpa);
    }

    public function test_cumulative_cgpa_calculation_across_multiple_semesters(): void
    {
        $student = Student::factory()->create(['cumulative_gpa' => 0.0]);
        $year = AcademicYear::factory()->create();
        $sem1 = Semester::factory()->create(['academic_year_id' => $year->id, 'semester_number' => 1]);
        $sem2 = Semester::factory()->create(['academic_year_id' => $year->id, 'semester_number' => 2]);

        // Semester 1: 15.0 CU, 60.0 points (GPA 4.00)
        StudentSemesterPerformance::create([
            'student_id' => $student->id,
            'semester_id' => $sem1->id,
            'academic_year_id' => $year->id,
            'credit_units_registered' => 15.0,
            'credit_units_earned' => 15.0,
            'weighted_grade_points' => 60.0,
            'gpa' => 4.00,
            'cumulative_credit_units_registered' => 15.0,
            'cumulative_credit_units_earned' => 15.0,
            'cumulative_weighted_grade_points' => 60.0,
            'cgpa' => 4.00,
            'academic_standing' => 'Normal Progress',
        ]);

        // Semester 2: Student registers 15.0 CU, earns 30.0 points (GPA 2.00)
        $reg2 = CourseRegistration::factory()->create(['student_id' => $student->id, 'semester_id' => $sem2->id]);
        $course = CourseUnit::factory()->create(['credit_units' => 15.0]);
        $item = CourseRegistrationItem::factory()->create(['course_registration_id' => $reg2->id, 'course_unit_id' => $course->id, 'credit_units' => 15.0]);
        $sheet = CourseAssessmentSheet::factory()->create(['course_unit_id' => $course->id, 'semester_id' => $sem2->id]);
        StudentMark::factory()->create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $item->id,
            'student_id' => $student->id,
            'final_score' => 50.0,
            'grade_point' => 2.0,
            'is_passed' => true,
        ]);

        $perf2 = $this->service->calculateSemesterGpa($student, $sem2);

        // Term 2 GPA: 30.0 / 15.0 = 2.00
        $this->assertEquals(2.00, $perf2->gpa);

        // Cumulative: 15 (Sem1) + 15 (Sem2) = 30.0 CU
        // Points: 60.0 + 30.0 = 90.0
        // CGPA: 90.0 / 30.0 = 3.00
        $this->assertEquals(30.0, $perf2->cumulative_credit_units_registered);
        $this->assertEquals(90.0, $perf2->cumulative_weighted_grade_points);
        $this->assertEquals(3.00, $perf2->cgpa);
        $this->assertEquals('Normal Progress', $perf2->academic_standing);
    }

    public function test_low_cgpa_triggers_academic_probation(): void
    {
        $student = Student::factory()->create();
        $year = AcademicYear::factory()->create();
        $semester = Semester::factory()->create(['academic_year_id' => $year->id]);

        $reg = CourseRegistration::factory()->create(['student_id' => $student->id, 'semester_id' => $semester->id]);
        $course = CourseUnit::factory()->create(['credit_units' => 15.0]);
        $item = CourseRegistrationItem::factory()->create(['course_registration_id' => $reg->id, 'course_unit_id' => $course->id, 'credit_units' => 15.0]);
        $sheet = CourseAssessmentSheet::factory()->create(['course_unit_id' => $course->id, 'semester_id' => $semester->id]);

        // Student scores D+ with 2.5 GP in 5 CU, and F in 10 CU -> 12.5 points / 15 CU = GPA 0.83 < 2.00
        StudentMark::factory()->create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $item->id,
            'student_id' => $student->id,
            'final_score' => 35.0,
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);

        $perf = $this->service->calculateSemesterGpa($student, $semester);

        $this->assertEquals(0.00, $perf->gpa);
        $this->assertEquals(0.00, $perf->cgpa);
        $this->assertEquals('Probation', $perf->academic_standing);
    }

    public function test_award_classification_resolution_for_bachelors_degree(): void
    {
        $programme = Programme::factory()->create(['award_type' => 'Bachelors']);
        $student = Student::factory()->create(['programme_id' => $programme->id, 'cumulative_gpa' => 4.60]);

        $firstClass = $this->service->resolveAwardClassification($student, 4.60);
        $this->assertNotNull($firstClass);
        $this->assertEquals('First Class Honours', $firstClass->name);

        $secondUpper = $this->service->resolveAwardClassification($student, 3.80);
        $this->assertNotNull($secondUpper);
        $this->assertEquals('Second Class Honours (Upper Division)', $secondUpper->name);

        $secondLower = $this->service->resolveAwardClassification($student, 3.00);
        $this->assertNotNull($secondLower);
        $this->assertEquals('Second Class Honours (Lower Division)', $secondLower->name);

        $pass = $this->service->resolveAwardClassification($student, 2.30);
        $this->assertNotNull($pass);
        $this->assertEquals('Pass Degree', $pass->name);
    }

    public function test_award_classification_resolution_for_diploma(): void
    {
        $programme = Programme::factory()->create(['award_type' => 'Diploma']);
        $student = Student::factory()->create(['programme_id' => $programme->id, 'cumulative_gpa' => 4.50]);

        $distinction = $this->service->resolveAwardClassification($student, 4.50);
        $this->assertNotNull($distinction);
        $this->assertEquals('Class I (Distinction)', $distinction->name);

        $credit = $this->service->resolveAwardClassification($student, 3.80);
        $this->assertNotNull($credit);
        $this->assertEquals('Class II (Credit)', $credit->name);

        $pass = $this->service->resolveAwardClassification($student, 2.50);
        $this->assertNotNull($pass);
        $this->assertEquals('Class III (Pass)', $pass->name);
    }

    public function test_award_classification_resolution_for_certificate(): void
    {
        $programme = Programme::factory()->create(['award_type' => 'Certificate']);
        $student = Student::factory()->create(['programme_id' => $programme->id, 'cumulative_gpa' => 4.50]);

        $distinction = $this->service->resolveAwardClassification($student, 4.50);
        $this->assertNotNull($distinction);
        $this->assertEquals('Distinction', $distinction->name);

        $credit = $this->service->resolveAwardClassification($student, 3.80);
        $this->assertNotNull($credit);
        $this->assertEquals('Credit', $credit->name);

        $pass = $this->service->resolveAwardClassification($student, 2.50);
        $this->assertNotNull($pass);
        $this->assertEquals('Pass', $pass->name);
    }

    public function test_record_grade_audit_creates_valid_audit_log(): void
    {
        $user = User::factory()->create(['name' => 'Dr. Jane Mugabi']);
        $mark = StudentMark::factory()->create();

        $log = $this->service->recordGradeAudit(
            mark: $mark,
            user: $user,
            type: 'exam',
            oldScore: 42.0,
            newScore: 48.0,
            reason: 'Question 4 mark addition mistake corrected after remarking.'
        );

        $this->assertDatabaseHas('grade_audit_logs', [
            'id' => $log->id,
            'student_mark_id' => $mark->id,
            'changed_by_id' => $user->id,
            'score_type' => 'exam',
            'old_score' => 42.0,
            'new_score' => 48.0,
            'reason' => 'Question 4 mark addition mistake corrected after remarking.',
        ]);

        $this->assertEquals('Final Examination', $log->score_type_label);
    }

    public function test_record_grade_audit_throws_exception_on_invalid_score_type(): void
    {
        $user = User::factory()->create();
        $mark = StudentMark::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->service->recordGradeAudit(
            mark: $mark,
            user: $user,
            type: 'invalid_type',
            oldScore: 10.0,
            newScore: 20.0,
            reason: 'Test invalid'
        );
    }
}
