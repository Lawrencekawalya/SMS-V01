<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CourseAssessmentSheet;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\StudentMark;
use App\Models\StudentSemesterPerformance;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AcademicAnalyticsController extends Controller
{
    /**
     * Display the Departmental & Faculty Academic Performance Analytics Dashboard.
     */
    public function index(Request $request): View
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::with('academicYear')->orderByDesc('start_date')->get();

        $activeSemester = Semester::where('is_active', true)->with('academicYear')->first();
        $currentAcademicYear = AcademicYear::where('is_current', true)->first()
            ?? $activeSemester?->academicYear
            ?? $academicYears->first();

        $selectedAcademicYearId = $request->query('academic_year_id');
        $requestedSemesterId = $request->query('semester_id');
        $selectedSemester = null;

        if ($requestedSemesterId) {
            $selectedSemester = Semester::with('academicYear')->find($requestedSemesterId);
            if ($selectedSemester) {
                if ($selectedAcademicYearId && (int) $selectedSemester->academic_year_id !== (int) $selectedAcademicYearId) {
                    // Mismatched academic year and semester supplied.
                    // Fall back to active semester if the chosen year is current, else first semester of chosen year.
                    $selectedSemester = ($activeSemester && (int) $activeSemester->academic_year_id === (int) $selectedAcademicYearId)
                        ? $activeSemester
                        : Semester::where('academic_year_id', $selectedAcademicYearId)->orderBy('semester_number')->first();
                } else {
                    $selectedAcademicYearId = $selectedSemester->academic_year_id;
                }
            }
        }

        if (! $selectedSemester) {
            if ($selectedAcademicYearId) {
                if ($activeSemester && (int) $activeSemester->academic_year_id === (int) $selectedAcademicYearId) {
                    $selectedSemester = $activeSemester;
                } else {
                    $selectedSemester = Semester::where('academic_year_id', $selectedAcademicYearId)
                        ->orderBy('semester_number')
                        ->first();
                }
            } else {
                // By default, open when the active semester is the one selected
                $selectedSemester = $activeSemester ?? $semesters->first();
                $selectedAcademicYearId = $selectedSemester?->academic_year_id;
            }
        }

        $selectedAcademicYear = AcademicYear::find($selectedAcademicYearId)
            ?? $selectedSemester?->academicYear
            ?? $currentAcademicYear;

        $facultiesData = [];
        $gradeDistribution = [
            'A' => 0,
            'B+' => 0,
            'B' => 0,
            'C+' => 0,
            'C' => 0,
            'D+' => 0,
            'D' => 0,
            'F' => 0,
        ];
        $totalEvaluatedMarksCount = 0;
        $courseAnomalies = [];

        $institutionKpis = [
            'total_students' => 0,
            'normal_progress_count' => 0,
            'probation_count' => 0,
            'average_gpa' => 0.0,
            'pass_rate' => 0.0,
        ];

        $standingData = ['rows' => [], 'totals' => [
            'total_students' => 0,
            'normal_progress_count' => 0,
            'retakes_count' => 0,
            'missed_papers_count' => 0,
            'probation_count' => 0,
        ]];

        $honoursData = ['rows' => [], 'totals' => [
            'total_students' => 0,
            'first_class_count' => 0,
            'second_upper_count' => 0,
            'second_lower_count' => 0,
            'pass_count' => 0,
            'probation_count' => 0,
        ]];

        if ($selectedSemester) {
            // Senate Report 1 & 2 aggregations
            $standingData = $this->buildProgramStandingData($selectedSemester);
            $honoursData = $this->buildHonoursPerformanceData($selectedSemester);

            // 1. Fetch all performances for this semester
            $performances = StudentSemesterPerformance::with(['student.programme.department.faculty'])
                ->where('semester_id', $selectedSemester->id)
                ->get();

            $institutionKpis['total_students'] = $performances->count();
            $institutionKpis['normal_progress_count'] = $performances->where('academic_standing', 'Normal Progress')->count();
            $institutionKpis['probation_count'] = $performances->where('academic_standing', 'Probation')->count();
            $institutionKpis['average_gpa'] = $performances->count() > 0 ? round($performances->avg('gpa'), 2) : 0.0;

            // 2. Compute Grade Distribution for the semester
            $marks = StudentMark::whereHas('courseAssessmentSheet', function ($q) use ($selectedSemester) {
                $q->where('semester_id', $selectedSemester->id);
            })->whereNotNull('final_score')->get();

            $totalEvaluatedMarksCount = $marks->count();
            $passedMarksCount = $marks->where('is_passed', true)->count();

            if ($totalEvaluatedMarksCount > 0) {
                $institutionKpis['pass_rate'] = round(($passedMarksCount / $totalEvaluatedMarksCount) * 100, 1);
            }

            foreach ($marks as $mark) {
                $letter = strtoupper(trim((string) $mark->grade_letter));
                if (isset($gradeDistribution[$letter])) {
                    $gradeDistribution[$letter]++;
                }
            }

            // 3. Faculty & Department Level Aggregation
            $faculties = Faculty::with(['departments.programmes'])->orderBy('name')->get();

            foreach ($faculties as $faculty) {
                $facultyDeptData = [];
                $facultyTotalStudents = 0;
                $facultyGpaSum = 0.0;
                $facultyGradedStudents = 0;
                $facultyPassMarksCount = 0;
                $facultyTotalMarksCount = 0;

                foreach ($faculty->departments as $department) {
                    $programmeIds = $department->programmes->pluck('id')->toArray();

                    $deptPerformances = $performances->filter(function ($p) use ($programmeIds) {
                        return in_array($p->student?->programme_id, $programmeIds, true);
                    });

                    $deptMarks = $marks->filter(function ($m) use ($programmeIds) {
                        return in_array($m->student?->programme_id, $programmeIds, true);
                    });

                    $deptTotalMarks = $deptMarks->count();
                    $deptPassedMarks = $deptMarks->where('is_passed', true)->count();
                    $deptPassRate = $deptTotalMarks > 0 ? round(($deptPassedMarks / $deptTotalMarks) * 100, 1) : 0.0;
                    $deptAvgGpa = $deptPerformances->count() > 0 ? round($deptPerformances->avg('gpa'), 2) : 0.0;
                    $deptProbation = $deptPerformances->where('academic_standing', 'Probation')->count();
                    $deptNormal = $deptPerformances->where('academic_standing', 'Normal Progress')->count();

                    $facultyDeptData[] = [
                        'department' => $department,
                        'total_students' => $deptPerformances->count(),
                        'normal_progress' => $deptNormal,
                        'probation' => $deptProbation,
                        'average_gpa' => $deptAvgGpa,
                        'pass_rate' => $deptPassRate,
                        'total_marks' => $deptTotalMarks,
                    ];

                    $facultyTotalStudents += $deptPerformances->count();
                    $facultyPassMarksCount += $deptPassedMarks;
                    $facultyTotalMarksCount += $deptTotalMarks;
                    if ($deptPerformances->count() > 0) {
                        $facultyGpaSum += ($deptAvgGpa * $deptPerformances->count());
                        $facultyGradedStudents += $deptPerformances->count();
                    }
                }

                $facultiesData[] = [
                    'faculty' => $faculty,
                    'departments' => $facultyDeptData,
                    'total_students' => $facultyTotalStudents,
                    'average_gpa' => $facultyGradedStudents > 0 ? round($facultyGpaSum / $facultyGradedStudents, 2) : 0.0,
                    'pass_rate' => $facultyTotalMarksCount > 0 ? round(($facultyPassMarksCount / $facultyTotalMarksCount) * 100, 1) : 0.0,
                ];
            }

            // 4. Anomaly Detection Watchlist (Courses with failure rate > 30% or abnormal extremes)
            $assessmentSheets = CourseAssessmentSheet::with(['courseUnit.department.faculty', 'studentMarks'])
                ->where('semester_id', $selectedSemester->id)
                ->get();

            foreach ($assessmentSheets as $sheet) {
                $sheetMarks = $sheet->studentMarks->whereNotNull('final_score');
                $count = $sheetMarks->count();
                if ($count === 0) {
                    continue;
                }

                $passed = $sheetMarks->where('is_passed', true)->count();
                $failed = $count - $passed;
                $failureRate = round(($failed / $count) * 100, 1);
                $passRate = round(($passed / $count) * 100, 1);

                if ($failureRate > 30.0) {
                    $courseAnomalies[] = [
                        'sheet' => $sheet,
                        'course' => $sheet->courseUnit,
                        'evaluated_count' => $count,
                        'passed_count' => $passed,
                        'failed_count' => $failed,
                        'failure_rate' => $failureRate,
                        'pass_rate' => $passRate,
                        'severity' => $failureRate >= 50.0 ? 'danger' : 'warning',
                        'type' => 'High Failure Rate',
                        'message' => "High failure rate of {$failureRate}% requires departmental syllabus and assessment review.",
                    ];
                }
            }

            // Sort anomalies by failure rate descending
            usort($courseAnomalies, fn ($a, $b) => $b['failure_rate'] <=> $a['failure_rate']);
        }

        $university = University::first();

        return view('academic.reports.analytics', [
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedSemester' => $selectedSemester,
            'institutionKpis' => $institutionKpis,
            'standingData' => $standingData,
            'honoursData' => $honoursData,
            'facultiesData' => $facultiesData,
            'gradeDistribution' => $gradeDistribution,
            'totalEvaluatedMarksCount' => $totalEvaluatedMarksCount,
            'courseAnomalies' => $courseAnomalies,
            'university' => $university,
        ]);
    }

    /**
     * Build the Senate Student Standing per Programme report dataset.
     *
     * @return array{
     *     rows: array<int, array<string, mixed>>,
     *     totals: array<string, int>
     * }
     */
    public function buildProgramStandingData(Semester $semester): array
    {
        $performances = StudentSemesterPerformance::with(['student.programme.department.faculty'])
            ->where('semester_id', $semester->id)
            ->get();

        $marks = StudentMark::whereHas('courseAssessmentSheet', function ($q) use ($semester) {
            $q->where('semester_id', $semester->id);
        })->with(['registrationItem'])->get();

        $programmes = Programme::with(['department.faculty'])->orderBy('name')->get();
        $report = [];

        $totals = [
            'total_students' => 0,
            'normal_progress_count' => 0,
            'retakes_count' => 0,
            'missed_papers_count' => 0,
            'probation_count' => 0,
        ];

        foreach ($programmes as $programme) {
            $progPerfs = $performances->filter(fn ($p) => $p->student?->programme_id === $programme->id);
            if ($progPerfs->isEmpty()) {
                continue;
            }

            $totalEnrolled = $progPerfs->count();
            $normalCount = 0;
            $retakeCount = 0;
            $mpCount = 0;
            $probationCount = 0;
            $studentsList = [];

            foreach ($progPerfs as $perf) {
                $student = $perf->student;
                $studentMarks = $marks->where('student_id', $perf->student_id);

                $hasRetake = $studentMarks->contains(fn ($m) => ! (bool) $m->is_passed && ! $m->isMissedWithPermission())
                    || $studentMarks->contains(fn ($m) => $m->registrationItem?->course_type === 'Retake' || (bool) $m->is_retake);

                $hasMp = $studentMarks->contains(fn ($m) => $m->isMissedWithPermission());

                $isProbation = (float) $perf->cgpa < 2.00 || $perf->academic_standing === 'Probation';

                $isNormal = ! $hasRetake && ! $hasMp && ! $isProbation;

                if ($isNormal) {
                    $normalCount++;
                }
                if ($hasRetake) {
                    $retakeCount++;
                }
                if ($hasMp) {
                    $mpCount++;
                }
                if ($isProbation) {
                    $probationCount++;
                }

                $statusLabel = 'Normal Progress';
                if ($hasRetake) {
                    $statusLabel = 'Retake';
                } elseif ($hasMp) {
                    $statusLabel = 'Missed Papers (MP)';
                } elseif ($isProbation) {
                    $statusLabel = 'Probation';
                }

                $studentsList[] = [
                    'student' => $student,
                    'performance' => $perf,
                    'is_normal' => $isNormal,
                    'has_retake' => $hasRetake,
                    'has_mp' => $hasMp,
                    'is_probation' => $isProbation,
                    'status_label' => $statusLabel,
                ];
            }

            $avgGpa = $totalEnrolled > 0 ? round($progPerfs->avg('gpa'), 2) : 0.00;

            $report[] = [
                'programme' => $programme,
                'department' => $programme->department,
                'faculty' => $programme->department?->faculty,
                'total_students' => $totalEnrolled,
                'normal_progress_count' => $normalCount,
                'normal_progress_pct' => $totalEnrolled > 0 ? round(($normalCount / $totalEnrolled) * 100, 1) : 0.0,
                'retakes_count' => $retakeCount,
                'retakes_pct' => $totalEnrolled > 0 ? round(($retakeCount / $totalEnrolled) * 100, 1) : 0.0,
                'missed_papers_count' => $mpCount,
                'missed_papers_pct' => $totalEnrolled > 0 ? round(($mpCount / $totalEnrolled) * 100, 1) : 0.0,
                'probation_count' => $probationCount,
                'probation_pct' => $totalEnrolled > 0 ? round(($probationCount / $totalEnrolled) * 100, 1) : 0.0,
                'average_gpa' => $avgGpa,
                'students' => $studentsList,
            ];

            $totals['total_students'] += $totalEnrolled;
            $totals['normal_progress_count'] += $normalCount;
            $totals['retakes_count'] += $retakeCount;
            $totals['missed_papers_count'] += $mpCount;
            $totals['probation_count'] += $probationCount;
        }

        return [
            'rows' => $report,
            'totals' => $totals,
        ];
    }

    /**
     * Build the Senate Degree Honours & Classification Performance report dataset.
     *
     * @return array{
     *     rows: array<int, array<string, mixed>>,
     *     totals: array<string, int>
     * }
     */
    public function buildHonoursPerformanceData(Semester $semester): array
    {
        $performances = StudentSemesterPerformance::with(['student.programme.department.faculty'])
            ->where('semester_id', $semester->id)
            ->get();

        $programmes = Programme::with(['department.faculty'])->orderBy('name')->get();
        $report = [];

        $totals = [
            'total_students' => 0,
            'first_class_count' => 0,
            'second_upper_count' => 0,
            'second_lower_count' => 0,
            'pass_count' => 0,
            'probation_count' => 0,
        ];

        foreach ($programmes as $programme) {
            $progPerfs = $performances->filter(fn ($p) => $p->student?->programme_id === $programme->id);
            if ($progPerfs->isEmpty()) {
                continue;
            }

            $totalEnrolled = $progPerfs->count();
            $firstClass = 0;
            $secondUpper = 0;
            $secondLower = 0;
            $passCount = 0;
            $probationCount = 0;
            $cgpaValues = [];

            foreach ($progPerfs as $perf) {
                $cgpa = (float) $perf->cgpa;
                $cgpaValues[] = $cgpa;

                // Statutory Uganda NCHE classification boundaries
                if ($cgpa >= 4.40) {
                    $firstClass++;
                } elseif ($cgpa >= 3.60) {
                    $secondUpper++;
                } elseif ($cgpa >= 2.80) {
                    $secondLower++;
                } elseif ($cgpa >= 2.00) {
                    $passCount++;
                } else {
                    $probationCount++;
                }
            }

            $avgCgpa = count($cgpaValues) > 0 ? round(array_sum($cgpaValues) / count($cgpaValues), 2) : 0.00;
            $topCgpa = count($cgpaValues) > 0 ? max($cgpaValues) : 0.00;

            $report[] = [
                'programme' => $programme,
                'department' => $programme->department,
                'faculty' => $programme->department?->faculty,
                'total_students' => $totalEnrolled,
                'first_class_count' => $firstClass,
                'first_class_pct' => $totalEnrolled > 0 ? round(($firstClass / $totalEnrolled) * 100, 1) : 0.0,
                'second_upper_count' => $secondUpper,
                'second_upper_pct' => $totalEnrolled > 0 ? round(($secondUpper / $totalEnrolled) * 100, 1) : 0.0,
                'second_lower_count' => $secondLower,
                'second_lower_pct' => $totalEnrolled > 0 ? round(($secondLower / $totalEnrolled) * 100, 1) : 0.0,
                'pass_count' => $passCount,
                'pass_pct' => $totalEnrolled > 0 ? round(($passCount / $totalEnrolled) * 100, 1) : 0.0,
                'probation_count' => $probationCount,
                'probation_pct' => $totalEnrolled > 0 ? round(($probationCount / $totalEnrolled) * 100, 1) : 0.0,
                'average_cgpa' => $avgCgpa,
                'top_cgpa' => $topCgpa,
            ];

            $totals['total_students'] += $totalEnrolled;
            $totals['first_class_count'] += $firstClass;
            $totals['second_upper_count'] += $secondUpper;
            $totals['second_lower_count'] += $secondLower;
            $totals['pass_count'] += $passCount;
            $totals['probation_count'] += $probationCount;
        }

        return [
            'rows' => $report,
            'totals' => $totals,
        ];
    }

    /**
     * Resolve the target semester for exports with active semester fallback and reconciliation.
     */
    protected function resolveSemesterForExport(Request $request): Semester
    {
        $activeSemester = Semester::where('is_active', true)->with('academicYear')->first();
        $selectedAcademicYearId = $request->query('academic_year_id');
        $requestedSemesterId = $request->query('semester_id');

        $semester = null;
        if ($requestedSemesterId) {
            $semester = Semester::with('academicYear')->find($requestedSemesterId);
            if ($semester && $selectedAcademicYearId && (int) $semester->academic_year_id !== (int) $selectedAcademicYearId) {
                $semester = ($activeSemester && (int) $activeSemester->academic_year_id === (int) $selectedAcademicYearId)
                    ? $activeSemester
                    : Semester::where('academic_year_id', $selectedAcademicYearId)->orderBy('semester_number')->first();
            }
        }

        if (! $semester) {
            if ($selectedAcademicYearId) {
                $semester = ($activeSemester && (int) $activeSemester->academic_year_id === (int) $selectedAcademicYearId)
                    ? $activeSemester
                    : Semester::where('academic_year_id', $selectedAcademicYearId)->orderBy('semester_number')->first();
            } else {
                $semester = $activeSemester ?? Semester::orderByDesc('start_date')->first();
            }
        }

        abort_if(! $semester, 404, 'No valid semester found for export.');

        return $semester;
    }

    /**
     * Export the Senate Academic Standing by Programme report as CSV.
     */
    public function exportProgramStanding(Request $request): StreamedResponse
    {
        $semester = $this->resolveSemesterForExport($request);

        $standingData = $this->buildProgramStandingData($semester);
        $rows = $standingData['rows'];
        $totals = $standingData['totals'];

        $fileName = sprintf(
            'senate_program_standing_%s_%s.csv',
            str_replace(['/', ' '], '_', $semester->name.'_'.$semester->academicYear->name),
            now()->format('Ymd_His')
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($rows, $totals, $semester) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['OFFICIAL SENATE ACADEMIC STANDING REPORT BY PROGRAMME'], ',', '"', '\\');
            fputcsv($handle, ['Academic Session:', $semester->name.' ('.$semester->academicYear->name.')'], ',', '"', '\\');
            fputcsv($handle, ['Generated At:', now()->format('d M Y H:i')], ',', '"', '\\');
            fputcsv($handle, [], ',', '"', '\\');

            fputcsv($handle, [
                '#',
                'Programme Code',
                'Programme Name',
                'Faculty',
                'Department',
                'Enrolled Candidates',
                'Normal Progress Count',
                'Normal Progress %',
                'Retakes (RTK) Count',
                'Retakes %',
                'Missed Papers (MP) Count',
                'Missed Papers %',
                'Probation Count',
                'Probation %',
                'Mean GPA',
            ], ',', '"', '\\');

            foreach ($rows as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row['programme']->code,
                    $row['programme']->name,
                    $row['faculty']->name ?? 'N/A',
                    $row['department']->name ?? 'N/A',
                    $row['total_students'],
                    $row['normal_progress_count'],
                    $row['normal_progress_pct'].'%',
                    $row['retakes_count'],
                    $row['retakes_pct'].'%',
                    $row['missed_papers_count'],
                    $row['missed_papers_pct'].'%',
                    $row['probation_count'],
                    $row['probation_pct'].'%',
                    number_format($row['average_gpa'], 2),
                ], ',', '"', '\\');
            }

            fputcsv($handle, [], ',', '"', '\\');
            fputcsv($handle, [
                'TOTALS',
                '',
                'Institutional Aggregate',
                '',
                '',
                $totals['total_students'],
                $totals['normal_progress_count'],
                $totals['total_students'] > 0 ? round(($totals['normal_progress_count'] / $totals['total_students']) * 100, 1).'%' : '0%',
                $totals['retakes_count'],
                $totals['total_students'] > 0 ? round(($totals['retakes_count'] / $totals['total_students']) * 100, 1).'%' : '0%',
                $totals['missed_papers_count'],
                $totals['total_students'] > 0 ? round(($totals['missed_papers_count'] / $totals['total_students']) * 100, 1).'%' : '0%',
                $totals['probation_count'],
                $totals['total_students'] > 0 ? round(($totals['probation_count'] / $totals['total_students']) * 100, 1).'%' : '0%',
                '',
            ], ',', '"', '\\');

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Export the Senate Honours & Performance Classification report as CSV.
     */
    public function exportHonoursPerformance(Request $request): StreamedResponse
    {
        $semester = $this->resolveSemesterForExport($request);

        $honoursData = $this->buildHonoursPerformanceData($semester);
        $rows = $honoursData['rows'];
        $totals = $honoursData['totals'];

        $fileName = sprintf(
            'senate_honours_performance_%s_%s.csv',
            str_replace(['/', ' '], '_', $semester->name.'_'.$semester->academicYear->name),
            now()->format('Ymd_His')
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($rows, $totals, $semester) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['OFFICIAL SENATE DEGREE HONOURS CLASSIFICATION PERFORMANCE REPORT'], ',', '"', '\\');
            fputcsv($handle, ['Academic Session:', $semester->name.' ('.$semester->academicYear->name.')'], ',', '"', '\\');
            fputcsv($handle, ['Statutory Grading Standard:', 'National Council for Higher Education (NCHE) Uganda'], ',', '"', '\\');
            fputcsv($handle, ['Generated At:', now()->format('d M Y H:i')], ',', '"', '\\');
            fputcsv($handle, [], ',', '"', '\\');

            fputcsv($handle, [
                '#',
                'Programme Code',
                'Programme Name',
                'Faculty',
                'Cohort Size',
                'First Class (4.40 - 5.00) Count',
                'First Class %',
                '2nd Class Upper (3.60 - 4.39) Count',
                '2nd Class Upper %',
                '2nd Class Lower (2.80 - 3.59) Count',
                '2nd Class Lower %',
                'Pass Degree (2.00 - 2.79) Count',
                'Pass Degree %',
                'Probation (< 2.00) Count',
                'Probation %',
                'Mean CGPA',
                'Highest CGPA',
            ], ',', '"', '\\');

            foreach ($rows as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row['programme']->code,
                    $row['programme']->name,
                    $row['faculty']->name ?? 'N/A',
                    $row['total_students'],
                    $row['first_class_count'],
                    $row['first_class_pct'].'%',
                    $row['second_upper_count'],
                    $row['second_upper_pct'].'%',
                    $row['second_lower_count'],
                    $row['second_lower_pct'].'%',
                    $row['pass_count'],
                    $row['pass_pct'].'%',
                    $row['probation_count'],
                    $row['probation_pct'].'%',
                    number_format($row['average_cgpa'], 2),
                    number_format($row['top_cgpa'], 2),
                ], ',', '"', '\\');
            }

            fputcsv($handle, [], ',', '"', '\\');
            fputcsv($handle, [
                'TOTALS',
                '',
                'Institutional Aggregate',
                '',
                $totals['total_students'],
                $totals['first_class_count'],
                $totals['total_students'] > 0 ? round(($totals['first_class_count'] / $totals['total_students']) * 100, 1).'%' : '0%',
                $totals['second_upper_count'],
                $totals['total_students'] > 0 ? round(($totals['second_upper_count'] / $totals['total_students']) * 100, 1).'%' : '0%',
                $totals['second_lower_count'],
                $totals['total_students'] > 0 ? round(($totals['second_lower_count'] / $totals['total_students']) * 100, 1).'%' : '0%',
                $totals['pass_count'],
                $totals['total_students'] > 0 ? round(($totals['pass_count'] / $totals['total_students']) * 100, 1).'%' : '0%',
                $totals['probation_count'],
                $totals['total_students'] > 0 ? round(($totals['probation_count'] / $totals['total_students']) * 100, 1).'%' : '0%',
                '',
                '',
            ], ',', '"', '\\');

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
