<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Users Table Migration
 * 
 * This migration creates the main users table that stores all user account information.
 * Users are the core of our personal finance system - everything else belongs to a user.
 * 
 * Why this structure?
 * - We store essential user info like name, email, password
 * - We add financial-specific fields like preferred currency, timezone
 * - We include security fields like email verification, password reset
 * - We add soft deletes for data retention (users can be "deleted" but data preserved)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This method defines what happens when we run the migration.
     * It creates the table structure.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Primary key - unique identifier for each user
            // Auto-incrementing integer (1, 2, 3, etc.)
            $table->id();
            
            // Basic user information
            // First name - varchar(255), required
            $table->string('first_name');
            
            // Last name - varchar(255), required  
            $table->string('last_name');
            
            // Email address - must be unique across all users
            // This is used for login and communication
            $table->string('email')->unique();
            
            // Email verification timestamp
            // NULL = not verified, timestamp = when they verified
            $table->timestamp('email_verified_at')->nullable();
            
            // Encrypted password
            // Laravel automatically hashes passwords for security
            $table->string('password');
            
            // Profile information
            // Profile photo path (stored in storage/app/public/avatars/)
            $table->string('avatar')->nullable();
            
            // Phone number for notifications (optional)
            $table->string('phone')->nullable();
            
            // Date of birth - useful for financial planning features
            $table->date('date_of_birth')->nullable();
            
            // Personal finance preferences
            // Default currency for this user (USD, EUR, GBP, etc.)
            $table->string('preferred_currency', 3)->default('USD');
            
            // User's timezone for proper date/time display
            $table->string('timezone')->default('UTC');
            
            // Locale for language/formatting preferences
            $table->string('locale')->default('en');
            
            // Financial settings
            // Monthly income - helps with budget calculations
            $table->decimal('monthly_income', 15, 2)->nullable();
            
            // Budget notification preferences
            $table->boolean('budget_alerts_enabled')->default(true);
            
            // Bill reminder preferences  
            $table->boolean('bill_reminders_enabled')->default(true);
            
            // How many days before bill due date to send reminders
            $table->integer('bill_reminder_days')->default(3);
            
            // Low balance alert threshold
            $table->decimal('low_balance_threshold', 15, 2)->default(100.00);
            
            // Security and access control
            // When user last logged in - helps track inactive accounts
            $table->timestamp('last_login_at')->nullable();
            
            // IP address of last login - security tracking
            $table->ipAddress('last_login_ip')->nullable();
            
            // Failed login attempts counter - for account lockout
            $table->integer('failed_login_attempts')->default(0);
            
            // When account was locked due to too many failed attempts
            $table->timestamp('locked_until')->nullable();
            
            // Two-factor authentication settings
            $table->boolean('two_factor_enabled')->default(false);
            
            // Backup codes for 2FA recovery (JSON array)
            $table->json('two_factor_backup_codes')->nullable();
            
            // Account status
            // active, suspended, inactive, deleted
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            
            // Remember token for "Remember Me" functionality
            $table->rememberToken();
            
            // Timestamps - created_at and updated_at
            // Laravel automatically manages these
            $table->timestamps();
            
            // Soft deletes - allows "deleting" users while preserving data
            // deleted_at timestamp - NULL = active, timestamp = deleted
            $table->softDeletes();
            
            // Database indexes for performance
            // Index on email for fast login lookups
            $table->index('email');
            
            // Index on status for filtering active users
            $table->index('status');
            
            // Index on created_at for sorting by registration date
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * This method defines what happens when we rollback the migration.
     * It should undo everything that the up() method did.
     */
    public function down(): void
    {
        // Drop the entire users table
        // This will permanently delete all user data!
        Schema::dropIfExists('users');
    }
};