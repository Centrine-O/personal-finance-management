<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Budgets Table Migration
 * 
 * Budgets help users plan their spending for specific time periods.
 * Each budget covers a specific period (month/year) and contains multiple category allocations.
 * 
 * Why this structure?
 * - Each budget belongs to a user and covers a specific time period
 * - Track planned vs actual spending
 * - Support for different budget periods (monthly, yearly)
 * - Roll-over unused budget amounts to next period
 * - Budget approval workflow for shared finances
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Budget ownership
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Budget period definition
            // Budget name (e.g., "January 2024 Budget", "Q1 2024 Business Budget")
            $table->string('name');
            
            // Budget period type
            $table->enum('period_type', ['weekly', 'monthly', 'yearly', 'custom']);
            
            // Budget period start date
            $table->date('start_date');
            
            // Budget period end date
            $table->date('end_date');
            
            // Budget totals and tracking
            // Total planned income for this period
            $table->decimal('planned_income', 15, 2)->default(0.00);
            
            // Total actual income for this period (calculated from transactions)
            $table->decimal('actual_income', 15, 2)->default(0.00);
            
            // Total planned expenses for this period
            $table->decimal('planned_expenses', 15, 2)->default(0.00);
            
            // Total actual expenses for this period (calculated from transactions)
            $table->decimal('actual_expenses', 15, 2)->default(0.00);
            
            // Budget settings and preferences
            // Currency for this budget
            $table->string('currency', 3)->default('USD');
            
            // Budget status
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft');
            
            // Is this budget template for creating future budgets?
            $table->boolean('is_template')->default(false);
            
            // Template name if this is a template
            $table->string('template_name')->nullable();
            
            // Budget rollover settings
            // Should unused amounts roll over to next period?
            $table->boolean('rollover_unused')->default(false);
            
            // Should overspent amounts be deducted from next period?
            $table->boolean('deduct_overspent')->default(false);
            
            // Notification settings
            // Send alert when spending reaches this percentage of budget
            $table->integer('alert_percentage')->default(80); // 80%
            
            // Should send weekly budget summary emails?
            $table->boolean('weekly_summary')->default(true);
            
            // Should send alerts when budget exceeded?
            $table->boolean('overspend_alerts')->default(true);
            
            // Budget approval (for household/shared budgets)
            // Who created this budget
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            // Who approved this budget
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            // When was this budget approved
            $table->timestamp('approved_at')->nullable();
            
            // Budget notes and description
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Soft deletes
            $table->softDeletes();
            
            // Database indexes
            // Primary lookup: user's budgets by period
            $table->index(['user_id', 'start_date', 'end_date']);
            
            // Status-based queries
            $table->index(['user_id', 'status']);
            
            // Template queries
            $table->index(['user_id', 'is_template']);
            
            // Active budget lookups
            $table->index(['user_id', 'status', 'start_date']);
            
            // Ensure no overlapping budget periods for the same user
            $table->unique(['user_id', 'start_date', 'end_date'], 'unique_user_budget_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};