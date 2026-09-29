<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCourseRequest;
use App\Http\Requests\DropCourseRequest;
use App\Models\CourseRegistration;
use App\Models\CourseRegistrationItem;
use App\Models\CourseUnit;
use App\Models\User;
use App\Services\CourseEligibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AddDropController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CourseEligibilityService $eligibilityService
    ) {}

    /**
     * Display the course Add/Drop adjustment workspace.
     */
    public function edit(CourseRegistration $registration): View|RedirectResponse
    {
        $registration->load([
            'student.programme.department',
            'student.campus',
            'student.curriculum',
            'semester.academicYear',
            'items.courseUnit.department',
        ]);

        if (! in_array($registration->status, ['approved', 'submitted', 'add_drop_pending'], true)) {
            return redirect()
                ->route('academic.registrations.show', $registration)
                ->with('warning', 'Add/Drop course adjustments are only available on approved or submitted registration slips.');
        }

        $semester = $registration->semester;
        $isAddDropOpen = $semester?->isAddDropOpen() ?? false;
        $canModify = $registration->canAddDrop();

        // Separate active and dropped registered items
        $activeItems = $registration->items()
            ->where('status', '!=', 'dropped')
            ->with('courseUnit.department')
            ->get();

        $droppedItems = $registration->items()
            ->where('status', 'dropped')
            ->with('courseUnit.department')
            ->get();

        $currentCredits = (float) $activeItems->sum('credit_units');

        // Retrieve student's eligible course catalog
        $eligibility = $this->eligibilityService->getEligibleCoursesForStudent($registration->student, $semester);

        // Filter available electives to exclude already active courses
        $activeCourseUnitIds = $activeItems->pluck('course_unit_id')->all();
        $availableElectives = $eligibility['available_electives']->reject(
            fn (CourseUnit $course) => in_array($course->id, $activeCourseUnitIds, true)
        );

        $minCredits = CourseEligibilityService::MIN_SEMESTER_CREDITS;
        $maxCredits = CourseEligibilityService::MAX_SEMESTER_CREDITS;

        return view('academic.registration.add-drop.edit', compact(
            'registration',
            'semester',
            'isAddDropOpen',
            'canModify',
            'activeItems',
            'droppedItems',
            'availableElectives',
            'currentCredits',
            'minCredits',
            'maxCredits'
        ));
    }

    /**
     * Drop an enrolled course unit from the student's registration.
     */
    public function dropCourse(CourseRegistration $registration, CourseRegistrationItem $item, DropCourseRequest $request): RedirectResponse
    {
        if ($item->course_registration_id !== $registration->id) {
            abort(404, 'The course registration item does not belong to this registration.');
        }

        if (! $registration->semester?->isAddDropOpen()) {
            return redirect()
                ->route('academic.registrations.add-drop.edit', $registration)
                ->with('error', 'The Add/Drop deadline for this semester has passed. Course adjustments are no longer allowed.');
        }

        if (! $registration->canAddDrop()) {
            return redirect()
                ->route('academic.registrations.add-drop.edit', $registration)
                ->with('error', 'This registration slip cannot undergo Add/Drop adjustments in its current status.');
        }

        if ($item->isDropped()) {
            return redirect()
                ->route('academic.registrations.add-drop.edit', $registration)
                ->with('warning', 'This course unit has already been dropped.');
        }

        if ($item->isCore()) {
            return redirect()
                ->route('academic.registrations.add-drop.edit', $registration)
                ->with('error', 'Mandatory Core course units cannot be dropped under standard Add/Drop procedures.');
        }

        // Enforce credit load floor (minimum 12.0 CU)
        $remainingCredits = (float) $registration->items()
            ->where('status', '!=', 'dropped')
            ->where('id', '!=', $item->id)
            ->sum('credit_units');

        if ($remainingCredits < CourseEligibilityService::MIN_SEMESTER_CREDITS) {
            return redirect()
                ->route('academic.registrations.add-drop.edit', $registration)
                ->with('error', 'Cannot drop course unit '.($item->courseUnit->code ?? '').'. Dropping this course would leave '.$remainingCredits.' CU, falling below the mandatory minimum of '.CourseEligibilityService::MIN_SEMESTER_CREDITS.' CU.');
        }

        $requiresApproval = (bool) config('academic.require_registration_approval', false);

        $item->update([
            'status' => 'dropped',
            'dropped_at' => now(),
            'drop_reason' => $request->validated('drop_reason'),
        ]);

        $registration->status = $requiresApproval ? 'add_drop_pending' : 'approved';
        if (! $requiresApproval) {
            $registration->approved_at = now();
            $registration->approved_by_user_id = auth()->id() ?? User::first()?->id;
        }
        $registration->save();
        $registration->recalculateTotalCredits();

        $message = $requiresApproval
            ? 'Course unit '.($item->courseUnit->code ?? '').' dropped successfully. Slip updated and marked as Add/Drop Pending.'
            : 'Course unit '.($item->courseUnit->code ?? '').' dropped successfully. Slip updated and confirmed.';

        return redirect()
            ->route('academic.registrations.add-drop.edit', $registration)
            ->with('success', $message);
    }

    /**
     * Add a new elective course unit to the registration slip.
     */
    public function addCourse(CourseRegistration $registration, AddCourseRequest $request): RedirectResponse
    {
        if (! $registration->semester?->isAddDropOpen()) {
            return redirect()
                ->route('academic.registrations.add-drop.edit', $registration)
                ->with('error', 'The Add/Drop deadline for this semester has passed. Course adjustments are no longer allowed.');
        }

        if (! $registration->canAddDrop()) {
            return redirect()
                ->route('academic.registrations.add-drop.edit', $registration)
                ->with('error', 'This registration slip cannot undergo Add/Drop adjustments in its current status.');
        }

        $courseUnit = CourseUnit::with('prerequisites')->findOrFail($request->validated('course_unit_id'));

        if ($courseUnit->status !== 'active') {
            return redirect()
                ->route('academic.registrations.add-drop.edit', $registration)
                ->with('error', 'The selected course unit is inactive and cannot be enrolled.');
        }

        $existingItem = $registration->items()
            ->where('course_unit_id', $courseUnit->id)
            ->first();

        if ($existingItem && ! $existingItem->isDropped()) {
            return redirect()
                ->route('academic.registrations.add-drop.edit', $registration)
                ->with('error', "Course unit {$courseUnit->code} is already actively enrolled in this registration slip.");
        }

        // Enforce credit load ceiling (maximum 24.0 CU)
        $currentCredits = (float) $registration->items()
            ->where('status', '!=', 'dropped')
            ->sum('credit_units');

        if (($currentCredits + (float) $courseUnit->credit_units) > CourseEligibilityService::MAX_SEMESTER_CREDITS) {
            $projectedCredits = $currentCredits + (float) $courseUnit->credit_units;

            return redirect()
                ->route('academic.registrations.add-drop.edit', $registration)
                ->with('error', "Cannot add course unit {$courseUnit->code}. Total semester load ({$projectedCredits} CU) would exceed the maximum institutional ceiling of ".CourseEligibilityService::MAX_SEMESTER_CREDITS.' CU.');
        }

        // Validate prerequisite compliance (only when enforced by institution policy)
        if (config('academic.enforce_prerequisites', false)) {
            $completedCourseUnitIds = CourseRegistrationItem::whereHas('courseRegistration', function ($q) use ($registration) {
                $q->where('student_id', $registration->student_id)
                    ->where('status', 'approved')
                    ->where('semester_id', '!=', $registration->semester_id);
            })
                ->where('status', '!=', 'dropped')
                ->pluck('course_unit_id')
                ->toArray();

            $unmetPrerequisites = [];
            foreach ($courseUnit->prerequisites as $prereq) {
                if (! in_array($prereq->id, $completedCourseUnitIds, true)) {
                    $unmetPrerequisites[] = "{$prereq->code} ({$prereq->name})";
                }
            }

            if (! empty($unmetPrerequisites)) {
                return redirect()
                    ->route('academic.registrations.add-drop.edit', $registration)
                    ->with('error', "Cannot add course unit {$courseUnit->code}. Unmet prerequisite requirement(s): ".implode(', ', $unmetPrerequisites).'.');
            }
        }

        $requiresApproval = (bool) config('academic.require_registration_approval', false);
        $itemStatus = $requiresApproval ? 'registered' : 'approved';

        // Create or reactivate item
        if ($existingItem) {
            $existingItem->update([
                'status' => $itemStatus,
                'credit_units' => $courseUnit->credit_units,
                'dropped_at' => null,
                'drop_reason' => null,
            ]);
        } else {
            $registration->items()->create([
                'course_unit_id' => $courseUnit->id,
                'course_type' => 'Elective',
                'credit_units' => $courseUnit->credit_units,
                'status' => $itemStatus,
            ]);
        }

        $registration->status = $requiresApproval ? 'add_drop_pending' : 'approved';
        if (! $requiresApproval) {
            $registration->approved_at = now();
            $registration->approved_by_user_id = auth()->id() ?? User::first()?->id;
        }
        $registration->save();
        $registration->recalculateTotalCredits();

        $message = $requiresApproval
            ? "Course unit {$courseUnit->code} ({$courseUnit->name}) added successfully. Slip updated and marked as Add/Drop Pending."
            : "Course unit {$courseUnit->code} ({$courseUnit->name}) added successfully. Slip updated and confirmed.";

        return redirect()
            ->route('academic.registrations.add-drop.edit', $registration)
            ->with('success', $message);
    }
}
