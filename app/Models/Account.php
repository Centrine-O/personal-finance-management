<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Account Model
 * 
 * Represents financial accounts like checking, savings, credit cards, etc.
 * Each account tracks its balance and all transactions that affect it.
 * 
 * Account Types:
 * - checking: Daily spending accounts
 * - savings: Interest-bearing savings accounts
 * - credit: Credit cards and lines of credit
 * - investment: Brokerage accounts, 401k, IRAs
 * - loan: Mortgages, car loans, personal loans
 * - cash: Physical cash wallets
 * 
 * Key Features:
 * - Balance tracking and calculations
 * - Credit limit management
 * - Bank API integration support
 * - Transaction history
 * - Visual customization
 */
class Account extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'institution_name',
        'account_number_last4',
        'balance',
        'initial_balance',
        'credit_limit',
        'interest_rate',
        'currency',
        'is_active',
        'include_in_net_worth',
        'color',
        'icon',
        'external_account_id',
        'auto_sync_enabled',
        'sort_order',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'balance' => 'decimal:2',
        'initial_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'interest_rate' => 'decimal:4',
        'is_active' => 'boolean',
        'include_in_net_worth' => 'boolean',
        'auto_sync_enabled' => 'boolean',
        'sort_order' => 'integer',
        'last_synced_at' => 'datetime',
        'balance_updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new account, set the sort order automatically
        static::creating(function ($account) {
            if (!$account->sort_order) {
                $maxOrder = static::where('user_id', $account->user_id)->max('sort_order');
                $account->sort_order = ($maxOrder ?? 0) + 1;
            }
            
            // Set initial balance_updated_at timestamp
            $account->balance_updated_at = now();
        });

        // When updating balance, update the balance_updated_at timestamp
        static::updating(function ($account) {
            if ($account->isDirty('balance')) {
                $account->balance_updated_at = now();
            }
        });
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the user who owns this account.
     * 
     * Usage: $account->user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions for this account.
     * 
     * Usage: $account->transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get recent transactions (last 30 days).
     * 
     * Usage: $account->recentTransactions
     */
    public function recentTransactions(): HasMany
    {
        return $this->transactions()
                    ->where('transaction_date', '>=', now()->subDays(30));
    }

    /**
     * Get all transfer transactions where this account is the destination.
     * 
     * Usage: $account->transfersIn
     */
    public function transfersIn(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transfer_account_id')
                    ->where('type', 'transfer');
    }

    /**
     * Get all bills paid from this account.
     * 
     * Usage: $account->bills
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get all goals linked to this account.
     * 
     * Usage: $account->goals
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Get all recurring transactions for this account.
     * 
     * Usage: $account->recurringTransactions
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope to get only active accounts.
     * 
     * Usage: Account::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get accounts by type.
     * 
     * Usage: Account::ofType('checking')->get()
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get accounts included in net worth calculations.
     * 
     * Usage: Account::includedInNetWorth()->get()
     */
    public function scopeIncludedInNetWorth($query)
    {
        return $query->where('include_in_net_worth', true);
    }

    /**
     * Scope to get accounts that need syncing.
     * 
     * Usage: Account::needingSync()->get()
     */
    public function scopeNeedingSync($query)
    {
        return $query->where('auto_sync_enabled', true)
                    ->where(function ($q) {
                        $q->whereNull('last_synced_at')
                          ->orWhere('last_synced_at', '<', now()->subHours(6));
                    });
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Get the account's display name with institution.
     * 
     * Example: "Chase Checking (...1234)"
     * 
     * Usage: $account->display_name
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->name;
        
        if ($this->institution_name) {
            $name = "{$this->institution_name} {$name}";
        }
        
        if ($this->account_number_last4) {
            $name .= " (...{$this->account_number_last4})";
        }
        
        return $name;
    }

    /**
     * Get the account balance formatted as currency.
     * 
     * Usage: $account->formatted_balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return $this->formatCurrency($this->balance);
    }

    /**
     * Get available credit for credit accounts.
     * 
     * Usage: $account->available_credit
     */
    public function getAvailableCreditAttribute(): float
    {
        if ($this->type === 'credit' && $this->credit_limit) {
            return max(0, $this->credit_limit - $this->balance);
        }
        
        return 0;
    }

    /**
     * Get credit utilization percentage for credit accounts.
     * 
     * Usage: $account->credit_utilization
     */
    public function getCreditUtilizationAttribute(): float
    {
        if ($this->type === 'credit' && $this->credit_limit && $this->credit_limit > 0) {
            return round(($this->balance / $this->credit_limit) * 100, 2);
        }
        
        return 0;
    }

    /**
     * Check if this is an asset account (adds to net worth).
     * 
     * Usage: $account->is_asset
     */
    public function getIsAssetAttribute(): bool
    {
        return in_array($this->type, ['checking', 'savings', 'investment', 'cash']);
    }

    /**
     * Check if this is a liability account (subtracts from net worth).
     * 
     * Usage: $account->is_liability
     */
    public function getIsLiabilityAttribute(): bool
    {
        return in_array($this->type, ['credit', 'loan']);
    }

    /**
     * Get the account's net worth contribution.
     * 
     * Assets contribute positively, liabilities negatively.
     * 
     * Usage: $account->net_worth_contribution
     */
    public function getNetWorthContributionAttribute(): float
    {
        if (!$this->include_in_net_worth) {
            return 0;
        }
        
        return $this->is_asset ? $this->balance : -$this->balance;
    }

    // ========================================
    // BALANCE CALCULATIONS
    // ========================================

    /**
     * Recalculate account balance from transactions.
     * 
     * This is useful for ensuring balance accuracy or fixing discrepancies.
     */
    public function recalculateBalance(): float
    {
        // Start with initial balance
        $calculatedBalance = $this->initial_balance;
        
        // Add all income transactions
        $totalIncome = $this->transactions()
                           ->where('type', 'income')
                           ->sum('amount');
        
        // Subtract all expense transactions  
        $totalExpenses = $this->transactions()
                             ->where('type', 'expense')
                             ->sum('amount');
        
        // Handle transfers
        $transfersOut = $this->transactions()
                            ->where('type', 'transfer')
                            ->sum('amount');
        
        $transfersIn = $this->transfersIn()->sum('amount');
        
        // Calculate final balance
        $calculatedBalance = $calculatedBalance + $totalIncome - $totalExpenses - $transfersOut + $transfersIn;
        
        // Update the stored balance if different
        if (abs($calculatedBalance - $this->balance) > 0.01) {
            $this->update(['balance' => $calculatedBalance]);
        }
        
        return $calculatedBalance;
    }

    /**
     * Update account balance based on a transaction.
     * 
     * This is called when transactions are created, updated, or deleted.
     */
    public function updateBalanceFromTransaction(Transaction $transaction, string $action = 'add'): void
    {
        $amount = $transaction->amount;
        $multiplier = ($action === 'add') ? 1 : -1;
        
        switch ($transaction->type) {
            case 'income':
                $this->increment('balance', $amount * $multiplier);
                break;
                
            case 'expense':
                $this->decrement('balance', $amount * $multiplier);
                break;
                
            case 'transfer':
                // For the source account, subtract the amount
                if ($transaction->account_id === $this->id) {
                    $this->decrement('balance', $amount * $multiplier);
                }
                // For the destination account, add the amount
                if ($transaction->transfer_account_id === $this->id) {
                    $this->increment('balance', $amount * $multiplier);
                }
                break;
        }
        
        $this->touch('balance_updated_at');
    }

    /**
     * Get account balance history for a specific period.
     * 
     * Returns daily balance snapshots.
     */
    public function getBalanceHistory(Carbon $startDate, Carbon $endDate): array
    {
        $history = [];
        $currentBalance = $this->initial_balance;
        $current = $startDate->copy();
        
        // Get all transactions for this period, ordered by date
        $transactions = $this->transactions()
                            ->whereBetween('transaction_date', [$startDate, $endDate])
                            ->orderBy('transaction_date')
                            ->orderBy('created_at')
                            ->get();
        
        while ($current->lte($endDate)) {
            // Add transactions for this day
            $dayTransactions = $transactions->where('transaction_date', $current->toDateString());
            
            foreach ($dayTransactions as $transaction) {
                switch ($transaction->type) {
                    case 'income':
                        $currentBalance += $transaction->amount;
                        break;
                    case 'expense':
                        $currentBalance -= $transaction->amount;
                        break;
                    case 'transfer':
                        if ($transaction->account_id === $this->id) {
                            $currentBalance -= $transaction->amount;
                        } elseif ($transaction->transfer_account_id === $this->id) {
                            $currentBalance += $transaction->amount;
                        }
                        break;
                }
            }
            
            $history[] = [
                'date' => $current->toDateString(),
                'balance' => $currentBalance,
                'transaction_count' => $dayTransactions->count(),
            ];
            
            $current->addDay();
        }
        
        return $history;
    }

    /**
     * Check if account balance is low (below user's threshold).
     */
    public function hasLowBalance(): bool
    {
        // Only check for asset accounts (not credit cards or loans)
        if (!$this->is_asset) {
            return false;
        }
        
        $threshold = $this->user->low_balance_threshold ?? 100;
        
        return $this->balance < $threshold;
    }

    /**
     * Get monthly spending from this account.
     */
    public function getMonthlySpending(Carbon $month): float
    {
        return $this->transactions()
                   ->where('type', 'expense')
                   ->whereYear('transaction_date', $month->year)
                   ->whereMonth('transaction_date', $month->month)
                   ->sum('amount');
    }

    /**
     * Get monthly income to this account.
     */
    public function getMonthlyIncome(Carbon $month): float
    {
        return $this->transactions()
                   ->where('type', 'income')
                   ->whereYear('transaction_date', $month->year)
                   ->whereMonth('transaction_date', $month->month)
                   ->sum('amount');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Format amount as currency using account's currency.
     */
    public function formatCurrency(float $amount): string
    {
        $symbol = '$'; // Default to USD symbol
        
        // You could extend this to support multiple currencies
        switch ($this->currency) {
            case 'EUR':
                $symbol = '€';
                break;
            case 'GBP':
                $symbol = '£';
                break;
            case 'JPY':
                $symbol = '¥';
                break;
        }
        
        return $symbol . number_format($amount, 2);
    }

    /**
     * Check if account is overdrawn (negative balance for asset accounts).
     */
    public function isOverdrawn(): bool
    {
        return $this->is_asset && $this->balance < 0;
    }

    /**
     * Check if credit account is over limit.
     */
    public function isOverLimit(): bool
    {
        return $this->type === 'credit' 
            && $this->credit_limit 
            && $this->balance > $this->credit_limit;
    }

    /**
     * Get account age in days.
     */
    public function getAccountAge(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Reorder accounts for a user.
     */
    public static function reorderForUser($userId, array $accountIds): void
    {
        foreach ($accountIds as $order => $accountId) {
            static::where('id', $accountId)
                  ->where('user_id', $userId)
                  ->update(['sort_order' => $order + 1]);
        }
    }
}