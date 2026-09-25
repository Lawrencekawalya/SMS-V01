<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurriculumRequest;
use App\Http\Requests\UpdateCurriculumRequest;
use App\Models\CourseUnit;
use App\Models\Curriculum;
use App\Models\Programme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    /**
     * Display a listing of the curriculums.
     */
    public function index(Request $request): View
    {
        $programmeId = $request->query('programme_id');
        $status = $request->query('status');

        $query = Curriculum::with(['programme.department.faculty.campus'])
            ->withCount('curriculumCourses');

        if ($programmeId) {
            $query->where('programme_id', $programmeId);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active');
        }

        $curriculums = $query->orderBy('programme_id')->orderBy('start_academic_year', 'desc')->get();
        $programmes = Programme::with('department')->orderBy('name')->get();

        return view('academic.curriculums.index', compact('curriculums', 'programmes', 'programmeId', 'status'));
    }

    /**
     * Show the form for creating a new curriculum.
     */
    public function create(Request $request): View
    {
        $programmeId = $request->query('programme_id');
        $programmes = Programme::active()->with('department')->orderBy('name')->get();

        return view('academic.curriculums.create', compact('programmes', 'programmeId'));
    }

    /**
     * Store a newly created curriculum in storage.
     */
    public function store(StoreCurriculumRequest $request): RedirectResponse
    {
        $curriculum = Curriculum::create($request->validated());

        return redirect()->route('academic.curriculums.show', $curriculum)
            ->with('success', "Curriculum version '{$curriculum->version_name}' created successfully. You can now allocate course units to study stages.");
    }

    /**
     * Display the specified curriculum dashboard (Curriculum Matrix).
     */
    public function show(Curriculum $curriculum): View
    {
        $curriculum->load([
            'programme.department.faculty.campus',
            'curriculumCourses.courseUnit.department',
        ]);

        $durationYears = $curriculum->programme->duration_years ?: 3;

        // Structure stage matrix: for each year (1..durationYears), and each semester (1, 2)
        $matrix = [];
        $totalMappedCredits = 0.0;

        for ($year = 1; $year <= $durationYears; $year++) {
            $matrix[$year] = [
                1 => [
                    'courses' => collect(),
                    'credits' => 0.0,
                ],
                2 => [
                    'courses' => collect(),
                    'credits' => 0.0,
                ],
                'year_credits' => 0.0,
            ];
        }

        foreach ($curriculum->curriculumCourses as $courseMapping) {
            $year = $courseMapping->study_year;
            $sem = $courseMapping->semester;
            $cu = (float) ($courseMapping->courseUnit->credit_units ?? 0);

            if (! isset($matrix[$year])) {
                $matrix[$year] = [
                    1 => ['courses' => collect(), 'credits' => 0.0],
                    2 => ['courses' => collect(), 'credits' => 0.0],
                    'year_credits' => 0.0,
                ];
            }

            if (! isset($matrix[$year][$sem])) {
                $matrix[$year][$sem] = ['courses' => collect(), 'credits' => 0.0];
            }

            $matrix[$year][$sem]['courses']->push($courseMapping);
            $matrix[$year][$sem]['credits'] += $cu;
            $matrix[$year]['year_credits'] += $cu;
            $totalMappedCredits += $cu;
        }

        // Available course units that are not yet added to this curriculum
        $assignedCourseIds = $curriculum->curriculumCourses->pluck('course_unit_id')->all();
        $availableCourses = CourseUnit::active()
            ->whereNotIn('id', $assignedCourseIds)
            ->with('department')
            ->orderBy('code')
            ->get();

        return view('academic.curriculums.show', compact('curriculum', 'matrix', 'durationYears', 'totalMappedCredits', 'availableCourses'));
    }

    /**
     * Show the form for editing the specified curriculum.
     */
    public function edit(Curriculum $curriculum): View
    {
        $programmes = Programme::with('department')->orderBy('name')->get();

        return view('academic.curriculums.edit', compact('curriculum', 'programmes'));
    }

    /**
     * Update the specified curriculum in storage.
     */
    public function update(UpdateCurriculumRequest $request, Curriculum $curriculum): RedirectResponse
    {
        $curriculum->update($request->validated());

        return redirect()->route('academic.curriculums.show', $curriculum)
            ->with('success', "Curriculum '{$curriculum->version_name}' updated successfully.");
    }

    /**
     * Remove the specified curriculum from storage.
     */
    public function destroy(Curriculum $curriculum): RedirectResponse
    {
        $name = $curriculum->version_name;
        $curriculum->delete();

        return redirect()->route('academic.curriculums.index')
            ->with('success', "Curriculum '{$name}' deleted successfully.");
    }
}
