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
        Schema::create('subscription_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('feature_name'); // e.g. 'ticket_limit', 'ai_limit', 'member_limit'
            $table->integer('used_count')->default(0);
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'feature_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_usages');
    }
};
