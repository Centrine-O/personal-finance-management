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
 * RecurringTransaction Model
 * 
 * Templates for automatically generating regular transactions.
 * Examples: Monthly salary, weekly allowance, quarterly dividends, rent payments
 * 
 * Key Features:
 * - Flexible frequency patterns (daily, weekly, monthly, custom)
 * - Automatic transaction generation
 * - Amount variation support for semi-variable transactions
 * - End date and occurrence limits
 * - Generation tracking and history
 * - Notification settings for generated transactions
 */
class RecurringTransaction extends Model
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
        'payee',
        'frequency',
        'frequency_interval',
        'start_date',
        'end_date',
        'next_due_date',
        'day_of_month',
        'day_of_week',
        'max_occurrences',
        'occurrences_count',
        'status',
        'auto_generate',
        'generate_days_ahead',
        'generate_as_pending',
        'notification_enabled',
        'allow_amount_variation',
        'min_amount',
        'max_amount',
        'default_tags',
        'default_notes',
        'last_generated_at',
        'total_generated_amount',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'total_generated_amount' => 'decimal:2',
        'frequency_interval' => 'integer',
        'day_of_month' => 'integer',
        'day_of_week' => 'integer',
        'max_occurrences' => 'integer',
        'occurrences_count' => 'integer',
        'generate_days_ahead' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_due_date' => 'date',
        'last_generated_at' => 'datetime',
        'auto_generate' => 'boolean',
        'generate_as_pending' => 'boolean',
        'notification_enabled' => 'boolean',
        'allow_amount_variation' => 'boolean',
        'default_tags' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When creating, set initial next_due_date if not provided
        static::creating(function ($recurringTransaction) {
            if (!$recurringTransaction->next_due_date) {
                $recurringTransaction->next_due_date = $recurringTransaction->start_date;
            }
        });
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the user who owns this recurring transaction.
     * 
     * Usage: $recurringTransaction->user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account for this recurring transaction.
     * 
     * Usage: $recurringTransaction->account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the destination account for transfers.
     * 
     * Usage: $recurringTransaction->transferAccount
     */
    public function transferAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_account_id');
    }

    /**
     * Get the category for this recurring transaction.
     * 
     * Usage: $recurringTransaction->category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all transactions generated from this recurring transaction.
     * 
     * Usage: $recurringTransaction->generatedTransactions
     */
    public function generatedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'recurring_transaction_id')
                    ->orderBy('transaction_date', 'desc');
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope to get active recurring transactions.
     * 
     * Usage: RecurringTransaction::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get recurring transactions by frequency.
     * 
     * Usage: RecurringTransaction::ofFrequency('monthly')->get()
     */
    public function scopeOfFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Scope to get recurring transactions by type.
     * 
     * Usage: RecurringTransaction::ofType('income')->get()
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get recurring transactions that need generation.
     * 
     * Usage: RecurringTransaction::needingGeneration()->get()
     */
    public function scopeNeedingGeneration($query)
    {
        return $query->where('status', 'active')
                    ->where('auto_generate', true)
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('max_occurrences')
                          ->orWhereRaw('occurrences_count < max_occurrences');
                    });
    }

    /**
     * Scope to get recurring transactions due for generation today.
     * 
     * Usage: RecurringTransaction::dueToday()->get()
     */
    public function scopeDueToday($query)
    {
        return $query->needingGeneration()
                    ->where('next_due_date', '<=', now()->addDays(0)); // Generate on due date
    }

    /**
     * Scope to get recurring transactions due within specified days ahead.
     * 
     * Usage: RecurringTransaction::dueWithinDays(3)->get()
     */
    public function scopeDueWithinDays($query, $days = null)
    {
        return $query->needingGeneration()
                    ->where(function ($q) use ($days) {
                        if ($days !== null) {
                            $q->where('next_due_date', '<=', now()->addDays($days));
                        } else {
                            // Use the individual generate_days_ahead setting
                            $q->whereRaw('next_due_date <= DATE_ADD(CURDATE(), INTERVAL generate_days_ahead DAY)');
                        }
                    });
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Get the frequency label.
     * 
     * Usage: $recurringTransaction->frequency_label
     */
    public function getFrequencyLabelAttribute(): string
    {
        return match($this->frequency) {
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'bi_weekly' => 'Bi-weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semi_annual' => 'Semi-annual',
            'annual' => 'Annual',
            'custom' => 'Custom',
            default => 'Unknown'
        };
    }

    /**
     * Get days until next generation.
     * 
     * Usage: $recurringTransaction->days_until_next
     */
    public function getDaysUntilNextAttribute(): int
    {
        return now()->diffInDays($this->next_due_date, false);
    }

    /**
     * Check if this recurring transaction is due for generation.
     * 
     * Usage: $recurringTransaction->is_due_for_generation
     */
    public function getIsDueForGenerationAttribute(): bool
    {
        if ($this->status !== 'active' || !$this->auto_generate) {
            return false;
        }
        
        // Check if end date has passed
        if ($this->end_date && $this->end_date < now()) {
            return false;
        }
        
        // Check if max occurrences reached
        if ($this->max_occurrences && $this->occurrences_count >= $this->max_occurrences) {
            return false;
        }
        
        // Check if due date is within generation window
        $generateDate = now()->addDays($this->generate_days_ahead);
        
        return $this->next_due_date <= $generateDate;
    }

    /**
     * Check if this recurring transaction has reached its end.
     * 
     * Usage: $recurringTransaction->has_ended
     */
    public function getHasEndedAttribute(): bool
    {
        // End date reached
        if ($this->end_date && $this->end_date < now()) {
            return true;
        }
        
        // Max occurrences reached
        if ($this->max_occurrences && $this->occurrences_count >= $this->max_occurrences) {
            return true;
        }
        
        return false;
    }

    /**
     * Get remaining occurrences.
     * 
     * Usage: $recurringTransaction->remaining_occurrences
     */
    public function getRemainingOccurrencesAttribute(): ?int
    {
        if (!$this->max_occurrences) {
            return null; // Unlimited
        }
        
        return max(0, $this->max_occurrences - $this->occurrences_count);
    }

    /**
     * Get average generated amount.
     * 
     * Usage: $recurringTransaction->average_generated_amount
     */
    public function getAverageGeneratedAmountAttribute(): float
    {
        if ($this->occurrences_count <= 0) {
            return $this->amount;
        }
        
        return $this->total_generated_amount / $this->occurrences_count;
    }

    // ========================================
    // TRANSACTION GENERATION METHODS
    // ========================================

    /**
     * Generate a transaction from this recurring template.
     */
    public function generateTransaction(float $amount = null, Carbon $date = null): Transaction
    {
        $amount = $amount ?? $this->getGenerationAmount();
        $date = $date ?? $this->next_due_date;
        
        // Create the transaction
        $transaction = Transaction::create([
            'user_id' => $this->user_id,
            'account_id' => $this->account_id,
            'transfer_account_id' => $this->transfer_account_id,
            'category_id' => $this->category_id,
            'recurring_transaction_id' => $this->id,
            'description' => $this->description,
            'amount' => $amount,
            'type' => $this->type,
            'transaction_date' => $date,
            'payee' => $this->payee,
            'tags' => $this->default_tags,
            'notes' => $this->default_notes,
            'status' => $this->generate_as_pending ? 'pending' : 'cleared',
        ]);
        
        // Update recurring transaction tracking
        $this->updateAfterGeneration($amount);
        
        // Send notification if enabled
        if ($this->notification_enabled) {
            // You could dispatch an event here for notification handling
        }
        
        return $transaction;
    }

    /**
     * Get the amount to use for generation.
     */
    protected function getGenerationAmount(): float
    {
        if (!$this->allow_amount_variation) {
            return $this->amount;
        }
        
        // For variable amounts, you might want to:
        // 1. Use the base amount
        // 2. Prompt user for amount
        // 3. Use some logic to determine amount
        
        // For now, just return the base amount
        return $this->amount;
    }

    /**
     * Update tracking data after generating a transaction.
     */
    protected function updateAfterGeneration(float $amount): void
    {
        $this->occurrences_count++;
        $this->total_generated_amount += $amount;
        $this->last_generated_at = now();
        
        // Calculate next due date
        $this->calculateNextDueDate();
        
        // Check if we've reached the end
        if ($this->has_ended) {
            $this->status = 'completed';
        }
        
        $this->save();
    }

    /**
     * Calculate the next due date based on frequency.
     */
    public function calculateNextDueDate(): void
    {
        $current = $this->next_due_date;
        
        $next = match($this->frequency) {
            'daily' => $current->copy()->addDay(),
            'weekly' => $current->copy()->addWeek(),
            'bi_weekly' => $current->copy()->addWeeks(2),
            'monthly' => $current->copy()->addMonth(),
            'quarterly' => $current->copy()->addMonths(3),
            'semi_annual' => $current->copy()->addMonths(6),
            'annual' => $current->copy()->addYear(),
            'custom' => $this->frequency_interval ? 
                        $current->copy()->addDays($this->frequency_interval) : 
                        $current->copy()->addMonth(),
            default => $current->copy()->addMonth()
        };
        
        // Adjust for specific day requirements
        if ($this->frequency === 'monthly' && $this->day_of_month) {
            $next = $next->day($this->day_of_month);
        }
        
        if (in_array($this->frequency, ['weekly', 'bi_weekly']) && $this->day_of_week !== null) {
            $next = $next->next($this->day_of_week);
        }
        
        $this->next_due_date = $next;
    }

    /**
     * Process automatic generation if due.
     */
    public function processAutoGeneration(): bool
    {
        if (!$this->is_due_for_generation) {
            return false;
        }
        
        try {
            $this->generateTransaction();
            return true;
        } catch (\Exception $e) {
            // Log error
            return false;
        }
    }

    /**
     * Generate multiple future transactions.
     */
    public function generateFutureTransactions(int $count): Collection
    {
        $transactions = collect();
        $currentDate = $this->next_due_date->copy();
        
        for ($i = 0; $i < $count; $i++) {
            if ($this->has_ended) {
                break;
            }
            
            $amount = $this->getGenerationAmount();
            
            $transaction = Transaction::create([
                'user_id' => $this->user_id,
                'account_id' => $this->account_id,
                'transfer_account_id' => $this->transfer_account_id,
                'category_id' => $this->category_id,
                'recurring_transaction_id' => $this->id,
                'description' => $this->description,
                'amount' => $amount,
                'type' => $this->type,
                'transaction_date' => $currentDate,
                'payee' => $this->payee,
                'tags' => $this->default_tags,
                'notes' => $this->default_notes,
                'status' => 'pending', // Future transactions start as pending
            ]);
            
            $transactions->push($transaction);
            
            // Update for next iteration
            $this->updateAfterGeneration($amount);
            $currentDate = $this->next_due_date->copy();
        }
        
        return $transactions;
    }

    // ========================================
    // RECURRING TRANSACTION MANAGEMENT
    // ========================================

    /**
     * Pause this recurring transaction.
     */
    public function pause(string $reason = null): void
    {
        $this->status = 'paused';
        
        if ($reason) {
            $this->notes = ($this->notes ?? '') . "\n[" . now()->format('Y-m-d') . "] Paused: " . $reason;
        }
        
        $this->save();
    }

    /**
     * Resume a paused recurring transaction.
     */
    public function resume(): void
    {
        $this->status = 'active';
        
        // If next due date has passed while paused, update it
        if ($this->next_due_date < now()) {
            $this->calculateNextDueDate();
        }
        
        $this->save();
    }

    /**
     * Cancel this recurring transaction.
     */
    public function cancel(string $reason = null): void
    {
        $this->status = 'cancelled';
        
        if ($reason) {
            $this->notes = ($this->notes ?? '') . "\n[" . now()->format('Y-m-d') . "] Cancelled: " . $reason;
        }
        
        $this->save();
    }

    /**
     * Update the amount and propagate to future pending transactions.
     */
    public function updateAmount(float $newAmount, bool $updatePending = true): void
    {
        $this->amount = $newAmount;
        $this->save();
        
        if ($updatePending) {
            // Update pending transactions that haven't occurred yet
            $this->generatedTransactions()
                 ->where('status', 'pending')
                 ->where('transaction_date', '>', now())
                 ->update(['amount' => $newAmount]);
        }
    }

    /**
     * Skip the next occurrence.
     */
    public function skipNext(string $reason = null): void
    {
        $this->calculateNextDueDate();
        
        if ($reason) {
            $this->notes = ($this->notes ?? '') . "\n[" . now()->format('Y-m-d') . "] Skipped next occurrence: " . $reason;
        }
        
        $this->save();
    }

    // ========================================
    // STATIC HELPER METHODS
    // ========================================

    /**
     * Get all recurring transactions that need processing today.
     */
    public static function getNeedingProcessing(): \Illuminate\Database\Eloquent\Collection
    {
        return static::dueToday()->get();
    }

    /**
     * Process all due recurring transactions.
     */
    public static function processAllDue(): array
    {
        $results = [
            'processed' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        $dueTransactions = static::getNeedingProcessing();
        
        foreach ($dueTransactions as $recurring) {
            try {
                if ($recurring->processAutoGeneration()) {
                    $results['processed']++;
                } else {
                    $results['failed']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'recurring_id' => $recurring->id,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Create a monthly salary recurring transaction.
     */
    public static function createMonthlySalary(
        User $user,
        Account $account,
        float $amount,
        int $payDay = 1,
        string $employer = 'Salary'
    ): RecurringTransaction {
        return static::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => Category::getSalaryCategory()->id,
            'description' => "Monthly salary from {$employer}",
            'amount' => $amount,
            'type' => 'income',
            'payee' => $employer,
            'frequency' => 'monthly',
            'day_of_month' => $payDay,
            'start_date' => now(),
            'auto_generate' => true,
            'generate_days_ahead' => 0,
            'generate_as_pending' => false,
            'notification_enabled' => true,
        ]);
    }

    /**
     * Get user's recurring transaction summary.
     */
    public static function getRecurringSummary(User $user): array
    {
        $recurring = $user->recurringTransactions()->active()->get();
        
        $monthlyIncome = $recurring->where('type', 'income')->sum('monthly_equivalent');
        $monthlyExpenses = $recurring->where('type', 'expense')->sum('monthly_equivalent');
        
        return [
            'total_active' => $recurring->count(),
            'income_recurring' => $recurring->where('type', 'income')->count(),
            'expense_recurring' => $recurring->where('type', 'expense')->count(),
            'transfer_recurring' => $recurring->where('type', 'transfer')->count(),
            'monthly_income_equivalent' => $monthlyIncome,
            'monthly_expense_equivalent' => $monthlyExpenses,
            'monthly_net' => $monthlyIncome - $monthlyExpenses,
            'auto_generating' => $recurring->where('auto_generate', true)->count(),
            'total_generated_this_year' => $user->recurringTransactions()
                                               ->whereYear('last_generated_at', now()->year)
                                               ->sum('total_generated_amount'),
        ];
    }

    /**
     * Get monthly equivalent amount for any frequency.
     */
    public function getMonthlyEquivalentAttribute(): float
    {
        return match($this->frequency) {
            'daily' => $this->amount * 30.44, // Average days per month
            'weekly' => $this->amount * 4.33, // Average weeks per month
            'bi_weekly' => $this->amount * 2.17,
            'monthly' => $this->amount,
            'quarterly' => $this->amount / 3,
            'semi_annual' => $this->amount / 6,
            'annual' => $this->amount / 12,
            'custom' => $this->frequency_interval ? $this->amount * (30.44 / $this->frequency_interval) : $this->amount,
            default => $this->amount
        };
    }
}