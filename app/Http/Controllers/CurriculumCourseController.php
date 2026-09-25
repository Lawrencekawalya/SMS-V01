<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignCurriculumCourseRequest;
use App\Models\Curriculum;
use App\Models\CurriculumCourse;
use Illuminate\Http\RedirectResponse;

class CurriculumCourseController extends Controller
{
    /**
     * Assign a course unit to a curriculum study stage.
     */
    public function store(AssignCurriculumCourseRequest $request, Curriculum $curriculum): RedirectResponse
    {
        $curriculumCourse = $curriculum->curriculumCourses()->create($request->validated());
        $curriculumCourse->load('courseUnit');

        return redirect()->route('academic.curriculums.show', $curriculum)
            ->with('success', "Course '{$curriculumCourse->courseUnit->code}' allocated to Year {$curriculumCourse->study_year}, Semester {$curriculumCourse->semester} ({$curriculumCourse->course_type}).");
    }

    /**
     * Remove a course unit mapping from the curriculum.
     */
    public function destroy(Curriculum $curriculum, CurriculumCourse $curriculumCourse): RedirectResponse
    {
        if ($curriculumCourse->curriculum_id !== $curriculum->id) {
            abort(404);
        }

        $code = $curriculumCourse->courseUnit?->code ?? 'Course';
        $curriculumCourse->delete();

        return redirect()->route('academic.curriculums.show', $curriculum)
            ->with('success', "Course '{$code}' removed from curriculum.");
    }
}
