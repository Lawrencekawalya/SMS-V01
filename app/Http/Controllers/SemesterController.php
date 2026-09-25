<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSemesterRequest;
use App\Http\Requests\UpdateSemesterRequest;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SemesterController extends Controller
{
    /**
     * Show the form for creating a new semester.
     */
    public function create(Request $request): View
    {
        $academicYearId = $request->query('academic_year_id');
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('academic.calendar.semesters.create', compact('academicYears', 'academicYearId'));
    }

    /**
     * Store a newly created semester in storage.
     */
    public function store(StoreSemesterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $isActive = ! empty($data['is_active']);
        $data['is_active'] = false; // We'll activate via activate() if requested

        $semester = Semester::create($data);

        if ($isActive) {
            $semester->activate();
        }

        return redirect()->route('academic.academic-years.index')
            ->with('success', "Semester '{$semester->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified semester.
     */
    public function edit(Semester $semester): View
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('academic.calendar.semesters.edit', compact('semester', 'academicYears'));
    }

    /**
     * Update the specified semester in storage.
     */
    public function update(UpdateSemesterRequest $request, Semester $semester): RedirectResponse
    {
        $data = $request->validated();
        $isActive = ! empty($data['is_active']);
        unset($data['is_active']);

        $semester->update($data);

        if ($isActive && ! $semester->is_active) {
            $semester->activate();
        }

        return redirect()->route('academic.academic-years.index')
            ->with('success', "Semester '{$semester->name}' updated successfully.");
    }

    /**
     * Remove the specified semester from storage.
     */
    public function destroy(Semester $semester): RedirectResponse
    {
        if ($semester->is_active) {
            return back()->with('error', 'Cannot delete the currently active semester.');
        }

        $name = $semester->name;
        $semester->delete();

        return redirect()->route('academic.academic-years.index')
            ->with('success', "Semester '{$name}' deleted successfully.");
    }

    /**
     * Activate the specified semester.
     */
    public function activate(Semester $semester): RedirectResponse
    {
        $semester->activate();

        return back()->with('success', "Semester '{$semester->name}' ({$semester->academicYear->name}) is now the active academic session.");
    }
}
