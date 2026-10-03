<?php

use App\Http\Controllers\AcademicAnalyticsController;
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
use App\Http\Controllers\GraduationClearanceController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\RegistrationApprovalController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SenateBroadSheetController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentResultController;
use App\Http\Controllers\UniversityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::prefix('academic')->group(function () {
    // 1. University Profile
    Route::controller(UniversityController::class)->prefix('university')->group(function () {
        Route::get('edit', 'edit')->name('university.edit');
        Route::match(['put', 'patch'], 'update/{university}', 'update')->name('university.update');
    });

    // 2. Campuses
    Route::controller(CampusController::class)->prefix('campus')->group(function () {
        Route::get('list', 'index')->name('campus.list');
        Route::get('create', 'create')->name('campus.create');
        Route::post('store', 'store')->name('campus.store');
        Route::get('show/{campus}', 'show')->name('campus.show');
        Route::get('edit/{campus}', 'edit')->name('campus.edit');
        Route::match(['put', 'patch'], 'update/{campus}', 'update')->name('campus.update');
        Route::delete('delete/{campus}', 'destroy')->name('campus.delete');
    });

    // 3. Faculties
    Route::controller(FacultyController::class)->prefix('faculty')->group(function () {
        Route::get('list', 'index')->name('faculty.list');
        Route::get('create', 'create')->name('faculty.create');
        Route::post('store', 'store')->name('faculty.store');
        Route::get('show/{faculty}', 'show')->name('faculty.show');
        Route::get('edit/{faculty}', 'edit')->name('faculty.edit');
        Route::match(['put', 'patch'], 'update/{faculty}', 'update')->name('faculty.update');
        Route::delete('delete/{faculty}', 'destroy')->name('faculty.delete');
    });

    // 4. Departments
    Route::controller(DepartmentController::class)->prefix('department')->group(function () {
        Route::get('list', 'index')->name('department.list');
        Route::get('create', 'create')->name('department.create');
        Route::post('store', 'store')->name('department.store');
        Route::get('show/{department}', 'show')->name('department.show');
        Route::get('edit/{department}', 'edit')->name('department.edit');
        Route::match(['put', 'patch'], 'update/{department}', 'update')->name('department.update');
        Route::delete('delete/{department}', 'destroy')->name('department.delete');
    });

    // 5. Programmes
    Route::controller(ProgrammeController::class)->prefix('programme')->group(function () {
        Route::get('list', 'index')->name('programme.list');
        Route::get('create', 'create')->name('programme.create');
        Route::post('store', 'store')->name('programme.store');
        Route::get('show/{programme}', 'show')->name('programme.show');
        Route::get('edit/{programme}', 'edit')->name('programme.edit');
        Route::match(['put', 'patch'], 'update/{programme}', 'update')->name('programme.update');
        Route::delete('delete/{programme}', 'destroy')->name('programme.delete');
    });

    // 6. Academic Years
    Route::controller(AcademicYearController::class)->prefix('academic-year')->group(function () {
        Route::get('list', 'index')->name('academic-year.list');
        Route::get('create', 'create')->name('academic-year.create');
        Route::post('store', 'store')->name('academic-year.store');
        Route::get('show/{academic_year}', 'show')->name('academic-year.show');
        Route::post('make-current/{academic_year}', 'makeCurrent')->name('academic-year.make-current');
        Route::get('edit/{academic_year}', 'edit')->name('academic-year.edit');
        Route::match(['put', 'patch'], 'update/{academic_year}', 'update')->name('academic-year.update');
        Route::delete('delete/{academic_year}', 'destroy')->name('academic-year.delete');
    });

    // 7. Semesters
    Route::controller(SemesterController::class)->prefix('semester')->group(function () {
        Route::get('create', 'create')->name('semester.create');
        Route::post('store', 'store')->name('semester.store');
        Route::post('activate/{semester}', 'activate')->name('semester.activate');
        Route::get('edit/{semester}', 'edit')->name('semester.edit');
        Route::match(['put', 'patch'], 'update/{semester}', 'update')->name('semester.update');
        Route::delete('delete/{semester}', 'destroy')->name('semester.delete');
    });

    // 8. Course Units
    Route::controller(CourseUnitController::class)->prefix('course')->group(function () {
        Route::get('list', 'index')->name('course.list');
        Route::get('create', 'create')->name('course.create');
        Route::post('store', 'store')->name('course.store');
        Route::get('show/{course_unit}', 'show')->name('course.show');
        Route::get('edit/{course_unit}', 'edit')->name('course.edit');
        Route::match(['put', 'patch'], 'update/{course_unit}', 'update')->name('course.update');
        Route::delete('delete/{course_unit}', 'destroy')->name('course.delete');
    });

    // 9. Curriculums
    Route::controller(CurriculumController::class)->prefix('curriculum')->group(function () {
        Route::get('list', 'index')->name('curriculum.list');
        Route::get('create', 'create')->name('curriculum.create');
        Route::post('store', 'store')->name('curriculum.store');
        Route::get('show/{curriculum}', 'show')->name('curriculum.show');
        Route::get('edit/{curriculum}', 'edit')->name('curriculum.edit');
        Route::match(['put', 'patch'], 'update/{curriculum}', 'update')->name('curriculum.update');
        Route::delete('delete/{curriculum}', 'destroy')->name('curriculum.delete');
        Route::post('stage-credit-limits/{curriculum}', 'updateStageCreditLimits')->name('curriculum.stage-credit-limits.update');
    });

    // 10. Curriculum Courses
    Route::controller(CurriculumCourseController::class)->prefix('curriculum-course')->group(function () {
        Route::post('store/{curriculum}', 'store')->name('curriculum.courses.store');
        Route::delete('delete/{curriculum}/{curriculumCourse}', 'destroy')->name('curriculum.courses.destroy');
    });

    // 11. Scheduled Academic Events
    Route::controller(AcademicEventController::class)->prefix('event')->group(function () {
        Route::get('list', 'index')->name('event.list');
        Route::get('feed', 'feed')->name('event.feed');
        Route::post('store', 'store')->name('event.store');
        Route::match(['put', 'patch'], 'update/{academic_event}', 'update')->name('event.update');
        Route::delete('delete/{academic_event}', 'destroy')->name('event.delete');
    });

    // 12. Students Directory
    Route::controller(StudentController::class)->prefix('student')->group(function () {
        Route::get('list', 'index')->name('student.list');
        Route::get('show/{student}', 'show')->name('student.show');
    });

    // 13. Course Registrations Hub
    Route::controller(CourseRegistrationController::class)->prefix('registration')->group(function () {
        Route::get('list', 'index')->name('registration.list');
        Route::get('create', 'create')->name('registration.create');
        Route::post('store', 'store')->name('registration.store');
        Route::get('eligibility', 'eligibilityCheck')->name('registration.eligibility');
        Route::get('active-session', 'activeSessionRoster')->name('registration.active-session');
        Route::get('print/{registration}', 'printSlip')->name('registration.print');
        Route::get('show/{registration}', 'show')->name('registration.show');
        Route::get('edit/{registration}', 'edit')->name('registration.edit');
        Route::match(['put', 'patch'], 'update/{registration}', 'update')->name('registration.update');
        Route::delete('delete/{registration}', 'destroy')->name('registration.delete');
    });

    // 14. Course Add/Drop Adjustment Engine
    Route::controller(AddDropController::class)->prefix('registration/add-drop')->group(function () {
        Route::get('{registration}', 'edit')->name('registration.add-drop.edit');
        Route::post('add/{registration}', 'addCourse')->name('registration.add-drop.add');
        Route::post('drop/{registration}/{item}', 'dropCourse')->name('registration.add-drop.drop');
    });

    // 15. Academic Advisor & Registrar Approvals
    Route::controller(RegistrationApprovalController::class)->prefix('approval')->group(function () {
        Route::get('list', 'index')->name('approval.list');
        Route::get('show/{registration}', 'show')->name('approval.show');
        Route::post('approve/{registration}', 'approve')->name('approval.approve');
        Route::post('reject/{registration}', 'reject')->name('approval.reject');
        Route::post('batch-approve', 'batchApprove')->name('approval.batch-approve');
    });

    // 16. Examinations, Grading Policy & Course Mark Sheets
    Route::controller(CourseAssessmentController::class)->prefix('assessment')->group(function () {
        Route::get('policy', 'policy')->name('assessment.policy');
        Route::post('policy', 'updatePolicy')->name('assessment.policy.update');
        Route::match(['put', 'patch'], 'policy/grading-scale', 'updateGradingScale')->name('assessment.policy.scale.update');
        Route::post('policy/grading-scale/reset', 'resetGradingScale')->name('assessment.policy.scale.reset');
        Route::match(['put', 'patch'], 'policy/awards', 'updateAwardClassifications')->name('assessment.policy.awards.update');
        Route::post('policy/awards/reset', 'resetAwardClassifications')->name('assessment.policy.awards.reset');

        Route::get('list', 'index')->name('assessment.list');
        Route::get('show/{sheet}', 'show')->name('assessment.show');
        Route::get('edit/{sheet}', 'edit')->name('assessment.edit');
        Route::get('entry/{sheet}', 'edit')->name('assessment.entry');
        Route::match(['put', 'patch'], 'update/{sheet}', 'update')->name('assessment.update');
        Route::post('submit/{sheet}', 'submitToHod')->name('assessment.submit');
    });

    // 17. Departmental & Senate Grade Moderation Workflow
    Route::controller(GradeModerationController::class)->prefix('moderation')->group(function () {
        Route::get('list', 'index')->name('moderation.list');
        Route::get('show/{sheet}', 'show')->name('moderation.show');
        Route::post('endorse/{sheet}', 'endorse')->name('moderation.endorse');
        Route::post('return/{sheet}', 'returnToLecturer')->name('moderation.return');
        Route::post('publish/{sheet}', 'publish')->name('moderation.publish');
    });

    // 18. Official Student Results, Result Slips & Transcripts
    Route::controller(StudentResultController::class)->prefix('result')->group(function () {
        Route::get('list', 'index')->name('result.list');
        Route::get('slip/{student}/{semester}', 'semesterResultSlip')->name('result.slip');
        Route::get('transcript/{student}', 'academicTranscript')->name('result.transcript');
    });

    // 19. Official Senate Master Broad-Sheet (Gazette / Master Ledger)
    Route::controller(SenateBroadSheetController::class)->prefix('broad-sheet')->group(function () {
        Route::get('list', 'index')->name('broad-sheet.list');
        Route::get('export', 'export')->name('broad-sheet.export');
    });

    // 20. Departmental & Faculty Academic Performance Analytics
    Route::controller(AcademicAnalyticsController::class)->prefix('analytics')->group(function () {
        Route::get('list', 'index')->name('analytics.list');
    });

    // 21. Graduation Clearance, Audit & Official Honors Roll Gazette
    Route::controller(GraduationClearanceController::class)->prefix('graduation')->group(function () {
        Route::get('list', 'index')->name('graduation.list');
        Route::get('audit/{student}', 'audit')->name('graduation.audit');
        Route::get('honors-roll', 'honorsRoll')->name('graduation.honors-roll');
    });
});
