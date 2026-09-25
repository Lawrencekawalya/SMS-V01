<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index(Request $request): View
    {
        $facultyId = $request->query('faculty_id');

        $query = Department::with(['faculty.campus', 'hod'])->withCount('programmes');

        if ($facultyId) {
            $query->where('faculty_id', $facultyId);
        }

        $departments = $query->orderBy('name')->get();
        $faculties = Faculty::with('campus')->orderBy('name')->get();

        return view('academic.departments.index', compact('departments', 'faculties', 'facultyId'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create(Request $request): View
    {
        $selectedFacultyId = $request->query('faculty_id');
        $faculties = Faculty::active()->with('campus')->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('academic.departments.create', compact('faculties', 'users', 'selectedFacultyId'));
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        Department::create($request->validated());

        return redirect()
            ->route('academic.departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department): View
    {
        $department->load(['faculty.campus', 'hod', 'programmes']);

        return view('academic.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department): View
    {
        $faculties = Faculty::active()->with('campus')->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('academic.departments.edit', compact('department', 'faculties', 'users'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return redirect()
            ->route('academic.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department): RedirectResponse
    {
        if ($department->programmes()->exists()) {
            return redirect()
                ->route('academic.departments.index')
                ->with('error', 'Cannot delete department because it contains active degree programmes.');
        }

        $department->delete();

        return redirect()
            ->route('academic.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
