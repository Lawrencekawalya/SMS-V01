<?php

namespace App\Services;

use App\Models\AwardClassification;
use App\Models\CourseUnit;
use App\Models\CurriculumCourse;
use App\Models\Student;
use App\Models\StudentMark;

class GraduationService
{
    /**
     * Audit a student's eligibility for official graduation clearance.
     *
     * @return array<string, mixed>
     */
    public function auditClearance(Student $student): array
    {
        $curriculum = $student->curriculum
            ?? $student->programme?->curriculums()->where('is_active', true)->first();

        // 1. Determine Required Graduation Credits
        $requiredCredits = (float) ($curriculum?->min_graduation_credits
            ?? ($student->programme?->required_credits_to_graduate ?? 0));

        if ($requiredCredits <= 0 && $curriculum) {
            $requiredCredits = $curriculum->totalMappedCredits();
        }

        // 2. Fetch all marks across all sessions
        $allMarks = StudentMark::where('student_id', $student->id)
            ->with(['courseAssessmentSheet.courseUnit', 'registrationItem.courseUnit'])
            ->get();

        // Group marks by Course Unit ID
        $courseMarks = [];
        foreach ($allMarks as $m) {
            $cuId = $m->courseAssessmentSheet?->course_unit_id
                ?? $m->registrationItem?->course_unit_id;

            if ($cuId) {
                $courseMarks[$cuId][] = $m;
            }
        }

        $passedCourseUnitIds = [];
        $unresolvedRetakes = [];
        $earnedCredits = 0.0;

        foreach ($courseMarks as $cuId => $marksList) {
            $hasPassed = false;
            $cu = null;

            foreach ($marksList as $mark) {
                $cu = $mark->courseAssessmentSheet?->courseUnit
                    ?? $mark->registrationItem?->courseUnit;

                if ((bool) $mark->is_passed) {
                    $hasPassed = true;
                    break;
                }
            }

            if (! $cu) {
                $cu = CourseUnit::find($cuId);
            }

            if ($hasPassed && $cu) {
                $passedCourseUnitIds[] = $cuId;
                $earnedCredits += (float) $cu->credit_units;
            } elseif (! $hasPassed && $cu) {
                $unresolvedRetakes[] = [
                    'course_unit' => $cu,
                    'attempts' => count($marksList),
                    'last_grade' => end($marksList)->grade_letter ?? 'F',
                ];
            }
        }

        // 3. Core Courses Audit Checklist
        $deficiencies = [];
        $coreChecklist = [];

        if ($curriculum) {
            $coreCourses = CurriculumCourse::where('curriculum_id', $curriculum->id)
                ->where('course_type', 'Core')
                ->with('courseUnit')
                ->orderBy('study_year')
                ->orderBy('semester')
                ->get();

            foreach ($coreCourses as $cc) {
                $cu = $cc->courseUnit;
                if (! $cu) {
                    continue;
                }

                $isCompleted = in_array($cu->id, $passedCourseUnitIds, true);
                $coreChecklist[] = [
                    'course_unit' => $cu,
                    'stage' => "Y{$cc->study_year}S{$cc->semester}",
                    'credit_units' => (float) $cu->credit_units,
                    'is_completed' => $isCompleted,
                ];

                if (! $isCompleted) {
                    $deficiencies[] = "Missing Core Course: {$cu->code} ({$cu->name}) from Year {$cc->study_year}, Sem {$cc->semester}.";
                }
            }
        }

        // 4. Unresolved Retakes Check
        foreach ($unresolvedRetakes as $retake) {
            $cu = $retake['course_unit'];
            $deficiencies[] = "Unresolved Retake: Failed {$cu->code} ({$cu->name}) has not been cleared.";
        }

        // 5. Credit Floor Check
        if ($requiredCredits > 0 && $earnedCredits < $requiredCredits) {
            $shortfall = $requiredCredits - $earnedCredits;
            $deficiencies[] = "Credit Shortfall: Earned {$earnedCredits} CU of {$requiredCredits} required CU (Deficit: {$shortfall} CU).";
        }

        // 6. Cumulative CGPA Check
        $latestPerf = $student->semesterPerformances()->orderByDesc('created_at')->first();
        $cgpa = (float) ($latestPerf?->cgpa ?? ($student->cumulative_gpa ?? 0.0));

        if ($cgpa < 2.00) {
            $deficiencies[] = "CGPA {$cgpa} is below the minimum graduation threshold of 2.00.";
        }

        // 7. Resolve Award Classification
        $awardType = strtolower($student->programme?->award_type ?? 'bachelors');
        $awardLevel = match (true) {
            str_contains($awardType, 'diploma') => 'diploma',
            str_contains($awardType, 'certificate') => 'certificate',
            default => 'degree',
        };

        $classification = AwardClassification::where('award_level', $awardLevel)
            ->where('min_cgpa', '<=', $cgpa)
            ->where('max_cgpa', '>=', $cgpa)
            ->first();

        $classificationName = $classification?->name ?? $this->resolveFallbackAwardName($cgpa, $awardLevel);
        $badgeClass = $classification?->badge_class ?? ($cgpa >= 3.60 ? 'text-bg-success' : ($cgpa >= 2.00 ? 'text-bg-info' : 'text-bg-danger'));

        $isCleared = empty($deficiencies) && $cgpa >= 2.00 && ($requiredCredits <= 0 || $earnedCredits >= $requiredCredits);
        $status = $isCleared ? 'Cleared for Graduation' : 'Academic Deficiencies / Pending';

        return [
            'student' => $student,
            'curriculum' => $curriculum,
            'programme' => $student->programme,
            'required_credits' => $requiredCredits,
            'earned_credits' => $earnedCredits,
            'credit_deficit' => max(0, $requiredCredits - $earnedCredits),
            'cgpa' => $cgpa,
            'award_classification' => $classificationName,
            'badge_class' => $badgeClass,
            'is_cleared' => $isCleared,
            'status' => $status,
            'deficiencies' => $deficiencies,
            'core_courses_checklist' => $coreChecklist,
            'unresolved_retakes' => $unresolvedRetakes,
            'passed_courses_count' => count($passedCourseUnitIds),
        ];
    }

    /**
     * Fallback resolution for award classification name when database tier is not mapped.
     */
    protected function resolveFallbackAwardName(float $cgpa, string $awardLevel): string
    {
        if ($awardLevel === 'diploma') {
            if ($cgpa >= 4.40) {
                return 'Class I (Distinction)';
            }
            if ($cgpa >= 3.60) {
                return 'Class II (Credit)';
            }
            if ($cgpa >= 2.00) {
                return 'Class III (Pass)';
            }

            return 'Fail / Academic Probation';
        }

        if ($awardLevel === 'certificate') {
            if ($cgpa >= 4.40) {
                return 'Distinction';
            }
            if ($cgpa >= 3.60) {
                return 'Credit';
            }
            if ($cgpa >= 2.00) {
                return 'Pass';
            }

            return 'Fail / Academic Probation';
        }

        // Degree default
        if ($cgpa >= 4.40) {
            return 'First Class Honours';
        }
        if ($cgpa >= 3.60) {
            return 'Second Class Honours (Upper Division)';
        }
        if ($cgpa >= 2.80) {
            return 'Second Class Honours (Lower Division)';
        }
        if ($cgpa >= 2.00) {
            return 'Pass Degree';
        }

        return 'Fail / Academic Probation';
    }
}
