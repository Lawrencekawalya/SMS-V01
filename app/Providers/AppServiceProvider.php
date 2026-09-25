<?php

namespace App\Providers;

use App\Models\AcademicEvent;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.partials.navbar', function ($view): void {
            if (Schema::hasTable('academic_events')) {
                $today = now()->toDateString();

                $upcomingEvents = AcademicEvent::with(['academicYear', 'semester'])
                    ->where(function ($query) use ($today) {
                        $query->where('end_date', '>=', $today)
                            ->orWhere(function ($sub) use ($today) {
                                $sub->whereNull('end_date')
                                    ->where('start_date', '>=', $today);
                            });
                    })
                    ->orderBy('start_date', 'asc')
                    ->take(5)
                    ->get();

                $upcomingCount = AcademicEvent::where(function ($query) use ($today) {
                    $query->where('end_date', '>=', $today)
                        ->orWhere(function ($sub) use ($today) {
                            $sub->whereNull('end_date')
                                ->where('start_date', '>=', $today);
                        });
                })->count();

                if ($upcomingEvents->isEmpty()) {
                    $upcomingEvents = AcademicEvent::with(['academicYear', 'semester'])
                        ->orderBy('start_date', 'desc')
                        ->take(5)
                        ->get();
                    $upcomingCount = AcademicEvent::count();
                }

                $view->with([
                    'navbarUpcomingEvents' => $upcomingEvents,
                    'navbarEventsCount' => $upcomingCount,
                ]);
            } else {
                $view->with([
                    'navbarUpcomingEvents' => collect(),
                    'navbarEventsCount' => 0,
                ]);
            }
        });
    }
}
