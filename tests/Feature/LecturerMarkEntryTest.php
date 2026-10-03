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
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LecturerMarkEntryTest extends TestCase
{
    use RefreshDatabase;

    protected User $instructor;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Department $department;

    protected CourseUnit $courseUnit;

    protected Student $student1;

    protected Student $student2;

    protected CourseAssessmentSheet $draftSheet;

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

        $curriculum = Curriculum::factory()->create([
            'programme_id' => $programme->id,
            'is_active' => true,
        ]);

        $this->courseUnit = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC2101',
            'name' => 'Data Structures & Algorithms',
            'credit_units' => 4.0,
            'status' => 'active',
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
            'name' => 'Dr. Alex Kato',
            'email' => 'alex.kato@bsu.ac.ug',
        ]);

        $this->student1 = Student::factory()->create([
            'programme_id' => $programme->id,
            'registration_number' => '25/BSCS/001',
            'student_number' => '2500700001',
            'first_name' => 'Timothy',
            'last_name' => 'Mugisha',
        ]);

        $this->student2 = Student::factory()->create([
            'programme_id' => $programme->id,
            'registration_number' => '25/BSCS/002',
            'student_number' => '2500700002',
            'first_name' => 'Sarah',
            'last_name' => 'Namubiru',
        ]);

        $this->draftSheet = CourseAssessmentSheet::create([
            'course_unit_id' => $this->courseUnit->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $this->academicYear->id,
            'instructor_id' => $this->instructor->id,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'pass_mark' => 50.0,
            'status' => 'draft',
        ]);
    }

    /**
     * Helper to create confirmed registration item for student in the course.
     */
    protected function registerStudent(Student $student): CourseRegistrationItem
    {
        $registration = CourseRegistration::firstOrCreate(
            [
                'student_id' => $student->id,
                'semester_id' => $this->semester->id,
            ],
            [
                'academic_year_id' => $this->academicYear->id,
                'study_year' => 2,
                'semester_number' => 1,
                'total_credits' => 4.0,
                'status' => 'approved',
            ]
        );

        return CourseRegistrationItem::firstOrCreate(
            [
                'course_registration_id' => $registration->id,
                'course_unit_id' => $this->courseUnit->id,
            ],
            [
                'course_type' => 'Core',
                'credit_units' => 4.0,
                'status' => 'approved',
            ]
        );
    }

    public function test_lecturer_can_view_mark_entry_workspace_for_draft_sheet(): void
    {
        $item1 = $this->registerStudent($this->student1);

        StudentMark::create([
            'course_assessment_sheet_id' => $this->draftSheet->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student1->id,
            'ca_score' => 30.0,
            'exam_score' => null,
            'final_score' => null,
            'grade_letter' => null,
            'grade_point' => null,
            'is_passed' => false,
            'is_retake' => false,
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('assessment.edit', $this->draftSheet));

        $response->assertStatus(200);
        $response->assertSee('Lecturer Mark Entry Workspace');
        $response->assertSee('CSC2101');
        $response->assertSee('Data Structures & Algorithms');
        $response->assertSee('Timothy Mugisha');
        $response->assertSee('25/BSCS/001');
        $response->assertSee('Save as Draft');
        $response->assertSee('Submit to Head of Department');
    }

    public function test_lecturer_cannot_access_entry_workspace_for_locked_sheet(): void
    {
        $this->draftSheet->update(['status' => 'submitted_to_hod']);

        $response = $this->actingAs($this->instructor)
            ->get(route('assessment.edit', $this->draftSheet));

        $response->assertRedirect(route('assessment.show', $this->draftSheet));
        $response->assertSessionHas('warning');
    }

    public function test_registered_students_without_marks_are_auto_synced_on_edit(): void
    {
        $regItem = $this->registerStudent($this->student1);

        // StudentMark does NOT exist prior to accessing edit workspace
        $this->assertDatabaseMissing('student_marks', [
            'course_assessment_sheet_id' => $this->draftSheet->id,
            'student_id' => $this->student1->id,
        ]);

        $response = $this->actingAs($this->instructor)
            ->get(route('assessment.edit', $this->draftSheet));

        $response->assertStatus(200);
        $this->assertDatabaseHas('student_marks', [
            'course_assessment_sheet_id' => $this->draftSheet->id,
            'student_id' => $this->student1->id,
            'course_registration_item_id' => $regItem->id,
        ]);
    }

    public function test_lecturer_can_save_marks_as_draft(): void
    {
        $item1 = $this->registerStudent($this->student1);
        $item2 = $this->registerStudent($this->student2);

        $mark1 = StudentMark::create([
            'course_assessment_sheet_id' => $this->draftSheet->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student1->id,
        ]);

        $mark2 = StudentMark::create([
            'course_assessment_sheet_id' => $this->draftSheet->id,
            'course_registration_item_id' => $item2->id,
            'student_id' => $this->student2->id,
        ]);

        $payload = [
            'action' => 'save_draft',
            'marks' => [
                [
                    'student_mark_id' => $mark1->id,
                    'ca_score' => 34.0,
                    'exam_score' => 48.0,
                    'lecturer_remarks' => 'Superb algorithms mastery',
                ],
                [
                    'student_mark_id' => $mark2->id,
                    'ca_score' => 22.0,
                    'exam_score' => 25.0,
                    'lecturer_remarks' => 'Needs revision in graph traversals',
                ],
            ],
        ];

        $response = $this->actingAs($this->instructor)
            ->put(route('assessment.update', $this->draftSheet), $payload);

        $response->assertRedirect(route('assessment.edit', $this->draftSheet));
        $response->assertSessionHas('success');

        // Check Student 1: 34 + 48 = 82 -> A, 5.0 GP, Passed
        $mark1->refresh();
        $this->assertEquals(34.0, (float) $mark1->ca_score);
        $this->assertEquals(48.0, (float) $mark1->exam_score);
        $this->assertEquals(82.0, (float) $mark1->final_score);
        $this->assertEquals('A', $mark1->grade_letter);
        $this->assertEquals(5.0, (float) $mark1->grade_point);
        $this->assertTrue($mark1->is_passed);
        $this->assertFalse($mark1->is_retake);

        // Check Student 2: 22 + 25 = 47 -> F, 0.0 GP, Retake
        $mark2->refresh();
        $this->assertEquals(22.0, (float) $mark2->ca_score);
        $this->assertEquals(25.0, (float) $mark2->exam_score);
        $this->assertEquals(47.0, (float) $mark2->final_score);
        $this->assertEquals('F', $mark2->grade_letter);
        $this->assertEquals(0.0, (float) $mark2->grade_point);
        $this->assertFalse($mark2->is_passed);
        $this->assertTrue($mark2->is_retake);

        // Sheet status remains draft
        $this->draftSheet->refresh();
        $this->assertEquals('draft', $this->draftSheet->status);
    }

    public function test_lecturer_can_save_partial_marks_without_finalizing_grade(): void
    {
        $item1 = $this->registerStudent($this->student1);

        $mark1 = StudentMark::create([
            'course_assessment_sheet_id' => $this->draftSheet->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student1->id,
        ]);

        $payload = [
            'action' => 'save_draft',
            'marks' => [
                [
                    'student_mark_id' => $mark1->id,
                    'ca_score' => 32.5,
                    'exam_score' => null,
                    'lecturer_remarks' => 'Coursework tests graded',
                ],
            ],
        ];

        $response = $this->actingAs($this->instructor)
            ->put(route('assessment.update', $this->draftSheet), $payload);

        $response->assertRedirect(route('assessment.edit', $this->draftSheet));

        $mark1->refresh();
        $this->assertEquals(32.5, (float) $mark1->ca_score);
        $this->assertNull($mark1->exam_score);
        $this->assertNull($mark1->final_score);
        $this->assertNull($mark1->grade_letter);
        $this->assertNull($mark1->grade_point);
        $this->assertFalse($mark1->is_passed);
        $this->assertFalse($mark1->is_retake);
    }

    public function test_lecturer_can_submit_marks_to_hod_from_entry_form(): void
    {
        $item1 = $this->registerStudent($this->student1);

        $mark1 = StudentMark::create([
            'course_assessment_sheet_id' => $this->draftSheet->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student1->id,
        ]);

        $payload = [
            'action' => 'submit_hod',
            'marks' => [
                [
                    'student_mark_id' => $mark1->id,
                    'ca_score' => 36.0,
                    'exam_score' => 45.0,
                    'lecturer_remarks' => 'Final marks ready for moderation',
                ],
            ],
        ];

        $response = $this->actingAs($this->instructor)
            ->put(route('assessment.update', $this->draftSheet), $payload);

        $response->assertRedirect(route('assessment.show', $this->draftSheet));
        $response->assertSessionHas('success');

        $this->draftSheet->refresh();
        $this->assertEquals('submitted_to_hod', $this->draftSheet->status);
        $this->assertNotNull($this->draftSheet->submitted_at);

        $mark1->refresh();
        $this->assertEquals(81.0, (float) $mark1->final_score);
    }

    public function test_marks_cannot_exceed_configured_ca_and_exam_weights(): void
    {
        $item1 = $this->registerStudent($this->student1);

        $mark1 = StudentMark::create([
            'course_assessment_sheet_id' => $this->draftSheet->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student1->id,
        ]);

        // CA weight is 40.0, Exam weight is 60.0
        $payload = [
            'action' => 'save_draft',
            'marks' => [
                [
                    'student_mark_id' => $mark1->id,
                    'ca_score' => 45.0, // Exceeds 40
                    'exam_score' => 65.0, // Exceeds 60
                ],
            ],
        ];

        $response = $this->actingAs($this->instructor)
            ->from(route('assessment.edit', $this->draftSheet))
            ->put(route('assessment.update', $this->draftSheet), $payload);

        $response->assertSessionHasErrors(['marks.0.ca_score', 'marks.0.exam_score']);
    }

    public function test_marks_cannot_be_negative(): void
    {
        $item1 = $this->registerStudent($this->student1);

        $mark1 = StudentMark::create([
            'course_assessment_sheet_id' => $this->draftSheet->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student1->id,
        ]);

        $payload = [
            'action' => 'save_draft',
            'marks' => [
                [
                    'student_mark_id' => $mark1->id,
                    'ca_score' => -5.0,
                    'exam_score' => -10.0,
                ],
            ],
        ];

        $response = $this->actingAs($this->instructor)
            ->from(route('assessment.edit', $this->draftSheet))
            ->put(route('assessment.update', $this->draftSheet), $payload);

        $response->assertSessionHasErrors(['marks.0.ca_score', 'marks.0.exam_score']);
    }

    public function test_audit_logs_are_recorded_when_scores_are_changed(): void
    {
        $item1 = $this->registerStudent($this->student1);

        $mark1 = StudentMark::create([
            'course_assessment_sheet_id' => $this->draftSheet->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student1->id,
            'ca_score' => 30.0,
            'exam_score' => 40.0,
            'final_score' => 70.0,
            'grade_letter' => 'B',
            'grade_point' => 4.0,
            'is_passed' => true,
        ]);

        // Revise CA from 30 -> 35, and Exam from 40 -> 45
        $payload = [
            'action' => 'save_draft',
            'marks' => [
                [
                    'student_mark_id' => $mark1->id,
                    'ca_score' => 35.0,
                    'exam_score' => 45.0,
                    'lecturer_remarks' => 'Section C remarking adjustment',
                ],
            ],
        ];

        $response = $this->actingAs($this->instructor)
            ->put(route('assessment.update', $this->draftSheet), $payload);

        $response->assertRedirect(route('assessment.edit', $this->draftSheet));

        $this->assertDatabaseHas('grade_audit_logs', [
            'student_mark_id' => $mark1->id,
            'changed_by_id' => $this->instructor->id,
            'score_type' => 'ca',
            'old_score' => 30.0,
            'new_score' => 35.0,
        ]);

        $this->assertDatabaseHas('grade_audit_logs', [
            'student_mark_id' => $mark1->id,
            'changed_by_id' => $this->instructor->id,
            'score_type' => 'exam',
            'old_score' => 40.0,
            'new_score' => 45.0,
        ]);
    }

    public function test_lecturer_can_submit_to_hod_via_dedicated_submit_endpoint(): void
    {
        $response = $this->actingAs($this->instructor)
            ->post(route('assessment.submit', $this->draftSheet));

        $response->assertRedirect(route('assessment.show', $this->draftSheet));
        $response->assertSessionHas('success');

        $this->draftSheet->refresh();
        $this->assertEquals('submitted_to_hod', $this->draftSheet->status);
        $this->assertNotNull($this->draftSheet->submitted_at);
    }

    public function test_cannot_submit_already_submitted_or_published_sheet(): void
    {
        $this->draftSheet->update(['status' => 'department_moderated']);

        $response = $this->actingAs($this->instructor)
            ->post(route('assessment.submit', $this->draftSheet));

        $response->assertRedirect(route('assessment.show', $this->draftSheet));
        $response->assertSessionHas('warning');

        $this->draftSheet->refresh();
        $this->assertEquals('department_moderated', $this->draftSheet->status);
    }

    public function test_action_buttons_displayed_on_index_and_show_views_when_sheet_is_editable(): void
    {
        $responseIndex = $this->actingAs($this->instructor)
            ->get(route('assessment.list'));

        $responseIndex->assertStatus(200);
        $responseIndex->assertSee(route('assessment.edit', $this->draftSheet));

        $responseShow = $this->actingAs($this->instructor)
            ->get(route('assessment.show', $this->draftSheet));

        $responseShow->assertStatus(200);
        $responseShow->assertSee('Enter / Edit Marks');
        $responseShow->assertSee('Submit to HoD');
    }

    public function test_guest_can_save_marks_in_demo_environment(): void
    {
        $item1 = $this->registerStudent($this->student1);

        $mark1 = StudentMark::create([
            'course_assessment_sheet_id' => $this->draftSheet->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student1->id,
        ]);

        $payload = [
            'action' => 'save_draft',
            'marks' => [
                [
                    'student_mark_id' => $mark1->id,
                    'ca_score' => 30.0,
                    'exam_score' => 45.0,
                    'lecturer_remarks' => 'Saved in unauthenticated mode',
                ],
            ],
        ];

        // Perform request WITHOUT actingAs()
        $response = $this->put(route('assessment.update', $this->draftSheet), $payload);

        $response->assertRedirect(route('assessment.edit', $this->draftSheet));
        $response->assertSessionHas('success');

        $mark1->refresh();
        $this->assertEquals(75.0, (float) $mark1->final_score);
    }
}
