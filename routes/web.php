<?php

use App\Http\Controllers\AcademicEventController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\CourseUnitController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\CurriculumCourseController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UniversityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::prefix('academic')->name('academic.')->group(function () {
    // University Profile
    Route::get('university', [UniversityController::class, 'edit'])->name('university.edit');
    Route::put('university/{university}', [UniversityController::class, 'update'])->name('university.update');

    // Campuses
    Route::resource('campuses', CampusController::class);

    // Faculties
    Route::resource('faculties', FacultyController::class);

    // Departments
    Route::resource('departments', DepartmentController::class);

    // Programmes
    Route::resource('programmes', ProgrammeController::class);

    // Academic Years & Calendar
    Route::post('academic-years/{academic_year}/make-current', [AcademicYearController::class, 'makeCurrent'])->name('academic-years.make-current');
    Route::resource('academic-years', AcademicYearController::class);

    // Semesters
    Route::post('semesters/{semester}/activate', [SemesterController::class, 'activate'])->name('semesters.activate');
    Route::resource('semesters', SemesterController::class)->except(['index', 'show']);

    // Course Units / Master Catalog
    Route::resource('courses', CourseUnitController::class)->parameters(['courses' => 'course_unit']);

    // Curriculums & Progression Matrix
    Route::resource('curriculums', CurriculumController::class);
    Route::post('curriculums/{curriculum}/courses', [CurriculumCourseController::class, 'store'])->name('curriculums.courses.store');
    Route::delete('curriculums/{curriculum}/courses/{curriculumCourse}', [CurriculumCourseController::class, 'destroy'])->name('curriculums.courses.destroy');

    // Scheduled Academic Events & Almanac
    Route::get('events', [AcademicEventController::class, 'index'])->name('events.index');
    Route::get('events/feed', [AcademicEventController::class, 'feed'])->name('events.feed');
    Route::post('events', [AcademicEventController::class, 'store'])->name('events.store');
    Route::put('events/{academic_event}', [AcademicEventController::class, 'update'])->name('events.update');
    Route::delete('events/{academic_event}', [AcademicEventController::class, 'destroy'])->name('events.destroy');

    // Students Directory (Academic Enrollment Foundation)
    Route::resource('students', StudentController::class)->only(['index', 'show']);
});
