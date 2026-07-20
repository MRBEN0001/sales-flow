<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ShopLoginController extends Controller
{
    public function create()
    {
        return view('central.login', [
            'tenantDomain' => config('app.tenant_domain'),
        ]);
    }

    public function redirect(Request $request)
    {
        $validated = $request->validate([
            'subdomain' => [
                'required',
                'string',
                'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/',
                Rule::exists('tenants', 'id'),
            ],
        ], [
            'subdomain.regex' => 'Use lowercase letters, numbers, and hyphens only.',
            'subdomain.exists' => 'No shop found with that address.',
        ]);

        return redirect(tenant_shop_login_url(Str::lower($validated['subdomain']), $request));
    }
}
