<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProgrammeRequest;
use App\Http\Requests\UpdateProgrammeRequest;
use App\Models\Department;
use App\Models\Programme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgrammeController extends Controller
{
    /**
     * Display a listing of the academic programmes.
     */
    public function index(Request $request): View
    {
        $departmentId = $request->query('department_id');
        $awardType = $request->query('award_type');

        $query = Programme::with(['department.faculty.campus']);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($awardType) {
            $query->where('award_type', $awardType);
        }

        $programmes = $query->orderBy('name')->get();
        $departments = Department::with('faculty')->orderBy('name')->get();
        $awardTypes = ['Certificate', 'Diploma', 'Bachelors', 'Postgraduate Diploma', 'Masters', 'Doctorate'];

        return view('academic.programmes.index', compact('programmes', 'departments', 'departmentId', 'awardType', 'awardTypes'));
    }

    /**
     * Show the form for creating a new programme.
     */
    public function create(Request $request): View
    {
        $selectedDepartmentId = $request->query('department_id');
        $departments = Department::active()->with('faculty.campus')->orderBy('name')->get();
        $awardTypes = ['Certificate', 'Diploma', 'Bachelors', 'Postgraduate Diploma', 'Masters', 'Doctorate'];

        return view('academic.programmes.create', compact('departments', 'awardTypes', 'selectedDepartmentId'));
    }

    /**
     * Store a newly created programme in storage.
     */
    public function store(StoreProgrammeRequest $request): RedirectResponse
    {
        Programme::create($request->validated());

        return redirect()
            ->route('academic.programmes.index')
            ->with('success', 'Academic programme created successfully.');
    }

    /**
     * Display the specified programme.
     */
    public function show(Programme $programme): View
    {
        $programme->load([
            'department.faculty.campus',
            'curriculums.curriculumCourses.courseUnit.department',
        ]);

        $activeCurriculum = $programme->curriculums->firstWhere('is_active', true)
            ?? $programme->curriculums->first();

        $matrix = [];
        $totalMappedCredits = 0.0;

        if ($activeCurriculum) {
            $durationYears = $programme->duration_years ?: 3;

            for ($year = 1; $year <= $durationYears; $year++) {
                $matrix[$year] = [
                    1 => ['courses' => collect(), 'credits' => 0.0],
                    2 => ['courses' => collect(), 'credits' => 0.0],
                    'year_credits' => 0.0,
                ];
            }

            foreach ($activeCurriculum->curriculumCourses as $courseMapping) {
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
        }

        return view('academic.programmes.show', compact('programme', 'activeCurriculum', 'matrix', 'totalMappedCredits'));
    }

    /**
     * Show the form for editing the specified programme.
     */
    public function edit(Programme $programme): View
    {
        $departments = Department::active()->with('faculty.campus')->orderBy('name')->get();
        $awardTypes = ['Certificate', 'Diploma', 'Bachelors', 'Postgraduate Diploma', 'Masters', 'Doctorate'];

        return view('academic.programmes.edit', compact('programme', 'departments', 'awardTypes'));
    }

    /**
     * Update the specified programme in storage.
     */
    public function update(UpdateProgrammeRequest $request, Programme $programme): RedirectResponse
    {
        $programme->update($request->validated());

        return redirect()
            ->route('academic.programmes.index')
            ->with('success', 'Academic programme updated successfully.');
    }

    /**
     * Remove the specified programme from storage.
     */
    public function destroy(Programme $programme): RedirectResponse
    {
        $programme->delete();

        return redirect()
            ->route('academic.programmes.index')
            ->with('success', 'Academic programme deleted successfully.');
    }
}
