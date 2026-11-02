<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\CourseAssessmentSheet;
use App\Models\CourseRegistration;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\University;
use App\Models\User;
use App\Services\GradingEngineService;
use Database\Seeders\AcademicCalendarSeeder;
use Database\Seeders\CampusSeeder;
use Database\Seeders\CourseUnitSeeder;
use Database\Seeders\CurriculumSeeder;
use Database\Seeders\DemoMissedPaperStudentsSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\FacultySeeder;
use Database\Seeders\GradingPolicySeeder;
use Database\Seeders\ProgrammeSeeder;
use Database\Seeders\UniversitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissedPaperAndSpecialExamTest extends TestCase
{
    use RefreshDatabase;

    protected Student $student;

    protected Semester $semester;

    protected CourseAssessmentSheet $sheet1;

    protected CourseAssessmentSheet $sheet2;

    protected CourseUnit $course1;

    protected CourseUnit $course2;

    protected User $admin;

    protected GradingEngineService $gradingEngine;

    protected function setUp(): void
    {
        parent::setUp();

        $university = University::factory()->create(['name' => 'Bishop Stuart University']);
        $campus = Campus::factory()->create(['university_id' => $university->id]);
        $faculty = Faculty::factory()->create(['campus_id' => $campus->id]);
        $department = Department::factory()->create(['faculty_id' => $faculty->id]);
        $programme = Programme::factory()->create(['department_id' => $department->id, 'code' => 'BSCS']);

        $academicYear = AcademicYear::factory()->create([
            'start_date' => now()->subMonths(6)->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
        ]);

        $this->semester = Semester::factory()->create([
            'academic_year_id' => $academicYear->id,
            'semester_number' => 1,
            'is_active' => true,
        ]);

        $this->course1 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1101',
            'credit_units' => 4.0,
        ]);

        $this->course2 = CourseUnit::factory()->create([
            'department_id' => $department->id,
            'code' => 'CSC1102',
            'credit_units' => 4.0,
        ]);

        $this->admin = User::factory()->create(['email' => 'admin@bsu.ac.ug']);

        $this->student = Student::factory()->create([
            'programme_id' => $programme->id,
            'campus_id' => $campus->id,
            'admission_academic_year_id' => $academicYear->id,
            'current_study_year' => 1,
            'current_semester' => 1,
        ]);

        $this->sheet1 = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->course1->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $academicYear->id,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'pass_mark' => 50.0,
            'status' => 'published',
        ]);

        $this->sheet2 = CourseAssessmentSheet::factory()->create([
            'course_unit_id' => $this->course2->id,
            'semester_id' => $this->semester->id,
            'academic_year_id' => $academicYear->id,
            'ca_weight' => 40.0,
            'exam_weight' => 60.0,
            'pass_mark' => 50.0,
            'status' => 'published',
        ]);

        $this->gradingEngine = app(GradingEngineService::class);
    }

    public function test_missed_exam_with_permission_remains_inc_and_does_not_penalize_gpa(): void
    {
        $reg = CourseRegistration::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->semester->academic_year_id,
            'semester_id' => $this->semester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
            'total_credits' => 8.0,
        ]);

        $item1 = CourseRegistrationItem::create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $this->course1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $item2 = CourseRegistrationItem::create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $this->course2->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        // Course 1: Sat normally -> CA: 30, Exam: 50 -> 80% (Grade A, 5.0 GP)
        StudentMark::create([
            'course_assessment_sheet_id' => $this->sheet1->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student->id,
            'ca_score' => 30.0,
            'exam_score' => 50.0,
            'final_score' => 80.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
            'is_retake' => false,
            'exam_status' => 'present',
        ]);

        // Course 2: Missed Exam with Permission (Medical grounds)
        $computed = $this->gradingEngine->computeMark(32.0, null, $this->sheet2, 'missed_permission');
        $this->assertEquals('INC', $computed['grade_letter']);
        $this->assertNull($computed['final_score']);
        $this->assertNull($computed['grade_point']);
        $this->assertFalse($computed['is_retake']);

        StudentMark::create([
            'course_assessment_sheet_id' => $this->sheet2->id,
            'course_registration_item_id' => $item2->id,
            'student_id' => $this->student->id,
            'ca_score' => $computed['ca_score'],
            'exam_score' => null,
            'final_score' => null,
            'grade_letter' => 'INC',
            'grade_point' => null,
            'is_passed' => false,
            'is_retake' => false,
            'exam_status' => 'missed_permission',
        ]);

        $perf = $this->gradingEngine->calculateSemesterGpa($this->student, $this->semester);

        // Course 2 is an approved incomplete; only Course 1 (4 CU) counts in registered divisor
        $this->assertEquals(4.0, $perf->credit_units_registered);
        $this->assertEquals(4.0, $perf->credit_units_earned);
        $this->assertEquals(5.00, $perf->gpa);
    }

    public function test_absent_without_permission_converts_to_automatic_fail_and_penalizes_gpa(): void
    {
        $reg = CourseRegistration::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->semester->academic_year_id,
            'semester_id' => $this->semester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
            'total_credits' => 8.0,
        ]);

        $item1 = CourseRegistrationItem::create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $this->course1->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $item2 = CourseRegistrationItem::create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $this->course2->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        // Course 1: Sat normally -> CA: 30, Exam: 50 -> 80% (Grade A, 5.0 GP)
        StudentMark::create([
            'course_assessment_sheet_id' => $this->sheet1->id,
            'course_registration_item_id' => $item1->id,
            'student_id' => $this->student->id,
            'ca_score' => 30.0,
            'exam_score' => 50.0,
            'final_score' => 80.0,
            'grade_letter' => 'A',
            'grade_point' => 5.0,
            'is_passed' => true,
            'is_retake' => false,
            'exam_status' => 'present',
        ]);

        // Course 2: Absent without permission (Unexcused)
        $computed = $this->gradingEngine->computeMark(24.0, 0.0, $this->sheet2, 'absent');
        $this->assertEquals('F', $computed['grade_letter']);
        $this->assertEquals(0.0, $computed['grade_point']);
        $this->assertTrue($computed['is_retake']);

        StudentMark::create([
            'course_assessment_sheet_id' => $this->sheet2->id,
            'course_registration_item_id' => $item2->id,
            'student_id' => $this->student->id,
            'ca_score' => $computed['ca_score'],
            'exam_score' => 0.0,
            'final_score' => $computed['final_score'],
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
            'exam_status' => 'absent',
        ]);

        $perf = $this->gradingEngine->calculateSemesterGpa($this->student, $this->semester);

        // Course 2 was unexcused absence; counts in registered credits (8 CU divisor) with 0.0 GP
        // GPA = (5.0 * 4 + 0.0 * 4) / 8 = 20 / 8 = 2.50
        $this->assertEquals(8.0, $perf->credit_units_registered);
        $this->assertEquals(4.0, $perf->credit_units_earned);
        $this->assertEquals(2.50, $perf->gpa);
    }

    public function test_recording_special_exam_mark_clears_inc_and_recalculates_gpa_with_audit_log(): void
    {
        $reg = CourseRegistration::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->semester->academic_year_id,
            'semester_id' => $this->semester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
            'total_credits' => 4.0,
        ]);

        $item2 = CourseRegistrationItem::create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $this->course2->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $mark = StudentMark::create([
            'course_assessment_sheet_id' => $this->sheet2->id,
            'course_registration_item_id' => $item2->id,
            'student_id' => $this->student->id,
            'ca_score' => 32.0,
            'exam_score' => null,
            'final_score' => null,
            'grade_letter' => 'INC',
            'grade_point' => null,
            'is_passed' => false,
            'is_retake' => false,
            'exam_status' => 'missed_permission',
        ]);

        // Student sits Special Examination and gets 48.0 / 60.0
        $updated = $this->gradingEngine->recordSpecialExamMark(
            $mark,
            48.0,
            'special',
            '2026-11-02',
            'Approved Special Exam on medical grounds.',
            $this->admin
        );

        $this->assertEquals(48.0, $updated->exam_score);
        $this->assertEquals(80.0, $updated->final_score);
        $this->assertEquals('A', $updated->grade_letter);
        $this->assertEquals(5.0, $updated->grade_point);
        $this->assertTrue($updated->is_passed);
        $this->assertEquals('special', $updated->sitting_type);
        $this->assertEquals('present', $updated->exam_status);

        // Audit log created
        $this->assertDatabaseHas('grade_audit_logs', [
            'student_mark_id' => $mark->id,
            'changed_by_id' => $this->admin->id,
            'score_type' => 'exam',
            'new_score' => 48.0,
        ]);

        // Performance recalculated
        $perf = $this->student->semesterPerformances()->where('semester_id', $this->semester->id)->first();
        $this->assertNotNull($perf);
        $this->assertEquals(4.0, $perf->credit_units_registered);
        $this->assertEquals(4.0, $perf->credit_units_earned);
        $this->assertEquals(5.00, $perf->gpa);
    }

    public function test_supplementary_examination_score_is_capped_at_pass_mark(): void
    {
        $reg = CourseRegistration::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->semester->academic_year_id,
            'semester_id' => $this->semester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
            'total_credits' => 4.0,
        ]);

        $item = CourseRegistrationItem::create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $this->course2->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $mark = StudentMark::create([
            'course_assessment_sheet_id' => $this->sheet2->id,
            'course_registration_item_id' => $item->id,
            'student_id' => $this->student->id,
            'ca_score' => 20.0,
            'exam_score' => 10.0,
            'final_score' => 30.0,
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'is_passed' => false,
            'is_retake' => true,
        ]);

        // Student takes Supplementary Exam and scores 55.0 on exam (total 75.0%)
        // Under supplementary capping, it is capped at Pass / C / 2.0 GP
        $updated = $this->gradingEngine->recordSpecialExamMark(
            $mark,
            55.0,
            'supplementary',
            '2026-11-02',
            'Supplementary exam session',
            $this->admin
        );

        $this->assertEquals('C', $updated->grade_letter);
        $this->assertEquals(2.0, $updated->grade_point);
        $this->assertTrue($updated->is_passed);
        $this->assertEquals('supplementary', $updated->sitting_type);
    }

    public function test_special_exams_index_and_record_route_works(): void
    {
        $reg = CourseRegistration::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->semester->academic_year_id,
            'semester_id' => $this->semester->id,
            'study_year' => 1,
            'semester_number' => 1,
            'status' => 'approved',
            'total_credits' => 4.0,
        ]);

        $item = CourseRegistrationItem::create([
            'course_registration_id' => $reg->id,
            'course_unit_id' => $this->course2->id,
            'course_type' => 'Core',
            'credit_units' => 4.0,
            'status' => 'approved',
        ]);

        $mark = StudentMark::create([
            'course_assessment_sheet_id' => $this->sheet2->id,
            'course_registration_item_id' => $item->id,
            'student_id' => $this->student->id,
            'ca_score' => 32.0,
            'exam_score' => null,
            'final_score' => null,
            'grade_letter' => 'INC',
            'grade_point' => null,
            'is_passed' => false,
            'is_retake' => false,
            'exam_status' => 'missed_permission',
        ]);

        // Access special exams index desk
        $response = $this->actingAs($this->admin)->get(route('assessment.special-exams'));
        $response->assertStatus(200);
        $response->assertSee('Special &amp; Supplementary Examinations Desk', false);

        // Submit special exam score via POST route
        $postResponse = $this->actingAs($this->admin)->post(route('assessment.mark-special-exam', $mark), [
            'special_exam_score' => 45.0,
            'sitting_type' => 'special',
            'special_exam_date' => '2026-11-02',
            'special_exam_remarks' => 'Approved by Faculty Board',
        ]);

        $postResponse->assertSessionHas('success');

        $mark->refresh();
        $this->assertEquals(45.0, $mark->exam_score);
        $this->assertEquals(77.0, $mark->final_score);
        $this->assertEquals('B+', $mark->grade_letter);
        $this->assertEquals(4.5, $mark->grade_point);
        $this->assertTrue($mark->is_passed);
        $this->assertEquals('present', $mark->exam_status);
        $this->assertEquals('special', $mark->sitting_type);
    }

    public function test_demo_missed_paper_students_seeder_runs_and_seeds_expected_demo_students(): void
    {
        $this->seed(UniversitySeeder::class);
        $this->seed(CampusSeeder::class);
        $this->seed(FacultySeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(ProgrammeSeeder::class);
        $this->seed(AcademicCalendarSeeder::class);
        $this->seed(CourseUnitSeeder::class);
        $this->seed(CurriculumSeeder::class);
        $this->seed(GradingPolicySeeder::class);
        $this->seed(DemoMissedPaperStudentsSeeder::class);

        // Kevin Mugisha (Missed with permission / Medical -> INC)
        $kevin = Student::where('registration_number', '25/BSU/BSCS/088')->first();
        $this->assertNotNull($kevin);
        $this->assertEquals('Kevin', $kevin->first_name);

        $kevinMark = StudentMark::where('student_id', $kevin->id)
            ->whereHas('courseAssessmentSheet.courseUnit', fn ($q) => $q->where('code', 'CSC1102'))
            ->first();

        $this->assertNotNull($kevinMark);
        $this->assertEquals('missed_permission', $kevinMark->exam_status);
        $this->assertEquals('INC', $kevinMark->grade_letter);
        $this->assertNull($kevinMark->final_score);

        $kevinPerf = $kevin->semesterPerformances()->first();
        $this->assertEquals(10.0, $kevinPerf->credit_units_registered); // 10 CU (4 CU INC skipped)
        $this->assertEquals(4.05, $kevinPerf->gpa);

        // Brenda Atuhaire (Absent without permission -> ABS / F)
        $brenda = Student::where('registration_number', '25/BSU/BSCS/099')->first();
        $this->assertNotNull($brenda);
        $this->assertEquals('Brenda', $brenda->first_name);

        $brendaMark = StudentMark::where('student_id', $brenda->id)
            ->whereHas('courseAssessmentSheet.courseUnit', fn ($q) => $q->where('code', 'CSC1102'))
            ->first();

        $this->assertNotNull($brendaMark);
        $this->assertEquals('absent', $brendaMark->exam_status);
        $this->assertEquals('F', $brendaMark->grade_letter);
        $this->assertEquals(0.0, $brendaMark->grade_point);
        $this->assertTrue($brendaMark->is_retake);

        $brendaPerf = $brenda->semesterPerformances()->first();
        $this->assertEquals(14.0, $brendaPerf->credit_units_registered); // 14 CU (penalized by 0.0 GP)
        $this->assertEquals(2.75, $brendaPerf->gpa);
    }
}
