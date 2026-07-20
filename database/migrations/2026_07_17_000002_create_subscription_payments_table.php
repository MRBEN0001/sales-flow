<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPaymentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('plan', 32);
            $table->unsignedBigInteger('amount'); // NGN (naira), not kobo
            $table->string('currency', 8)->default('NGN');
            $table->string('reference')->unique();
            $table->string('status', 32)->default('pending'); // pending|success|failed
            $table->string('paystack_status')->nullable();
            $table->string('channel')->nullable();
            $table->string('paid_email')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
}
