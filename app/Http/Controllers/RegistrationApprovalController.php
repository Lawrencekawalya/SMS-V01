<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectRegistrationRequest;
use App\Models\CourseRegistration;
use App\Models\Department;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationApprovalController extends Controller
{
    /**
     * Display the Academic Advisor & Registrar Course Registration Approvals Hub.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status', 'pending');

        $query = CourseRegistration::with([
            'student.programme.department',
            'student.curriculum',
            'semester.academicYear',
            'approvedBy',
            'items.courseUnit',
        ]);

        // Filter by Department
        if ($request->filled('department_id')) {
            $query->whereHas('student.programme', function ($q) use ($request) {
                $q->where('department_id', $request->integer('department_id'));
            });
        }

        // Filter by Programme
        if ($request->filled('programme_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('programme_id', $request->integer('programme_id'));
            });
        }

        // Filter by Study Year
        if ($request->filled('study_year')) {
            $query->where('study_year', $request->integer('study_year'));
        }

        // Filter by Status
        if ($statusFilter === 'pending') {
            $query->whereIn('status', ['submitted', 'add_drop_pending']);
        } elseif ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $registrations = $query->orderByDesc('submitted_at')
            ->orderByDesc('updated_at')
            ->get();

        // Calculate KPI summaries
        $kpi = [
            'total_pending' => CourseRegistration::whereIn('status', ['submitted', 'add_drop_pending'])->count(),
            'approved_term' => CourseRegistration::where('status', 'approved')->count(),
            'add_drop_pending' => CourseRegistration::where('status', 'add_drop_pending')->count(),
            'rejected_count' => CourseRegistration::where('status', 'rejected')->count(),
        ];

        $departments = Department::orderBy('name')->get();
        $programmes = Programme::orderBy('name')->get();
        $studyYears = [1, 2, 3, 4, 5];

        return view('academic.approvals.index', compact(
            'registrations',
            'kpi',
            'departments',
            'programmes',
            'studyYears',
            'statusFilter'
        ));
    }

    /**
     * Display detailed advisor inspection view for a course registration slip.
     */
    public function show(CourseRegistration $registration): View
    {
        $registration->load([
            'student.programme.department.faculty',
            'student.campus',
            'student.curriculum',
            'semester.academicYear',
            'approvedBy',
            'items.courseUnit.department',
            'items.courseUnit.prerequisites',
        ]);

        $coreItems = $registration->items->where('course_type', 'Core')->where('status', '!=', 'dropped');
        $electiveItems = $registration->items->where('course_type', '!=', 'Core')->where('status', '!=', 'dropped');
        $droppedItems = $registration->items->where('status', 'dropped');

        $coreCredits = (float) $coreItems->sum('credit_units');
        $electiveCredits = (float) $electiveItems->sum('credit_units');

        return view('academic.approvals.show', compact(
            'registration',
            'coreItems',
            'electiveItems',
            'droppedItems',
            'coreCredits',
            'electiveCredits'
        ));
    }

    /**
     * Formally approve a submitted or add/drop pending course registration slip.
     */
    public function approve(CourseRegistration $registration, Request $request): RedirectResponse
    {
        $reviewerId = auth()->id() ?? User::first()?->id;

        DB::transaction(function () use ($registration, $request, $reviewerId) {
            $remarks = $request->input('advisor_remarks') ?: 'Registration verified and approved by Academic Advisor.';

            $registration->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by_user_id' => $reviewerId,
                'advisor_remarks' => $remarks,
            ]);

            // Mark active enrolled course units as approved
            $registration->items()
                ->where('status', '!=', 'dropped')
                ->update(['status' => 'approved']);
        });

        return redirect()
            ->back()
            ->with('success', "Registration slip #REG-{$registration->id} for {$registration->student->full_name} has been officially approved.");
    }

    /**
     * Formally reject a submitted course registration slip and provide actionable feedback.
     */
    public function reject(CourseRegistration $registration, RejectRegistrationRequest $request): RedirectResponse
    {
        $reviewerId = auth()->id() ?? User::first()?->id;

        $registration->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by_user_id' => $reviewerId,
            'advisor_remarks' => $request->validated('advisor_remarks'),
        ]);

        return redirect()
            ->back()
            ->with('warning', "Registration slip #REG-{$registration->id} was rejected with corrective feedback sent to {$registration->student->full_name}.");
    }

    /**
     * Batch approve multiple selected pending registration slips.
     */
    public function batchApprove(Request $request): RedirectResponse
    {
        $request->validate([
            'registration_ids' => ['required', 'array', 'min:1'],
            'registration_ids.*' => ['integer', 'exists:course_registrations,id'],
        ]);

        $ids = $request->input('registration_ids', []);
        $reviewerId = auth()->id() ?? User::first()?->id;

        $count = 0;
        DB::transaction(function () use ($ids, $reviewerId, &$count) {
            $registrations = CourseRegistration::whereIn('id', $ids)
                ->whereIn('status', ['submitted', 'add_drop_pending'])
                ->get();

            foreach ($registrations as $registration) {
                $registration->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by_user_id' => $reviewerId,
                    'advisor_remarks' => 'Batch approved by Academic Advisor / Department Registrar.',
                ]);

                $registration->items()
                    ->where('status', '!=', 'dropped')
                    ->update(['status' => 'approved']);

                $count++;
            }
        });

        return redirect()
            ->route('academic.approvals.index')
            ->with('success', "Successfully batch approved {$count} course registration slip(s).");
    }
}
