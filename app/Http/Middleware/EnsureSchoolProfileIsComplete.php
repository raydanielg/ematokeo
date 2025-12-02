<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Models\SchoolClass;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolProfileIsComplete
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $school = School::query()->first();

            $schoolIncomplete = ! $school || empty($school->name) || empty($school->school_code);

            if ($schoolIncomplete && ! $request->routeIs('settings.school-information')) {
                return redirect()->route('settings.school-information');
            }

            // If school is complete, require at least one class before allowing other pages
            if (! $schoolIncomplete) {
                $hasAnyClass = SchoolClass::query()->exists();

                if (! $hasAnyClass && ! $request->routeIs('classes.index') && ! $request->routeIs('settings.school-information')) {
                    return redirect()->route('classes.index');
                }
            }
        }

        return $next($request);
    }
}
