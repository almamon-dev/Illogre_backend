<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('pricing_plan_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->string('external_payment_id')->unique()->nullable()->comment('Stripe Session/Intent ID');
            $blueprint->decimal('amount', 10, 2);
            $blueprint->string('currency', 10)->default('USD');
            $blueprint->string('status')->default('pending')->comment('pending, completed, failed, refunded');
            $blueprint->string('payment_method')->default('card');
            $blueprint->json('metadata')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
