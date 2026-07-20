<?php

namespace App\Models;

use Carbon\Carbon;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'data' => 'array',
    ];

    public const STATUS_TRIAL = 'trial';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'shop_name',
            'owner_email',
            'owner_phone',
            'trial_ends_at',
            'subscription_status',
            'subscription_plan',
            'subscription_ends_at',
            'created_at',
            'updated_at',
        ];
    }

    public function isOnTrial(): bool
    {
        $this->syncTrialExpiry();

        return $this->subscription_status === self::STATUS_TRIAL
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function hasActiveSubscription(): bool
    {
        $this->syncSubscriptionExpiry();

        return $this->isOnTrial()
            || (
                $this->subscription_status === self::STATUS_ACTIVE
                && $this->subscription_ends_at
                && $this->subscription_ends_at->isFuture()
            );
    }

    public function trialDaysRemaining(): int
    {
        if (! $this->trial_ends_at || $this->trial_ends_at->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($this->trial_ends_at, false);
    }

    /**
     * Date when shop access currently ends (paid period or trial).
     */
    public function accessEndsAt(): ?Carbon
    {
        if ($this->subscription_status === self::STATUS_ACTIVE && $this->subscription_ends_at) {
            return $this->subscription_ends_at;
        }

        if ($this->subscription_status === self::STATUS_TRIAL && $this->trial_ends_at) {
            return $this->trial_ends_at;
        }

        return $this->subscription_ends_at ?: $this->trial_ends_at;
    }

    public function subscriptionDaysRemaining(): int
    {
        $ends = $this->accessEndsAt();

        if (! $ends || $ends->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($ends, false);
    }

    public function planLabel(): ?string
    {
        if (! $this->subscription_plan) {
            return null;
        }

        return $this->subscription_plan === 'yearly' ? 'Yearly' : 'Monthly';
    }

    /**
     * If the free-trial end date has passed, mark the shop expired.
     */
    public function syncTrialExpiry(): void
    {
        if ($this->subscription_status !== self::STATUS_TRIAL) {
            return;
        }

        if (! $this->trial_ends_at || $this->trial_ends_at->isFuture()) {
            return;
        }

        $this->forceFill(['subscription_status' => self::STATUS_EXPIRED])->save();
    }

    /**
     * If a paid subscription end date has passed, mark the shop expired.
     */
    public function syncSubscriptionExpiry(): void
    {
        $this->syncTrialExpiry();

        if ($this->subscription_status !== self::STATUS_ACTIVE) {
            return;
        }

        // Avoid false expiry from a stale in-memory model after payment.
        if (! $this->subscription_ends_at) {
            $this->refresh();
        }

        if ($this->subscription_ends_at && $this->subscription_ends_at->isFuture()) {
            return;
        }

        $this->forceFill(['subscription_status' => self::STATUS_EXPIRED])->save();
    }
}
