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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Platform Roles
            // super_admin: Main platform admin
            // owner: Subscriber (Team Creator)
            // member: Team Staff
            $table->enum('user_type', ['super_admin', 'owner', 'member'])->default('owner');

            // Team-specific role (e.g., 'Support Manager')
            $table->string('role')->nullable();

            $table->enum('status', ['active', 'invited', 'disabled', 'pending'])->default('active');

            $table->string('reset_password_token', 100)->nullable();
            $table->timestamp('reset_password_token_expire_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
