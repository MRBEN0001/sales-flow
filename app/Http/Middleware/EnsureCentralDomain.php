<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCentralDomain
{
    public function handle(Request $request, Closure $next)
    {
        if (! in_array($request->getHost(), config('tenancy.central_domains'), true)) {
            abort(404);
        }

        return $next($request);
    }
}
