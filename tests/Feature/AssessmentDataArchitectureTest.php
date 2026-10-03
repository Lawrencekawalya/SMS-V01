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
use App\Models\GradeAuditLog;
use App\Models\GradingScaleTier;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\StudentSemesterPerformance;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentDataArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected User $instructor;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Department $department;

    protected CourseUnit $courseUnit;

    protected Student $student;

    protected CourseRegistration $registration;

    protected CourseRegistrationItem $registrationItem;

    protected function setUp(): void
    {
        parent::setUp();

        $university = University::factory()->create();
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id]);
        $this->department = Department::factory()->create(['faculty_id' => $faculty->id]);

        $programme = Programme::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'BSCS',
        ]);

        $curriculum = Curriculum::factory()->create([
            'programme_id' => $programme->id,
            'is_active' => true,
        ]);

        $this->academicYear = AcademicYear::factory()->create([
            'is_current' => true,
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'semester_number' => 1,
            'is_active' => true,
        ]);

        $this->instructor = User::factory()->create([
            'name' => 'Dr. Paul Ssemakula',
            'email' => 'paul.ssemakula@bsu.ac.ug',
        ]);

        $this->courseUnit = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC2101',
            'name' => 'Data Structures & Algorithms',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $this->student = Student::factory()->create([
            'campus_id' => $campus->id,
            'programme_id' => $programme->id,
            'curriculum_id' => $curriculum->id,
            'registration_number' => '25/BSCS/099',
            'status' => 'active',
        ]);

        $this->registration = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'study_year' => 2,
            'semester_number' => 1,
            'status' => 'approved',
        ]);

        $this->registrationItem = CourseRegistrationItem::factory()->create([
            'course_registration_id' => $this->registration->id,
            'course_unit_id' => $this->courseUnit->id,
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);
    }

    public function test_institutional_assessment_policy_configuration_is_loaded(): void
    {
        $caWeight = config('academic.assessment_ca_weight');
        $examWeight = config('academic.assessment_exam_weight');
        $passMark = config('academic.assessment_pass_mark');
        $gradingScale = config('academic.grading_scale');

        $this->assertEquals(40.0, $caWeight);
        $this->assertEquals(60.0, $examWeight);
        $this->assertEquals(100.0, $caWeight + $examWeight);
        $this->assertEquals(50.0, $passMark);
        $this->assertIsArray($gradingScale);
        $this->assertCount(8, $gradingScale);

        // Verify top grade and fail grade
        $this->assertEquals('A', $gradingScale[0]['grade_letter']);
        $this->assertEquals(5.0, $gradingScale[0]['grade_point']);
        $this->assertEquals('F', $gradingScale[7]['grade_letter']);
        $this->assertEquals(0.0, $gradingScale[7]['grade_point']);
    }

    public function test_can_create_course_assessment_sheet_with_valid_relations(): void
    {
        $sheet = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'instructor_id' => $this->instructor->id,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'pass_mark' => 50.0,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('course_assessment_sheets', [
            'id' => $sheet->id,
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'status' => 'draft',
        ]);

        $this->assertTrue($sheet->canBeEdited());
        $this->assertFalse($sheet->isSubmittedToHod());
        $this->assertFalse($sheet->isPublished());
        $this->assertEquals('Draft (Lecturer)', $sheet->status_label);
        $this->assertEquals('text-bg-secondary', $sheet->status_badge_class);
        $this->assertTrue($sheet->courseUnit->is($this->courseUnit));
        $this->assertTrue($sheet->semester->is($this->semester));
        $this->assertTrue($sheet->academicYear->is($this->academicYear));
        $this->assertTrue($sheet->instructor->is($this->instructor));
    }

    public function test_unique_constraint_enforces_one_mark_sheet_per_course_per_semester(): void
    {
        CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'instructor_id' => $this->instructor->id,
            'status' => 'draft',
        ]);

        $this->expectException(QueryException::class);

        // Attempt duplicate sheet creation for same course in same semester
        CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'instructor_id' => $this->instructor->id,
            'status' => 'draft',
        ]);
    }

    public function test_can_record_student_mark_linked_to_registration_item(): void
    {
        $sheet = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'instructor_id' => $this->instructor->id,
        ]);

        $mark = StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $this->registrationItem->id,
            'student_id' => $this->student->id,
            'ca_score' => 33.5,
            'exam_score' => 47.0,
            'final_score' => 80.5,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
            'is_retake' => false,
            'lecturer_remarks' => 'Top student in class',
        ]);

        $this->assertDatabaseHas('student_marks', [
            'id' => $mark->id,
            'course_assessment_sheet_id' => $sheet->id,
            'student_id' => $this->student->id,
            'final_score' => 80.5,
            'grade_letter' => 'A',
            'is_passed' => 1,
        ]);

        $this->assertTrue($mark->courseAssessmentSheet->is($sheet));
        $this->assertTrue($mark->registrationItem->is($this->registrationItem));
        $this->assertTrue($mark->student->is($this->student));
        $this->assertEquals('text-bg-success', $mark->grade_badge_class);
    }

    public function test_unique_constraint_enforces_one_mark_per_student_per_sheet(): void
    {
        $sheet = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $this->registrationItem->id,
            'student_id' => $this->student->id,
            'ca_score' => 25.0,
        ]);

        $this->expectException(QueryException::class);

        // Attempt duplicate mark for same student on same sheet
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $this->registrationItem->id,
            'student_id' => $this->student->id,
            'ca_score' => 30.0,
        ]);
    }

    public function test_sheet_pass_rate_and_average_score_calculations(): void
    {
        $sheet = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        // Student 1: 80% (Pass)
        StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $this->registrationItem->id,
            'student_id' => $this->student->id,
            'ca_score' => 32.0,
            'exam_score' => 48.0,
            'final_score' => 80.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
        ]);

        // Student 2: 40% (Fail)
        $student2 = Student::factory()->create();
        $reg2 = CourseRegistration::factory()->create([
            'student_id' => $student2->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        $item2 = CourseRegistrationItem::factory()->create([
            'course_registration_id' => $reg2->id,
            'course_unit_id' => $this->courseUnit->id,
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $item2->id,
            'student_id' => $student2->id,
            'ca_score' => 15.0,
            'exam_score' => 25.0,
            'final_score' => 40.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);

        // Total 2 graded: 1 passed, 1 failed -> 50% pass rate, average = (80+40)/2 = 60.0
        $this->assertEquals(50.0, $sheet->pass_rate);
        $this->assertEquals(60.0, $sheet->average_score);
    }

    public function test_can_create_grade_audit_log_when_score_is_adjusted(): void
    {
        $sheet = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $mark = StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $this->registrationItem->id,
            'student_id' => $this->student->id,
            'ca_score' => 25.0,
            'exam_score' => 42.0,
            'final_score' => 67.0,
            'grade_letter' => 'C+',
            'grade_point' => 3.5,
            'is_passed' => true,
        ]);

        $auditLog = GradeAuditLog::create([
            'student_mark_id' => $mark->id,
            'changed_by_id' => $this->instructor->id,
            'score_type' => 'exam',
            'old_score' => 42.0,
            'new_score' => 48.0,
            'reason' => 'Remarking of question 3 script approved by HoD.',
        ]);

        $this->assertDatabaseHas('grade_audit_logs', [
            'id' => $auditLog->id,
            'student_mark_id' => $mark->id,
            'changed_by_id' => $this->instructor->id,
            'score_type' => 'exam',
            'new_score' => 48.0,
        ]);

        $this->assertTrue($auditLog->studentMark->is($mark));
        $this->assertTrue($auditLog->changedBy->is($this->instructor));
        $this->assertEquals('Final Examination', $auditLog->score_type_label);
        $this->assertCount(1, $mark->auditLogs);
    }

    public function test_can_record_student_semester_performance_summary(): void
    {
        $perf = StudentSemesterPerformance::create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'credit_units_registered' => 16.0,
            'credit_units_earned' => 16.0,
            'weighted_grade_points' => 72.0,
            'gpa' => 4.50,
            'cumulative_credit_units_registered' => 32.0,
            'cumulative_credit_units_earned' => 32.0,
            'cumulative_weighted_grade_points' => 140.0,
            'cgpa' => 4.38,
            'academic_standing' => 'Normal Progress',
        ]);

        $this->assertDatabaseHas('student_semester_performances', [
            'id' => $perf->id,
            'student_id' => $this->student->id,
            'semester_id' => $this->semester->id,
            'gpa' => 4.50,
            'academic_standing' => 'Normal Progress',
        ]);

        $this->assertTrue($perf->isNormalProgress());
        $this->assertFalse($perf->isProbation());
        $this->assertEquals('text-bg-success', $perf->standing_badge_class);
    }

    public function test_unique_constraint_enforces_one_performance_record_per_student_per_semester(): void
    {
        StudentSemesterPerformance::create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'gpa' => 4.0,
            'cgpa' => 4.0,
        ]);

        $this->expectException(QueryException::class);

        // Duplicate semester performance record for same student and semester
        StudentSemesterPerformance::create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'gpa' => 3.5,
            'cgpa' => 3.5,
        ]);
    }

    public function test_inverse_relationships_on_existing_models_function_correctly(): void
    {
        $sheet = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'instructor_id' => $this->instructor->id,
        ]);

        $mark = StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $this->registrationItem->id,
            'student_id' => $this->student->id,
            'ca_score' => 30.0,
            'exam_score' => 45.0,
            'final_score' => 75.0,
            'grade_letter' => 'B+',
            'grade_point' => 4.5,
            'is_passed' => true,
        ]);

        $perf = StudentSemesterPerformance::create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'gpa' => 4.5,
            'cgpa' => 4.5,
        ]);

        // CourseUnit -> assessmentSheets
        $this->assertTrue($this->courseUnit->assessmentSheets->contains($sheet));

        // CourseRegistrationItem -> studentMark
        $this->assertTrue($this->registrationItem->studentMark->is($mark));

        // Student -> marks & semesterPerformances
        $this->assertTrue($this->student->marks->contains($mark));
        $this->assertTrue($this->student->semesterPerformances->contains($perf));

        // User -> instructedSheets
        $this->assertTrue($this->instructor->instructedSheets->contains($sheet));
    }

    public function test_assessment_index_page_renders_kpis_and_table(): void
    {
        $sheet = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'instructor_id' => $this->instructor->id,
            'status' => 'draft',
        ]);

        $response = $this->get(route('assessment.list'));

        $response->assertOk();
        $response->assertSee('Course Assessment &amp; Mark Sheets', false);
        $response->assertSee('Master Course Mark Sheets Directory');
        $response->assertSee('Total Mark Sheets');
        $response->assertSee('Pending HoD Moderation');
        $response->assertSee('Senate Published');
        $response->assertSee($this->courseUnit->code);
        $response->assertSee($this->courseUnit->name);
        $response->assertSee('Dr. Paul Ssemakula');
        $response->assertSee('sheets-table');
    }

    public function test_assessment_index_filters_work_correctly(): void
    {
        // Sheet 1 for CS department
        CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'submitted_to_hod',
        ]);

        // Sheet 2 for other department
        $otherDept = Department::factory()->create(['faculty_id' => $this->department->faculty_id]);
        $otherCourse = CourseUnit::factory()->create([
            'department_id' => $otherDept->id,
            'code' => 'BIT3101',
            'name' => 'Advanced Database Administration',
        ]);
        CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $otherCourse->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'published',
        ]);

        // Filter by department
        $responseDept = $this->get(route('assessment.list', ['department_id' => $this->department->id]));
        $responseDept->assertOk();
        $responseDept->assertSee('CSC2101');
        $responseDept->assertDontSee('BIT3101');

        // Filter by status
        $responseStatus = $this->get(route('assessment.list', ['status' => 'published']));
        $responseStatus->assertOk();
        $responseStatus->assertSee('BIT3101');
        $responseStatus->assertDontSee('CSC2101');
    }

    public function test_assessment_show_page_displays_roster_and_grades(): void
    {
        $sheet = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'instructor_id' => $this->instructor->id,
            'status' => 'submitted_to_hod',
            'moderation_remarks' => 'Review of midterm and final exams conducted.',
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $this->registrationItem->id,
            'student_id' => $this->student->id,
            'ca_score' => 35.0,
            'exam_score' => 45.0,
            'final_score' => 80.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
            'lecturer_remarks' => 'Outstanding participation',
        ]);

        $response = $this->get(route('assessment.show', $sheet));

        $response->assertOk();
        $response->assertSee('Course Assessment Sheet Details');
        $response->assertSee('CSC2101');
        $response->assertSee('Data Structures &amp; Algorithms', false);
        $response->assertSee('25/BSCS/099');
        $response->assertSee('Outstanding participation');
        $response->assertSee('Review of midterm and final exams conducted.');
        $response->assertSee('marks-table');
    }

    public function test_sidebar_includes_examinations_and_grading_navigation(): void
    {
        $response = $this->get(route('assessment.list'));

        $response->assertOk();
        $response->assertSee('Examinations & Grading');
        $response->assertSee('Grading Policy & Scale');
        $response->assertSee('Course Mark Sheets');
        $response->assertSee(route('assessment.policy'));
        $response->assertSee(route('assessment.list'));
    }

    public function test_institutional_grading_policy_page_renders_with_nche_scale(): void
    {
        $response = $this->get(route('assessment.policy'));

        $response->assertOk();
        $response->assertSee('Institutional Assessment &amp; Grading Standards', false);
        $response->assertSee('Continuous Assessment (CA)');
        $response->assertSee('Final Examination');
        $response->assertSee('Minimum Pass Mark');
        $response->assertSee('Grading Scale (NCHE 5.0 Standard)');
        $response->assertSee('Exceptional / Distinction');
        $response->assertSee('Very Good');
        $response->assertSee('Clear Pass');
        $response->assertSee('Marginal Pass');
        $response->assertSee('Pass (Minimum Passing Grade)');
        $response->assertSee('Fail (Requires Retake)');
        $response->assertSee('First Class Honours');
        $response->assertSee('Second Class Honours (Upper Division)');
        $response->assertSee('Course Mark Sheets Directory');
    }

    public function test_policy_update_validates_that_ca_and_exam_weights_sum_to_100_percent(): void
    {
        // Unbalanced: 40 + 50 = 90%
        $response = $this->post(route('assessment.policy.update'), [
            'ca_weight' => 40.0,
            'exam_weight' => 50.0,
            'pass_mark' => 50.0,
        ]);

        $response->assertSessionHasErrors(['ca_weight']);
    }

    public function test_can_update_institutional_assessment_policy_with_valid_weights(): void
    {
        // Valid 30% CA + 70% Exam = 100%
        $response = $this->post(route('assessment.policy.update'), [
            'ca_weight' => 30.0,
            'exam_weight' => 70.0,
            'pass_mark' => 50.0,
        ]);

        $response->assertRedirect(route('assessment.policy'));
        $response->assertSessionHas('success');

        $this->assertEquals(30.0, config('academic.assessment_ca_weight'));
        $this->assertEquals(70.0, config('academic.assessment_exam_weight'));
        $this->assertEquals(50.0, config('academic.assessment_pass_mark'));

        // Reset to standard 40 / 60
        config([
            'academic.assessment_ca_weight' => 40.0,
            'academic.assessment_exam_weight' => 60.0,
            'academic.assessment_pass_mark' => 50.0,
        ]);
    }

    public function test_can_edit_and_reset_grading_scale_tiers(): void
    {
        GradingScaleTier::seedDefaults();
        $tierA = GradingScaleTier::where('grade_letter', 'A')->firstOrFail();

        // Update tier A to start at 85% instead of 80%
        $response = $this->put(route('assessment.policy.scale.update'), [
            'tiers' => [
                [
                    'id' => $tierA->id,
                    'grade_letter' => 'A',
                    'min_score' => 85.0,
                    'max_score' => 100.0,
                    'grade_point' => 5.0,
                    'classification' => 'Summa Cum Laude / Exceptional',
                ],
            ],
        ]);

        $response->assertRedirect(route('assessment.policy'));
        $response->assertSessionHas('success');

        $this->assertEquals(85.0, $tierA->fresh()->min_score);
        $this->assertEquals('Summa Cum Laude / Exceptional', $tierA->fresh()->classification);

        // Reset to defaults
        $resetResponse = $this->post(route('assessment.policy.scale.reset'));
        $resetResponse->assertRedirect(route('assessment.policy'));
        $resetResponse->assertSessionHas('success');

        $this->assertEquals(80.0, GradingScaleTier::where('grade_letter', 'A')->first()->min_score);
    }

    public function test_can_edit_and_reset_award_classifications_for_degree_diploma_and_certificates(): void
    {
        AwardClassification::seedDefaults();

        $degreeFirst = AwardClassification::where('award_level', 'degree')
            ->where('name', 'First Class Honours')
            ->firstOrFail();

        // Update First Class Honours threshold
        $response = $this->put(route('assessment.policy.awards.update'), [
            'awards' => [
                [
                    'id' => $degreeFirst->id,
                    'name' => 'First Class Honours (Dean\'s List)',
                    'min_cgpa' => 4.50,
                    'max_cgpa' => 5.00,
                    'academic_standing' => 'Normal Progress',
                ],
            ],
        ]);

        $response->assertRedirect(route('assessment.policy'));
        $response->assertSessionHas('success');

        $this->assertEquals(4.50, $degreeFirst->fresh()->min_cgpa);
        $this->assertEquals('First Class Honours (Dean\'s List)', $degreeFirst->fresh()->name);

        // Verify diploma and certificate classifications exist
        $this->assertTrue(AwardClassification::where('award_level', 'diploma')->exists());
        $this->assertTrue(AwardClassification::where('award_level', 'certificate')->exists());

        // Reset awards to defaults
        $resetResponse = $this->post(route('assessment.policy.awards.reset'));
        $resetResponse->assertRedirect(route('assessment.policy'));
        $resetResponse->assertSessionHas('success');

        $this->assertEquals(4.40, AwardClassification::where('award_level', 'degree')
            ->where('name', 'First Class Honours')
            ->first()->min_cgpa);
    }
}
