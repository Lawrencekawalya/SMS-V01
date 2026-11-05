<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\University;
use App\Services\GraduationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GraduationClearanceController extends Controller
{
    public function __construct(
        protected GraduationService $graduationService
    ) {}

    /**
     * Display the Graduation Candidates Clearance Directory.
     */
    public function index(Request $request): View
    {
        $programmes = Programme::where('status', 'active')->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::with('academicYear')->orderByDesc('start_date')->get();

        $currentAcademicYear = AcademicYear::where('is_current', true)->first() ?? $academicYears->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $selectedProgrammeId = $request->query('programme_id');

        // 1. Academic Year: Defaults to Current Academic Year (with option for -- All Academic Years --)
        if ($request->has('academic_year_id')) {
            $selectedAcademicYearId = $request->query('academic_year_id');
        } else {
            $selectedAcademicYearId = $currentAcademicYear?->id ?? '';
        }

        // 2. Semester: Defaults to Active Semester (with option for -- All Semesters --)
        if ($request->has('semester_id')) {
            $selectedSemesterId = $request->query('semester_id');
        } else {
            $selectedSemesterId = $activeSemester?->id ?? '';
        }

        // 3. Clearance Status: Defaults to 'cleared' (Cleared Only)
        if ($request->has('status')) {
            $rawStatus = $request->query('status');
            $filterStatus = in_array($rawStatus, ['all', '', null], true) ? 'all' : $rawStatus;
        } else {
            $filterStatus = 'cleared';
        }

        $search = $request->query('search');

        $query = Student::with(['programme.department.faculty', 'curriculum', 'user'])
            ->where('status', 'active');

        if ($selectedProgrammeId) {
            $query->where('programme_id', $selectedProgrammeId);
        }

        if ($selectedAcademicYearId) {
            $query->where(function ($q) use ($selectedAcademicYearId, $selectedSemesterId) {
                $q->whereHas('courseRegistrations', function ($rq) use ($selectedAcademicYearId, $selectedSemesterId) {
                    $rq->where('academic_year_id', $selectedAcademicYearId);
                    if ($selectedSemesterId) {
                        $rq->where('semester_id', $selectedSemesterId);
                    }
                })->orWhereHas('semesterPerformances', function ($pq) use ($selectedAcademicYearId, $selectedSemesterId) {
                    $pq->where('academic_year_id', $selectedAcademicYearId);
                    if ($selectedSemesterId) {
                        $pq->where('semester_id', $selectedSemesterId);
                    }
                });

                if (! $selectedSemesterId) {
                    $q->orWhere('admission_academic_year_id', $selectedAcademicYearId);
                }
            });
        } elseif ($selectedSemesterId) {
            $query->where(function ($q) use ($selectedSemesterId) {
                $q->whereHas('courseRegistrations', function ($rq) use ($selectedSemesterId) {
                    $rq->where('semester_id', $selectedSemesterId);
                })->orWhereHas('semesterPerformances', function ($pq) use ($selectedSemesterId) {
                    $pq->where('semester_id', $selectedSemesterId);
                });
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('registration_number')->get();

        // Audit each candidate
        $auditedCandidates = [];
        $totalCleared = 0;
        $totalDeficient = 0;

        foreach ($students as $student) {
            $audit = $this->graduationService->auditClearance($student);

            if ($audit['is_cleared']) {
                $totalCleared++;
            } else {
                $totalDeficient++;
            }

            if ($filterStatus === 'cleared' && ! $audit['is_cleared']) {
                continue;
            }
            if ($filterStatus === 'deficiencies' && $audit['is_cleared']) {
                continue;
            }

            $auditedCandidates[] = $audit;
        }

        return view('academic.graduation.index', [
            'programmes' => $programmes,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'selectedProgrammeId' => $selectedProgrammeId,
            'selectedAcademicYearId' => $selectedAcademicYearId,
            'selectedSemesterId' => $selectedSemesterId,
            'filterStatus' => $filterStatus,
            'search' => $search,
            'candidates' => $auditedCandidates,
            'stats' => [
                'total_candidates' => count($students),
                'total_cleared' => $totalCleared,
                'total_deficient' => $totalDeficient,
                'clearance_rate' => count($students) > 0 ? round(($totalCleared / count($students)) * 100, 1) : 0.0,
            ],
        ]);
    }

    /**
     * Display the Comprehensive Student Clearance Audit Sheet.
     */
    public function audit(Student $student): View
    {
        $student->load(['programme.department.faculty', 'curriculum', 'user']);
        $auditResult = $this->graduationService->auditClearance($student);
        $university = University::first();

        return view('academic.graduation.audit', [
            'student' => $student,
            'audit' => $auditResult,
            'university' => $university,
        ]);
    }

    /**
     * Display the Official Graduation Gazette / Honors Roll Booklet.
     */
    public function honorsRoll(Request $request): View
    {
        $programmes = Programme::where('status', 'active')->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::with('academicYear')->orderByDesc('start_date')->get();

        $currentAcademicYear = AcademicYear::where('is_current', true)->first() ?? $academicYears->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $selectedProgrammeId = $request->query('programme_id');

        if ($request->has('academic_year_id')) {
            $selectedAcademicYearId = $request->query('academic_year_id');
        } else {
            $selectedAcademicYearId = $currentAcademicYear?->id ?? '';
        }

        if ($request->has('semester_id')) {
            $selectedSemesterId = $request->query('semester_id');
        } else {
            $selectedSemesterId = $activeSemester?->id ?? '';
        }

        $query = Student::with(['programme.department.faculty', 'curriculum', 'user'])
            ->where('status', 'active');

        if ($selectedProgrammeId) {
            $query->where('programme_id', $selectedProgrammeId);
        }

        if ($selectedAcademicYearId) {
            $query->where(function ($q) use ($selectedAcademicYearId, $selectedSemesterId) {
                $q->whereHas('courseRegistrations', function ($rq) use ($selectedAcademicYearId, $selectedSemesterId) {
                    $rq->where('academic_year_id', $selectedAcademicYearId);
                    if ($selectedSemesterId) {
                        $rq->where('semester_id', $selectedSemesterId);
                    }
                })->orWhereHas('semesterPerformances', function ($pq) use ($selectedAcademicYearId, $selectedSemesterId) {
                    $pq->where('academic_year_id', $selectedAcademicYearId);
                    if ($selectedSemesterId) {
                        $pq->where('semester_id', $selectedSemesterId);
                    }
                });

                if (! $selectedSemesterId) {
                    $q->orWhere('admission_academic_year_id', $selectedAcademicYearId);
                }
            });
        } elseif ($selectedSemesterId) {
            $query->where(function ($q) use ($selectedSemesterId) {
                $q->whereHas('courseRegistrations', function ($rq) use ($selectedSemesterId) {
                    $rq->where('semester_id', $selectedSemesterId);
                })->orWhereHas('semesterPerformances', function ($pq) use ($selectedSemesterId) {
                    $pq->where('semester_id', $selectedSemesterId);
                });
            });
        }

        $students = $query->orderBy('registration_number')->get();

        // Audit candidates and keep cleared students
        $clearedByProgramme = [];
        $totalGraduands = 0;

        foreach ($students as $student) {
            $audit = $this->graduationService->auditClearance($student);
            if ($audit['is_cleared']) {
                $progName = $student->programme ? $student->programme->name.' ('.$student->programme->code.')' : 'General Programme';
                $award = $audit['award_classification'];

                $clearedByProgramme[$progName][$award][] = [
                    'student' => $student,
                    'audit' => $audit,
                ];

                $totalGraduands++;
            }
        }

        $university = University::first();

        return view('academic.graduation.honors-roll', [
            'programmes' => $programmes,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'selectedProgrammeId' => $selectedProgrammeId,
            'selectedAcademicYearId' => $selectedAcademicYearId,
            'selectedSemesterId' => $selectedSemesterId,
            'clearedByProgramme' => $clearedByProgramme,
            'totalGraduands' => $totalGraduands,
            'university' => $university,
        ]);
    }
}
