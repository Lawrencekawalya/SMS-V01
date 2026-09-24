<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUniversityRequest;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UniversityController extends Controller
{
    /**
     * Show the form for editing the primary university profile.
     */
    public function edit(): View
    {
        $university = University::firstOrCreate(
            ['code' => 'APEX-UNI'],
            ['name' => 'Apex University of Science and Technology']
        );

        return view('academic.university.edit', compact('university'));
    }

    /**
     * Update the primary university profile in storage.
     */
    public function update(UpdateUniversityRequest $request, University $university): RedirectResponse
    {
        $university->update($request->validated());

        return redirect()
            ->route('academic.university.edit')
            ->with('success', 'University settings updated successfully.');
    }
}
