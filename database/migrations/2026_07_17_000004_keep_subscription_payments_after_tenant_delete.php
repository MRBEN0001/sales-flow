<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KeepSubscriptionPaymentsAfterTenantDelete extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        // Keep payment history even if a shop is deleted/recreated with the same id.
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }
}
