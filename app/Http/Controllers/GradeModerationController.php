<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReturnMarkSheetRequest;
use App\Models\AcademicYear;
use App\Models\CourseAssessmentSheet;
use App\Models\Department;
use App\Models\GradingScaleTier;
use App\Models\Semester;
use App\Models\User;
use App\Services\GradingEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradeModerationController extends Controller
{
    public function __construct(
        protected GradingEngineService $gradingEngine
    ) {}

    /**
     * Display the Head of Department Moderation Desk.
     */
    public function index(Request $request): View
    {
        $academicYearId = $request->query('academic_year_id');
        $semesterId = $request->query('semester_id');
        $departmentId = $request->query('department_id');
        $status = $request->query('status');

        $query = CourseAssessmentSheet::with([
            'courseUnit.department',
            'semester',
            'academicYear',
            'instructor',
            'moderatedBy',
            'publishedBy',
        ])->withCount([
            'studentMarks as total_students_count',
            'studentMarks as graded_students_count' => function ($q) {
                $q->whereNotNull('final_score');
            },
            'studentMarks as passed_students_count' => function ($q) {
                $q->where('is_passed', true);
            },
        ]);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        if ($departmentId) {
            $query->whereHas('courseUnit', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $sheets = $query->latest('submitted_at')->latest('updated_at')->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::with('academicYear')->orderByDesc('start_date')->get();
        $departments = Department::where('status', 'active')->orderBy('name')->get();

        $stats = [
            'total' => CourseAssessmentSheet::count(),
            'pending_hod' => CourseAssessmentSheet::where('status', 'submitted_to_hod')->count(),
            'moderated' => CourseAssessmentSheet::where('status', 'department_moderated')->count(),
            'published' => CourseAssessmentSheet::where('status', 'published')->count(),
            'returned' => CourseAssessmentSheet::where('status', 'returned_for_revision')->count(),
        ];

        return view('academic.assessments.moderation.index', compact(
            'sheets',
            'academicYears',
            'semesters',
            'departments',
            'academicYearId',
            'semesterId',
            'departmentId',
            'status',
            'stats'
        ));
    }

    /**
     * Display a comprehensive inspection view of a mark sheet for moderation.
     */
    public function show(CourseAssessmentSheet $sheet): View
    {
        $sheet->load([
            'courseUnit.department',
            'semester',
            'academicYear',
            'instructor',
            'moderatedBy',
            'publishedBy',
            'studentMarks' => function ($q) {
                $q->with([
                    'student.programme',
                    'auditLogs.changedBy',
                ]);
            },
        ]);

        $statistics = $this->gradingEngine->computeSheetStatistics($sheet);
        $gradingScaleTiers = GradingScaleTier::ordered()->get();

        return view('academic.assessments.moderation.show', compact('sheet', 'statistics', 'gradingScaleTiers'));
    }

    /**
     * Head of Department endorses the mark sheet, advancing it for Senate sign-off.
     */
    public function endorse(Request $request, CourseAssessmentSheet $sheet): RedirectResponse
    {
        if (! in_array($sheet->status, ['submitted_to_hod', 'returned_for_revision'], true)) {
            return redirect()
                ->route('moderation.show', $sheet)
                ->with('warning', "Mark sheet cannot be endorsed because its current status is '{$sheet->status_label}'.");
        }

        $user = auth()->user() ?? User::first();
        $remarks = $request->input('remarks');

        $sheet->update([
            'status' => 'department_moderated',
            'moderated_at' => now(),
            'moderated_by_id' => $user?->id,
            'moderation_remarks' => $remarks ? trim($remarks) : $sheet->moderation_remarks,
        ]);

        return redirect()
            ->route('moderation.show', $sheet)
            ->with('success', "Mark sheet for {$sheet->courseUnit->code} has been endorsed by the Department and recommended for Senate publication.");
    }

    /**
     * Head of Department returns the mark sheet to the lecturer for revisions.
     */
    public function returnToLecturer(ReturnMarkSheetRequest $request, CourseAssessmentSheet $sheet): RedirectResponse
    {
        if (! in_array($sheet->status, ['submitted_to_hod', 'department_moderated'], true)) {
            return redirect()
                ->route('moderation.show', $sheet)
                ->with('warning', "Mark sheet cannot be returned because its current status is '{$sheet->status_label}'.");
        }

        $user = auth()->user() ?? User::first();

        $sheet->update([
            'status' => 'returned_for_revision',
            'moderated_at' => now(),
            'moderated_by_id' => $user?->id,
            'moderation_remarks' => $request->validated('remarks'),
        ]);

        $instructorName = $sheet->instructor?->name ?? 'the instructor';

        return redirect()
            ->route('moderation.show', $sheet)
            ->with('warning', "Mark sheet for {$sheet->courseUnit->code} has been returned to {$instructorName} for revision.");
    }

    /**
     * Senate / Academic Registrar officially approves and publishes the marks.
     * This triggers automatic calculation of semester GPAs and cumulative CGPAs.
     */
    public function publish(Request $request, CourseAssessmentSheet $sheet): RedirectResponse
    {
        if ($sheet->status === 'published') {
            return redirect()
                ->route('moderation.show', $sheet)
                ->with('info', "Mark sheet for {$sheet->courseUnit->code} is already officially published.");
        }

        if (! in_array($sheet->status, ['department_moderated', 'submitted_to_hod'], true)) {
            return redirect()
                ->route('moderation.show', $sheet)
                ->with('error', "Mark sheet cannot be published directly from status '{$sheet->status_label}'.");
        }

        DB::transaction(function () use ($sheet) {
            $user = auth()->user() ?? User::first();

            $sheet->update([
                'status' => 'published',
                'published_at' => now(),
                'published_by_id' => $user?->id,
            ]);

            // Retrieve all distinct students on this mark sheet
            $students = $sheet->studentMarks()
                ->with('student')
                ->get()
                ->pluck('student')
                ->filter()
                ->unique('id');

            // Trigger automated recalculation of semester GPA and cumulative CGPA
            foreach ($students as $student) {
                $this->gradingEngine->calculateSemesterGpa($student, $sheet->semester);
            }
        });

        $count = $sheet->studentMarks()->count();

        return redirect()
            ->route('moderation.show', $sheet)
            ->with('success', "Mark sheet for {$sheet->courseUnit->code} has been officially approved and published by Senate. Semester GPAs and cumulative CGPAs recalculated for {$count} students.");
    }
}
