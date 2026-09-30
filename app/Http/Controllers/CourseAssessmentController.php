<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitMarksRequest;
use App\Http\Requests\UpdateAssessmentPolicyRequest;
use App\Models\AcademicYear;
use App\Models\AwardClassification;
use App\Models\CourseAssessmentSheet;
use App\Models\CourseRegistrationItem;
use App\Models\Department;
use App\Models\GradingScaleTier;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\User;
use App\Services\GradingEngineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourseAssessmentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected GradingEngineService $gradingEngine
    ) {}

    /**
     * Display the institutional assessment policy and grading scale configuration.
     */
    public function policy(): View
    {
        $caWeight = (float) config('academic.assessment_ca_weight', 40.0);
        $examWeight = (float) config('academic.assessment_exam_weight', 60.0);
        $passMark = (float) config('academic.assessment_pass_mark', 50.0);

        // Ensure database records exist; fallback to defaults if empty
        if (GradingScaleTier::count() === 0) {
            GradingScaleTier::seedDefaults();
        }
        if (AwardClassification::count() === 0) {
            AwardClassification::seedDefaults();
        }

        $gradingScale = GradingScaleTier::ordered()->get();
        $degreeClassifications = AwardClassification::forLevel('degree')->get();
        $diplomaClassifications = AwardClassification::forLevel('diploma')->get();
        $certificateClassifications = AwardClassification::forLevel('certificate')->get();

        $stats = [
            'total_sheets' => CourseAssessmentSheet::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'total_graded' => StudentMark::whereNotNull('final_score')->count(),
            'passed_count' => StudentMark::where('is_passed', true)->count(),
        ];

        return view('academic.assessments.policy', compact(
            'caWeight',
            'examWeight',
            'passMark',
            'gradingScale',
            'degreeClassifications',
            'diplomaClassifications',
            'certificateClassifications',
            'stats'
        ));
    }

    /**
     * Update the institutional assessment policy weights and pass threshold.
     */
    public function updatePolicy(UpdateAssessmentPolicyRequest $request): RedirectResponse
    {
        $ca = (float) $request->input('ca_weight');
        $exam = (float) $request->input('exam_weight');
        $pass = (float) $request->input('pass_mark');

        // Update environment file if writable and not running automated tests
        if (! app()->environment('testing')) {
            $envPath = base_path('.env');
            if (file_exists($envPath) && is_writable($envPath)) {
                $envContent = (string) file_get_contents($envPath);

                $replacements = [
                    'ASSESSMENT_CA_WEIGHT' => $ca,
                    'ASSESSMENT_EXAM_WEIGHT' => $exam,
                    'ASSESSMENT_PASS_MARK' => $pass,
                ];

                foreach ($replacements as $key => $value) {
                    if (preg_match("/^{$key}=.*/m", $envContent)) {
                        $envContent = (string) preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
                    } else {
                        $envContent .= "\n{$key}={$value}\n";
                    }
                }

                file_put_contents($envPath, $envContent);
            }
        }

        config([
            'academic.assessment_ca_weight' => $ca,
            'academic.assessment_exam_weight' => $exam,
            'academic.assessment_pass_mark' => $pass,
        ]);

        return redirect()
            ->route('academic.assessments.policy')
            ->with('success', 'Institutional assessment weighting and minimum pass threshold updated successfully.');
    }

    /**
     * Update the grading scale tiers (mark ranges, GP, classifications).
     */
    public function updateGradingScale(Request $request): RedirectResponse
    {
        $request->validate([
            'tiers' => ['required', 'array'],
            'tiers.*.id' => ['required', 'exists:grading_scale_tiers,id'],
            'tiers.*.grade_letter' => ['required', 'string', 'max:5'],
            'tiers.*.min_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'tiers.*.max_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'tiers.*.grade_point' => ['required', 'numeric', 'min:0', 'max:5'],
            'tiers.*.classification' => ['required', 'string', 'max:100'],
        ]);

        foreach ($request->input('tiers') as $tierData) {
            $tier = GradingScaleTier::findOrFail($tierData['id']);
            $isPass = $tierData['grade_letter'] !== 'F' && (float) $tierData['grade_point'] > 0.0;

            $badgeClass = match ($tierData['grade_letter']) {
                'A', 'B+' => 'text-bg-success',
                'B' => 'text-bg-primary',
                'C+', 'C' => 'text-bg-info',
                'D+', 'D' => 'text-bg-warning',
                default => 'text-bg-danger',
            };

            $tier->update([
                'grade_letter' => trim($tierData['grade_letter']),
                'min_score' => (float) $tierData['min_score'],
                'max_score' => (float) $tierData['max_score'],
                'grade_point' => (float) $tierData['grade_point'],
                'classification' => trim($tierData['classification']),
                'badge_class' => $badgeClass,
                'is_pass' => $isPass,
            ]);
        }

        return redirect()
            ->route('academic.assessments.policy')
            ->with('success', 'Grading scale tiers updated successfully.');
    }

    /**
     * Reset the grading scale tiers to official NCHE 5.0 defaults.
     */
    public function resetGradingScale(): RedirectResponse
    {
        GradingScaleTier::seedDefaults();

        return redirect()
            ->route('academic.assessments.policy')
            ->with('success', 'Grading scale reset to official NCHE 5.0 statutory standards.');
    }

    /**
     * Update degree, diploma, or certificate award classifications.
     */
    public function updateAwardClassifications(Request $request): RedirectResponse
    {
        $request->validate([
            'awards' => ['required', 'array'],
            'awards.*.id' => ['required', 'exists:award_classifications,id'],
            'awards.*.name' => ['required', 'string', 'max:100'],
            'awards.*.min_cgpa' => ['required', 'numeric', 'min:0', 'max:5'],
            'awards.*.max_cgpa' => ['required', 'numeric', 'min:0', 'max:5'],
            'awards.*.academic_standing' => ['required', 'string', 'max:40'],
        ]);

        foreach ($request->input('awards') as $awardData) {
            $award = AwardClassification::findOrFail($awardData['id']);
            $badgeClass = match ($awardData['academic_standing']) {
                'Normal Progress' => ((float) $awardData['min_cgpa'] >= 4.0 ? 'text-bg-success' : 'text-bg-primary'),
                'Probation' => 'text-bg-danger',
                default => 'text-bg-info',
            };

            $award->update([
                'name' => trim($awardData['name']),
                'min_cgpa' => (float) $awardData['min_cgpa'],
                'max_cgpa' => (float) $awardData['max_cgpa'],
                'academic_standing' => trim($awardData['academic_standing']),
                'badge_class' => $badgeClass,
            ]);
        }

        return redirect()
            ->route('academic.assessments.policy')
            ->with('success', 'Academic award classifications updated successfully.');
    }

    /**
     * Reset award classifications to collegiate defaults.
     */
    public function resetAwardClassifications(): RedirectResponse
    {
        AwardClassification::seedDefaults();

        return redirect()
            ->route('academic.assessments.policy')
            ->with('success', 'Award classifications reset to standard collegiate defaults.');
    }

    /**
     * Display a listing of course assessment mark sheets.
     */
    public function index(Request $request): View
    {
        // Automatically ensure mark sheets and student rosters exist for all registered courses
        $this->syncAssessmentSheetsFromRegistrations();

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
        ])->withCount(['studentMarks as total_students_count', 'studentMarks as graded_students_count' => function ($q) {
            $q->whereNotNull('final_score');
        }]);

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

        $sheets = $query->latest('created_at')->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::with('academicYear')->orderByDesc('start_date')->get();
        $departments = Department::where('status', 'active')->orderBy('name')->get();

        $stats = [
            'total' => CourseAssessmentSheet::count(),
            'draft' => CourseAssessmentSheet::where('status', 'draft')->count(),
            'submitted' => CourseAssessmentSheet::where('status', 'submitted_to_hod')->count(),
            'moderated' => CourseAssessmentSheet::where('status', 'department_moderated')->count(),
            'published' => CourseAssessmentSheet::where('status', 'published')->count(),
            'returned' => CourseAssessmentSheet::where('status', 'returned_for_revision')->count(),
            'total_graded_marks' => StudentMark::whereNotNull('final_score')->count(),
        ];

        return view('academic.assessments.index', compact(
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
     * Display the specified assessment mark sheet.
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

        return view('academic.assessments.show', compact('sheet'));
    }

    /**
     * Show the interactive mark entry spreadsheet workspace for lecturers.
     */
    public function edit(CourseAssessmentSheet $sheet): View|RedirectResponse
    {
        if (! in_array($sheet->status, ['draft', 'returned_for_revision'], true)) {
            return redirect()
                ->route('academic.assessments.show', $sheet)
                ->with('warning', "Mark sheet for {$sheet->courseUnit->code} is currently '{$sheet->status_label}' and is locked against modifications.");
        }

        // Auto-synchronize registered students who do not yet have a mark entry record
        $registrationItems = CourseRegistrationItem::with('courseRegistration')
            ->whereHas('courseRegistration', function ($q) use ($sheet) {
                $q->where('semester_id', $sheet->semester_id);
            })
            ->where('course_unit_id', $sheet->course_unit_id)
            ->where('status', '!=', 'dropped')
            ->get();

        foreach ($registrationItems as $regItem) {
            if ($regItem->courseRegistration) {
                StudentMark::firstOrCreate(
                    [
                        'course_assessment_sheet_id' => $sheet->id,
                        'student_id' => $regItem->courseRegistration->student_id,
                    ],
                    [
                        'course_registration_item_id' => $regItem->id,
                    ]
                );
            }
        }

        $sheet->load([
            'courseUnit.department',
            'semester',
            'academicYear',
            'instructor',
        ]);

        $studentMarks = $sheet->studentMarks()
            ->with(['student.programme'])
            ->join('students', 'student_marks.student_id', '=', 'students.id')
            ->orderBy('students.registration_number')
            ->select('student_marks.*')
            ->get();

        $gradingScaleTiers = GradingScaleTier::ordered()->get();

        return view('academic.assessments.entry', compact('sheet', 'studentMarks', 'gradingScaleTiers'));
    }

    /**
     * Batch save student coursework and examination marks as draft or submit to HoD.
     */
    public function update(SubmitMarksRequest $request, CourseAssessmentSheet $sheet): RedirectResponse
    {
        if (! in_array($sheet->status, ['draft', 'returned_for_revision'], true)) {
            return redirect()
                ->route('academic.assessments.show', $sheet)
                ->with('error', "Mark sheet cannot be updated because it is currently '{$sheet->status_label}'.");
        }

        DB::transaction(function () use ($request, $sheet) {
            $user = auth()->user() ?? User::first();
            $marksData = $request->input('marks', []);

            foreach ($marksData as $item) {
                /** @var StudentMark $mark */
                $mark = StudentMark::where('course_assessment_sheet_id', $sheet->id)
                    ->where('id', $item['student_mark_id'])
                    ->firstOrFail();

                $caInput = isset($item['ca_score']) && $item['ca_score'] !== '' && $item['ca_score'] !== null
                    ? (float) $item['ca_score']
                    : null;
                $examInput = isset($item['exam_score']) && $item['exam_score'] !== '' && $item['exam_score'] !== null
                    ? (float) $item['exam_score']
                    : null;
                $remarks = ! empty($item['lecturer_remarks']) ? trim($item['lecturer_remarks']) : null;

                $oldCa = $mark->ca_score !== null ? (float) $mark->ca_score : null;
                $oldExam = $mark->exam_score !== null ? (float) $mark->exam_score : null;
                $oldFinal = $mark->final_score !== null ? (float) $mark->final_score : null;

                if ($caInput !== null && $examInput !== null) {
                    $computed = $this->gradingEngine->computeMark($caInput, $examInput, $sheet);
                    $mark->update([
                        'ca_score' => $computed['ca_score'],
                        'exam_score' => $computed['exam_score'],
                        'final_score' => $computed['final_score'],
                        'grade_letter' => $computed['grade_letter'],
                        'grade_point' => $computed['grade_point'],
                        'is_passed' => $computed['is_passed'],
                        'is_retake' => $computed['is_retake'],
                        'lecturer_remarks' => $remarks,
                    ]);

                    // Audit logs if scores were changed from previous recorded final score
                    if ($oldFinal !== null && $user && ($oldCa !== $computed['ca_score'] || $oldExam !== $computed['exam_score'])) {
                        if ($oldCa !== null && $oldCa !== $computed['ca_score']) {
                            $this->gradingEngine->recordGradeAudit($mark, $user, 'ca', $oldCa, $computed['ca_score'], $remarks ?? 'Coursework score revised by instructor.');
                        }
                        if ($oldExam !== null && $oldExam !== $computed['exam_score']) {
                            $this->gradingEngine->recordGradeAudit($mark, $user, 'exam', $oldExam, $computed['exam_score'], $remarks ?? 'Examination score revised by instructor.');
                        }
                    }
                } else {
                    $mark->update([
                        'ca_score' => $caInput,
                        'exam_score' => $examInput,
                        'final_score' => null,
                        'grade_letter' => null,
                        'grade_point' => null,
                        'is_passed' => false,
                        'is_retake' => false,
                        'lecturer_remarks' => $remarks,
                    ]);
                }
            }

            if ($request->input('action') === 'submit_hod') {
                $sheet->update([
                    'status' => 'submitted_to_hod',
                    'submitted_at' => now(),
                ]);
            }
        });

        if ($request->input('action') === 'submit_hod') {
            return redirect()
                ->route('academic.assessments.show', $sheet)
                ->with('success', "Marks successfully saved and mark sheet for {$sheet->courseUnit->code} submitted to Head of Department for moderation.");
        }

        return redirect()
            ->route('academic.assessments.edit', $sheet)
            ->with('success', "Student marks for {$sheet->courseUnit->code} saved as draft successfully.");
    }

    /**
     * Formally submit a completed draft or revised mark sheet to the Head of Department.
     */
    public function submitToHod(CourseAssessmentSheet $sheet): RedirectResponse
    {
        if (! in_array($sheet->status, ['draft', 'returned_for_revision'], true)) {
            return redirect()
                ->route('academic.assessments.show', $sheet)
                ->with('warning', "Only draft or returned mark sheets can be submitted to the HoD. Current status: {$sheet->status_label}.");
        }

        $sheet->update([
            'status' => 'submitted_to_hod',
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('academic.assessments.show', $sheet)
            ->with('success', "Mark sheet for {$sheet->courseUnit->code} has been submitted to the Head of Department for departmental moderation.");
    }

    /**
     * Automatically synchronize assessment sheets and student mark rosters
     * for all courses that students have registered for across active semesters.
     */
    protected function syncAssessmentSheetsFromRegistrations(): void
    {
        $caWeight = (float) config('academic.assessment_ca_weight', 40.0);
        $examWeight = (float) config('academic.assessment_exam_weight', 60.0);
        $passMark = (float) config('academic.assessment_pass_mark', 50.0);

        $distinctCourses = CourseRegistrationItem::query()
            ->join('course_registrations', 'course_registration_items.course_registration_id', '=', 'course_registrations.id')
            ->where('course_registration_items.status', '!=', 'dropped')
            ->select([
                'course_registration_items.course_unit_id',
                'course_registrations.semester_id',
                'course_registrations.academic_year_id',
            ])
            ->distinct()
            ->get();

        foreach ($distinctCourses as $record) {
            $sheet = CourseAssessmentSheet::firstOrCreate(
                [
                    'course_unit_id' => $record->course_unit_id,
                    'semester_id' => $record->semester_id,
                ],
                [
                    'academic_year_id' => $record->academic_year_id,
                    'ca_weight' => $caWeight,
                    'exam_weight' => $examWeight,
                    'pass_mark' => $passMark,
                    'status' => 'draft',
                ]
            );

            // Synchronize registered students who are not yet on this sheet
            $items = CourseRegistrationItem::query()
                ->whereHas('courseRegistration', function ($q) use ($sheet) {
                    $q->where('semester_id', $sheet->semester_id);
                })
                ->where('course_unit_id', $sheet->course_unit_id)
                ->where('status', '!=', 'dropped')
                ->with('courseRegistration')
                ->get();

            foreach ($items as $item) {
                if ($item->courseRegistration) {
                    StudentMark::firstOrCreate(
                        [
                            'course_assessment_sheet_id' => $sheet->id,
                            'student_id' => $item->courseRegistration->student_id,
                        ],
                        [
                            'course_registration_item_id' => $item->id,
                        ]
                    );
                }
            }
        }
    }
}
