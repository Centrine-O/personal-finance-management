<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Recurring Transactions Table Migration
 * 
 * Recurring transactions are templates for creating regular transactions automatically.
 * Examples: Monthly salary, weekly allowance, quarterly dividends, etc.
 * 
 * Why this structure?
 * - Template for generating actual transactions
 * - Flexible frequency patterns (weekly, monthly, custom)
 * - Track when next transaction should be created
 * - Support for end dates and limited occurrences
 * - Link generated transactions back to their recurring source
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Recurring transaction ownership and relationships
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Default account for transactions
            $table->foreignId('account_id')
                  ->constrained('accounts')
                  ->onDelete('cascade');
            
            // Default category for transactions
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->onDelete('restrict');
            
            // For recurring transfers
            $table->foreignId('transfer_account_id')
                  ->nullable()
                  ->constrained('accounts')
                  ->onDelete('set null');
            
            // Transaction template details
            // Description template for generated transactions
            $table->string('description');
            
            // Default amount for generated transactions
            $table->decimal('amount', 15, 2);
            
            // Transaction type (income, expense, transfer)
            $table->enum('type', ['income', 'expense', 'transfer']);
            
            // Default payee for generated transactions
            $table->string('payee')->nullable();
            
            // Recurrence pattern
            // How often should transactions be generated?
            $table->enum('frequency', [
                'daily',        // Every day
                'weekly',       // Every week
                'bi_weekly',    // Every 2 weeks
                'monthly',      // Every month
                'quarterly',    // Every 3 months
                'semi_annual',  // Every 6 months
                'annual',       // Once per year
                'custom'        // Custom interval
            ]);
            
            // Custom frequency interval (for custom frequency)
            // Example: every 45 days = custom frequency with interval 45
            $table->integer('frequency_interval')->nullable();
            
            // Recurrence timing
            // When should the first transaction be generated?
            $table->date('start_date');
            
            // When should recurring transactions stop? (optional)
            $table->date('end_date')->nullable();
            
            // Next scheduled transaction date
            $table->date('next_due_date');
            
            // Day of month for monthly recurrence (1-31)
            $table->integer('day_of_month')->nullable();
            
            // Day of week for weekly recurrence (0=Sunday, 6=Saturday)
            $table->integer('day_of_week')->nullable();
            
            // Recurrence limits
            // Maximum number of transactions to generate (optional)
            $table->integer('max_occurrences')->nullable();
            
            // Number of transactions generated so far
            $table->integer('occurrences_count')->default(0);
            
            // Recurring transaction status and settings
            // Current status
            $table->enum('status', [
                'active',       // Currently generating transactions
                'paused',       // Temporarily stopped
                'completed',    // Finished (reached end date or max occurrences)
                'cancelled'     // Manually stopped
            ])->default('active');
            
            // Should generate transactions automatically?
            $table->boolean('auto_generate')->default(true);
            
            // How many days in advance should transactions be generated?
            $table->integer('generate_days_ahead')->default(0);
            
            // Transaction generation settings
            // Should generated transactions be marked as pending?
            $table->boolean('generate_as_pending')->default(false);
            
            // Should send notification when transaction is generated?
            $table->boolean('notification_enabled')->default(false);
            
            // Amount variation settings (for semi-variable recurring transactions)
            // Is the amount allowed to vary?
            $table->boolean('allow_amount_variation')->default(false);
            
            // Minimum allowed amount
            $table->decimal('min_amount', 15, 2)->nullable();
            
            // Maximum allowed amount
            $table->decimal('max_amount', 15, 2)->nullable();
            
            // Tags to apply to generated transactions (JSON array)
            $table->json('default_tags')->nullable();
            
            // Default notes for generated transactions
            $table->text('default_notes')->nullable();
            
            // Tracking and history
            // When was the last transaction generated?
            $table->timestamp('last_generated_at')->nullable();
            
            // Total amount generated from this recurring transaction
            $table->decimal('total_generated_amount', 15, 2)->default(0.00);
            
            // Notes about this recurring transaction
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Soft deletes
            $table->softDeletes();
            
            // Database indexes
            // Primary lookup: user's active recurring transactions
            $table->index(['user_id', 'status']);
            
            // Due date queries (for transaction generation)
            $table->index(['next_due_date', 'status', 'auto_generate']);
            
            // Account-based queries
            $table->index(['account_id', 'status']);
            
            // Category-based queries
            $table->index(['category_id', 'status']);
            
            // Frequency-based queries
            $table->index(['frequency', 'status']);
            
            // Generation scheduling
            $table->index(['status', 'auto_generate', 'next_due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};