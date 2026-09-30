<?php

namespace App\Services;

use App\Models\CourseRegistration;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use App\Models\CurriculumCourse;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentMark;
use Illuminate\Support\Collection;

class CourseEligibilityService
{
    /**
     * Institutional bounds for credit units per semester.
     */
    public const MIN_SEMESTER_CREDITS = 12.0;

    public const MAX_SEMESTER_CREDITS = 24.0;

    /**
     * Compute the full course eligibility basket for a student in a given semester.
     *
     * @return array{
     *     student: Student,
     *     semester: ?Semester,
     *     stage: array{study_year: int, semester_number: int, curriculum_version: ?string, programme_code: string},
     *     mandatory_core_courses: Collection<int, CourseUnit>,
     *     available_electives: Collection<int, CourseUnit>,
     *     blocked_courses: Collection<int, array{course: CourseUnit, reason: string, stage_string: string}>,
     *     summary: array{
     *         core_credits: float,
     *         min_credits: float,
     *         max_credits: float,
     *         is_student_active: bool,
     *         is_registration_open: bool,
     *         existing_registration: ?CourseRegistration
     *     }
     * }
     */
    public function getEligibleCoursesForStudent(Student $student, ?Semester $semester = null): array
    {
        $semester = $semester ?? Semester::where('is_active', true)->first() ?? Semester::first();

        $curriculum = $student->curriculum;
        $studyYear = $student->current_study_year;
        $semesterNumber = $student->current_semester;

        // Retrieve course units previously completed or enrolled in approved registrations
        $completedRegistrationCourseIds = CourseRegistrationItem::whereHas('courseRegistration', function ($q) use ($student, $semester) {
            $q->where('student_id', $student->id)
                ->where('status', 'approved');
            if ($semester) {
                $q->where('semester_id', '!=', $semester->id);
            }
        })
            ->where('status', '!=', 'dropped')
            ->pluck('course_unit_id')
            ->toArray();

        // Also retrieve any course units officially passed in published marks
        $passedCourseUnitIds = StudentMark::where('student_id', $student->id)
            ->where('is_passed', true)
            ->whereHas('courseAssessmentSheet')
            ->with('courseAssessmentSheet')
            ->get()
            ->pluck('courseAssessmentSheet.course_unit_id')
            ->toArray();

        $completedCourseUnitIds = array_unique(array_merge($completedRegistrationCourseIds, $passedCourseUnitIds));

        // Check if student already has a registration slip for this semester
        $existingRegistration = null;
        if ($semester) {
            $existingRegistration = CourseRegistration::where('student_id', $student->id)
                ->where('semester_id', $semester->id)
                ->first();
        }

        $mandatoryCoreCourses = collect();
        $availableElectives = collect();
        $blockedCourses = collect();

        if ($curriculum) {
            // Retrieve all curriculum course allocations with prerequisites eager-loaded
            $curriculumCourses = CurriculumCourse::with(['courseUnit.prerequisites', 'courseUnit.department'])
                ->where('curriculum_id', $curriculum->id)
                ->get();

            foreach ($curriculumCourses as $cc) {
                $course = $cc->courseUnit;
                if (! $course || $course->status !== 'active') {
                    continue;
                }

                // Attach pivot data on the course object for easy view access
                $course->curriculum_study_year = $cc->study_year;
                $course->curriculum_semester = $cc->semester;
                $course->curriculum_course_type = $cc->course_type;

                // 1. Stage Constraint: Higher study years are strictly blocked
                if ($cc->study_year > $studyYear) {
                    $blockedCourses->push([
                        'course' => $course,
                        'reason' => "Stage restriction: Mapped to Year {$cc->study_year}, Semester {$cc->semester}. You are currently in Year {$studyYear}.",
                        'stage_string' => "Year {$cc->study_year}, Sem {$cc->semester}",
                    ]);

                    continue;
                }

                // If in same study year but future semester
                if ($cc->study_year === $studyYear && $cc->semester > $semesterNumber) {
                    $blockedCourses->push([
                        'course' => $course,
                        'reason' => "Stage restriction: Mapped to Semester {$cc->semester}. Current term is Semester {$semesterNumber}.",
                        'stage_string' => "Year {$cc->study_year}, Sem {$cc->semester}",
                    ]);

                    continue;
                }

                // 2. Prerequisite Check: Verify all prerequisites for this course have been completed (if enabled)
                if (config('academic.enforce_prerequisites', false)) {
                    $unmetPrerequisites = [];
                    foreach ($course->prerequisites as $prereq) {
                        if (! in_array($prereq->id, $completedCourseUnitIds, true)) {
                            $unmetPrerequisites[] = "{$prereq->code} ({$prereq->name})";
                        }
                    }

                    if (! empty($unmetPrerequisites)) {
                        $blockedCourses->push([
                            'course' => $course,
                            'reason' => 'Prerequisite requirement not met: Requires '.implode(', ', $unmetPrerequisites).' passed.',
                            'stage_string' => "Year {$cc->study_year}, Sem {$cc->semester}",
                        ]);

                        continue;
                    }
                }

                // 3. Skip already completed courses from prior approved registrations
                if (in_array($course->id, $completedCourseUnitIds, true)) {
                    continue;
                }

                // 4. Course is eligible for current stage or allowable carry-over
                $isCurrentStage = ($cc->study_year === $studyYear && $cc->semester === $semesterNumber);

                if ($isCurrentStage && $cc->course_type === 'Core') {
                    $mandatoryCoreCourses->push($course);
                } else {
                    $availableElectives->push($course);
                }
            }
        }

        $coreCredits = (float) $mandatoryCoreCourses->sum('credit_units');
        $stageBounds = $curriculum ? $curriculum->getStageCreditBounds($studyYear, $semesterNumber) : ['min' => self::MIN_SEMESTER_CREDITS, 'max' => self::MAX_SEMESTER_CREDITS];
        $stageMin = $stageBounds['min'];
        $stageMax = max($stageBounds['max'], $coreCredits);

        return [
            'student' => $student,
            'semester' => $semester,
            'stage' => [
                'study_year' => $studyYear,
                'semester_number' => $semesterNumber,
                'curriculum_version' => $curriculum?->version_name,
                'programme_code' => $student->programme->code ?? 'N/A',
            ],
            'mandatory_core_courses' => $mandatoryCoreCourses,
            'available_electives' => $availableElectives,
            'blocked_courses' => $blockedCourses,
            'summary' => [
                'core_credits' => $coreCredits,
                'min_credits' => $stageMin,
                'max_credits' => $stageMax,
                'is_student_active' => $student->status === 'active',
                'is_registration_open' => $semester?->isRegistrationOpen() ?? false,
                'existing_registration' => $existingRegistration,
                'prerequisites_enforced' => (bool) config('academic.enforce_prerequisites', false),
            ],
        ];
    }

