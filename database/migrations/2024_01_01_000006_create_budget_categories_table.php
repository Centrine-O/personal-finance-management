<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Budget Categories Table Migration
 * 
 * This is a pivot table that connects budgets with categories and stores the allocated amounts.
 * Each record represents: "In this budget, we allocated $X to this category"
 * 
 * Why this structure?
 * - Many-to-many relationship between budgets and categories
 * - Each budget can have multiple categories
 * - Each category can appear in multiple budgets
 * - Store allocated amount, spent amount, and remaining amount
 * - Track budget performance per category
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_categories', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Foreign key relationships
            // The budget this allocation belongs to
            $table->foreignId('budget_id')
                  ->constrained('budgets')
                  ->onDelete('cascade'); // Delete allocation if budget is deleted
            
            // The category this allocation is for
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->onDelete('cascade'); // Delete allocation if category is deleted
            
            // Budget allocation amounts
            // How much money was allocated to this category in this budget
            $table->decimal('allocated_amount', 15, 2)->default(0.00);
            
            // How much has actually been spent in this category (calculated from transactions)
            $table->decimal('spent_amount', 15, 2)->default(0.00);
            
            // Remaining amount (allocated - spent)
            // This is calculated but stored for performance
            $table->decimal('remaining_amount', 15, 2)->default(0.00);
            
            // Budget performance tracking
            // Previous period's spending in this category (for comparison)
            $table->decimal('previous_period_spent', 15, 2)->nullable();
            
            // Percentage of budget used (0-100+)
            $table->decimal('usage_percentage', 5, 2)->default(0.00);
            
            // Category-specific budget settings
            // Should this category send alerts when overspent?
            $table->boolean('alert_on_overspend')->default(true);
            
            // Custom alert threshold for this category (percentage)
            $table->integer('alert_threshold')->nullable(); // If null, use budget default
            
            // Is this a fixed amount (like rent) or flexible (like groceries)?
            $table->boolean('is_fixed_amount')->default(false);
            
            // Priority level for this category (1 = highest, 5 = lowest)
            $table->integer('priority')->default(3);
            
            // Notes specific to this category in this budget
            $table->text('notes')->nullable();
            
            // Rollover settings (category-specific overrides)
            // Should unused amount from this category roll over?
            $table->boolean('rollover_unused')->nullable(); // NULL = use budget default
            
            // Historical tracking
            // When was the spent amount last calculated?
            $table->timestamp('last_calculated_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Database constraints and indexes
            // Ensure each category appears only once per budget
            $table->unique(['budget_id', 'category_id'], 'unique_budget_category');
            
            // Index for budget-specific queries
            $table->index('budget_id');
            
            // Index for category-specific queries (budget history for a category)
            $table->index('category_id');
            
            // Index for performance queries
            $table->index(['budget_id', 'usage_percentage']);
            
            // Index for priority-based sorting
            $table->index(['budget_id', 'priority', 'allocated_amount']);
            
            // Check constraint to ensure amounts are non-negative
            $table->check('allocated_amount >= 0', 'allocated_amount_positive');
            $table->check('spent_amount >= 0', 'spent_amount_positive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_categories');
    }
};