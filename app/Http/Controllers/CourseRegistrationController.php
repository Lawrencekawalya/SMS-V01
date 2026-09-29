<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitCourseRegistrationRequest;
use App\Models\AcademicYear;
use App\Models\CourseRegistration;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use App\Models\CurriculumCourse;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use App\Services\CourseEligibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourseRegistrationController extends Controller
{
    /**
     * Display a listing of course registration slips.
     */
    public function index(Request $request): View
    {
        $academicYearId = $request->query('academic_year_id');
        $semesterId = $request->query('semester_id');
        $programmeId = $request->query('programme_id');
        $status = $request->query('status');

        $query = CourseRegistration::with([
            'student.programme',
            'student.curriculum',
            'academicYear',
            'semester',
            'approvedBy',
            'items.courseUnit',
        ]);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        if ($programmeId) {
            $query->whereHas('student', function ($q) use ($programmeId) {
                $q->where('programme_id', $programmeId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $registrations = $query->latest('created_at')->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::with('academicYear')->orderByDesc('start_date')->get();
        $programmes = Programme::where('status', 'active')->orderBy('name')->get();

        $stats = [
            'total' => CourseRegistration::count(),
            'submitted' => CourseRegistration::where('status', 'submitted')->count(),
            'approved' => CourseRegistration::where('status', 'approved')->count(),
            'rejected' => CourseRegistration::where('status', 'rejected')->count(),
            'add_drop_pending' => CourseRegistration::where('status', 'add_drop_pending')->count(),
        ];

        return view('academic.registration.index', compact(
            'registrations',
            'academicYears',
            'semesters',
            'programmes',
            'academicYearId',
            'semesterId',
            'programmeId',
            'status',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new course registration slip.
     */
    public function create(Request $request, CourseEligibilityService $eligibilityService): View
    {
        $students = Student::with(['programme', 'curriculum'])
            ->where('status', 'active')
            ->orderBy('registration_number')
            ->get();

        $activeSemester = Semester::where('is_active', true)->with('academicYear')->first()
            ?? Semester::with('academicYear')->first();

        $selectedStudentId = $request->query('student_id');
        $selectedStudent = $selectedStudentId
            ? $students->firstWhere('id', (int) $selectedStudentId)
            : $students->first();

        $eligibility = null;
        if ($selectedStudent && $activeSemester) {
            $eligibility = $eligibilityService->getEligibleCoursesForStudent($selectedStudent, $activeSemester);
        }

        return view('academic.registration.create', compact(
            'students',
            'selectedStudent',
            'activeSemester',
            'eligibility'
        ));
    }

    /**
     * Store a newly created course registration in storage.
     */
    public function store(SubmitCourseRegistrationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $targetStatus = $request->input('action_status', $request->input('status', 'submitted'));

        $student = Student::findOrFail($validated['student_id']);
        $semester = Semester::findOrFail($validated['semester_id']);

        $existingRegistration = CourseRegistration::where('student_id', $student->id)
            ->where('semester_id', $semester->id)
            ->first();

        if ($existingRegistration && ! $existingRegistration->canBeEdited()) {
            return redirect()->route('academic.registrations.show', $existingRegistration)
                ->with('error', "A registration slip (#REG-{$existingRegistration->id}) is already active in {$existingRegistration->status} state.");
        }

        $requiresApproval = (bool) config('academic.require_registration_approval', false);
        $isSubmitting = $targetStatus === 'submitted';
        if (! $requiresApproval && $isSubmitting) {
            $targetStatus = 'approved';
        }

        $registration = DB::transaction(function () use ($student, $semester, $targetStatus, $validated, $existingRegistration, $requiresApproval, $isSubmitting) {
            $reg = $existingRegistration ?? new CourseRegistration;
            $approvedAt = (! $requiresApproval && $isSubmitting) ? now() : null;
            $approvedByUserId = (! $requiresApproval && $isSubmitting) ? (auth()->id() ?? User::first()?->id) : null;

            $reg->fill([
                'student_id' => $student->id,
                'academic_year_id' => $semester->academic_year_id,
                'semester_id' => $semester->id,
                'study_year' => $student->current_study_year,
                'semester_number' => $student->current_semester,
                'status' => $targetStatus,
                'submitted_at' => $isSubmitting ? now() : null,
                'approved_at' => $approvedAt,
                'approved_by_user_id' => $approvedByUserId,
            ]);
            $reg->save();

            // Clear old items if updating
            $reg->items()->delete();

            // Attach new items
            $selectedCourses = CourseUnit::whereIn('id', $validated['course_unit_ids'])->get();
            $curriculumCourses = CurriculumCourse::where('curriculum_id', $student->curriculum_id)
                ->whereIn('course_unit_id', $selectedCourses->pluck('id'))
                ->get()
                ->keyBy('course_unit_id');

            foreach ($selectedCourses as $course) {
                $curriculumCourse = $curriculumCourses->get($course->id);
                $courseType = $curriculumCourse ? $curriculumCourse->course_type : 'Elective';

                CourseRegistrationItem::create([
                    'course_registration_id' => $reg->id,
                    'course_unit_id' => $course->id,
                    'course_type' => $courseType,
                    'credit_units' => $course->credit_units,
                    'status' => $targetStatus === 'approved' ? 'approved' : 'registered',
                ]);
            }

            $reg->recalculateTotalCredits();

            return $reg;
        });

        $message = match ($targetStatus) {
            'approved' => "Course registration slip #REG-{$registration->id} has been registered and confirmed successfully.",
            'submitted' => "Course registration slip #REG-{$registration->id} has been submitted for Academic Advisor review.",
            default => "Course registration slip #REG-{$registration->id} saved as draft.",
        };

        return redirect()->route('academic.registrations.show', $registration)->with('success', $message);
    }

    /**
     * Show the form for editing a draft or rejected registration slip.
     */
    public function edit(CourseRegistration $registration, CourseEligibilityService $eligibilityService): View|RedirectResponse
    {
        if (! $registration->canBeEdited()) {
            return redirect()->route('academic.registrations.show', $registration)
                ->with('warning', 'Only draft or rejected course registration slips can be edited.');
        }

        $registration->load(['student.programme', 'student.curriculum', 'semester.academicYear', 'items.courseUnit']);

        $eligibility = $eligibilityService->getEligibleCoursesForStudent($registration->student, $registration->semester);
        $activeSemester = $registration->semester;
        $selectedStudent = $registration->student;

        return view('academic.registration.edit', compact(
            'registration',
            'eligibility',
            'activeSemester',
            'selectedStudent'
        ));
    }

    /**
     * Update an existing course registration in storage.
     */
    public function update(SubmitCourseRegistrationRequest $request, CourseRegistration $registration): RedirectResponse
    {
        if (! $registration->canBeEdited()) {
            return redirect()->route('academic.registrations.show', $registration)
                ->with('error', 'This registration slip can no longer be edited directly.');
        }

        $validated = $request->validated();
        $targetStatus = $request->input('action_status', $request->input('status', 'submitted'));

        $requiresApproval = (bool) config('academic.require_registration_approval', false);
        $isSubmitting = $targetStatus === 'submitted';
        if (! $requiresApproval && $isSubmitting) {
            $targetStatus = 'approved';
        }

        DB::transaction(function () use ($registration, $targetStatus, $validated, $requiresApproval, $isSubmitting) {
            $registration->status = $targetStatus;
            $registration->submitted_at = $isSubmitting ? now() : null;
            if (! $requiresApproval && $isSubmitting) {
                $registration->approved_at = now();
                $registration->approved_by_user_id = auth()->id() ?? User::first()?->id;
            }
            $registration->save();

            // Re-sync items
            $registration->items()->delete();

            $selectedCourses = CourseUnit::whereIn('id', $validated['course_unit_ids'])->get();
            $curriculumCourses = CurriculumCourse::where('curriculum_id', $registration->student->curriculum_id)
                ->whereIn('course_unit_id', $selectedCourses->pluck('id'))
                ->get()
                ->keyBy('course_unit_id');

            foreach ($selectedCourses as $course) {
                $curriculumCourse = $curriculumCourses->get($course->id);
                $courseType = $curriculumCourse ? $curriculumCourse->course_type : 'Elective';

                CourseRegistrationItem::create([
                    'course_registration_id' => $registration->id,
                    'course_unit_id' => $course->id,
                    'course_type' => $courseType,
                    'credit_units' => $course->credit_units,
                    'status' => $targetStatus === 'approved' ? 'approved' : 'registered',
                ]);
            }

            $registration->recalculateTotalCredits();
        });

        $message = match ($targetStatus) {
            'approved' => "Course registration slip #REG-{$registration->id} has been updated and confirmed successfully.",
            'submitted' => "Course registration slip #REG-{$registration->id} has been submitted for Academic Advisor review.",
            default => "Course registration slip #REG-{$registration->id} draft updated successfully.",
        };

        return redirect()->route('academic.registrations.show', $registration)->with('success', $message);
    }

    /**
     * Display the specified course registration slip.
     */
    public function show(CourseRegistration $registration): View
    {
        $registration->load([
            'student.programme.department.faculty.campus',
            'student.curriculum',
            'academicYear',
            'semester',
            'approvedBy',
            'items.courseUnit',
        ]);

        return view('academic.registration.show', compact('registration'));
    }

    /**
     * Display the official printable version of the registration slip.
     */
    public function printSlip(CourseRegistration $registration): View
    {
        $registration->load([
            'student.programme.department.faculty.campus',
            'student.curriculum',
            'student.academicAdvisor',
            'academicYear',
            'semester',
            'approvedBy',
            'items.courseUnit.department',
        ]);

        return view('academic.registration.print', compact('registration'));
    }

    /**
     * Display the Course Eligibility & Prerequisite Inspector diagnostic tool.
     */
    public function eligibilityCheck(Request $request, CourseEligibilityService $eligibilityService): View
    {
        $students = Student::with(['programme', 'curriculum'])
            ->where('status', 'active')
            ->orderBy('registration_number')
            ->get();

        $selectedStudentId = $request->query('student_id');
        $selectedStudent = $selectedStudentId
            ? $students->firstWhere('id', (int) $selectedStudentId)
            : $students->first();

        $activeSemester = Semester::where('is_active', true)->with('academicYear')->first()
            ?? Semester::with('academicYear')->first();

        $eligibility = null;
        if ($selectedStudent && $activeSemester) {
            $eligibility = $eligibilityService->getEligibleCoursesForStudent($selectedStudent, $activeSemester);
        }

        return view('academic.registration.eligibility-check', compact(
            'students',
            'selectedStudent',
            'eligibility',
            'activeSemester'
        ));
    }

    /**
     * Display the Active University Calendar Session and multi-cohort student progression stage demonstration roster.
     */
    public function activeSessionRoster(Request $request): View
    {
        $allSemesters = Semester::with('academicYear')->orderByDesc('start_date')->get();
        $activeSemester = Semester::where('is_active', true)->with('academicYear')->first()
            ?? $allSemesters->first();

        $selectedSemesterId = $request->query('semester_id');
        $currentSemester = $selectedSemesterId
            ? ($allSemesters->firstWhere('id', (int) $selectedSemesterId) ?? $activeSemester)
            : $activeSemester;

        $studyYear = $request->query('study_year');
        $programmeId = $request->query('programme_id');
        $status = $request->query('status');

        $query = CourseRegistration::with([
            'student.programme.department.faculty',
            'student.curriculum',
            'student.academicAdvisor',
            'academicYear',
            'semester',
            'approvedBy',
            'items.courseUnit',
        ]);

        if ($currentSemester) {
            $query->where('semester_id', $currentSemester->id);
        }

        if ($studyYear) {
            $query->where('study_year', (int) $studyYear);
        }

        if ($programmeId) {
            $query->whereHas('student', function ($q) use ($programmeId) {
                $q->where('programme_id', $programmeId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $registrations = $query->orderBy('study_year')
            ->orderBy('semester_number')
            ->latest('id')
            ->get();

        // Calculate session-wide metrics for the hero card (unfiltered by table filters)
        $allSessionRegistrations = $currentSemester
            ? CourseRegistration::where('semester_id', $currentSemester->id)->get()
            : collect();

        $stats = [
            'total' => $allSessionRegistrations->count(),
            'year_1' => $allSessionRegistrations->where('study_year', 1)->count(),
            'year_2' => $allSessionRegistrations->where('study_year', 2)->count(),
            'year_3' => $allSessionRegistrations->where('study_year', 3)->count(),
            'year_4' => $allSessionRegistrations->where('study_year', 4)->count(),
            'approved' => $allSessionRegistrations->where('status', 'approved')->count(),
            'pending' => $allSessionRegistrations->whereIn('status', ['submitted', 'add_drop_pending'])->count(),
            'draft' => $allSessionRegistrations->where('status', 'draft')->count(),
            'total_credits' => $allSessionRegistrations->sum('total_credits'),
        ];

        $programmes = Programme::where('status', 'active')->orderBy('name')->get();

        return view('academic.registration.active-session', compact(
            'allSemesters',
            'activeSemester',
            'currentSemester',
            'registrations',
            'stats',
            'programmes',
            'studyYear',
            'programmeId',
            'status'
        ));
    }
}
