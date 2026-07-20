<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TrialDeviceFingerprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class TrialEligibilityService
{
    public const COOKIE_NAME = 'sf_device';

    /**
     * True when this browser/device has never registered a shop.
     */
    public function deviceAlreadyHasShop(?string $rawFingerprint, ?Request $request = null): bool
    {
        return $this->findExistingShop($rawFingerprint, $request) !== null;
    }

    /**
     * Latest shop linked to this device/browser, if any.
     *
     * Checks the JS fingerprint and the cross-subdomain device cookie
     * (needed because browsers like Brave farble canvas/UA per origin,
     * so shop.example.com and example.com often produce different hashes).
     */
    public function findExistingShop(?string $rawFingerprint, ?Request $request = null): ?Tenant
    {
        $hashes = array_values(array_unique(array_filter([
            $this->hashFingerprint($rawFingerprint),
            $this->cookieHash($request),
        ])));

        if ($hashes === []) {
            return null;
        }

        $record = TrialDeviceFingerprint::query()
            ->whereIn('fingerprint', $hashes)
            ->latest('updated_at')
            ->first();

        if (! $record) {
            return null;
        }

        return Tenant::query()->find($record->tenant_id);
    }

    /**
     * @deprecated Prefer deviceAlreadyHasShop — kept for clarity in older call sites.
     */
    public function qualifiesForTrial(?string $rawFingerprint, ?Request $request = null): bool
    {
        return ! $this->deviceAlreadyHasShop($rawFingerprint, $request);
    }

    public function remember(string $tenantId, ?string $rawFingerprint, Request $request, bool $receivedTrial = true): void
    {
        $hash = $this->hashFingerprint($rawFingerprint);

        if (! $hash) {
            return;
        }

        TrialDeviceFingerprint::query()->updateOrCreate(
            [
                'fingerprint' => $hash,
                'tenant_id' => $tenantId,
            ],
            [
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                'received_trial' => $receivedTrial,
            ]
        );

        $this->queueDeviceCookie($hash);
    }

    public function hashFingerprint(?string $rawFingerprint): ?string
    {
        $raw = trim((string) $rawFingerprint);

        if ($raw === '' || strlen($raw) < 16) {
            return null;
        }

        return hash('sha256', $raw);
    }

    public function cookieHash(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

        $value = trim((string) $request->cookie(self::COOKIE_NAME));

        if ($value === '' || ! preg_match('/^[a-f0-9]{64}$/', $value)) {
            return null;
        }

        return $value;
    }

    public function queueDeviceCookie(string $hash): void
    {
        Cookie::queue(cookie(
            self::COOKIE_NAME,
            $hash,
            60 * 24 * 400,
            '/',
            $this->cookieDomain(),
            (bool) config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax') ?? 'lax'
        ));
    }

    /**
     * Parent domain so the cookie is shared by central + shop subdomains
     * (e.g. .salesflow.top or .localhost).
     */
    public function cookieDomain(): ?string
    {
        $domain = trim((string) config('app.tenant_domain'));

        if ($domain === '') {
            return null;
        }

        return '.'.ltrim($domain, '.');
    }
}
