<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Password Reset Tokens Table Migration
 * 
 * This table stores temporary tokens for password reset functionality.
 * When a user requests a password reset, a token is generated and stored here.
 * 
 * Why this structure?
 * - Secure password reset process
 * - Tokens expire automatically for security
 * - One token per email address
 * - Laravel's built-in password reset uses this table
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // Email address that requested the reset
            // This is the primary key - one reset token per email
            $table->string('email')->primary();
            
            // The reset token (hashed for security)
            $table->string('token');
            
            // When this token was created
            // Tokens automatically expire after a certain time
            $table->timestamp('created_at')->nullable();
            
            // Index for token lookups
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};