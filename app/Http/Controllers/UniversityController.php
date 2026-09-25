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
        $university = University::first() ?? University::create([
            'name' => 'Apex University of Science and Technology',
            'code' => 'APEX-UNI',
        ]);

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
