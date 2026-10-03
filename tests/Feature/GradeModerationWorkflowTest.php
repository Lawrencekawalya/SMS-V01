<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
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
use Tests\TestCase;

class GradeModerationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $hodUser;

    protected User $lecturer;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Department $department;

    protected CourseUnit $courseUnit;

    protected Student $student1;

    protected Student $student2;

    protected CourseAssessmentSheet $sheet;

    protected function setUp(): void
    {
        parent::setUp();

        GradingScaleTier::seedDefaults();

        $university = University::factory()->create();
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create([
            'campus_id' => $campus->id,
            'code' => 'FSC',
        ]);
        $this->department = Department::factory()->create(['faculty_id' => $faculty->id]);

        $programme = Programme::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'BSCS',
        ]);

        Curriculum::factory()->create([
            'programme_id' => $programme->id,
            'is_active' => true,
        ]);

        $this->courseUnit = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC3105',
            'name' => 'Database Systems Engineering',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $this->academicYear = AcademicYear::factory()->create([
            'is_current' => true,
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);

        $this->lecturer = User::factory()->create([
            'name' => 'Dr. Lecturer Example',
            'email' => 'lecturer@university.ac.ug',
        ]);

        $this->hodUser = User::factory()->create([
            'name' => 'Prof. Head of Department',
            'email' => 'hod.cs@university.ac.ug',
        ]);

        $this->actingAs($this->hodUser);

        // Create 2 students with registrations
        $studentUser1 = User::factory()->create(['name' => 'Student Alice']);
        $this->student1 = Student::factory()->create([
            'user_id' => $studentUser1->id,
            'programme_id' => $programme->id,
            'registration_number' => '24/U/001',
        ]);

        $reg1 = CourseRegistration::factory()->create([
            'student_id' => $this->student1->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'confirmed',
        ]);

        $regItem1 = CourseRegistrationItem::create([
            'course_registration_id' => $reg1->id,
            'course_unit_id' => $this->courseUnit->id,
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $studentUser2 = User::factory()->create(['name' => 'Student Bob']);
        $this->student2 = Student::factory()->create([
            'user_id' => $studentUser2->id,
            'programme_id' => $programme->id,
            'registration_number' => '24/U/002',
        ]);

        $reg2 = CourseRegistration::factory()->create([
            'student_id' => $this->student2->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'confirmed',
        ]);

        $regItem2 = CourseRegistrationItem::create([
            'course_registration_id' => $reg2->id,
            'course_unit_id' => $this->courseUnit->id,
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        // Create Assessment Sheet in submitted_to_hod status
        $this->sheet = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'instructor_id' => $this->lecturer->id,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'pass_mark' => 50.0,
            'status' => 'submitted_to_hod',
            'submitted_at' => now(),
        ]);

        // Create marks: Alice has 85 (A), Bob has 42 (F)
        StudentMark::create([
            'course_assessment_sheet_id' => $this->sheet->id,
            'course_registration_item_id' => $regItem1->id,
            'student_id' => $this->student1->id,
            'ca_score' => 35.0,
            'exam_score' => 50.0,
            'final_score' => 85.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
            'is_retake' => false,
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $this->sheet->id,
            'course_registration_item_id' => $regItem2->id,
            'student_id' => $this->student2->id,
            'ca_score' => 18.0,
            'exam_score' => 24.0,
            'final_score' => 42.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);
    }

    public function test_hod_moderation_desk_displays_submitted_sheets_and_metrics(): void
    {
        $response = $this->get(route('moderation.list'));

        $response->assertOk();
        $response->assertViewIs('academic.assessments.moderation.index');
        $response->assertSee('HoD Moderation Desk');
        $response->assertSee('CSC3105');
        $response->assertSee('Database Systems Engineering');
        $response->assertSee('Dr. Lecturer Example');
        $response->assertSee('Pending HoD Moderation');
    }

    public function test_hod_can_filter_moderation_desk_by_status(): void
    {
        // Another sheet in draft status
        $courseUnit2 = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC3109',
        ]);
        CourseAssessmentSheet::create([
            'course_unit_id' => $courseUnit2->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'draft',
        ]);

        $response = $this->get(route('moderation.list', ['status' => 'submitted_to_hod']));
        $response->assertOk();
        $response->assertSee('CSC3105');
        $response->assertDontSee('CSC3109');
    }

    public function test_hod_can_view_detailed_inspection_workspace_with_statistics(): void
    {
        $response = $this->get(route('moderation.show', $this->sheet));

        $response->assertOk();
        $response->assertViewIs('academic.assessments.moderation.show');
        $response->assertSee('CSC3105');
        $response->assertSee('Class Mean &amp; Variance', false);
        $response->assertSee('Class Grade Distribution Histogram');
        $response->assertSee('Student Alice');
        $response->assertSee('Student Bob');
        $response->assertSee('Endorse &amp; Submit to Senate', false);
        $response->assertSee('Return for Revision');
    }

    public function test_anomaly_detection_identifies_high_failure_rate(): void
    {
        /** @var GradingEngineService $engine */
        $engine = app(GradingEngineService::class);

        // Add 1 more student with F to reach 3 students (Alice: 85, Bob: 42, Charlie: 40) -> 66.7% fail rate
        $studentUser3 = User::factory()->create(['name' => 'Student Charlie']);
        $student3 = Student::factory()->create([
            'user_id' => $studentUser3->id,
            'programme_id' => $this->student1->programme_id,
        ]);
        $reg3 = CourseRegistration::factory()->create([
            'student_id' => $student3->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'confirmed',
        ]);
        $regItem3 = CourseRegistrationItem::create([
            'course_registration_id' => $reg3->id,
            'course_unit_id' => $this->courseUnit->id,
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $this->sheet->id,
            'course_registration_item_id' => $regItem3->id,
            'student_id' => $student3->id,
            'ca_score' => 15.0,
            'exam_score' => 25.0,
            'final_score' => 40.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);

        $this->sheet->load('studentMarks');
        $stats = $engine->computeSheetStatistics($this->sheet);

        $this->assertEquals(66.7, $stats['fail_rate']);
        $this->assertNotEmpty($stats['anomalies']);

        $anomalyTitles = array_column($stats['anomalies'], 'title');
        $this->assertContains('Disproportionate Failure Rate', $anomalyTitles);
    }

    public function test_hod_can_endorse_mark_sheet_advancing_to_department_moderated(): void
    {
        $response = $this->post(route('moderation.endorse', $this->sheet), [
            'remarks' => 'Moderation committee reviewed and approved scores.',
        ]);

        $response->assertRedirect(route('moderation.show', $this->sheet));
        $response->assertSessionHas('success');

        $this->sheet->refresh();
        $this->assertEquals('department_moderated', $this->sheet->status);
        $this->assertNotNull($this->sheet->moderated_at);
        $this->assertEquals($this->hodUser->id, $this->sheet->moderated_by_id);
        $this->assertEquals('Moderation committee reviewed and approved scores.', $this->sheet->moderation_remarks);
    }

    public function test_hod_can_return_mark_sheet_with_mandatory_remarks(): void
    {
        $response = $this->post(route('moderation.return', $this->sheet), [
            'remarks' => 'Please recheck coursework scores for candidate Bob.',
        ]);

        $response->assertRedirect(route('moderation.show', $this->sheet));
        $response->assertSessionHas('warning');

        $this->sheet->refresh();
        $this->assertEquals('returned_for_revision', $this->sheet->status);
        $this->assertEquals('Please recheck coursework scores for candidate Bob.', $this->sheet->moderation_remarks);
    }

    public function test_hod_return_requires_at_least_ten_character_remarks(): void
    {
        $response = $this->post(route('moderation.return', $this->sheet), [
            'remarks' => 'Too short',
        ]);

        $response->assertSessionHasErrors(['remarks']);

        $this->sheet->refresh();
        $this->assertEquals('submitted_to_hod', $this->sheet->status);
    }

    public function test_lecturer_can_re_edit_returned_sheet(): void
    {
        // Return sheet
        $this->sheet->update([
            'status' => 'returned_for_revision',
            'moderation_remarks' => 'Revise exam marks.',
        ]);

        $this->actingAs($this->lecturer);

        // Edit workspace should be accessible (200 OK, not redirected)
        $response = $this->get(route('assessment.edit', $this->sheet));
        $response->assertOk();
        $response->assertViewIs('academic.assessments.entry');
    }

    public function test_senate_can_publish_mark_sheet_and_trigger_automated_gpa_calculation(): void
    {
        // First endorse the sheet
        $this->sheet->update([
            'status' => 'department_moderated',
            'moderated_at' => now(),
            'moderated_by_id' => $this->hodUser->id,
        ]);

        $response = $this->post(route('moderation.publish', $this->sheet));

        $response->assertRedirect(route('moderation.show', $this->sheet));
        $response->assertSessionHas('success');

        $this->sheet->refresh();
        $this->assertEquals('published', $this->sheet->status);
        $this->assertNotNull($this->sheet->published_at);
        $this->assertEquals($this->hodUser->id, $this->sheet->published_by_id);

        // Verify automated GPA calculation for Student Alice (Alice scored A = 5.0 GP on a 4 CU course)
        $performanceAlice = StudentSemesterPerformance::where('student_id', $this->student1->id)
            ->where('semester_id', $this->semester->id)
            ->first();

        $this->assertNotNull($performanceAlice);
        $this->assertEquals(4.0, (float) $performanceAlice->credit_units_registered);
        $this->assertEquals(4.0, (float) $performanceAlice->credit_units_earned);
        $this->assertEquals(5.00, (float) $performanceAlice->gpa);
        $this->assertEquals(5.00, (float) $performanceAlice->cgpa);
        $this->assertEquals('Normal Progress', $performanceAlice->academic_standing);

        // Alice's student model cumulative_gpa updated
        $this->student1->refresh();
        $this->assertEquals(5.00, (float) $this->student1->cumulative_gpa);

        // Verify automated GPA calculation for Student Bob (Bob scored F = 0.0 GP on 4 CU course)
        $performanceBob = StudentSemesterPerformance::where('student_id', $this->student2->id)
            ->where('semester_id', $this->semester->id)
            ->first();

        $this->assertNotNull($performanceBob);
        $this->assertEquals(4.0, (float) $performanceBob->credit_units_registered);
        $this->assertEquals(0.0, (float) $performanceBob->credit_units_earned);
        $this->assertEquals(0.00, (float) $performanceBob->gpa);
        $this->assertEquals('Probation', $performanceBob->academic_standing);
    }

    public function test_published_sheet_is_locked_against_further_lecturer_modifications(): void
    {
        $this->sheet->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($this->lecturer);

        // Edit workspace should redirect with warning
        $response = $this->get(route('assessment.edit', $this->sheet));
        $response->assertRedirect(route('assessment.show', $this->sheet));
        $response->assertSessionHas('warning');
    }
}
