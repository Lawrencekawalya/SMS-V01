<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFacultyRequest;
use App\Http\Requests\UpdateFacultyRequest;
use App\Models\Campus;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacultyController extends Controller
{
    /**
     * Display a listing of the faculties.
     */
    public function index(Request $request): View
    {
        $campusId = $request->query('campus_id');

        $query = Faculty::with(['campus', 'dean'])->withCount('departments');

        if ($campusId) {
            $query->where('campus_id', $campusId);
        }

        $faculties = $query->orderBy('name')->get();
        $campuses = Campus::orderBy('name')->get();

        return view('academic.faculties.index', compact('faculties', 'campuses', 'campusId'));
    }

    /**
     * Show the form for creating a new faculty.
     */
    public function create(): View
    {
        $campuses = Campus::active()->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('academic.faculties.create', compact('campuses', 'users'));
    }

    /**
     * Store a newly created faculty in storage.
     */
    public function store(StoreFacultyRequest $request): RedirectResponse
    {
        Faculty::create($request->validated());

        return redirect()
            ->route('academic.faculties.index')
            ->with('success', 'Faculty created successfully.');
    }

    /**
     * Display the specified faculty.
     */
    public function show(Faculty $faculty): View
    {
        $faculty->load(['campus', 'dean', 'departments.hod', 'departments.programmes']);

        return view('academic.faculties.show', compact('faculty'));
    }

    /**
     * Show the form for editing the specified faculty.
     */
    public function edit(Faculty $faculty): View
    {
        $campuses = Campus::active()->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('academic.faculties.edit', compact('faculty', 'campuses', 'users'));
    }

    /**
     * Update the specified faculty in storage.
     */
    public function update(UpdateFacultyRequest $request, Faculty $faculty): RedirectResponse
    {
        $faculty->update($request->validated());

        return redirect()
            ->route('academic.faculties.index')
            ->with('success', 'Faculty updated successfully.');
    }

    /**
     * Remove the specified faculty from storage.
     */
    public function destroy(Faculty $faculty): RedirectResponse
    {
        if ($faculty->departments()->exists()) {
            return redirect()
                ->route('academic.faculties.index')
                ->with('error', 'Cannot delete faculty because it contains active departments.');
        }

        $faculty->delete();

        return redirect()
            ->route('academic.faculties.index')
            ->with('success', 'Faculty deleted successfully.');
    }
}
