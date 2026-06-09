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
        Schema::create('ai_automation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('mode', ['supervised', 'copilot', 'autopilot'])->default('copilot');
            $table->integer('human_led_threshold')->default(60);  // 0-60%
            $table->integer('ai_assisted_threshold')->default(80); // 60-80%
            $table->integer('ai_driven_threshold')->default(100);  // 80-100%
            $table->timestamps();
            
            // Each user (owner) should have only one settings row
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_automation_settings');
    }
};
