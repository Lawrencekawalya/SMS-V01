<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUniversityRequest;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UniversityController extends Controller
{
    /**
     * Show the form for editing the primary university profile.
     */
    public function edit(): View
    {
        $university = University::first() ?? University::create([
            'name' => 'Bishop Stuart University',
            'code' => 'BSU',
        ]);

        return view('academic.university.edit', compact('university'));
    }

    /**
     * Update the primary university profile in storage.
     */
    public function update(UpdateUniversityRequest $request, University $university): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->boolean('remove_logo')) {
            if ($university->logo_path && Storage::disk('public')->exists($university->logo_path)) {
                Storage::disk('public')->delete($university->logo_path);
            }
            $university->logo_path = null;
        }

        if ($request->hasFile('logo')) {
            if ($university->logo_path && Storage::disk('public')->exists($university->logo_path)) {
                Storage::disk('public')->delete($university->logo_path);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $university->logo_path = $path;
        }

        $university->fill([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'website' => $validated['website'] ?? null,
        ]);

        $university->save();

        return redirect()
            ->route('university.edit')
            ->with('success', 'University settings and institution logo updated successfully.');
    }
}
