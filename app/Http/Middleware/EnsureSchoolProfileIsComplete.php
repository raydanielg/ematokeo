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
            $user = $request->user();
            $school = $user->school;

            $schoolIncomplete = ! $school || empty($school->name) || empty($school->school_code);

            // Pass flag to view
            $request->session()->put('schoolIncomplete', $schoolIncomplete);

            if ($schoolIncomplete && ! $request->routeIs('settings.school-information')) {
                return redirect()->route('settings.school-information');
            }

            // If school is complete, require at least one class before allowing other pages
            if (! $schoolIncomplete) {
                $hasAnyClass = SchoolClass::where('school_id', $school->id)->exists();

                if (! $hasAnyClass && ! $request->routeIs('classes.index') && ! $request->routeIs('settings.school-information')) {
                    return redirect()->route('classes.index');
                }
            }
        }

        return $next($request);
    }
}
