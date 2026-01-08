<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacherPasswordChanged
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'teacher') {
            return $next($request);
        }

        if (! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('teacher.change-password') || $request->routeIs('teacher.change-password.update')) {
            return $next($request);
        }

        return redirect()->route('teacher.change-password');
    }
}
