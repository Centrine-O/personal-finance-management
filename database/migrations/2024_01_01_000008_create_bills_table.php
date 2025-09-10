<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Bills Table Migration
 * 
 * Bills represent recurring expenses like rent, utilities, subscriptions, etc.
 * The system can remind users about upcoming due dates and track payment history.
 * 
 * Why this structure?
 * - Track recurring bills with flexible frequency patterns
 * - Link to accounts for automatic payment tracking
 * - Generate reminders before due dates
 * - Track payment history and missed payments
 * - Support for variable amounts (like utility bills)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Bill ownership and relationships
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Account that pays this bill
            $table->foreignId('account_id')
                  ->nullable()
                  ->constrained('accounts')
                  ->onDelete('set null');
            
            // Category for this bill expense
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->onDelete('restrict'); // Don't delete categories with active bills
            
            // Bill information
            // Name of the bill (e.g., "Rent", "Electric Bill", "Netflix")
            $table->string('name');
            
            // Payee/company name
            $table->string('payee');
            
            // Bill description
            $table->text('description')->nullable();
            
            // Financial details
            // Expected amount (can be different from actual payment)
            $table->decimal('amount', 15, 2);
            
            // Is this amount fixed or variable?
            $table->boolean('is_fixed_amount')->default(true);
            
            // Minimum amount (for variable bills like utilities)
            $table->decimal('minimum_amount', 15, 2)->nullable();
            
            // Maximum amount (for variable bills)
            $table->decimal('maximum_amount', 15, 2)->nullable();
            
            // Average amount over last 12 months (calculated field)
            $table->decimal('average_amount', 15, 2)->nullable();
            
            // Recurrence pattern
            // How often this bill occurs
            $table->enum('frequency', [
                'weekly',       // Every week
                'bi_weekly',    // Every 2 weeks
                'monthly',      // Every month
                'quarterly',    // Every 3 months
                'semi_annual',  // Every 6 months
                'annual',       // Once per year
                'custom'        // Custom frequency
            ])->default('monthly');
            
            // Next due date
            $table->date('next_due_date');
            
            // Day of month for monthly bills (1-31)
            $table->integer('due_day')->nullable();
            
            // Custom frequency interval (for custom frequency)
            // Example: every 45 days = custom frequency with interval 45
            $table->integer('frequency_interval')->nullable();
            
            // Bill status and settings
            // Current status of this bill
            $table->enum('status', [
                'active',       // Currently active
                'paused',       // Temporarily stopped
                'cancelled',    // Permanently stopped
                'paid_off'      // Bill is paid off (like loans)
            ])->default('active');
            
            // Should send reminder notifications?
            $table->boolean('reminder_enabled')->default(true);
            
            // How many days before due date to send first reminder
            $table->integer('reminder_days_before')->default(3);
            
            // Should send second reminder closer to due date?
            $table->boolean('second_reminder_enabled')->default(false);
            $table->integer('second_reminder_days_before')->default(1);
            
            // Automatic payment settings
            // Is this bill paid automatically?
            $table->boolean('auto_pay_enabled')->default(false);
            
            // Auto-pay amount (might differ from bill amount)
            $table->decimal('auto_pay_amount', 15, 2)->nullable();
            
            // Visual customization
            // Color for bill display
            $table->string('color', 7)->default('#EF4444'); // Default red
            
            // Icon for bill display
            $table->string('icon')->default('receipt-tax');
            
            // Payment tracking
            // Total amount paid for this bill (lifetime)
            $table->decimal('total_paid', 15, 2)->default(0.00);
            
            // Number of payments made
            $table->integer('payment_count')->default(0);
            
            // Last payment date
            $table->date('last_paid_date')->nullable();
            
            // Last payment amount
            $table->decimal('last_paid_amount', 15, 2)->nullable();
            
            // Number of missed payments
            $table->integer('missed_payments')->default(0);
            
            // Bill history and analysis
            // When was this bill first set up?
            $table->date('first_bill_date')->nullable();
            
            // Notes about this bill
            $table->text('notes')->nullable();
            
            // External reference (account number, policy number, etc.)
            $table->string('reference_number')->nullable();
            
            // Website URL for online bill pay
            $table->string('website_url')->nullable();
            
            // Phone number for customer service
            $table->string('phone_number')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Soft deletes
            $table->softDeletes();
            
            // Database indexes
            // Primary lookup: user's active bills
            $table->index(['user_id', 'status']);
            
            // Due date queries (for reminders)
            $table->index(['next_due_date', 'status', 'reminder_enabled']);
            
            // Account-based queries
            $table->index(['account_id', 'status']);
            
            // Category-based queries
            $table->index(['category_id', 'status']);
            
            // Auto-pay queries
            $table->index(['auto_pay_enabled', 'next_due_date']);
            
            // Frequency-based queries
            $table->index(['user_id', 'frequency', 'status']);
            
            // Payment history queries
            $table->index(['user_id', 'last_paid_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};