<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $schoolIncomplete = false;

        if ($request->user()) {
            $user = $request->user();

            // Admin users do not manage a specific school profile,
            // so they should never be blocked by the schoolIncomplete flag.
            if ($user->role !== 'admin') {
                $school = $user->school;
                $schoolIncomplete = ! $school || empty($school->name) || empty($school->school_code);
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'schoolIncomplete' => $schoolIncomplete,
        ];
    }
}
