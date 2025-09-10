<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Transaction Model
 * 
 * The core of our financial system. Every transaction represents a movement of money:
 * - Income: Money coming in (salary, freelance, gifts)
 * - Expense: Money going out (rent, food, entertainment)
 * - Transfer: Money moving between accounts
 * 
 * Key Features:
 * - Detailed transaction metadata (payee, location, notes)
 * - Receipt attachment support
 * - Split transactions (one transaction divided into multiple categories)
 * - Recurring transaction links
 * - Reconciliation status for bank sync
 * - Business and tax tracking
 * - Budget integration
 */
class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'transfer_account_id',
        'description',
        'amount',
        'type',
        'transaction_date',
        'status',
        'payee',
        'reference_number',
        'location',
        'tags',
        'notes',
        'recurring_transaction_id',
        'external_transaction_id',
        'receipt_path',
        'receipt_filename',
        'receipt_processed',
        'is_budgeted',
        'budget_variance',
        'is_business',
        'is_tax_deductible',
        'is_reimbursable',
        'is_reimbursed',
        'parent_transaction_id',
        'is_split',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'budget_variance' => 'decimal:2',
        'tags' => 'array',
        'imported_at' => 'datetime',
        'receipt_processed' => 'boolean',
        'is_budgeted' => 'boolean',
        'is_business' => 'boolean',
        'is_tax_deductible' => 'boolean',
        'is_reimbursable' => 'boolean',
        'is_reimbursed' => 'boolean',
        'is_split' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // After creating a transaction, update the account balance
        static::created(function ($transaction) {
            $transaction->updateAccountBalances('add');
            $transaction->updateBudgetSpending();
        });

        // After updating a transaction, recalculate balances
        static::updated(function ($transaction) {
            // If amount, type, or account changed, recalculate balances
            if ($transaction->wasChanged(['amount', 'type', 'account_id', 'transfer_account_id'])) {
                $transaction->updateAccountBalances('update');
                $transaction->updateBudgetSpending();
            }
        });

        // Before deleting a transaction, update balances
        static::deleting(function ($transaction) {
            $transaction->updateAccountBalances('remove');
            $transaction->updateBudgetSpending('remove');
        });
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the user who owns this transaction.
     * 
     * Usage: $transaction->user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account this transaction belongs to.
     * 
     * Usage: $transaction->account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the destination account for transfers.
     * 
     * Usage: $transaction->transferAccount
     */
    public function transferAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_account_id');
    }

    /**
     * Get the category this transaction belongs to.
     * 
     * Usage: $transaction->category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the recurring transaction template (if this is a recurring transaction).
     * 
     * Usage: $transaction->recurringTransaction
     */
    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class);
    }

    /**
     * Get the parent transaction (for split transactions).
     * 
     * Usage: $transaction->parentTransaction
     */
    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'parent_transaction_id');
    }

    /**
     * Get all child transactions (for split transactions).
     * 
     * Usage: $transaction->childTransactions
     */
    public function childTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id')
                    ->orderBy('amount', 'desc');
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope to filter by transaction type.
     * 
     * Usage: Transaction::ofType('expense')->get()
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by date range.
     * 
     * Usage: Transaction::inDateRange('2024-01-01', '2024-01-31')->get()
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by current month.
     * 
     * Usage: Transaction::currentMonth()->get()
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereYear('transaction_date', now()->year)
                    ->whereMonth('transaction_date', now()->month);
    }

    /**
     * Scope to filter by current year.
     * 
     * Usage: Transaction::currentYear()->get()
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereYear('transaction_date', now()->year);
    }

    /**
     * Scope to get only main transactions (not split children).
     * 
     * Usage: Transaction::mainTransactions()->get()
     */
    public function scopeMainTransactions($query)
    {
        return $query->whereNull('parent_transaction_id');
    }

    /**
     * Scope to get only split child transactions.
     * 
     * Usage: Transaction::splitChildren()->get()
     */
    public function scopeSplitChildren($query)
    {
        return $query->whereNotNull('parent_transaction_id');
    }

    /**
     * Scope to get business transactions.
     * 
     * Usage: Transaction::business()->get()
     */
    public function scopeBusiness($query)
    {
        return $query->where('is_business', true);
    }

    /**
     * Scope to get tax-deductible transactions.
     * 
     * Usage: Transaction::taxDeductible()->get()
     */
    public function scopeTaxDeductible($query)
    {
        return $query->where('is_tax_deductible', true);
    }

    /**
     * Scope to get transactions with receipts.
     * 
     * Usage: Transaction::withReceipts()->get()
     */
    public function scopeWithReceipts($query)
    {
        return $query->whereNotNull('receipt_path');
    }

    /**
     * Scope to search transactions by description or payee.
     * 
     * Usage: Transaction::search('grocery')->get()
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('description', 'like', "%{$term}%")
              ->orWhere('payee', 'like', "%{$term}%")
              ->orWhere('notes', 'like', "%{$term}%");
        });
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Get formatted transaction amount with currency symbol.
     * 
     * Usage: $transaction->formatted_amount
     */
    public function getFormattedAmountAttribute(): string
    {
        $symbol = '$'; // Default currency symbol
        
        // You could extend this to use the account's currency
        return $symbol . number_format($this->amount, 2);
    }

    /**
     * Get the transaction amount with appropriate sign for display.
     * 
     * Income is positive, expenses are negative.
     * 
     * Usage: $transaction->signed_amount
     */
    public function getSignedAmountAttribute(): float
    {
        switch ($this->type) {
            case 'income':
                return $this->amount;
            case 'expense':
                return -$this->amount;
            case 'transfer':
                // For transfers, the sign depends on which account you're viewing from
                return $this->amount; // Base amount, sign determined by context
            default:
                return $this->amount;
        }
    }

    /**
     * Get the receipt URL if a receipt exists.
     * 
     * Usage: $transaction->receipt_url
     */
    public function getReceiptUrlAttribute(): ?string
    {
        if ($this->receipt_path) {
            return asset('storage/' . $this->receipt_path);
        }
        
        return null;
    }

    /**
     * Check if transaction has a receipt attached.
     * 
     * Usage: $transaction->has_receipt
     */
    public function getHasReceiptAttribute(): bool
    {
        return !empty($this->receipt_path);
    }

    /**
     * Get transaction age in days.
     * 
     * Usage: $transaction->age_in_days
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->transaction_date->diffInDays(now());
    }

    /**
     * Check if transaction is recent (within last 7 days).
     * 
     * Usage: $transaction->is_recent
     */
    public function getIsRecentAttribute(): bool
    {
        return $this->age_in_days <= 7;
    }

    /**
     * Check if this is a split transaction (has children).
     * 
     * Usage: $transaction->is_split_parent
     */
    public function getIsSplitParentAttribute(): bool
    {
        return $this->childTransactions()->exists();
    }

    /**
     * Check if this is a child of a split transaction.
     * 
     * Usage: $transaction->is_split_child
     */
    public function getIsSplitChildAttribute(): bool
    {
        return !is_null($this->parent_transaction_id);
    }

    /**
     * Set tags attribute (ensure it's always an array).
     */
    public function setTagsAttribute($value)
    {
        $this->attributes['tags'] = is_array($value) ? json_encode($value) : $value;
    }

    // ========================================
    // TRANSACTION OPERATIONS
    // ========================================

    /**
     * Update account balances when transaction is created, updated, or deleted.
     */
    protected function updateAccountBalances(string $action): void
    {
        // Update primary account balance
        if ($this->account) {
            $this->account->updateBalanceFromTransaction($this, $action);
        }

        // Update transfer account balance (for transfers)
        if ($this->type === 'transfer' && $this->transferAccount) {
            $this->transferAccount->updateBalanceFromTransaction($this, $action);
        }
    }

    /**
     * Update budget spending when transaction changes.
     */
    protected function updateBudgetSpending(string $action = 'add'): void
    {
        // Only update budget for expense transactions
        if ($this->type !== 'expense') {
            return;
        }

        // Find active budget for this transaction's date
        $budget = $this->user->budgets()
                            ->where('status', 'active')
                            ->where('start_date', '<=', $this->transaction_date)
                            ->where('end_date', '>=', $this->transaction_date)
                            ->first();

        if (!$budget) {
            return;
        }

        // Find budget category allocation
        $budgetCategory = $budget->budgetCategories()
                                ->where('category_id', $this->category_id)
                                ->first();

        if (!$budgetCategory) {
            return;
        }

        // Update spent amount
        $multiplier = ($action === 'remove') ? -1 : 1;
        $budgetCategory->increment('spent_amount', $this->amount * $multiplier);
        
        // Recalculate remaining amount and usage percentage
        $budgetCategory->remaining_amount = $budgetCategory->allocated_amount - $budgetCategory->spent_amount;
        $budgetCategory->usage_percentage = $budgetCategory->allocated_amount > 0 
            ? ($budgetCategory->spent_amount / $budgetCategory->allocated_amount) * 100 
            : 0;
        
        $budgetCategory->save();
    }

    /**
     * Create a transfer transaction between two accounts.
     */
    public static function createTransfer(
        User $user,
        Account $fromAccount,
        Account $toAccount,
        float $amount,
        string $description,
        Carbon $date = null
    ): Transaction {
        return static::create([
            'user_id' => $user->id,
            'account_id' => $fromAccount->id,
            'transfer_account_id' => $toAccount->id,
            'category_id' => Category::getTransferCategory()->id, // Special transfer category
            'description' => $description,
            'amount' => $amount,
            'type' => 'transfer',
            'transaction_date' => $date ?? now(),
            'status' => 'cleared',
        ]);
    }

    /**
     * Split this transaction into multiple categories.
     */
    public function splitIntoCategories(array $splits): Collection
    {
        $splitTransactions = collect();
        $totalSplitAmount = 0;

        foreach ($splits as $split) {
            $splitAmount = $split['amount'];
            $totalSplitAmount += $splitAmount;

            $splitTransaction = static::create([
                'user_id' => $this->user_id,
                'account_id' => $this->account_id,
                'category_id' => $split['category_id'],
                'parent_transaction_id' => $this->id,
                'description' => $split['description'] ?? $this->description,
                'amount' => $splitAmount,
                'type' => $this->type,
                'transaction_date' => $this->transaction_date,
                'status' => $this->status,
                'payee' => $this->payee,
                'notes' => $split['notes'] ?? null,
                'is_split' => true,
            ]);

            $splitTransactions->push($splitTransaction);
        }

        // Verify split amounts match original
        if (abs($totalSplitAmount - $this->amount) > 0.01) {
            throw new \Exception('Split amounts do not match original transaction amount');
        }

        // Mark original transaction as split parent
        $this->update(['is_split' => true]);

        return $splitTransactions;
    }

    /**
     * Duplicate this transaction with optional modifications.
     */
    public function duplicate(array $overrides = []): Transaction
    {
        $attributes = $this->toArray();
        
        // Remove fields that shouldn't be duplicated
        unset($attributes['id'], $attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);
        
        // Apply overrides
        $attributes = array_merge($attributes, $overrides);
        
        // Set new transaction date if not specified
        if (!isset($overrides['transaction_date'])) {
            $attributes['transaction_date'] = now();
        }

        return static::create($attributes);
    }

    /**
     * Add tags to this transaction.
     */
    public function addTags(array $newTags): void
    {
        $currentTags = $this->tags ?? [];
        $updatedTags = array_unique(array_merge($currentTags, $newTags));
        
        $this->update(['tags' => $updatedTags]);
    }

    /**
     * Remove tags from this transaction.
     */
    public function removeTags(array $tagsToRemove): void
    {
        $currentTags = $this->tags ?? [];
        $updatedTags = array_diff($currentTags, $tagsToRemove);
        
        $this->update(['tags' => array_values($updatedTags)]);
    }

    // ========================================
    // STATIC HELPER METHODS
    // ========================================

    /**
     * Get spending by category for a date range.
     */
    public static function getSpendingByCategory(
        User $user,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        return static::where('user_id', $user->id)
                    ->where('type', 'expense')
                    ->inDateRange($startDate, $endDate)
                    ->join('categories', 'transactions.category_id', '=', 'categories.id')
                    ->groupBy('categories.id', 'categories.name')
                    ->selectRaw('categories.id, categories.name, SUM(transactions.amount) as total_amount')
                    ->orderBy('total_amount', 'desc')
                    ->get();
    }

    /**
     * Get monthly spending trend.
     */
    public static function getMonthlySpendingTrend(User $user, int $months = 12): Collection
    {
        $startDate = now()->subMonths($months)->startOfMonth();
        
        return static::where('user_id', $user->id)
                    ->where('type', 'expense')
                    ->where('transaction_date', '>=', $startDate)
                    ->selectRaw('
                        YEAR(transaction_date) as year,
                        MONTH(transaction_date) as month,
                        SUM(amount) as total_amount,
                        COUNT(*) as transaction_count
                    ')
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();
    }

    /**
     * Get top payees by spending amount.
     */
    public static function getTopPayees(User $user, int $limit = 10): Collection
    {
        return static::where('user_id', $user->id)
                    ->where('type', 'expense')
                    ->whereNotNull('payee')
                    ->selectRaw('payee, SUM(amount) as total_amount, COUNT(*) as transaction_count')
                    ->groupBy('payee')
                    ->orderBy('total_amount', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Generate import hash for duplicate detection.
     */
    public static function generateImportHash(array $transactionData): string
    {
        return md5(json_encode([
            'account_id' => $transactionData['account_id'],
            'amount' => $transactionData['amount'],
            'transaction_date' => $transactionData['transaction_date'],
            'description' => $transactionData['description'],
            'external_id' => $transactionData['external_transaction_id'] ?? null,
        ]));
    }
}