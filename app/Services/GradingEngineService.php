<?php

namespace App\Services;

use App\Models\AwardClassification;
use App\Models\CourseAssessmentSheet;
use App\Models\GradeAuditLog;
use App\Models\GradingScaleTier;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\StudentSemesterPerformance;
use App\Models\User;
use InvalidArgumentException;

class GradingEngineService
{
    /**
     * Compute final score, letter grade, grade points, and pass/retake status.
     *
     * @return array{
     *     ca_score: ?float,
     *     exam_score: ?float,
     *     final_score: ?float,
     *     grade_letter: ?string,
     *     grade_point: ?float,
     *     classification: string,
     *     badge_class: string,
     *     is_passed: bool,
     *     is_retake: bool,
     *     exam_status: string,
     *     sitting_type: string
     * }
     */
    public function computeMark(
        ?float $caScore,
        ?float $examScore,
        ?CourseAssessmentSheet $sheet = null,
        string $examStatus = 'present',
        string $sittingType = 'main'
    ): array {
        $ca = $caScore !== null ? round(max(0.0, $caScore), 2) : null;

        // 1. Approved Absence / Missed Exam with Permission (Medical / Official)
        if ($examStatus === 'missed_permission') {
            return [
                'ca_score' => $ca,
                'exam_score' => null,
                'final_score' => null,
                'grade_letter' => 'INC',
                'grade_point' => null,
                'classification' => 'Incomplete (Approved Absence / Medical)',
                'badge_class' => 'text-bg-warning text-dark',
                'is_passed' => false,
                'is_retake' => false,
                'exam_status' => 'missed_permission',
                'sitting_type' => $sittingType,
            ];
        }

        // 2. Absent without Permission (Unexcused Absence -> Automatic 0 / F / Retake)
        if ($examStatus === 'absent') {
            $finalScore = $ca !== null ? $ca : 0.0;

            return [
                'ca_score' => $ca !== null ? $ca : 0.0,
                'exam_score' => 0.0,
                'final_score' => $finalScore,
                'grade_letter' => 'F',
                'grade_point' => 0.0,
                'classification' => 'Absent without Permission (Automatic Fail / Retake)',
                'badge_class' => 'text-bg-danger',
                'is_passed' => false,
                'is_retake' => true,
                'exam_status' => 'absent',
                'sitting_type' => $sittingType,
            ];
        }

        // 3. Normal / Present Sitting with incomplete entry
        if ($ca === null || $examScore === null) {
            return [
                'ca_score' => $ca,
                'exam_score' => $examScore !== null ? round(max(0.0, $examScore), 2) : null,
                'final_score' => null,
                'grade_letter' => null,
                'grade_point' => null,
                'classification' => 'Pending Examination Entry',
                'badge_class' => 'text-bg-secondary',
                'is_passed' => false,
                'is_retake' => false,
                'exam_status' => 'present',
                'sitting_type' => $sittingType,
            ];
        }

        // 4. Completed sitting (Main, Special, or Supplementary)
        $exam = round(max(0.0, $examScore), 2);
        $finalScore = round($ca + $exam, 2);

        $passMark = $sheet ? (float) $sheet->pass_mark : (float) config('academic.assessment_pass_mark', 50.0);
        $gradeDetails = $this->resolveGrade($finalScore);

        // Supplementary examination capping policy (if applicable: capped at Pass / C / 2.0 GP)
        if ($sittingType === 'supplementary' && $finalScore >= $passMark) {
            $gradeDetails['grade_letter'] = 'C';
            $gradeDetails['grade_point'] = 2.0;
            $gradeDetails['classification'] = 'Pass (Supplementary Sitting)';
            $gradeDetails['badge_class'] = 'text-bg-info';
        }

        $isPassed = $finalScore >= $passMark && $gradeDetails['grade_point'] > 0.0;

        return [
            'ca_score' => $ca,
            'exam_score' => $exam,
            'final_score' => $finalScore,
            'grade_letter' => $gradeDetails['grade_letter'],
            'grade_point' => $gradeDetails['grade_point'],
            'classification' => $gradeDetails['classification'],
            'badge_class' => $gradeDetails['badge_class'],
            'is_passed' => $isPassed,
            'is_retake' => ! $isPassed,
            'exam_status' => 'present',
            'sitting_type' => $sittingType,
        ];
    }

