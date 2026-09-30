<?php

use App\Http\Controllers\AcademicEventController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AddDropController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\CourseAssessmentController;
use App\Http\Controllers\CourseRegistrationController;
use App\Http\Controllers\CourseUnitController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\CurriculumCourseController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\GradeModerationController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\RegistrationApprovalController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentResultController;
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
    Route::post('curriculums/{curriculum}/stage-credit-limits', [CurriculumController::class, 'updateStageCreditLimits'])->name('curriculums.stage-credit-limits.update');

    // Scheduled Academic Events & Almanac
    Route::get('events', [AcademicEventController::class, 'index'])->name('events.index');
    Route::get('events/feed', [AcademicEventController::class, 'feed'])->name('events.feed');
    Route::post('events', [AcademicEventController::class, 'store'])->name('events.store');
    Route::put('events/{academic_event}', [AcademicEventController::class, 'update'])->name('events.update');
    Route::delete('events/{academic_event}', [AcademicEventController::class, 'destroy'])->name('events.destroy');

    // Students Directory (Academic Enrollment Foundation)
    Route::resource('students', StudentController::class)->only(['index', 'show']);

    // Course Eligibility & Prerequisite Inspector
    Route::get('registrations/eligibility', [CourseRegistrationController::class, 'eligibilityCheck'])->name('registrations.eligibility');

    // Active Session Cohorts & Student Progression Stage Roster
    Route::get('registrations/active-session', [CourseRegistrationController::class, 'activeSessionRoster'])->name('registrations.active-session');

    // Official Printable Registration Slip
    Route::get('registrations/{registration}/print', [CourseRegistrationController::class, 'printSlip'])->name('registrations.print');

    // Course Add/Drop Adjustment Workflow Engine
    Route::get('registrations/{registration}/add-drop', [AddDropController::class, 'edit'])->name('registrations.add-drop.edit');
    Route::post('registrations/{registration}/add-drop/drop/{item}', [AddDropController::class, 'dropCourse'])->name('registrations.add-drop.drop');
    Route::post('registrations/{registration}/add-drop/add', [AddDropController::class, 'addCourse'])->name('registrations.add-drop.add');

    // Academic Advisor & Registrar Approval Portal
    Route::post('approvals/batch-approve', [RegistrationApprovalController::class, 'batchApprove'])->name('approvals.batch-approve');
    Route::post('approvals/{registration}/approve', [RegistrationApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('approvals/{registration}/reject', [RegistrationApprovalController::class, 'reject'])->name('approvals.reject');
    Route::get('approvals/{registration}', [RegistrationApprovalController::class, 'show'])->name('approvals.show');
    Route::get('approvals', [RegistrationApprovalController::class, 'index'])->name('approvals.index');

    // Course Registrations & Enrollment Slips Hub
    Route::resource('registrations', CourseRegistrationController::class);

    // Examinations, Grading & Assessment Hub
    Route::get('assessments/policy', [CourseAssessmentController::class, 'policy'])->name('assessments.policy');
    Route::post('assessments/policy', [CourseAssessmentController::class, 'updatePolicy'])->name('assessments.policy.update');
    Route::put('assessments/policy/grading-scale', [CourseAssessmentController::class, 'updateGradingScale'])->name('assessments.policy.scale.update');
    Route::post('assessments/policy/grading-scale/reset', [CourseAssessmentController::class, 'resetGradingScale'])->name('assessments.policy.scale.reset');
    Route::put('assessments/policy/awards', [CourseAssessmentController::class, 'updateAwardClassifications'])->name('assessments.policy.awards.update');
    Route::post('assessments/policy/awards/reset', [CourseAssessmentController::class, 'resetAwardClassifications'])->name('assessments.policy.awards.reset');

    // Departmental & Senate Grade Moderation Workflow
    Route::get('assessments/moderation', [GradeModerationController::class, 'index'])->name('assessments.moderation.index');
    Route::get('assessments/moderation/{sheet}', [GradeModerationController::class, 'show'])->name('assessments.moderation.show');
    Route::post('assessments/moderation/{sheet}/endorse', [GradeModerationController::class, 'endorse'])->name('assessments.moderation.endorse');
    Route::post('assessments/moderation/{sheet}/return', [GradeModerationController::class, 'returnToLecturer'])->name('assessments.moderation.return');
    Route::post('assessments/moderation/{sheet}/publish', [GradeModerationController::class, 'publish'])->name('assessments.moderation.publish');

    Route::get('assessments', [CourseAssessmentController::class, 'index'])->name('assessments.index');
    Route::get('assessments/{sheet}', [CourseAssessmentController::class, 'show'])->name('assessments.show');
    Route::get('assessments/{sheet}/entry', [CourseAssessmentController::class, 'edit'])->name('assessments.edit');
    Route::put('assessments/{sheet}', [CourseAssessmentController::class, 'update'])->name('assessments.update');
    Route::post('assessments/{sheet}/submit', [CourseAssessmentController::class, 'submitToHod'])->name('assessments.submit');

    // Official Student Results, Result Slips & Cumulative Transcripts
    Route::get('results', [StudentResultController::class, 'index'])->name('results.index');
    Route::get('results/slip/{student}/{semester}', [StudentResultController::class, 'semesterResultSlip'])->name('results.slip');
    Route::get('results/transcript/{student}', [StudentResultController::class, 'academicTranscript'])->name('results.transcript');
});
