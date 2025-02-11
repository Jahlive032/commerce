<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Récupérer l'utilisateur connecté
        $user = $request->user();

        // Redirection basée sur le rôle
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isManager()) {
            return redirect()->route('manager.dashboard');
        }

        // Pour les clients
        if ($user->isClient()) {
            // Vérifier si l'email est vérifié
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            return redirect()->route('client.index');
        }

        return redirect('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->isAdmin() && !$user->hasVerifiedEmail()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isManager()&& !$user->hasVerifiedEmail()) {
            return redirect()->route('manager.dashboard');
        }
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->route('client.dashboard');
    }
}
