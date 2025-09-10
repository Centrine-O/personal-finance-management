<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Categories Table Migration
 * 
 * Categories help users organize their income and expenses.
 * Examples: "Food", "Transportation", "Salary", "Freelance Income"
 * 
 * Why this structure?
 * - Categories can be global (system-wide) or user-specific
 * - Support for parent/child relationships (subcategories)
 * - Different types: income, expense, transfer
 * - Color coding for visual organization
 * - Icons for better UX
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Category ownership
            // NULL = system category (available to all users)
            // user_id = personal category (only for specific user)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade'); // Delete category if user is deleted
            
            // Hierarchical categories (parent/child relationships)
            // NULL = top-level category
            // parent_id = this is a subcategory of another category
            // Example: "Food" -> "Groceries", "Restaurants"
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('categories')
                  ->onDelete('cascade'); // Delete subcategories if parent is deleted
            
            // Category information
            // Category name (e.g., "Food", "Transportation", "Salary")
            $table->string('name');
            
            // Detailed description (optional)
            $table->text('description')->nullable();
            
            // Category type - determines how it affects budget calculations
            $table->enum('type', ['income', 'expense', 'transfer'])->default('expense');
            
            // Visual customization
            // Hex color code for category display (#FF5733)
            $table->string('color', 7)->default('#6B7280'); // Default gray
            
            // Icon name (from icon library like Heroicons)
            $table->string('icon')->default('currency-dollar');
            
            // Category settings
            // Is this category currently active?
            $table->boolean('is_active')->default(true);
            
            // Should this category be included in budget calculations?
            $table->boolean('is_budgetable')->default(true);
            
            // Default budget amount suggestion for this category
            $table->decimal('suggested_budget', 15, 2)->nullable();
            
            // Category ordering (for display purposes)
            $table->integer('sort_order')->default(0);
            
            // Timestamps
            $table->timestamps();
            
            // Soft deletes - preserve category data even when "deleted"
            // This is important because transactions reference categories
            $table->softDeletes();
            
            // Database indexes for performance
            // Index for finding user's categories
            $table->index(['user_id', 'is_active']);
            
            // Index for finding categories by type
            $table->index(['type', 'is_active']);
            
            // Index for hierarchical queries (finding subcategories)
            $table->index('parent_id');
            
            // Index for ordering categories
            $table->index(['sort_order', 'name']);
            
            // Composite index for common queries
            $table->index(['user_id', 'type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};