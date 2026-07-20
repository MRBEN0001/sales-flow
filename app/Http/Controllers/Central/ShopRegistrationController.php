<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Mail\ShopCreatedMail;
use App\Models\Tenant;
use App\Services\TrialEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ShopRegistrationController extends Controller
{
    public function create()
    {
        return view('central.register', [
            'tenantDomain' => config('app.tenant_domain'),
            'monthlyPriceNgn' => subscription_monthly_price_ngn(),
            'trialDays' => config('subscription.trial_days'),
            'checkDeviceUrl' => route('central.register.check_device'),
        ]);
    }

    public function checkDevice(Request $request, TrialEligibilityService $eligibility)
    {
        $validated = $request->validate([
            'device_fingerprint' => ['required', 'string', 'min:16', 'max:500'],
        ]);

        $tenant = $eligibility->findExistingShop($validated['device_fingerprint'], $request);

        if (! $tenant) {
            return response()->json(['has_shop' => false]);
        }

        $request->session()->put('existing_shop_tenant_id', $tenant->id);

        return response()->json([
            'has_shop' => true,
            'redirect' => route('central.register.existing'),
        ]);
    }

    public function existing(Request $request)
    {
        $tenantId = $request->session()->get('existing_shop_tenant_id');

        if (! $tenantId) {
            return redirect()->route('central.register');
        }

        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            $request->session()->forget('existing_shop_tenant_id');

            return redirect()->route('central.register');
        }

        $onTrial = $tenant->isOnTrial();
        $needsPayment = ! $tenant->hasActiveSubscription();

        return view('central.register-existing', [
            'shopName' => $tenant->shop_name,
            'shopAddress' => $tenant->id.'.'.config('app.tenant_domain'),
            'status' => $tenant->fresh()->subscription_status,
            'planLabel' => $tenant->planLabel(),
            'onTrial' => $onTrial,
            'needsPayment' => $needsPayment,
            'accessEndsAt' => $tenant->accessEndsAt(),
            'daysLeft' => $tenant->subscriptionDaysRemaining(),
            'trialDaysLeft' => $tenant->trialDaysRemaining(),
            'plansUrl' => route('central.plans'),
            'monthlyPriceNgn' => subscription_monthly_price_ngn(),
        ]);
    }

    public function store(Request $request, TrialEligibilityService $eligibility)
    {
        $shopName = Str::upper(trim((string) $request->input('shop_name', '')));
        $subdomain = $this->subdomainFromShopName($shopName);

        $request->merge([
            'shop_name' => $shopName,
            'subdomain' => $subdomain,
            'admin_email' => Str::lower(trim((string) $request->input('admin_email', ''))),
            'admin_email_confirmation' => Str::lower(trim((string) $request->input('admin_email_confirmation', ''))),
            'owner_phone' => $this->normalizePhone((string) $request->input('owner_phone', '')),
        ]);

        $validated = $request->validate([
            'shop_name' => ['required', 'string', 'max:120'],
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:40',
                'regex:/^[a-z0-9]+$/',
                Rule::notIn($this->reservedSubdomains()),
                Rule::unique('tenants', 'id'),
            ],
            'admin_name' => ['required', 'string', 'max:120'],
            'admin_email' => [
                'required',
                'email',
                'max:190',
                'confirmed',
                Rule::unique('tenants', 'owner_email'),
            ],
            'owner_phone' => [
                'required',
                'string',
                'min:10',
                'max:20',
                'regex:/^[0-9]{10,15}$/',
                function ($attribute, $value, $fail) {
                    if ($this->phoneAlreadyUsed((string) $value)) {
                        $fail('This phone number is already registered to another shop.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_fingerprint' => ['required', 'string', 'min:16', 'max:500'],
        ], [
            'subdomain.regex' => 'Shop address must be lowercase letters and numbers only (no spaces).',
            'subdomain.min' => 'Shop name must produce an address of at least 3 letters or numbers.',
            'subdomain.not_in' => 'This shop address is reserved.',
            'subdomain.unique' => 'This shop address is already taken. Try a different shop name.',
            'admin_email.confirmed' => 'The admin email confirmation does not match.',
            'admin_email.unique' => 'This email is already registered to another shop.',
            'owner_phone.regex' => 'Enter a valid phone number (digits only).',
            'device_fingerprint.required' => 'Could not verify this device. Please enable JavaScript and try again.',
        ]);

        if ($eligibility->deviceAlreadyHasShop($validated['device_fingerprint'], $request)) {
            $existing = $eligibility->findExistingShop($validated['device_fingerprint'], $request);
            if ($existing) {
                $request->session()->put('existing_shop_tenant_id', $existing->id);
            }

            return redirect()->route('central.register.existing');
        }

        $subdomain = $validated['subdomain'];

        // Leftover DB from a failed earlier attempt (tenant row gone, database still there).
        $this->dropOrphanedTenantDatabase($subdomain);

        $trialEndsAt = now()->addDays(config('subscription.trial_days'));

        try {
            $tenant = Tenant::create([
                'id' => $subdomain,
                'shop_name' => $validated['shop_name'],
                'owner_email' => $validated['admin_email'],
                'owner_phone' => $validated['owner_phone'],
                'trial_ends_at' => $trialEndsAt,
                'subscription_status' => Tenant::STATUS_TRIAL,
                'admin_name' => $validated['admin_name'],
                'admin_email' => $validated['admin_email'],
                'admin_password' => Hash::make($validated['password']),
            ]);
        } catch (\Stancl\Tenancy\Exceptions\TenantDatabaseAlreadyExistsException $e) {
            $this->cleanupFailedRegistration($subdomain);

            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'device_fingerprint']))
                ->withErrors([
                    'shop_name' => 'That shop address had leftover data from a previous attempt. It has been cleared — please submit again.',
                ]);
        } catch (\Throwable $e) {
            $this->cleanupFailedRegistration($subdomain);
            Log::error('Shop registration failed.', [
                'subdomain' => $subdomain,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'device_fingerprint']))
                ->withErrors([
                    'shop_name' => 'Could not create the shop right now. Please try again or pick a different shop name.',
                ]);
        }

        $tenant->createDomain([
            'domain' => $subdomain,
        ]);

        $eligibility->remember(
            $tenant->id,
            $validated['device_fingerprint'],
            $request,
            true
        );

        $loginUrl = tenant_shop_login_url($subdomain, $request);

        $freshTenant = $tenant->fresh();
        $companyEmail = config('dev.admin.email');
        $notifyCompany = $companyEmail
            && strcasecmp($companyEmail, $validated['admin_email']) !== 0;

        try {
            Mail::to($validated['admin_email'])->send(
                new ShopCreatedMail($freshTenant, $loginUrl, true, $validated['admin_name'])
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to send shop created email.', [
                'tenant' => $tenant->id,
                'email' => $validated['admin_email'],
                'message' => $e->getMessage(),
            ]);
        }

        // Separate email to company (config/dev.php admin email).
        // Mailtrap free plan rate-limits back-to-back sends, so wait + retry once.
        if ($notifyCompany) {
            $sent = false;

            for ($attempt = 1; $attempt <= 2; $attempt++) {
                try {
                    sleep(3);
                    Mail::to($companyEmail)->send(
                        new ShopCreatedMail($freshTenant, $loginUrl, true, $validated['admin_name'], true)
                    );
                    $sent = true;
                    Log::info('Sent shop created company notification.', [
                        'tenant' => $tenant->id,
                        'email' => $companyEmail,
                        'attempt' => $attempt,
                    ]);
                    break;
                } catch (\Throwable $e) {
                    Log::warning('Failed to send shop created company notification.', [
                        'tenant' => $tenant->id,
                        'email' => $companyEmail,
                        'attempt' => $attempt,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            if (! $sent) {
                Log::error('Company shop-created email was not delivered after retries.', [
                    'tenant' => $tenant->id,
                    'email' => $companyEmail,
                ]);
            }
        }

        return redirect()
            ->route('central.register.success')
            ->with('shop_login_url', $loginUrl)
            ->with('shop_name', $validated['shop_name'])
            ->with('on_trial', true);
    }

    public function success()
    {
        if (! session('shop_login_url')) {
            return redirect()->route('central.home');
        }

        return view('central.register-success', [
            'shopLoginUrl' => session('shop_login_url'),
            'shopName' => session('shop_name'),
            'trialDays' => config('subscription.trial_days'),
            'onTrial' => (bool) session('on_trial', true),
            'monthlyPriceNgn' => subscription_monthly_price_ngn(),
        ]);
    }

    /**
     * Drop a tenant database that exists without a matching tenants row.
     */
    protected function dropOrphanedTenantDatabase(string $tenantId): void
    {
        if (Tenant::query()->whereKey($tenantId)->exists()) {
            return;
        }

        $placeholder = new Tenant(['id' => $tenantId]);
        $manager = $placeholder->database()->manager();
        $databaseName = $placeholder->database()->getName();

        if ($manager->databaseExists($databaseName)) {
            $manager->deleteDatabase($placeholder);
            Log::info('Dropped orphaned tenant database before registration.', [
                'database' => $databaseName,
            ]);
        }
    }

    /**
     * Remove a partially created tenant (and its database when possible).
     */
    protected function cleanupFailedRegistration(string $tenantId): void
    {
        $tenant = Tenant::query()->find($tenantId);

        if ($tenant) {
            try {
                $tenant->delete();
            } catch (\Throwable $e) {
                Log::warning('Failed deleting partial tenant during cleanup.', [
                    'tenant' => $tenantId,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $this->dropOrphanedTenantDatabase($tenantId);
    }

    protected function subdomainFromShopName(string $shopName): string
    {
        $subdomain = preg_replace('/[^a-z0-9]/', '', Str::lower($shopName));

        return substr((string) $subdomain, 0, 40);
    }

    /**
     * Store phones as digits only. Nigerian local numbers (0XXXXXXXXXX)
     * are converted to 234XXXXXXXXXX so the same number can't be reused
     * in a different format.
     */
    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (Str::startsWith($digits, '0') && strlen($digits) === 11) {
            $digits = '234'.substr($digits, 1);
        }

        return $digits;
    }

    protected function phoneAlreadyUsed(string $normalizedPhone): bool
    {
        if ($normalizedPhone === '') {
            return false;
        }

        return Tenant::query()
            ->whereNotNull('owner_phone')
            ->get(['owner_phone'])
            ->contains(function (Tenant $tenant) use ($normalizedPhone) {
                return $this->normalizePhone((string) $tenant->owner_phone) === $normalizedPhone;
            });
    }

    protected function reservedSubdomains(): array
    {
        return [
            'www', 'app', 'api', 'admin', 'mail', 'ftp', 'localhost',
            'staging', 'dev', 'test', 'demo', 'support', 'help', 'billing',
        ];
    }
}