    /**
     * Resolve grade letter, point, classification, and badge class from a final percentage score.
     *
     * @return array{
     *     grade_letter: string,
     *     grade_point: float,
     *     classification: string,
     *     badge_class: string
     * }
     */
    public function resolveGrade(float $finalScore): array
    {
        $score = round($finalScore, 2);

        // 1. Try querying dynamic database GradingScaleTier records
        if (class_exists(GradingScaleTier::class) && GradingScaleTier::count() > 0) {
            $tier = GradingScaleTier::ordered()
                ->where('min_score', '<=', $score)
                ->where('max_score', '>=', $score)
                ->first();

            if ($tier) {
                return [
                    'grade_letter' => $tier->grade_letter,
                    'grade_point' => (float) $tier->grade_point,
                    'classification' => $tier->classification,
                    'badge_class' => $tier->badge_class,
                ];
            }
        }

        // 2. Fallback to centralized config/academic.php grading scale
        $gradingScale = config('academic.grading_scale', []);
        foreach ($gradingScale as $scale) {
            if ($score >= $scale['min_score'] && $score <= $scale['max_score']) {
                return [
                    'grade_letter' => $scale['grade_letter'],
                    'grade_point' => (float) $scale['grade_point'],
                    'classification' => $scale['classification'],
                    'badge_class' => $scale['badge_class'],
                ];
            }
        }

        // 3. Absolute fallback to F (Fail) if score is out of standard range or negative
        return [
            'grade_letter' => 'F',
            'grade_point' => 0.0,
            'classification' => 'Fail (Requires Retake)',
            'badge_class' => 'text-bg-danger',
        ];
    }

    /**
     * Calculate semester GPA, earned credits, and cumulative CGPA for a student.
     */
    public function calculateSemesterGpa(Student $student, Semester $semester): StudentSemesterPerformance
    {
        // Retrieve all student marks for this semester with registration item details
        $marks = StudentMark::whereHas('courseAssessmentSheet', function ($q) use ($semester) {
            $q->where('semester_id', $semester->id);
        })
            ->where('student_id', $student->id)
            ->with(['registrationItem', 'courseAssessmentSheet'])
            ->get();

        $creditUnitsRegistered = 0.0;
        $creditUnitsEarned = 0.0;
        $weightedGradePoints = 0.0;

        foreach ($marks as $mark) {
            // Check credit units from registration item or course unit
            $credits = $mark->registrationItem?->credit_units
                ?? $mark->courseAssessmentSheet?->courseUnit?->credit_units
                ?? 0.0;

            if ($mark->registrationItem && $mark->registrationItem->isDropped()) {
                continue; // Skip dropped courses
            }

            // Incomplete paper with approved permission (INC) does not penalize GPA divisor
            if ($mark->isMissedWithPermission() || ($mark->final_score === null && $mark->grade_letter === 'INC')) {
                continue;
            }

            if ($mark->final_score !== null) {
                $creditUnitsRegistered += $credits;
                $weightedGradePoints += ($credits * (float) $mark->grade_point);

                if ($mark->is_passed) {
                    $creditUnitsEarned += $credits;
                }
            }
        }

        // Semester GPA = Total Weighted Grade Points / Total Registered Credits
        $gpa = $creditUnitsRegistered > 0.0
            ? round($weightedGradePoints / $creditUnitsRegistered, 2)
            : 0.00;

        // Cumulative calculations evaluated up to this semester (applying institutional retake CGPA policy)
        $cumulative = $this->computeCumulativeMetrics($student, $semester);

        $performance = StudentSemesterPerformance::updateOrCreate(
            [
                'student_id' => $student->id,
                'semester_id' => $semester->id,
            ],
            [
                'academic_year_id' => $semester->academic_year_id,
                'credit_units_registered' => $creditUnitsRegistered,
                'credit_units_earned' => $creditUnitsEarned,
                'weighted_grade_points' => $weightedGradePoints,
                'gpa' => $gpa,
                'cumulative_credit_units_registered' => $cumulative['cumulative_credits_registered'],
                'cumulative_credit_units_earned' => $cumulative['cumulative_credits_earned'],
                'cumulative_weighted_grade_points' => $cumulative['cumulative_weighted_points'],
                'cgpa' => $cumulative['cgpa'],
                'academic_standing' => $cumulative['academic_standing'],
            ]
        );

        // Update student record's cached cumulative_gpa
        $student->update(['cumulative_gpa' => $cumulative['cgpa']]);

        return $performance;
    }

