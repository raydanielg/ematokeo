<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        Vite::prefetch(concurrency: 3);

        Inertia::share('announcementsHeader', function () {
            return Announcement::orderByDesc('created_at')
                ->limit(5)
                ->get([
                    'id',
                    'title',
                    'body',
                    'created_at',
                ]);
        });

        Inertia::share('schoolIncomplete', function () {
            $school = School::query()->first();

            // Incomplete if there is no school at all, or key fields are missing
            if (! $school) {
                return true;
            }

            return empty($school->name) || empty($school->school_code);
        });

        Inertia::share('classesIncomplete', function () {
            // If school itself is not yet set, treat classes as incomplete by default
            $school = School::query()->first();

            if (! $school || empty($school->name) || empty($school->school_code)) {
                return true;
            }

            // Classes are considered incomplete only when there are none defined
            return SchoolClass::query()->count() === 0;
        });
    }
}
