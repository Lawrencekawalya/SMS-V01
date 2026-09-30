<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CourseRegistration;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\StudentSemesterPerformance;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SenateBroadSheetController extends Controller
{
    /**
     * Display the Master Senate Broad-Sheet ledger.
     */
    public function index(Request $request): View
    {
        $matrixData = $this->buildBroadSheetMatrix($request);

        return view('academic.reports.broad-sheet', $matrixData);
    }

    /**
     * Export the Master Senate Broad-Sheet as a CSV spreadsheet.
     */
    public function export(Request $request): StreamedResponse
    {
        $matrixData = $this->buildBroadSheetMatrix($request);

        $programme = $matrixData['selectedProgramme'];
        $semester = $matrixData['selectedSemester'];
        $studyYear = $matrixData['selectedStudyYear'];
        $courseUnits = $matrixData['courseUnits'];
        $studentsMatrix = $matrixData['studentsMatrix'];

        $fileName = sprintf(
            'senate_broad_sheet_%s_Y%dS%d_%s.csv',
            $programme ? $programme->code : 'ALL',
            $studyYear,
            $semester ? $semester->semester_number : 1,
            now()->format('Ymd_His')
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($courseUnits, $studentsMatrix, $programme, $semester, $studyYear) {
            $handle = fopen('php://output', 'w');

            // BOM for UTF-8 Excel support
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Report Header metadata
            fputcsv($handle, ['OFFICIAL SENATE MASTER BROAD-SHEET']);
            fputcsv($handle, ['Programme:', $programme ? $programme->name.' ('.$programme->code.')' : 'All Programmes']);
            fputcsv($handle, ['Academic Term:', $semester ? $semester->name.' ('.$semester->academicYear->name.')' : 'N/A', 'Study Year:', 'Year '.$studyYear]);
            fputcsv($handle, ['Generated At:', now()->format('d M Y H:i')]);
            fputcsv($handle, []); // Blank separator row

            // Table Header row
            $headerRow = ['#', 'Student Reg No', 'Student ID', 'Student Name', 'Stage'];
            foreach ($courseUnits as $cu) {
                $headerRow[] = "{$cu->code} ({$cu->credit_units} CU) Score";
                $headerRow[] = "{$cu->code} Grade";
                $headerRow[] = "{$cu->code} GP";
            }
            $headerRow[] = 'Registered CU';
            $headerRow[] = 'Earned CU';
            $headerRow[] = 'Total WGP';
            $headerRow[] = 'Semester GPA';
            $headerRow[] = 'Cumulative CGPA';
            $headerRow[] = 'Academic Standing';
            $headerRow[] = 'Remarks / Retakes';

            fputcsv($handle, $headerRow);

            // Student rows
            foreach ($studentsMatrix as $index => $row) {
                $student = $row['student'];
                $perf = $row['performance'];
                $marksMap = $row['marks'];

                $dataRow = [
                    $index + 1,
                    $student->registration_number,
                    $student->student_number,
                    $student->user->name ?? $student->full_name,
                    "Year {$row['study_year']}, Sem {$row['semester_number']}",
                ];

                foreach ($courseUnits as $cu) {
                    $mark = $marksMap[$cu->id] ?? null;
                    if ($mark && $mark['final_score'] !== null) {
                        $dataRow[] = number_format($mark['final_score'], 1);
                        $dataRow[] = $mark['grade_letter'];
                        $dataRow[] = number_format($mark['grade_point'], 1);
                    } else {
                        $dataRow[] = '-';
                        $dataRow[] = '-';
                        $dataRow[] = '-';
                    }
                }

                $dataRow[] = number_format($row['credits_registered'], 1);
                $dataRow[] = number_format($row['credits_earned'], 1);
                $dataRow[] = number_format($row['weighted_points'], 1);
                $dataRow[] = number_format($row['gpa'], 2);
                $dataRow[] = number_format($row['cgpa'], 2);
                $dataRow[] = $row['standing'];
                $dataRow[] = ! empty($row['failed_courses']) ? 'Retake: '.implode(', ', $row['failed_courses']) : 'Pass';

                fputcsv($handle, $dataRow);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Compute and compile the 2D cross-tabular matrix.
     *
     * @return array<string, mixed>
     */
    protected function buildBroadSheetMatrix(Request $request): array
    {
        $programmes = Programme::where('status', 'active')->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::with('academicYear')->orderByDesc('start_date')->get();

        $selectedProgrammeId = $request->query('programme_id') ?? $programmes->first()?->id;
        $selectedAcademicYearId = $request->query('academic_year_id')
            ?? (AcademicYear::where('is_current', true)->first()?->id ?? $academicYears->first()?->id);
        $selectedSemesterId = $request->query('semester_id')
            ?? (Semester::where('is_active', true)->first()?->id ?? $semesters->first()?->id);
        $selectedStudyYear = (int) ($request->query('study_year') ?? 1);

        $selectedProgramme = Programme::with('department.faculty')->find($selectedProgrammeId);
        $selectedAcademicYear = AcademicYear::find($selectedAcademicYearId);
        $selectedSemester = Semester::with('academicYear')->find($selectedSemesterId);

        $studentsMatrix = [];
        $courseUnits = collect();
        $courseStats = [];
        $cohortStats = [
            'total_students' => 0,
            'normal_progress_count' => 0,
            'probation_count' => 0,
            'cohort_average_gpa' => 0.0,
            'cohort_average_cgpa' => 0.0,
        ];

        if ($selectedProgramme && $selectedSemester) {
            // 1. Fetch all students registered in this cohort
            $registrations = CourseRegistration::with([
                'student.user',
                'items.courseUnit',
            ])
                ->whereHas('student', function ($q) use ($selectedProgrammeId) {
                    $q->where('programme_id', $selectedProgrammeId);
                })
                ->where('semester_id', $selectedSemester->id)
                ->where('study_year', $selectedStudyYear)
                ->whereIn('status', ['approved', 'confirmed', 'submitted'])
                ->get();

            $cohortStudentIds = $registrations->pluck('student_id')->unique()->toArray();

            // Also check any students with marks in this semester & study year
            if (empty($cohortStudentIds)) {
                $cohortStudentIds = Student::where('programme_id', $selectedProgrammeId)
                    ->where('current_study_year', $selectedStudyYear)
                    ->pluck('id')
                    ->toArray();
            }

            // 2. Discover all course units active for this cohort stage
            // A: From curriculum course mapping
            $curriculumCourseUnits = CourseUnit::whereHas('curriculumCourses', function ($q) use ($selectedProgrammeId, $selectedStudyYear, $selectedSemester) {
                $q->whereHas('curriculum', fn ($cq) => $cq->where('programme_id', $selectedProgrammeId))
                    ->where('study_year', $selectedStudyYear)
                    ->where('semester', $selectedSemester->semester_number);
            })->get();

            // B: From registered items
            $registeredCourseUnitIds = CourseRegistrationItem::whereIn('course_registration_id', $registrations->pluck('id'))
                ->pluck('course_unit_id')
                ->unique();
            $registeredCourseUnits = CourseUnit::whereIn('id', $registeredCourseUnitIds)->get();

            // C: From assessment sheets in this semester
            $sheetCourseUnitIds = StudentMark::whereIn('student_id', $cohortStudentIds)
                ->whereHas('courseAssessmentSheet', fn ($sq) => $sq->where('semester_id', $selectedSemester->id))
                ->with('courseAssessmentSheet')
                ->get()
                ->pluck('courseAssessmentSheet.course_unit_id')
                ->unique();
            $sheetCourseUnits = CourseUnit::whereIn('id', $sheetCourseUnitIds)->get();

            $courseUnits = $curriculumCourseUnits->concat($registeredCourseUnits)->concat($sheetCourseUnits)->unique('id')->values();

            // 3. Eager load all student marks and semester performances
            $marks = StudentMark::whereIn('student_id', $cohortStudentIds)
                ->whereHas('courseAssessmentSheet', fn ($sq) => $sq->where('semester_id', $selectedSemester->id))
                ->with('courseAssessmentSheet')
                ->get();

            $performances = StudentSemesterPerformance::whereIn('student_id', $cohortStudentIds)
                ->where('semester_id', $selectedSemester->id)
                ->get()
                ->keyBy('student_id');

            $students = Student::with('user')
                ->whereIn('id', $cohortStudentIds)
                ->orderBy('registration_number')
                ->get();

            // 4. Construct 2D Matrix Rows
            $totalGpaSum = 0.0;
            $totalCgpaSum = 0.0;
            $gradedStudentCount = 0;

            foreach ($students as $student) {
                $studentReg = $registrations->firstWhere('student_id', $student->id);
                $studentMarks = $marks->where('student_id', $student->id);
                $perf = $performances->get($student->id);

                $marksMap = [];
                $failedCourses = [];
                $computedRegCredits = 0.0;
                $computedEarnedCredits = 0.0;
                $computedWgp = 0.0;

                foreach ($studentMarks as $sm) {
                    $cuId = $sm->courseAssessmentSheet?->course_unit_id;
                    if (! $cuId) {
                        continue;
                    }

                    $cu = $courseUnits->firstWhere('id', $cuId);
                    $cuCredits = (float) ($cu?->credit_units ?? 0.0);
                    $finalScore = $sm->final_score !== null ? (float) $sm->final_score : null;
                    $gp = $sm->grade_point !== null ? (float) $sm->grade_point : 0.0;
                    $isPassed = (bool) $sm->is_passed;

                    $marksMap[$cuId] = [
                        'final_score' => $finalScore,
                        'grade_letter' => $sm->grade_letter,
                        'grade_point' => $gp,
                        'is_passed' => $isPassed,
                    ];

                    if ($finalScore !== null) {
                        $computedRegCredits += $cuCredits;
                        $computedWgp += ($cuCredits * $gp);
                        if ($isPassed) {
                            $computedEarnedCredits += $cuCredits;
                        } else {
                            $failedCourses[] = $cu?->code ?? 'Course';
                        }
                    }
                }

                $regCredits = (float) ($perf?->credit_units_registered ?? $computedRegCredits);
                $earnedCredits = (float) ($perf?->credit_units_earned ?? $computedEarnedCredits);
                $wgp = (float) ($perf?->weighted_grade_points ?? $computedWgp);
                $gpa = (float) ($perf?->gpa ?? ($regCredits > 0 ? round($wgp / $regCredits, 2) : 0.0));
                $cgpa = (float) ($perf?->cgpa ?? ($student->cumulative_gpa > 0 ? $student->cumulative_gpa : $gpa));
                $standing = $perf?->academic_standing ?? ($cgpa >= 2.0 ? 'Normal Progress' : 'Probation');

                if ($gpa > 0) {
                    $totalGpaSum += $gpa;
                    $totalCgpaSum += $cgpa;
                    $gradedStudentCount++;
                }

                if ($standing === 'Normal Progress') {
                    $cohortStats['normal_progress_count']++;
                } else {
                    $cohortStats['probation_count']++;
                }

                $studentsMatrix[] = [
                    'student' => $student,
                    'registration' => $studentReg,
                    'study_year' => $studentReg?->study_year ?? $selectedStudyYear,
                    'semester_number' => $studentReg?->semester_number ?? $selectedSemester->semester_number,
                    'marks' => $marksMap,
                    'performance' => $perf,
                    'credits_registered' => $regCredits,
                    'credits_earned' => $earnedCredits,
                    'weighted_points' => $wgp,
                    'gpa' => $gpa,
                    'cgpa' => $cgpa,
                    'standing' => $standing,
                    'failed_courses' => $failedCourses,
                ];
            }

            $cohortStats['total_students'] = count($students);
            if ($gradedStudentCount > 0) {
                $cohortStats['cohort_average_gpa'] = round($totalGpaSum / $gradedStudentCount, 2);
                $cohortStats['cohort_average_cgpa'] = round($totalCgpaSum / $gradedStudentCount, 2);
            }

            // 5. Compute per-course statistics for matrix footer
            foreach ($courseUnits as $cu) {
                $scores = [];
                $passedCount = 0;
                foreach ($studentsMatrix as $row) {
                    $m = $row['marks'][$cu->id] ?? null;
                    if ($m && $m['final_score'] !== null) {
                        $scores[] = (float) $m['final_score'];
                        if ($m['is_passed']) {
                            $passedCount++;
                        }
                    }
                }

                $count = count($scores);
                $courseStats[$cu->id] = [
                    'evaluated_count' => $count,
                    'average' => $count > 0 ? round(array_sum($scores) / $count, 1) : 0.0,
                    'pass_rate' => $count > 0 ? round(($passedCount / $count) * 100, 1) : 0.0,
                    'highest' => ! empty($scores) ? max($scores) : 0.0,
                    'lowest' => ! empty($scores) ? min($scores) : 0.0,
                ];
            }
        }

        $university = University::first();

        return [
            'programmes' => $programmes,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'selectedProgramme' => $selectedProgramme,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedSemester' => $selectedSemester,
            'selectedStudyYear' => $selectedStudyYear,
            'courseUnits' => $courseUnits,
            'studentsMatrix' => $studentsMatrix,
            'courseStats' => $courseStats,
            'cohortStats' => $cohortStats,
            'university' => $university,
        ];
    }
}
