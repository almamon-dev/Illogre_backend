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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email')->nullable()->index();
            $table->string('customer_avatar')->nullable();
            $table->string('subject');
            $table->string('category')->nullable();
            $table->string('source')->default('Chat');
            $table->integer('confidence')->default(0);
            $table->string('status')->default('Pending');
            $table->string('assigned')->default('AI Agent');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
