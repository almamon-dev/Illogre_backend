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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('shopify_order_id')->unique()->index();
            $table->string('order_number')->nullable();
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('financial_status')->nullable(); // paid, pending, partially_refunded, etc.
            $table->string('fulfillment_status')->nullable(); // fulfilled, null, partial
            $table->timestamp('shopify_created_at')->nullable();
            $table->json('raw_data')->nullable(); // Store full payload just in case
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
