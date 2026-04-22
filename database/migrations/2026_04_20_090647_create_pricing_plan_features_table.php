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
        Schema::create('pricing_plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_plan_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g. 'ticket_limit', 'member_limit', 'premium_support'
            $table->string('value')->nullable(); // e.g. '2500', '10', 'true'
            $table->boolean('is_limit')->default(false); // To distinguish from descriptive features
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plan_features');
    }
};
