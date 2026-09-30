<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CourseAssessmentSheet;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Semester;
use App\Models\StudentMark;
use App\Models\StudentSemesterPerformance;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicAnalyticsController extends Controller
{
    /**
     * Display the Departmental & Faculty Academic Performance Analytics Dashboard.
     */
    public function index(Request $request): View
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::with('academicYear')->orderByDesc('start_date')->get();

        $selectedAcademicYearId = $request->query('academic_year_id')
            ?? (AcademicYear::where('is_current', true)->first()?->id ?? $academicYears->first()?->id);
        $selectedSemesterId = $request->query('semester_id')
            ?? (Semester::where('is_active', true)->first()?->id ?? $semesters->first()?->id);

        $selectedAcademicYear = AcademicYear::find($selectedAcademicYearId);
        $selectedSemester = Semester::with('academicYear')->find($selectedSemesterId);

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

        if ($selectedSemester) {
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
            'facultiesData' => $facultiesData,
            'gradeDistribution' => $gradeDistribution,
            'totalEvaluatedMarksCount' => $totalEvaluatedMarksCount,
            'courseAnomalies' => $courseAnomalies,
            'university' => $university,
        ]);
    }
}
