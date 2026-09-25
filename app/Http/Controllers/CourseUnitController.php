<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseUnitRequest;
use App\Http\Requests\UpdateCourseUnitRequest;
use App\Models\CourseUnit;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseUnitController extends Controller
{
    /**
     * Display a listing of the course units.
     */
    public function index(Request $request): View
    {
        $departmentId = $request->query('department_id');
        $status = $request->query('status');

        $query = CourseUnit::with(['department.faculty.campus']);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $courseUnits = $query->orderBy('code')->get();
        $departments = Department::with('faculty.campus')->orderBy('name')->get();

        return view('academic.courses.index', compact('courseUnits', 'departments', 'departmentId', 'status'));
    }

    /**
     * Show the form for creating a new course unit.
     */
    public function create(Request $request): View
    {
        $departmentId = $request->query('department_id');
        $departments = Department::with('faculty.campus')->orderBy('name')->get();

        return view('academic.courses.create', compact('departments', 'departmentId'));
    }

    /**
     * Store a newly created course unit in storage.
     */
    public function store(StoreCourseUnitRequest $request): RedirectResponse
    {
        $courseUnit = CourseUnit::create($request->validated());

        return redirect()->route('academic.courses.index')
            ->with('success', "Course Unit '{$courseUnit->code} - {$courseUnit->name}' created successfully.");
    }

    /**
     * Display the specified course unit.
     */
    public function show(CourseUnit $courseUnit): View
    {
        $courseUnit->load('department.faculty.campus');

        return view('academic.courses.show', compact('courseUnit'));
    }

    /**
     * Show the form for editing the specified course unit.
     */
    public function edit(CourseUnit $courseUnit): View
    {
        $departments = Department::with('faculty.campus')->orderBy('name')->get();

        return view('academic.courses.edit', compact('courseUnit', 'departments'));
    }

    /**
     * Update the specified course unit in storage.
     */
    public function update(UpdateCourseUnitRequest $request, CourseUnit $courseUnit): RedirectResponse
    {
        $courseUnit->update($request->validated());

        return redirect()->route('academic.courses.index')
            ->with('success', "Course Unit '{$courseUnit->code}' updated successfully.");
    }

    /**
     * Remove the specified course unit from storage.
     */
    public function destroy(CourseUnit $courseUnit): RedirectResponse
    {
        $code = $courseUnit->code;
        $courseUnit->delete();

        return redirect()->route('academic.courses.index')
            ->with('success', "Course Unit '{$code}' deleted successfully.");
    }
}
