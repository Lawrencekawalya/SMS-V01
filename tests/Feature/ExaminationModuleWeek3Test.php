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
use App\Services\GradingEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExaminationModuleWeek3Test extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $lecturer;

    protected User $hodUser;

    protected University $university;

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

        $this->university = University::factory()->create([
            'name' => 'Bishop Stuart University',
            'code' => 'BSU',
        ]);

        $campus = Campus::factory()->create(['university_id' => $this->university->id]);
        $faculty = Faculty::factory()->create([
            'campus_id' => $campus->id,
            'code' => 'FAS',
            'name' => 'Faculty of Applied Sciences',
        ]);

        $this->department = Department::factory()->create([
            'faculty_id' => $faculty->id,
            'name' => 'Department of Computer Science',
            'code' => 'CS',
        ]);

        $this->programme = Programme::factory()->create([
            'department_id' => $this->department->id,
            'name' => 'Bachelor of Science in Software Engineering',
            'code' => 'BSSE',
            'award_type' => 'bachelors',
        ]);

        Curriculum::factory()->create([
            'programme_id' => $this->programme->id,
            'version_name' => '2026 Curriculum Standard',
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
            'code' => 'SE1101',
            'name' => 'Software Engineering Principles',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $this->courseUnit2 = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'SE1102',
            'name' => 'Database Design & Management',
            'credit_units' => 3.0,
            'status' => 'active',
        ]);

        $this->superAdmin = User::factory()->create([
            'name' => 'Academic Registrar Official',
            'email' => 'registrar@bsu.ac.ug',
        ]);

        $this->lecturer = User::factory()->create([
            'name' => 'Eng. Joseph Lecturer',
            'email' => 'joseph.lecturer@bsu.ac.ug',
        ]);

        $this->hodUser = User::factory()->create([
            'name' => 'Dr. Department Head',
            'email' => 'hod.cs@bsu.ac.ug',
        ]);

        // Student 1: Alice (High achiever)
        $userAlice = User::factory()->create(['name' => 'Alice Arinda']);
        $this->studentAlice = Student::factory()->create([
            'user_id' => $userAlice->id,
            'first_name' => 'Alice',
            'last_name' => 'Arinda',
            'programme_id' => $this->programme->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'registration_number' => '26/BSU/BSSE/001',
            'student_number' => '26001001',
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);

        // Student 2: Bob (Struggling student)
        $userBob = User::factory()->create(['name' => 'Bob Byamukama']);
        $this->studentBob = Student::factory()->create([
            'user_id' => $userBob->id,
            'first_name' => 'Bob',
            'last_name' => 'Byamukama',
            'programme_id' => $this->programme->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'registration_number' => '26/BSU/BSSE/002',
            'student_number' => '26001002',
            'current_study_year' => 1,
            'current_semester' => 1,
            'status' => 'active',
        ]);
    }

    /**
     * Journey 1: End-to-End Governance Lifecycle.
     * Enrollment -> Marks Draft Entry -> HoD Submission -> HoD Moderation Endorsement ->
     * Senate Publication -> Automated GPA Engine -> Official Printable Result Slip & Transcript.
     */
    public function test_journey_one_full_governance_lifecycle_from_entry_to_published_transcript(): void
    {
        // 1. Student confirms course registration for the semester
        $regAlice = CourseRegistration::factory()->create([
            'student_id' => $this->studentAlice->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'confirmed',
        ]);

        $itemAlice = CourseRegistrationItem::create([
            'course_registration_id' => $regAlice->id,
            'course_unit_id' => $this->courseUnit1->id,
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        // 2. Assessment Sheet is created and assigned to lecturer
        $sheet = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseUnit1->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'instructor_id' => $this->lecturer->id,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'pass_mark' => 50.0,
            'status' => 'draft',
        ]);

        $markAlice = StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $itemAlice->id,
            'student_id' => $this->studentAlice->id,
        ]);

        // 3. Lecturer logs in and accesses marks entry workspace
        $this->actingAs($this->lecturer);
        $entryResponse = $this->get(route('academic.assessments.edit', $sheet));
        $entryResponse->assertOk();
        $entryResponse->assertViewIs('academic.assessments.entry');
        $entryResponse->assertSee('SE1101');
        $entryResponse->assertSee('Alice Arinda');

        // 4. Lecturer enters CA (36.0) and Exam (54.0) marks -> Total: 90.0% (A, 5.0 GP) and submits to HoD
        $submitResponse = $this->put(route('academic.assessments.update', $sheet), [
            'action' => 'submit_hod',
            'marks' => [
                [
                    'student_mark_id' => $markAlice->id,
                    'ca_score' => 36.0,
                    'exam_score' => 54.0,
                    'lecturer_remarks' => 'Outstanding analytical and implementation capability.',
                ],
            ],
        ]);

        $submitResponse->assertRedirect(route('academic.assessments.show', $sheet));
        $submitResponse->assertSessionHas('success');

        // Verify mark and sheet status
        $sheet->refresh();
        $markAlice->refresh();
        $this->assertEquals('submitted_to_hod', $sheet->status);
        $this->assertNotNull($sheet->submitted_at);
        $this->assertEquals(90.0, (float) $markAlice->final_score);
        $this->assertEquals('A', $markAlice->grade_letter);
        $this->assertEquals(5.0, (float) $markAlice->grade_point);
        $this->assertTrue((bool) $markAlice->is_passed);

        // 5. HoD logs in, inspects sheet and statistical distribution on Moderation Desk
        $this->actingAs($this->hodUser);
        $deskResponse = $this->get(route('academic.assessments.moderation.show', $sheet));
        $deskResponse->assertOk();
        $deskResponse->assertViewIs('academic.assessments.moderation.show');
        $deskResponse->assertSee('Class Mean &amp; Variance', false);
        $deskResponse->assertSee('Endorse &amp; Submit to Senate', false);

        // 6. HoD endorses sheet
        $endorseResponse = $this->post(route('academic.assessments.moderation.endorse', $sheet), [
            'remarks' => 'Departmental moderation committee validated assessment standards.',
        ]);
        $endorseResponse->assertRedirect(route('academic.assessments.moderation.show', $sheet));

        $sheet->refresh();
        $this->assertEquals('department_moderated', $sheet->status);
        $this->assertEquals($this->hodUser->id, $sheet->moderated_by_id);

        // 7. Academic Registrar / Senate officially publishes marks
        $this->actingAs($this->superAdmin);
        $publishResponse = $this->post(route('academic.assessments.moderation.publish', $sheet));
        $publishResponse->assertRedirect(route('academic.assessments.moderation.show', $sheet));

        $sheet->refresh();
        $this->assertEquals('published', $sheet->status);
        $this->assertNotNull($sheet->published_at);

        // 8. Automated GPA & CGPA Engine verification
        $perfAlice = StudentSemesterPerformance::where('student_id', $this->studentAlice->id)
            ->where('semester_id', $this->semester->id)
            ->first();

        $this->assertNotNull($perfAlice);
        $this->assertEquals(4.0, (float) $perfAlice->credit_units_registered);
        $this->assertEquals(4.0, (float) $perfAlice->credit_units_earned);
        $this->assertEquals(5.00, (float) $perfAlice->gpa);
        $this->assertEquals(5.00, (float) $perfAlice->cgpa);
        $this->assertEquals('Normal Progress', $perfAlice->academic_standing);

        $this->studentAlice->refresh();
        $this->assertEquals(5.00, (float) $this->studentAlice->cumulative_gpa);

        // 9. Student / Registrar views the official printable semester result slip
        $slipResponse = $this->get(route('academic.results.slip', [
            'student' => $this->studentAlice,
            'semester' => $this->semester,
        ]));
        $slipResponse->assertOk();
        $slipResponse->assertViewIs('academic.assessments.results.slip');
        $slipResponse->assertSee('Official Semester Result Slip');
        $slipResponse->assertSee('Alice Arinda');
        $slipResponse->assertSee('SE1101');
        $slipResponse->assertSee('90.0');
        $slipResponse->assertSee('5.00');
        $slipResponse->assertSee('Normal Progress');

        // 10. Student / Registrar views the official cumulative academic transcript
        $transcriptResponse = $this->get(route('academic.results.transcript', $this->studentAlice));
        $transcriptResponse->assertOk();
        $transcriptResponse->assertViewIs('academic.assessments.results.transcript');
        $transcriptResponse->assertSee('Official Cumulative Academic Transcript');
        $transcriptResponse->assertSee('ALICE ARINDA');
        $transcriptResponse->assertSee('FIRST CLASS HONOURS');
        $transcriptResponse->assertSee('OFFICIAL<br>UNIVERSITY<br>SEAL', false);
    }

    /**
     * Journey 2: Score Adjustment with Immutable Audit Logging.
     * Score adjustment after initial grading triggers GradeAuditLog records.
     */
    public function test_journey_two_score_adjustment_generates_immutable_grade_audit_log(): void
    {
        $regAlice = CourseRegistration::factory()->create([
            'student_id' => $this->studentAlice->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'confirmed',
        ]);

        $itemAlice = CourseRegistrationItem::create([
            'course_registration_id' => $regAlice->id,
            'course_unit_id' => $this->courseUnit1->id,
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $sheet = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseUnit1->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'instructor_id' => $this->lecturer->id,
            'status' => 'draft',
        ]);

        $markAlice = StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $itemAlice->id,
            'student_id' => $this->studentAlice->id,
            'ca_score' => 25.0,
            'exam_score' => 35.0,
            'final_score' => 60.0,
            'grade_letter' => 'C',
            'grade_point' => 3.0,
            'is_passed' => true,
        ]);

        $this->actingAs($this->lecturer);

        // Adjust CA from 25 -> 32 and Exam from 35 -> 48 (Final: 80.0%, Grade A)
        $updateResponse = $this->put(route('academic.assessments.update', $sheet), [
            'action' => 'save_draft',
            'marks' => [
                [
                    'student_mark_id' => $markAlice->id,
                    'ca_score' => 32.0,
                    'exam_score' => 48.0,
                    'lecturer_remarks' => 'Coursework script remarked following student verification query.',
                ],
            ],
        ]);

        $updateResponse->assertRedirect(route('academic.assessments.edit', $sheet));

        $markAlice->refresh();
        $this->assertEquals(80.0, (float) $markAlice->final_score);
        $this->assertEquals('A', $markAlice->grade_letter);

        // Verify GradeAuditLog records were generated for both components
        $this->assertDatabaseHas('grade_audit_logs', [
            'student_mark_id' => $markAlice->id,
            'changed_by_id' => $this->lecturer->id,
            'score_type' => 'ca',
            'old_score' => 25.0,
            'new_score' => 32.0,
        ]);

        $this->assertDatabaseHas('grade_audit_logs', [
            'student_mark_id' => $markAlice->id,
            'changed_by_id' => $this->lecturer->id,
            'score_type' => 'exam',
            'old_score' => 35.0,
            'new_score' => 48.0,
        ]);

        // Audit trail is visible on the detailed inspection workspace
        $moderationResponse = $this->actingAs($this->hodUser)
            ->get(route('academic.assessments.moderation.show', $sheet));
        $moderationResponse->assertOk();
        $moderationResponse->assertSee('Examination Score Revision Audit Trail');
        $moderationResponse->assertSee('Coursework script remarked following student verification query.');
    }

    /**
     * Journey 3: Course Failure, Retake Flagging & Academic Probation Standing.
     * Evaluates a struggling student with marks < 50.0% -> flagged as retake, CGPA < 2.0 -> Probation.
     */
    public function test_journey_three_course_failure_triggers_retake_and_probation_standing(): void
    {
        $regBob = CourseRegistration::factory()->create([
            'student_id' => $this->studentBob->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'confirmed',
        ]);

        $itemBob1 = CourseRegistrationItem::create([
            'course_registration_id' => $regBob->id,
            'course_unit_id' => $this->courseUnit1->id,
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $itemBob2 = CourseRegistrationItem::create([
            'course_registration_id' => $regBob->id,
            'course_unit_id' => $this->courseUnit2->id,
            'credit_units' => 3.0,
            'status' => 'registered',
        ]);

        $sheet1 = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseUnit1->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'pass_mark' => 50.0,
            'status' => 'published',
        ]);

        $sheet2 = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseUnit2->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'pass_mark' => 50.0,
            'status' => 'published',
        ]);

        // Course 1: Score 38.0% (Fail -> Grade F, GP 0.0)
        $markBob1 = StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $itemBob1->id,
            'student_id' => $this->studentBob->id,
            'ca_score' => 14.0,
            'exam_score' => 24.0,
            'final_score' => 38.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);

        // Course 2: Score 42.0% (Fail -> Grade F, GP 0.0)
        $markBob2 = StudentMark::create([
            'course_assessment_sheet_id' => $sheet2->id,
            'course_registration_item_id' => $itemBob2->id,
            'student_id' => $this->studentBob->id,
            'ca_score' => 18.0,
            'exam_score' => 24.0,
            'final_score' => 42.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);

        /** @var GradingEngineService $gradingEngine */
        $gradingEngine = app(GradingEngineService::class);
        $performance = $gradingEngine->calculateSemesterGpa($this->studentBob, $this->semester);

        $this->studentBob->refresh();

        // Registered: 7 CU, Earned: 0 CU, GPA: 0.00, Standing: Probation
        $this->assertEquals(7.0, (float) $performance->credit_units_registered);
        $this->assertEquals(0.0, (float) $performance->credit_units_earned);
        $this->assertEquals(0.00, (float) $performance->gpa);
        $this->assertEquals(0.00, (float) $performance->cgpa);
        $this->assertEquals('Probation', $performance->academic_standing);
        $this->assertEquals(0.00, (float) $this->studentBob->cumulative_gpa);

        // Verify Results Hub filters by Probation
        $this->actingAs($this->superAdmin);
        $filterResponse = $this->get(route('academic.results.index', [
            'academic_standing' => 'Probation',
        ]));
        $filterResponse->assertOk();
        $filterResponse->assertSee('Bob Byamukama');
        $filterResponse->assertSee('Probation');

        // Verify Printable Result Slip indicates Probation & Retake flags
        $slipResponse = $this->get(route('academic.results.slip', [
            'student' => $this->studentBob,
            'semester' => $this->semester,
        ]));
        $slipResponse->assertOk();
        $slipResponse->assertSee('Bob Byamukama');
        $slipResponse->assertSee('Probation');
        $slipResponse->assertSee('38.0');
        $slipResponse->assertSee('42.0');
    }
}