    /**
     * Compute cumulative academic performance metrics for a student up to a given semester.
     *
     * @return array{
     *     cumulative_credits_registered: float,
     *     cumulative_credits_earned: float,
     *     cumulative_weighted_points: float,
     *     cgpa: float,
     *     academic_standing: string
     * }
     */
    public function computeCumulativeMetrics(Student $student, ?Semester $upToSemester = null): array
    {
        $policy = config('academic.retake_cgpa_policy', 'replace_fail');

        $marksQuery = StudentMark::where('student_id', $student->id)
            ->whereNotNull('final_score')
            ->whereHas('courseAssessmentSheet')
            ->with(['courseAssessmentSheet.courseUnit', 'courseAssessmentSheet.semester.academicYear', 'registrationItem']);

        if ($upToSemester) {
            $upToYear = $upToSemester->academicYear;
            $marksQuery->whereHas('courseAssessmentSheet.semester', function ($q) use ($upToSemester, $upToYear): void {
                $q->whereHas('academicYear', function ($yq) use ($upToYear): void {
                    if ($upToYear) {
                        $yq->where('start_date', '<', $upToYear->start_date);
                    }
                })->orWhere(function ($sq) use ($upToSemester): void {
                    $sq->where('academic_year_id', $upToSemester->academic_year_id)
                        ->where('semester_number', '<=', $upToSemester->semester_number);
                });
            });
        }

        $allMarks = $marksQuery->get()->filter(function ($mark): bool {
            return ! ($mark->registrationItem && $mark->registrationItem->isDropped())
                && ! ($mark->isMissedWithPermission() || ($mark->final_score === null && $mark->grade_letter === 'INC'));
        });

        $cumulativeCreditsRegistered = 0.0;
        $cumulativeCreditsEarned = 0.0;
        $cumulativeWeightedPoints = 0.0;

        if ($policy === 'replace_fail') {
            // Group by course unit. For each course unit, use the best attempt (highest GP, then highest score).
            $grouped = $allMarks->groupBy(function ($mark) {
                return $mark->courseAssessmentSheet?->course_unit_id
                    ?? $mark->registrationItem?->course_unit_id;
            });

            foreach ($grouped as $courseMarks) {
                /** @var StudentMark $bestAttempt */
                $bestAttempt = $courseMarks->sortByDesc(function ($m): array {
                    return [
                        (float) ($m->grade_point ?? 0.0),
                        (float) ($m->final_score ?? 0.0),
                    ];
                })->first();

                $credits = (float) ($bestAttempt->registrationItem?->credit_units
                    ?? $bestAttempt->courseAssessmentSheet?->courseUnit?->credit_units
                    ?? 0.0);

                $cumulativeCreditsRegistered += $credits;
                $cumulativeWeightedPoints += ($credits * (float) $bestAttempt->grade_point);

                if ((bool) $bestAttempt->is_passed) {
                    $cumulativeCreditsEarned += $credits;
                }
            }
        } else {
            // accumulate_all: sum every attempt across all semesters
            foreach ($allMarks as $mark) {
                $credits = (float) ($mark->registrationItem?->credit_units
                    ?? $mark->courseAssessmentSheet?->courseUnit?->credit_units
                    ?? 0.0);

                $cumulativeCreditsRegistered += $credits;
                $cumulativeWeightedPoints += ($credits * (float) $mark->grade_point);

                if ((bool) $mark->is_passed) {
                    $cumulativeCreditsEarned += $credits;
                }
            }
        }

        // Incorporate any standalone / legacy StudentSemesterPerformance records for terms without detailed StudentMark rows
        $semestersWithMarks = $allMarks->map(function ($m) {
            return $m->courseAssessmentSheet?->semester_id;
        })->filter()->unique()->toArray();

        $perfQuery = StudentSemesterPerformance::where('student_id', $student->id);
        if (! empty($semestersWithMarks)) {
            $perfQuery->whereNotIn('semester_id', $semestersWithMarks);
        }

        if ($upToSemester) {
            $upToYear = $upToSemester->academicYear;
            $perfQuery->whereHas('semester', function ($q) use ($upToSemester, $upToYear): void {
                $q->whereHas('academicYear', function ($yq) use ($upToYear): void {
                    if ($upToYear) {
                        $yq->where('start_date', '<', $upToYear->start_date);
                    }
                })->orWhere(function ($sq) use ($upToSemester): void {
                    $sq->where('academic_year_id', $upToSemester->academic_year_id)
                        ->where('semester_number', '<=', $upToSemester->semester_number);
                });
            });
        }

        $standalonePerformances = $perfQuery->get();
        foreach ($standalonePerformances as $perf) {
            $cumulativeCreditsRegistered += (float) $perf->credit_units_registered;
            $cumulativeCreditsEarned += (float) $perf->credit_units_earned;
            $cumulativeWeightedPoints += (float) $perf->weighted_grade_points;
        }

        $cgpa = $cumulativeCreditsRegistered > 0.0
            ? round($cumulativeWeightedPoints / $cumulativeCreditsRegistered, 2)
            : 0.00;

        $standing = $cgpa >= 2.00 ? 'Normal Progress' : 'Probation';

        return [
            'cumulative_credits_registered' => $cumulativeCreditsRegistered,
            'cumulative_credits_earned' => $cumulativeCreditsEarned,
            'cumulative_weighted_points' => $cumulativeWeightedPoints,
            'cgpa' => $cgpa,
            'academic_standing' => $standing,
        ];
    }

