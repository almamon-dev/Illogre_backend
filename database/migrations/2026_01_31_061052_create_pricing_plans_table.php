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
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Free Trial", "Monthly", "Quarterly", "Annual"
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->decimal('price', 10, 2)->default(0); // Price in dollars
            $table->enum('billing_period', ['trial', 'monthly', 'quarterly', 'annual']); // Billing frequency
            $table->integer('trial_days')->default(0); // Number of trial days (0 if not a trial)
            $table->boolean('is_active')->default(true); // Is this plan currently available
            $table->boolean('is_popular')->default(false); // Mark as "Most Popular"
            $table->integer('order')->default(0); // Display order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
