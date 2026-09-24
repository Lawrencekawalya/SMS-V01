<?php

use App\Http\Controllers\CampusController;
use App\Http\Controllers\FacultyController;
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
});
