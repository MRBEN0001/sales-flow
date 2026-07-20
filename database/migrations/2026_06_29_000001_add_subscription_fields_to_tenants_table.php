<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionFieldsToTenantsTable extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('shop_name')->nullable()->after('id');
            $table->string('owner_email')->nullable()->after('shop_name');
            $table->timestamp('trial_ends_at')->nullable()->after('owner_email');
            $table->string('subscription_status', 32)->default('trial')->after('trial_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['shop_name', 'owner_email', 'trial_ends_at', 'subscription_status']);
        });
    }
}
