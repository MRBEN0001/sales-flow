<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConfigureTenantSession
{
    public function handle(Request $request, Closure $next)
    {
        if (tenant()) {
            config([
                'session.cookie' => Str::slug(config('app.name'), '_').'_'.tenant('id').'_session',
            ]);
        }

        return $next($request);
    }
}
