<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Goals Table Migration
 * 
 * Goals help users save money for specific objectives like emergency funds,
 * vacations, house down payments, retirement, etc.
 * 
 * Why this structure?
 * - Each goal has a target amount and target date
 * - Track progress with current saved amount
 * - Calculate required monthly/weekly contributions
 * - Support for linked accounts (where goal money is saved)
 * - Goal categories and priority levels
 * - Automated contribution tracking
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Goal ownership
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Optional: Account where goal funds are saved
            $table->foreignId('account_id')
                  ->nullable()
                  ->constrained('accounts')
                  ->onDelete('set null');
            
            // Goal information
            // Name of the goal (e.g., "Emergency Fund", "Hawaii Vacation")
            $table->string('name');
            
            // Detailed description of the goal
            $table->text('description')->nullable();
            
            // Goal type/category
            $table->enum('type', [
                'emergency_fund',   // 3-6 months of expenses
                'vacation',         // Travel and vacation funds
                'house',           // Home down payment
                'car',             // Vehicle purchase
                'retirement',      // Retirement savings
                'education',       // Education/tuition
                'debt_payoff',     // Paying off specific debt
                'investment',      // Investment funding
                'other'           // Custom goals
            ])->default('other');
            
            // Financial targets
            // Target amount to save
            $table->decimal('target_amount', 15, 2);
            
            // Current amount saved toward this goal
            $table->decimal('current_amount', 15, 2)->default(0.00);
            
            // Target completion date
            $table->date('target_date');
            
            // Starting date for this goal
            $table->date('start_date');
            
            // Goal progress calculations
            // Required monthly contribution to meet goal
            $table->decimal('required_monthly_contribution', 15, 2)->default(0.00);
            
            // Required weekly contribution to meet goal
            $table->decimal('required_weekly_contribution', 15, 2)->default(0.00);
            
            // Progress percentage (0-100)
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            
            // Goal settings and preferences
            // Goal priority (1 = highest, 5 = lowest)
            $table->integer('priority')->default(3);
            
            // Goal status
            $table->enum('status', [
                'active',       // Currently saving toward this goal
                'paused',       // Temporarily stopped
                'completed',    // Goal reached
                'cancelled',    // Goal abandoned
                'on_hold'       // Waiting to start
            ])->default('active');
            
            // Visual customization
            // Color for goal display
            $table->string('color', 7)->default('#10B981'); // Default green
            
            // Icon for goal display
            $table->string('icon')->default('currency-dollar');
            
            // Automation settings
            // Should automatically transfer money to this goal?
            $table->boolean('auto_contribute')->default(false);
            
            // Amount to automatically contribute
            $table->decimal('auto_contribute_amount', 15, 2)->nullable();
            
            // Frequency of automatic contributions
            $table->enum('auto_contribute_frequency', [
                'weekly', 'bi_weekly', 'monthly', 'quarterly'
            ])->nullable();
            
            // Which day to make automatic contributions
            $table->integer('auto_contribute_day')->nullable(); // 1-31 for monthly, 1-7 for weekly
            
            // Notification settings
            // Send progress update notifications?
            $table->boolean('progress_notifications')->default(true);
            
            // Send reminder notifications if behind?
            $table->boolean('reminder_notifications')->default(true);
            
            // Milestone tracking
            // Notify at these percentage milestones (JSON array)
            // Example: [25, 50, 75, 100]
            $table->json('milestone_notifications')->default('[]');
            
            // Which milestones have already been reached (JSON array)
            $table->json('milestones_reached')->default('[]');
            
            // Goal completion
            // When was this goal completed?
            $table->timestamp('completed_at')->nullable();
            
            // How much extra was saved beyond the goal?
            $table->decimal('excess_amount', 15, 2)->default(0.00);
            
            // Goal notes and motivation
            $table->text('notes')->nullable();
            
            // Motivational note (displayed on progress screen)
            $table->string('motivation_note')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Soft deletes
            $table->softDeletes();
            
            // Database indexes
            // Primary lookup: user's goals
            $table->index(['user_id', 'status']);
            
            // Target date queries (for deadline alerts)
            $table->index(['user_id', 'target_date', 'status']);
            
            // Account-linked goals
            $table->index(['account_id', 'status']);
            
            // Goal type queries
            $table->index(['user_id', 'type']);
            
            // Priority-based sorting
            $table->index(['user_id', 'priority', 'target_date']);
            
            // Auto-contribution queries
            $table->index(['auto_contribute', 'auto_contribute_frequency']);
            
            // Progress tracking
            $table->index(['user_id', 'progress_percentage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};