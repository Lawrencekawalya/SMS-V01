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
use App\Models\StudentSemesterPerformance;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentResultsAndTranscriptsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected AcademicYear $academicYear;

    protected Semester $semester1;

    protected Semester $semester2;

    protected Department $department;

    protected Programme $programme;

    protected CourseUnit $course1;

    protected CourseUnit $course2;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        GradingScaleTier::seedDefaults();
        AwardClassification::seedDefaults();

        $university = University::factory()->create([
            'name' => 'Bishop Stuart University',
        ]);
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create([
            'campus_id' => $campus->id,
            'code' => 'FSC',
        ]);
        $this->department = Department::factory()->create(['faculty_id' => $faculty->id]);

        $this->programme = Programme::factory()->create([
            'department_id' => $this->department->id,
            'name' => 'Bachelor of Science in Computer Science',
            'code' => 'BSCS',
            'award_type' => 'bachelors',
        ]);

        Curriculum::factory()->create([
            'programme_id' => $this->programme->id,
            'is_active' => true,
        ]);

        $this->academicYear = AcademicYear::factory()->create([
            'name' => '2026/2027',
            'is_current' => true,
        ]);

        $this->semester1 = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester 1',
            'semester_number' => 1,
            'is_active' => true,
        ]);

        $this->semester2 = Semester::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Semester 2',
            'semester_number' => 2,
            'is_active' => false,
        ]);

        $this->course1 = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC1101',
            'name' => 'Structured Programming with C',
            'credit_units' => 4.0,
            'status' => 'active',
        ]);

        $this->course2 = CourseUnit::factory()->create([
            'department_id' => $this->department->id,
            'code' => 'CSC1102',
            'name' => 'Computer Architecture & Systems',
            'credit_units' => 3.0,
            'status' => 'active',
        ]);

        $this->adminUser = User::factory()->create([
            'name' => 'Dean of Science',
            'email' => 'dean@university.ac.ug',
        ]);
        $this->actingAs($this->adminUser);

        $studentUser = User::factory()->create([
            'name' => 'Alice Kemigisha',
            'email' => 'alice@student.bsu.ac.ug',
        ]);

        $this->student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'programme_id' => $this->programme->id,
            'admission_academic_year_id' => $this->academicYear->id,
            'registration_number' => '24/BSU/BSCS/001',
            'student_number' => '24001001',
            'cumulative_gpa' => 4.50,
            'status' => 'active',
        ]);
    }

    public function test_results_directory_page_renders_with_metrics_and_student_roster(): void
    {
        $response = $this->get(route('result.list'));

        $response->assertOk();
        $response->assertViewIs('academic.assessments.results.index');
        $response->assertSee('Student Results &amp; Academic Transcripts', false);
        $response->assertSee('Alice Kemigisha');
        $response->assertSee('24/BSU/BSCS/001');
        $response->assertSee('4.50');
        $response->assertSee('Transcript');
    }

    public function test_results_directory_filters_by_search_query(): void
    {
        $otherStudentUser = User::factory()->create(['name' => 'Bob Muhwezi']);
        $otherStudent = Student::factory()->create([
            'user_id' => $otherStudentUser->id,
            'programme_id' => $this->programme->id,
            'registration_number' => '24/BSU/BSCS/099',
        ]);

        $response = $this->get(route('result.list', ['search' => 'Kemigisha']));

        $response->assertOk();
        $response->assertSee('Alice Kemigisha');
        $response->assertDontSee('Bob Muhwezi');
    }

    public function test_semester_result_slip_renders_with_marks_and_gpa_summary(): void
    {
        // 1. Create registration and items
        $reg = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'confirmed',
        ]);

        $item1 = CourseRegistrationItem::create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $this->course1->id,
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $item2 = CourseRegistrationItem::create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $this->course2->id,
            'credit_units' => 3.0,
            'status' => 'registered',
        ]);

        // 2. Create assessment sheets and marks
        $sheet1 = CourseAssessmentSheet::create([
            'course_unit_id' => $this->course1->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'pass_mark' => 50.0,
            'status' => 'published',
        ]);

        $sheet2 = CourseAssessmentSheet::create([
            'course_unit_id' => $this->course2->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'pass_mark' => 50.0,
            'status' => 'published',
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet1->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student->id,
            'ca_score' => 35.0,
            'exam_score' => 50.0,
            'final_score' => 85.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
            'is_retake' => false,
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet2->id,
            'course_registration_item_id' => $item2->id,
            'student_id' => $this->student->id,
            'ca_score' => 30.0,
            'exam_score' => 46.0,
            'final_score' => 76.0,
            'grade_letter' => 'B+',
            'grade_point' => 4.5,
            'is_passed' => true,
            'is_retake' => false,
        ]);

        // 3. Create performance record
        // (4*5.0 + 3*4.5) / 7 = (20 + 13.5) / 7 = 33.5 / 7 = 4.79
        StudentSemesterPerformance::create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'credit_units_registered' => 7.0,
            'credit_units_earned' => 7.0,
            'weighted_grade_points' => 33.5,
            'gpa' => 4.79,
            'cumulative_credit_units_registered' => 7.0,
            'cumulative_credit_units_earned' => 7.0,
            'cumulative_weighted_grade_points' => 33.5,
            'cgpa' => 4.79,
            'academic_standing' => 'Normal Progress',
        ]);

        $response = $this->get(route('result.slip', [
            'student' => $this->student,
            'semester' => $this->semester1,
        ]));

        $response->assertOk();
        $response->assertViewIs('academic.assessments.results.slip');
        $response->assertSee('Official Semester Result Slip');
        $response->assertSee('Alice Kemigisha');
        $response->assertSee('24/BSU/BSCS/001');
        $response->assertSee('CSC1101');
        $response->assertSee('Structured Programming with C');
        $response->assertSee('CSC1102');
        $response->assertSee('Computer Architecture &amp; Systems', false);
        $response->assertSee('4.79');
        $response->assertSee('Normal Progress');
        $response->assertSee('Academic Registrar');
        $response->assertSee('Head of Department');
    }

    public function test_semester_result_slip_auto_computes_performance_if_not_cached(): void
    {
        $reg = CourseRegistration::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'confirmed',
        ]);

        $item1 = CourseRegistrationItem::create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $this->course1->id,
            'credit_units' => 4.0,
            'status' => 'registered',
        ]);

        $sheet = CourseAssessmentSheet::create([
            'course_unit_id' => $this->course1->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'published',
        ]);

        StudentMark::create([
            'course_assessment_sheet_id' => $sheet->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student->id,
            'ca_score' => 32.0,
            'exam_score' => 48.0,
            'final_score' => 80.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
        ]);

        // Note: No StudentSemesterPerformance pre-created
        $this->assertDatabaseMissing('student_semester_performances', [
            'student_id' => $this->student->id,
            'semester_id' => $this->semester1->id,
        ]);

        $response = $this->get(route('result.slip', [
            'student' => $this->student,
            'semester' => $this->semester1,
        ]));

        $response->assertOk();
        // Should have automatically computed and stored performance record
        $this->assertDatabaseHas('student_semester_performances', [
            'student_id' => $this->student->id,
            'semester_id' => $this->semester1->id,
            'gpa' => 5.00,
            'academic_standing' => 'Normal Progress',
        ]);
    }

    public function test_cumulative_academic_transcript_renders_all_semesters_and_award_classification(): void
    {
        // Semester 1 Performance (4.80 CGPA -> First Class Honours)
        StudentSemesterPerformance::create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester1->id,
            'academic_year_id' => $this->academicYear->id,
            'credit_units_registered' => 18.0,
            'credit_units_earned' => 18.0,
            'weighted_grade_points' => 86.4,
            'gpa' => 4.80,
            'cumulative_credit_units_registered' => 18.0,
            'cumulative_credit_units_earned' => 18.0,
            'cumulative_weighted_grade_points' => 86.4,
            'cgpa' => 4.80,
            'academic_standing' => 'Normal Progress',
        ]);

        // Semester 2 Performance
        StudentSemesterPerformance::create([
            'student_id' => $this->student->id,
            'semester_id' => $this->semester2->id,
            'academic_year_id' => $this->academicYear->id,
            'credit_units_registered' => 18.0,
            'credit_units_earned' => 18.0,
            'weighted_grade_points' => 82.8,
            'gpa' => 4.60,
            'cumulative_credit_units_registered' => 36.0,
            'cumulative_credit_units_earned' => 36.0,
            'cumulative_weighted_grade_points' => 169.2,
            'cgpa' => 4.70,
            'academic_standing' => 'Normal Progress',
        ]);

        $this->student->update(['cumulative_gpa' => 4.70]);

        $response = $this->get(route('result.transcript', $this->student));

        $response->assertOk();
        $response->assertViewIs('academic.assessments.results.transcript');
        $response->assertSee('Official Cumulative Academic Transcript');
        $response->assertSee('ALICE KEMIGISHA');
        $response->assertSee('24/BSU/BSCS/001');
        $response->assertSee('SEMESTER 1');
        $response->assertSee('SEMESTER 2');
        $response->assertSee('FIRST CLASS HONOURS');
        $response->assertSee('Normal Progress');
        $response->assertSee('Academic Registrar');
        $response->assertSee('OFFICIAL<br>UNIVERSITY<br>SEAL', false);
    }
}
