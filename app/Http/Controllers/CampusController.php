<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampusRequest;
use App\Http\Requests\UpdateCampusRequest;
use App\Models\Campus;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CampusController extends Controller
{
    /**
     * Display a listing of the campuses.
     */
    public function index(): View
    {
        $campuses = Campus::with('university')
            ->withCount('faculties')
            ->orderByDesc('is_main_campus')
            ->orderBy('name')
            ->paginate(10);

        return view('academic.campuses.index', compact('campuses'));
    }

    /**
     * Show the form for creating a new campus.
     */
    public function create(): View
    {
        $universities = University::all();

        return view('academic.campuses.create', compact('universities'));
    }

    /**
     * Store a newly created campus in storage.
     */
    public function store(StoreCampusRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();
            $isMain = $request->boolean('is_main_campus');

            if ($isMain) {
                Campus::where('university_id', $data['university_id'])
                    ->update(['is_main_campus' => false]);
            }

            $data['is_main_campus'] = $isMain;
            Campus::create($data);
        });

        return redirect()
            ->route('academic.campuses.index')
            ->with('success', 'Campus created successfully.');
    }

    /**
     * Display the specified campus.
     */
    public function show(Campus $campus): View
    {
        $campus->load(['university', 'faculties.dean']);

        return view('academic.campuses.show', compact('campus'));
    }

    /**
     * Show the form for editing the specified campus.
     */
    public function edit(Campus $campus): View
    {
        $universities = University::all();

        return view('academic.campuses.edit', compact('campus', 'universities'));
    }

    /**
     * Update the specified campus in storage.
     */
    public function update(UpdateCampusRequest $request, Campus $campus): RedirectResponse
    {
        DB::transaction(function () use ($request, $campus) {
            $data = $request->validated();
            $isMain = $request->boolean('is_main_campus');

            if ($isMain) {
                Campus::where('university_id', $campus->university_id)
                    ->where('id', '!=', $campus->id)
                    ->update(['is_main_campus' => false]);
            }

            $data['is_main_campus'] = $isMain;
            $campus->update($data);
        });

        return redirect()
            ->route('academic.campuses.index')
            ->with('success', 'Campus updated successfully.');
    }

    /**
     * Remove the specified campus from storage.
     */
    public function destroy(Campus $campus): RedirectResponse
    {
        if ($campus->faculties()->exists()) {
            return redirect()
                ->route('academic.campuses.index')
                ->with('error', 'Cannot delete campus because it contains active faculties.');
        }

        $campus->delete();

        return redirect()
            ->route('academic.campuses.index')
            ->with('success', 'Campus deleted successfully.');
    }
}
