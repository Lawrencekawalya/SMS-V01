<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\GradingScaleTier;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\StudentSemesterPerformance;
use App\Models\University;
use App\Services\GradingEngineService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentResultController extends Controller
{
    public function __construct(
        protected GradingEngineService $gradingEngine
    ) {}

    /**
     * Display the Student Results & Academic Transcripts directory.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $programmeId = $request->query('programme_id');
        $academicYearId = $request->query('academic_year_id');
        $semesterId = $request->query('semester_id');
        $standing = $request->query('academic_standing');

        $query = Student::with([
            'user',
            'programme.department',
            'campus',
            'semesterPerformances.semester.academicYear',
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($programmeId) {
            $query->where('programme_id', $programmeId);
        }

        if ($standing) {
            $query->whereHas('semesterPerformances', function ($q) use ($standing) {
                $q->where('academic_standing', $standing);
            });
        }

        $students = $query->orderBy('registration_number')->paginate(25)->withQueryString();

        $programmes = Programme::where('status', 'active')->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::with('academicYear')->orderByDesc('start_date')->get();

        $stats = [
            'total_students' => Student::count(),
            'normal_progress' => StudentSemesterPerformance::where('academic_standing', 'Normal Progress')->distinct('student_id')->count('student_id'),
            'probation' => StudentSemesterPerformance::where('academic_standing', 'Probation')->distinct('student_id')->count('student_id'),
            'average_cgpa' => Student::where('cumulative_gpa', '>', 0)->avg('cumulative_gpa') ?? 0.0,
        ];

        return view('academic.assessments.results.index', compact(
            'students',
            'programmes',
            'academicYears',
            'semesters',
            'search',
            'programmeId',
            'academicYearId',
            'semesterId',
            'standing',
            'stats'
        ));
    }

    /**
     * Display the official, printable Semester Result Slip for a student.
     */
    public function semesterResultSlip(Student $student, Semester $semester): View
    {
        $student->load([
            'user',
            'programme.department.faculty',
            'campus',
            'admissionAcademicYear',
        ]);

        $semester->load('academicYear');

        // Retrieve all marks registered/graded for this student in this semester
        $marks = StudentMark::where('student_id', $student->id)
            ->whereHas('courseAssessmentSheet', function ($q) use ($semester) {
                $q->where('semester_id', $semester->id);
            })
            ->with([
                'courseAssessmentSheet.courseUnit',
                'courseAssessmentSheet.instructor',
                'registrationItem',
            ])
            ->get();

        // Retrieve or compute semester performance
        $performance = StudentSemesterPerformance::where('student_id', $student->id)
            ->where('semester_id', $semester->id)
            ->first();

        if (! $performance && $marks->whereNotNull('final_score')->isNotEmpty()) {
            $performance = $this->gradingEngine->calculateSemesterGpa($student, $semester);
        }

        $university = University::first();
        $gradingScaleTiers = GradingScaleTier::ordered()->get();

        return view('academic.assessments.results.slip', compact(
            'student',
            'semester',
            'marks',
            'performance',
            'university',
            'gradingScaleTiers'
        ));
    }

    /**
     * Display the official, cumulative multi-semester Academic Transcript for a student.
     */
    public function academicTranscript(Student $student): View
    {
        $student->load([
            'user',
            'programme.department.faculty',
            'campus',
            'curriculum',
            'admissionAcademicYear',
        ]);

        $transcriptData = $this->gradingEngine->buildTranscriptData($student);

        $cumulative = $this->gradingEngine->computeCumulativeMetrics($student);
        $cumulativeCgpa = $cumulative['cgpa'];
        $awardClassification = $this->gradingEngine->resolveAwardClassification($student, $cumulativeCgpa);
        $academicStanding = $cumulative['academic_standing'];

        $summary = [
            'total_registered_credits' => $cumulative['cumulative_credits_registered'],
            'total_earned_credits' => $cumulative['cumulative_credits_earned'],
            'cumulative_weighted_points' => $cumulative['cumulative_weighted_points'],
            'cumulative_cgpa' => $cumulativeCgpa,
            'academic_standing' => $academicStanding,
            'award_classification' => $awardClassification,
        ];

        $university = University::first();
        $gradingScaleTiers = GradingScaleTier::ordered()->get();

        return view('academic.assessments.results.transcript', compact(
            'student',
            'transcriptData',
            'summary',
            'university',
            'gradingScaleTiers'
        ));
    }
}