    /**
     * Validate a set of selected course IDs against institutional eligibility and credit load rules.
     *
     * @param  array<int, int>  $selectedCourseUnitIds
     * @return array{
     *     is_valid: bool,
     *     errors: array<int, string>,
     *     total_credits: float,
     *     selected_courses: Collection<int, CourseUnit>,
     *     missing_core_courses: Collection<int, CourseUnit>
     * }
     */
    public function validateCourseSelection(Student $student, Semester $semester, array $selectedCourseUnitIds): array
    {
        $errors = [];

        // 1. Verify student academic standing
        if ($student->status !== 'active') {
            $errors[] = "Student is not in active standing (Current status: {$student->status}). Registration blocked.";
        }

        // 2. Verify semester registration window
        if (! $semester->isRegistrationOpen()) {
            $errors[] = 'Semester course registration window is currently closed.';
        }

        $eligibility = $this->getEligibleCoursesForStudent($student, $semester);

        $selectedCourses = CourseUnit::whereIn('id', $selectedCourseUnitIds)->get();
        $selectedIds = $selectedCourses->pluck('id')->toArray();

        // 3. Mandatory Core Courses Enforcement
        $missingCore = collect();
        foreach ($eligibility['mandatory_core_courses'] as $coreCourse) {
            if (! in_array($coreCourse->id, $selectedIds, true)) {
                $missingCore->push($coreCourse);
                $errors[] = "Mandatory core course {$coreCourse->code} ({$coreCourse->name}) must be selected.";
            }
        }

        // 4. Verify no blocked courses (stage constraints or unmet prerequisites) were selected
        $blockedCourseIds = $eligibility['blocked_courses']->pluck('course.id')->toArray();
        foreach ($selectedCourses as $course) {
            if (in_array($course->id, $blockedCourseIds, true)) {
                $blockedInfo = $eligibility['blocked_courses']->firstWhere('course.id', $course->id);
                $errors[] = "Course {$course->code} is not eligible: {$blockedInfo['reason']}";
            }
        }

        // 5. Verify courses belong to student's curriculum
        $curriculumCourseIds = CurriculumCourse::where('curriculum_id', $student->curriculum_id)
            ->pluck('course_unit_id')
            ->toArray();

        foreach ($selectedCourses as $course) {
            if (! in_array($course->id, $curriculumCourseIds, true)) {
                $errors[] = "Course {$course->code} is not part of your assigned curriculum.";
            }
        }

        // 6. Credit Load Floor & Ceiling Enforcement
        $totalCredits = (float) $selectedCourses->sum('credit_units');
        $minCredits = (float) ($eligibility['summary']['min_credits'] ?? self::MIN_SEMESTER_CREDITS);
        $maxCredits = (float) ($eligibility['summary']['max_credits'] ?? self::MAX_SEMESTER_CREDITS);

        if ($totalCredits < $minCredits) {
            $errors[] = "Total registered credits ({$totalCredits} CU) is below the institutional minimum limit of {$minCredits} CU.";
        }

        if ($totalCredits > $maxCredits) {
            $errors[] = "Total registered credits ({$totalCredits} CU) exceeds the institutional maximum limit of {$maxCredits} CU.";
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'total_credits' => $totalCredits,
            'selected_courses' => $selectedCourses,
            'missing_core_courses' => $missingCore,
        ];
    }
}
