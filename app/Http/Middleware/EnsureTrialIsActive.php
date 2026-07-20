<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class EnsureTrialIsActive
{
    public function handle(Request $request, Closure $next)
    {
        /** @var Tenant|null $tenant */
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        $tenant->syncSubscriptionExpiry();

        if ($tenant->hasActiveSubscription()) {
            return $next($request);
        }

        if ($request->routeIs('subscription.*')) {
            return $next($request);
        }

        if ($request->routeIs('logout')) {
            return $next($request);
        }

        return redirect()->route('subscription.expired');
    }
}
