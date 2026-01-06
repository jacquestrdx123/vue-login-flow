<?php

namespace App\Http\Controllers\{NAMESPACE};

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class {CONTROLLER_CLASS} extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): Response
    {
        return Inertia::render('{GUARD_NAME}/Login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember', false);

        if (Auth::guard('{GUARD_NAME}')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended('{URL_PREFIX}');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('{GUARD_NAME}')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('{URL_PREFIX}/login');
    }
}