    /**
     * Compute cumulative CGPA across all historical completed terms for a student.
     */
    public function calculateCgpa(Student $student): float
    {
        $metrics = $this->computeCumulativeMetrics($student);
        $student->update(['cumulative_gpa' => $metrics['cgpa']]);

        return $metrics['cgpa'];
    }

    /**
     * Resolve the academic award honours classification for a student based on CGPA and level.
     */
    public function resolveAwardClassification(Student $student, ?float $cgpa = null): ?AwardClassification
    {
        $cgpa = $cgpa ?? $this->calculateCgpa($student);
        $level = $this->resolveAwardLevel($student);

        if (class_exists(AwardClassification::class)) {
            $award = AwardClassification::where('award_level', $level)
                ->where('min_cgpa', '<=', $cgpa)
                ->where('max_cgpa', '>=', $cgpa)
                ->first();

            if ($award) {
                return $award;
            }
        }

        return null;
    }

    /**
     * Helper to map student programme to statutory award level (degree, diploma, certificate).
     */
    public function resolveAwardLevel(Student $student): string
    {
        $awardType = strtolower($student->programme?->award_type ?? 'bachelors');

        if (str_contains($awardType, 'diploma') && ! str_contains($awardType, 'postgraduate')) {
            return 'diploma';
        }

        if (str_contains($awardType, 'certificate')) {
            return 'certificate';
        }

        // Default to degree level for Bachelors, Postgraduate Diplomas, Masters, Doctorates
        return 'degree';
    }

    /**
     * Create an immutable audit log when a student mark or examination score is adjusted.
     */
    public function recordGradeAudit(
        StudentMark $mark,
        User $user,
        string $type,
        ?float $oldScore,
        float $newScore,
        string $reason
    ): GradeAuditLog {
        if (! in_array($type, ['ca', 'exam', 'final'], true)) {
            throw new InvalidArgumentException("Invalid score type '{$type}'. Must be 'ca', 'exam', or 'final'.");
        }

        return GradeAuditLog::create([
            'student_mark_id' => $mark->id,
            'changed_by_id' => $user->id,
            'score_type' => $type,
            'old_score' => $oldScore,
            'new_score' => $newScore,
            'reason' => trim($reason),
        ]);
    }

