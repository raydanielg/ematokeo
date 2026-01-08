<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\RecentActivity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        RecentActivity::create([
            'user_id' => Auth::id(),
            'action' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->header('User-Agent'),
            'occurred_at' => now(),
        ]);

        $user = $request->user();

        if ($user && $user->school_id && $user->role !== 'admin') {
            $firstSchoolUserId = User::query()
                ->where('school_id', $user->school_id)
                ->orderBy('id')
                ->value('id');

            if ($firstSchoolUserId && (int) $firstSchoolUserId === (int) $user->id) {
                $user->forceFill(['role' => 'admin'])->save();
                $user->refresh();
            }
        }

        $targetRoute = 'dashboard';
        if ($user && $user->role === 'admin') {
            $targetRoute = 'admin.dashboard';
        } elseif ($user && $user->role === 'teacher') {
            $targetRoute = 'teacher.dashboard';
        }

        return redirect()->intended(route($targetRoute, absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            RecentActivity::create([
                'user_id' => Auth::id(),
                'action' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->header('User-Agent'),
                'occurred_at' => now(),
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
