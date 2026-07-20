<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DevAuthController extends Controller
{
    public function create()
    {
        if (Auth::guard('dev')->check()) {
            return redirect()->route('dev.dashboard');
        }

        return view('central.dev.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('dev')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Invalid dev dashboard credentials.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dev.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('dev')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dev.login');
    }
}
