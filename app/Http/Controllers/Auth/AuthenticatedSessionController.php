<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Services\TrialEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, TrialEligibilityService $eligibility)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $tenant = tenant();
        $user = Auth::guard('web')->user();

        // Do not associate Sales Flow's global support-admin device with
        // customer shops when support logs in for maintenance.
        $isSupportAdmin = $user
            && Str::lower((string) $user->email) === Str::lower((string) config('dev.admin.email'));

        if ($tenant && ! $isSupportAdmin) {
            $eligibility->remember(
                (string) $tenant->id,
                $request->input('device_fingerprint'),
                $request,
                true
            );
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
