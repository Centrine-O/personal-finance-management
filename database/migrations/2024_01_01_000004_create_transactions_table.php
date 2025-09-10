<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Transactions Table Migration
 * 
 * Transactions are the individual financial events - every expense, income, or transfer.
 * This is the most important table as it contains all the actual financial data.
 * 
 * Why this structure?
 * - Every transaction belongs to a user and account
 * - Categorized for budgeting and reporting
 * - Support for recurring transactions
 * - Attachment support (receipts, invoices)
 * - Detailed metadata for analysis
 * - Reconciliation support for bank sync
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Transaction ownership and relationships
            // Every transaction belongs to a user
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Source account (where money comes from or goes to)
            $table->foreignId('account_id')
                  ->constrained('accounts')
                  ->onDelete('cascade');
            
            // Transaction category (for organization and budgeting)
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->onDelete('restrict'); // Don't allow deleting categories with transactions
            
            // For transfers: the destination account
            $table->foreignId('transfer_account_id')
                  ->nullable()
                  ->constrained('accounts')
                  ->onDelete('set null');
            
            // Core transaction data
            // Transaction description/memo
            $table->string('description');
            
            // Transaction amount (always positive - type determines if income/expense)
            $table->decimal('amount', 15, 2);
            
            // Transaction type
            $table->enum('type', ['income', 'expense', 'transfer']);
            
            // When the transaction occurred (not when it was entered)
            $table->date('transaction_date');
            
            // Transaction status for bank reconciliation
            $table->enum('status', ['pending', 'cleared', 'reconciled'])->default('cleared');
            
            // Additional transaction details
            // Payee/merchant name
            $table->string('payee')->nullable();
            
            // Reference number (check number, confirmation code, etc.)
            $table->string('reference_number')->nullable();
            
            // Location where transaction occurred
            $table->string('location')->nullable();
            
            // Tags for additional organization (JSON array)
            // Example: ["business", "tax-deductible", "reimbursable"]
            $table->json('tags')->nullable();
            
            // Additional notes about the transaction
            $table->text('notes')->nullable();
            
            // Recurring transaction support
            // If this is part of a recurring series, link to parent
            $table->foreignId('recurring_transaction_id')
                  ->nullable()
                  ->constrained('recurring_transactions')
                  ->onDelete('set null');
            
            // External integration data
            // External transaction ID from bank/API
            $table->string('external_transaction_id')->nullable();
            
            // When this transaction was imported/synced
            $table->timestamp('imported_at')->nullable();
            
            // Hash for duplicate detection during import
            $table->string('import_hash')->nullable();
            
            // Receipt and attachment support
            // Path to uploaded receipt image
            $table->string('receipt_path')->nullable();
            
            // Original receipt filename
            $table->string('receipt_filename')->nullable();
            
            // Has receipt been processed/scanned?
            $table->boolean('receipt_processed')->default(false);
            
            // Budget tracking
            // Was this transaction planned in a budget?
            $table->boolean('is_budgeted')->default(false);
            
            // Budget variance (if budgeted amount differs from actual)
            $table->decimal('budget_variance', 15, 2)->nullable();
            
            // Analysis and reporting fields
            // Is this a business expense?
            $table->boolean('is_business')->default(false);
            
            // Is this tax deductible?
            $table->boolean('is_tax_deductible')->default(false);
            
            // Is this reimbursable?
            $table->boolean('is_reimbursable')->default(false);
            
            // Has this been reimbursed?
            $table->boolean('is_reimbursed')->default(false);
            
            // Split transaction support
            // If this is part of a split transaction, reference the parent
            $table->foreignId('parent_transaction_id')
                  ->nullable()
                  ->constrained('transactions')
                  ->onDelete('cascade');
            
            // Is this the main transaction or a split child?
            $table->boolean('is_split')->default(false);
            
            // Timestamps
            $table->timestamps();
            
            // Soft deletes - preserve transaction history
            $table->softDeletes();
            
            // Database indexes for performance optimization
            // Primary lookup: user's transactions by date
            $table->index(['user_id', 'transaction_date', 'deleted_at']);
            
            // Account-specific queries
            $table->index(['account_id', 'transaction_date']);
            
            // Category-based reporting
            $table->index(['category_id', 'transaction_date']);
            
            // Budget analysis queries
            $table->index(['user_id', 'type', 'transaction_date']);
            
            // Reconciliation queries
            $table->index(['account_id', 'status']);
            
            // External integration lookups
            $table->index('external_transaction_id');
            $table->index('import_hash');
            
            // Recurring transaction queries
            $table->index('recurring_transaction_id');
            
            // Split transaction queries
            $table->index('parent_transaction_id');
            
            // Business/tax reporting
            $table->index(['user_id', 'is_business', 'transaction_date']);
            $table->index(['user_id', 'is_tax_deductible', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};