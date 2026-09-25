<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcademicEventRequest;
use App\Http\Requests\UpdateAcademicEventRequest;
use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicEventController extends Controller
{
    /**
     * Display a listing of scheduled academic events and almanac.
     */
    public function index(): View
    {
        $academicEvents = AcademicEvent::with(['academicYear', 'semester'])
            ->orderBy('start_date', 'asc')
            ->get();

        $academicYears = AcademicYear::with('semesters')
            ->orderBy('start_date', 'desc')
            ->get();

        $activeYear = AcademicYear::where('is_current', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        return view('academic.events.index', compact('academicEvents', 'academicYears', 'activeYear', 'activeSemester'));
    }

    /**
     * Store a newly created academic event in storage.
     */
    public function store(StoreAcademicEventRequest $request): RedirectResponse
    {
        $event = AcademicEvent::create($request->validated());

        return redirect()->back(fallback: route('academic.academic-years.index'))
            ->with('success', "Academic event '{$event->title}' scheduled successfully.");
    }

    /**
     * Update the specified academic event in storage.
     */
    public function update(UpdateAcademicEventRequest $request, AcademicEvent $academicEvent): RedirectResponse
    {
        $academicEvent->update($request->validated());

        return redirect()->back(fallback: route('academic.academic-years.index'))
            ->with('success', "Academic event '{$academicEvent->title}' updated successfully.");
    }

    /**
     * Remove the specified academic event from storage.
     */
    public function destroy(AcademicEvent $academicEvent): RedirectResponse
    {
        $title = $academicEvent->title;
        $academicEvent->delete();

        return redirect()->back(fallback: route('academic.academic-years.index'))
            ->with('success', "Academic event '{$title}' deleted successfully.");
    }

    /**
     * Return FullCalendar JSON feed for events.
     */
    public function feed(Request $request): JsonResponse
    {
        $query = AcademicEvent::with(['academicYear', 'semester']);

        if ($yearId = $request->query('academic_year_id')) {
            $query->where('academic_year_id', $yearId);
        }

        if ($semesterId = $request->query('semester_id')) {
            $query->where('semester_id', $semesterId);
        }

        $events = $query->get()->map(fn (AcademicEvent $event) => $event->toFullCalendarArray());

        return response()->json($events);
    }
}
