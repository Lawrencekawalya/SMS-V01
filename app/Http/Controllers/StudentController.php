<?php

namespace App\Http\Controllers;

use App\Models\Programme;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request): View
    {
        $programmeId = $request->query('programme_id');
        $studyYear = $request->query('study_year');
        $status = $request->query('status');

        $query = Student::with([
            'programme.department',
            'curriculum',
            'campus',
            'admissionAcademicYear',
        ]);

        if ($programmeId) {
            $query->where('programme_id', $programmeId);
        }

        if ($studyYear) {
            $query->where('current_study_year', $studyYear);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $students = $query->orderBy('registration_number')->get();
        $programmes = Programme::orderBy('name')->get();

        $stats = [
            'total' => Student::count(),
            'active' => Student::where('status', 'active')->count(),
            'freshers' => Student::where('current_study_year', 1)->where('current_semester', 1)->count(),
            'continuing' => Student::where('current_study_year', '>', 1)->count(),
        ];

        return view('academic.students.index', compact('students', 'programmes', 'programmeId', 'studyYear', 'status', 'stats'));
    }

    /**
     * Display the specified student profile.
     */
    public function show(Student $student): View
    {
        $student->load([
            'programme.department.faculty.campus',
            'curriculum.curriculumCourses.courseUnit',
            'campus',
            'admissionAcademicYear',
            'academicAdvisor',
        ]);

        return view('academic.students.show', compact('student'));
    }
}
