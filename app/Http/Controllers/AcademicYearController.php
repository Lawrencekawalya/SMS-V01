<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years and sessions.
     */
    public function index(): View
    {
        $academicYears = AcademicYear::with('semesters')
            ->orderByDesc('start_date')
            ->get();

        $currentYear = AcademicYear::with('semesters')->where('is_current', true)->first();
        $activeSemester = Semester::with('academicYear')->where('is_active', true)->first();

        $academicEvents = AcademicEvent::with(['academicYear', 'semester'])
            ->orderBy('start_date')
            ->get();

        return view('academic.calendar.index', compact('academicYears', 'currentYear', 'activeSemester', 'academicEvents'));
    }

    /**
     * Show the form for creating a new academic year.
     */
    public function create(): View
    {
        return view('academic.calendar.years.create');
    }

    /**
     * Store a newly created academic year in storage.
     */
    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $isCurrent = ! empty($data['is_current']);
        $data['is_current'] = false; // We'll use makeCurrent() if requested

        $academicYear = AcademicYear::create($data);

        if ($isCurrent) {
            $academicYear->makeCurrent();
        }

        return redirect()->route('academic.academic-years.index')
            ->with('success', "Academic Year '{$academicYear->name}' created successfully.");
    }

    /**
     * Display the specified academic year.
     */
    public function show(AcademicYear $academicYear): View
    {
        $academicYear->load('semesters');

        return view('academic.calendar.years.show', compact('academicYear'));
    }

    /**
     * Show the form for editing the specified academic year.
     */
    public function edit(AcademicYear $academicYear): View
    {
        return view('academic.calendar.years.edit', compact('academicYear'));
    }

    /**
     * Update the specified academic year in storage.
     */
    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $data = $request->validated();
        $isCurrent = ! empty($data['is_current']);
        unset($data['is_current']);

        $academicYear->update($data);

        if ($isCurrent && ! $academicYear->is_current) {
            $academicYear->makeCurrent();
        }

        return redirect()->route('academic.academic-years.index')
            ->with('success', "Academic Year '{$academicYear->name}' updated successfully.");
    }

    /**
     * Remove the specified academic year from storage.
     */
    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_current) {
            return back()->with('error', 'Cannot delete the currently active Academic Year.');
        }

        if ($academicYear->semesters()->where('is_active', true)->exists()) {
            return back()->with('error', 'Cannot delete an Academic Year that contains an active semester.');
        }

        $name = $academicYear->name;
        $academicYear->delete();

        return redirect()->route('academic.academic-years.index')
            ->with('success', "Academic Year '{$name}' deleted successfully.");
    }

    /**
     * Set the specified academic year as the current academic year.
     */
    public function makeCurrent(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->makeCurrent();

        return back()->with('success', "Academic Year '{$academicYear->name}' is now set as the current academic year.");
    }
}
