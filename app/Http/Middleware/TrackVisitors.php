<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visitor;

class TrackVisitors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Track visitor - count every page visit
        try {
            $ipAddress = $request->ip();
            
            // Always create a new visitor record for each page visit
            Visitor::create([
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'page' => $request->path(),
            ]);
        } catch (\Exception $e) {
            // Silently fail if tracking fails
        }

        return $next($request);
    }
}
