<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Accounts Table Migration
 * 
 * Accounts represent where money is stored or owed.
 * Examples: Checking Account, Savings Account, Credit Card, Investment Account
 * 
 * Why this structure?
 * - Each user can have multiple accounts
 * - Different account types have different behaviors
 * - Track balance, credit limits, interest rates
 * - Support for account linking (bank API integration)
 * - Security for sensitive account information
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Account ownership
            // Every account belongs to a specific user
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // Delete account if user is deleted
            
            // Account identification
            // User-friendly name (e.g., "Chase Checking", "Emergency Savings")
            $table->string('name');
            
            // Account type - determines behavior and features
            $table->enum('type', [
                'checking',    // Daily spending account
                'savings',     // Interest-bearing savings account
                'credit',      // Credit card or line of credit
                'investment',  // Brokerage, 401k, IRA, etc.
                'loan',        // Mortgage, car loan, personal loan
                'cash'         // Physical cash wallet
            ]);
            
            // Bank/Institution information
            // Name of financial institution
            $table->string('institution_name')->nullable();
            
            // Last 4 digits of account number (for identification)
            // We never store full account numbers for security
            $table->string('account_number_last4', 4)->nullable();
            
            // Account balances
            // Current account balance
            $table->decimal('balance', 15, 2)->default(0.00);
            
            // Initial balance when account was added
            $table->decimal('initial_balance', 15, 2)->default(0.00);
            
            // Credit limit (for credit cards and lines of credit)
            $table->decimal('credit_limit', 15, 2)->nullable();
            
            // Interest rate (annual percentage rate)
            $table->decimal('interest_rate', 5, 4)->nullable(); // e.g., 0.0425 for 4.25%
            
            // Account settings
            // Currency for this account
            $table->string('currency', 3)->default('USD');
            
            // Is this account currently active?
            $table->boolean('is_active')->default(true);
            
            // Should this account be included in net worth calculations?
            $table->boolean('include_in_net_worth')->default(true);
            
            // Visual customization
            // Hex color for account display
            $table->string('color', 7)->default('#3B82F6'); // Default blue
            
            // Icon name for account display
            $table->string('icon')->default('credit-card');
            
            // Account integration (for bank API connections)
            // External account ID from bank API (Plaid, Yodlee, etc.)
            $table->string('external_account_id')->nullable();
            
            // When account data was last synced from bank
            $table->timestamp('last_synced_at')->nullable();
            
            // Is automatic sync enabled?
            $table->boolean('auto_sync_enabled')->default(false);
            
            // Account ordering for display
            $table->integer('sort_order')->default(0);
            
            // Security and tracking
            // When balance was last manually updated
            $table->timestamp('balance_updated_at')->nullable();
            
            // Notes about the account
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Soft deletes - preserve account history
            $table->softDeletes();
            
            // Database indexes for performance
            // Index for finding user's accounts
            $table->index(['user_id', 'is_active']);
            
            // Index for account type queries
            $table->index(['user_id', 'type']);
            
            // Index for net worth calculations
            $table->index(['user_id', 'include_in_net_worth', 'is_active']);
            
            // Index for display ordering
            $table->index(['user_id', 'sort_order']);
            
            // Index for external account lookups
            $table->index('external_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};