    /**
     * Record a Special or Supplementary Examination score for a student mark,
     * recompute final grade and metrics, log the audit trail, and update semester performance.
     */
    public function recordSpecialExamMark(
        StudentMark $mark,
        float $specialExamScore,
        string $sittingType = 'special',
        ?string $examDate = null,
        ?string $remarks = null,
        ?User $user = null
    ): StudentMark {
        $sheet = $mark->courseAssessmentSheet;
        $caScore = $mark->ca_score !== null ? (float) $mark->ca_score : 0.0;
        $oldExam = $mark->exam_score !== null ? (float) $mark->exam_score : null;
        $examDate = $examDate ?? now()->toDateString();
        $user = $user ?? auth()->user() ?? User::first();

        $computed = $this->computeMark(
            $caScore,
            $specialExamScore,
            $sheet,
            'present',
            $sittingType
        );

        $mark->update([
            'exam_score' => $computed['exam_score'],
            'special_exam_score' => $computed['exam_score'],
            'special_exam_date' => $examDate,
            'special_exam_remarks' => $remarks,
            'exam_status' => 'present',
            'sitting_type' => $sittingType,
            'final_score' => $computed['final_score'],
            'grade_letter' => $computed['grade_letter'],
            'grade_point' => $computed['grade_point'],
            'is_passed' => $computed['is_passed'],
            'is_retake' => $computed['is_retake'],
            'lecturer_remarks' => $remarks ?? ($sittingType === 'special' ? 'Cleared on Special Examination' : 'Cleared on Supplementary Examination'),
        ]);

        // Record audit log
        if ($user) {
            $sittingLabel = $sittingType === 'special' ? 'Special Examination' : 'Supplementary Examination';
            $auditReason = "{$sittingLabel} score recorded ({$specialExamScore}/".($sheet ? number_format($sheet->exam_weight, 0) : '60').'). '.($remarks ?? '');
            $this->recordGradeAudit($mark, $user, 'exam', $oldExam, (float) $computed['exam_score'], trim($auditReason));
        }

        // Recompute semester GPA and CGPA for the student
        if ($sheet && $sheet->semester && $mark->student) {
            $this->calculateSemesterGpa($mark->student, $sheet->semester);
        }

        return $mark->fresh();
    }

