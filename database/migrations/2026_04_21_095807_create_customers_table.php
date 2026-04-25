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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('shopify_customer_id')->nullable()->index();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->string('status')->default('New'); // New, Returning, VIP
            $table->text('notes')->nullable();
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();

            // Ensure unique email per owner
            $table->unique(['owner_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
