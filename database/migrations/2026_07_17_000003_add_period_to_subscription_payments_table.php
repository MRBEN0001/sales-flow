<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPeriodToSubscriptionPaymentsTable extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->timestamp('period_starts_at')->nullable()->after('paid_at');
            $table->timestamp('period_ends_at')->nullable()->after('period_starts_at');
        });

        // Preserve a useful period for payments completed before this column existed.
        $lastEndByTenant = [];

        DB::table('subscription_payments')
            ->where('status', 'success')
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get()
            ->each(function ($payment) use (&$lastEndByTenant) {
                $paidAt = Carbon::parse($payment->paid_at ?: $payment->updated_at);
                $previousEnd = $lastEndByTenant[$payment->tenant_id] ?? null;
                $startsAt = $previousEnd && $previousEnd->greaterThan($paidAt)
                    ? $previousEnd->copy()
                    : $paidAt->copy();
                $months = $payment->plan === 'yearly' ? 12 : 1;
                $endsAt = $startsAt->copy()->addMonthsNoOverflow($months);

                DB::table('subscription_payments')
                    ->where('id', $payment->id)
                    ->update([
                        'period_starts_at' => $startsAt,
                        'period_ends_at' => $endsAt,
                    ]);

                $lastEndByTenant[$payment->tenant_id] = $endsAt;
            });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn(['period_starts_at', 'period_ends_at']);
        });
    }
}