    /**
     * Compute comprehensive mark sheet statistics, grade distribution curve,
     * standard deviation, and academic anomaly detection alerts.
     *
     * @return array{
     *     enrolled_count: int,
     *     graded_count: int,
     *     ungraded_count: int,
     *     pass_count: int,
     *     fail_count: int,
     *     pass_rate: float,
     *     fail_rate: float,
     *     average_score: float,
     *     highest_score: float,
     *     lowest_score: float,
     *     standard_deviation: float,
     *     grade_distribution: array<string, array<string, mixed>>,
     *     anomalies: array<int, array<string, string>>
     * }
     */
    public function computeSheetStatistics(CourseAssessmentSheet $sheet): array
    {
        $allMarks = $sheet->studentMarks;
        $enrolledCount = $allMarks->count();
        $gradedMarks = $allMarks->filter(fn (StudentMark $m) => $m->final_score !== null);
        $gradedCount = $gradedMarks->count();
        $ungradedCount = $enrolledCount - $gradedCount;

        $passedMarks = $gradedMarks->where('is_passed', true);
        $failedMarks = $gradedMarks->where('is_passed', false);
        $passCount = $passedMarks->count();
        $failCount = $failedMarks->count();

        $passRate = $gradedCount > 0 ? round(($passCount / $gradedCount) * 100, 1) : 0.0;
        $failRate = $gradedCount > 0 ? round(($failCount / $gradedCount) * 100, 1) : 0.0;

        /** @var array<int, float> $scores */
        $scores = $gradedMarks->pluck('final_score')->map(fn ($val) => (float) $val)->values()->all();
        $averageScore = $gradedCount > 0 ? round(array_sum($scores) / $gradedCount, 1) : 0.0;
        $highestScore = $gradedCount > 0 ? max($scores) : 0.0;
        $lowestScore = $gradedCount > 0 ? min($scores) : 0.0;

        // Calculate sample standard deviation
        $standardDeviation = 0.0;
        if ($gradedCount > 1) {
            $mean = array_sum($scores) / $gradedCount;
            $variance = array_sum(array_map(fn ($x) => ($x - $mean) ** 2, $scores)) / ($gradedCount - 1);
            $standardDeviation = round(sqrt($variance), 2);
        }

        // Compute Grade Distribution Histogram by Tiers
        $tiers = class_exists(GradingScaleTier::class) && GradingScaleTier::count() > 0
            ? GradingScaleTier::ordered()->get()
            : collect();

        $gradeDistribution = [];
        if ($tiers->isNotEmpty()) {
            foreach ($tiers as $tier) {
                $count = $gradedMarks->where('grade_letter', $tier->grade_letter)->count();
                $percentage = $gradedCount > 0 ? round(($count / $gradedCount) * 100, 1) : 0.0;

                $gradeDistribution[$tier->grade_letter] = [
                    'grade_letter' => $tier->grade_letter,
                    'grade_point' => (float) $tier->grade_point,
                    'classification' => $tier->classification,
                    'badge_class' => $tier->badge_class,
                    'min_score' => (float) $tier->min_score,
                    'max_score' => (float) $tier->max_score,
                    'count' => $count,
                    'percentage' => $percentage,
                ];
            }
        } else {
            $configTiers = config('academic.grading_scale', []);
            foreach ($configTiers as $tier) {
                $count = $gradedMarks->where('grade_letter', $tier['grade_letter'])->count();
                $percentage = $gradedCount > 0 ? round(($count / $gradedCount) * 100, 1) : 0.0;

                $gradeDistribution[$tier['grade_letter']] = [
                    'grade_letter' => $tier['grade_letter'],
                    'grade_point' => (float) $tier['grade_point'],
                    'classification' => $tier['classification'],
                    'badge_class' => $tier['badge_class'],
                    'min_score' => (float) $tier['min_score'],
                    'max_score' => (float) $tier['max_score'],
                    'count' => $count,
                    'percentage' => $percentage,
                ];
            }
        }

        // Anomaly Detection Algorithm
        $anomalies = [];

        if ($gradedCount >= 3) {
            // Disproportionate Failure Rate (> 20%)
            if ($failRate > 20.0) {
                $anomalies[] = [
                    'level' => 'danger',
                    'title' => 'Disproportionate Failure Rate',
                    'description' => "High failure rate detected: {$failRate}% of assessed students ({$failCount} out of {$gradedCount}) failed this course (moderation alert threshold is 20%). HoD inquiry into exam rigor and marking consistency is recommended.",
                    'icon' => 'bi-exclamation-triangle-fill',
                ];
            }

            // Abnormally low or high class average
            if ($averageScore < 45.0) {
                $anomalies[] = [
                    'level' => 'danger',
                    'title' => 'Abnormally Low Class Average',
                    'description' => "Class mean score is critically low at {$averageScore}%, falling below the university statutory pass mark of 50%.",
                    'icon' => 'bi-graph-down-arrow',
                ];
            } elseif ($averageScore > 82.0) {
                $anomalies[] = [
                    'level' => 'warning',
                    'title' => 'Grade Inflation Alert',
                    'description' => "Class mean score is unusually elevated at {$averageScore}%. Ensure assessment rigor adheres to institutional standards.",
                    'icon' => 'bi-graph-up-arrow',
                ];
            }
        }

        if ($gradedCount >= 5) {
            // Zero A/A+ rates
            $aGradesCount = $gradedMarks->filter(fn (StudentMark $m) => in_array($m->grade_letter, ['A', 'A+'], true))->count();
            if ($aGradesCount === 0) {
                $anomalies[] = [
                    'level' => 'warning',
                    'title' => 'Zero Distinction / A Grades',
                    'description' => "0% of evaluated students achieved an 'A' grade out of {$gradedCount} candidates. Verify coursework and examination top-end scoring calibration.",
                    'icon' => 'bi-award',
                ];
            }

            // Suspicious Clustering (low standard deviation)
            if ($standardDeviation < 3.5 && $standardDeviation > 0) {
                $anomalies[] = [
                    'level' => 'warning',
                    'title' => 'Suspicious Score Clustering',
                    'description' => "Mark distribution exhibits abnormally low variance (Standard Deviation: {$standardDeviation}). Student scores are bunched tightly together.",
                    'icon' => 'bi-diagram-3-fill',
                ];
            }
        }

        if ($ungradedCount > 0) {
            $anomalies[] = [
                'level' => 'info',
                'title' => 'Unrecorded Student Marks',
                'description' => "{$ungradedCount} enrolled student(s) currently have no marks recorded on this assessment sheet.",
                'icon' => 'bi-info-circle-fill',
            ];
        }

        return [
            'enrolled_count' => $enrolledCount,
            'graded_count' => $gradedCount,
            'ungraded_count' => $ungradedCount,
            'pass_count' => $passCount,
            'fail_count' => $failCount,
            'pass_rate' => $passRate,
            'fail_rate' => $failRate,
            'average_score' => $averageScore,
            'highest_score' => $highestScore,
            'lowest_score' => $lowestScore,
            'standard_deviation' => $standardDeviation,
            'grade_distribution' => $gradeDistribution,
            'anomalies' => $anomalies,
        ];
    }
}
