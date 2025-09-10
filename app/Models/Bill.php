<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Bill Model
 * 
 * Represents recurring bills and payment reminders.
 * Examples: Rent, utilities, subscriptions, insurance, etc.
 * 
 * Key Features:
 * - Flexible recurrence patterns (weekly, monthly, quarterly, etc.)
 * - Payment tracking and history
 * - Reminder notifications before due dates
 * - Variable amount support (for bills like utilities)
 * - Auto-pay integration
 * - Payment status tracking
 */
class Bill extends Model
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
        'name',
        'payee',
        'description',
        'amount',
        'is_fixed_amount',
        'minimum_amount',
        'maximum_amount',
        'average_amount',
        'frequency',
        'next_due_date',
        'due_day',
        'frequency_interval',
        'status',
        'reminder_enabled',
        'reminder_days_before',
        'second_reminder_enabled',
        'second_reminder_days_before',
        'auto_pay_enabled',
        'auto_pay_amount',
        'color',
        'icon',
        'total_paid',
        'payment_count',
        'last_paid_date',
        'last_paid_amount',
        'missed_payments',
        'first_bill_date',
        'notes',
        'reference_number',
        'website_url',
        'phone_number',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'average_amount' => 'decimal:2',
        'auto_pay_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'last_paid_amount' => 'decimal:2',
        'payment_count' => 'integer',
        'missed_payments' => 'integer',
        'due_day' => 'integer',
        'frequency_interval' => 'integer',
        'reminder_days_before' => 'integer',
        'second_reminder_days_before' => 'integer',
        'next_due_date' => 'date',
        'last_paid_date' => 'date',
        'first_bill_date' => 'date',
        'is_fixed_amount' => 'boolean',
        'reminder_enabled' => 'boolean',
        'second_reminder_enabled' => 'boolean',
        'auto_pay_enabled' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a bill, set first_bill_date if not provided
        static::creating(function ($bill) {
            if (!$bill->first_bill_date) {
                $bill->first_bill_date = $bill->next_due_date;
            }
        });
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the user who owns this bill.
     * 
     * Usage: $bill->user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account that pays this bill.
     * 
     * Usage: $bill->account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the category this bill belongs to.
     * 
     * Usage: $bill->category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope to get active bills.
     * 
     * Usage: Bill::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get bills by frequency.
     * 
     * Usage: Bill::ofFrequency('monthly')->get()
     */
    public function scopeOfFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Scope to get bills due soon.
     * 
     * Usage: Bill::dueSoon(7)->get() // Due within 7 days
     */
    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('next_due_date', '<=', now()->addDays($days))
                    ->where('status', 'active');
    }

    /**
     * Scope to get overdue bills.
     * 
     * Usage: Bill::overdue()->get()
     */
    public function scopeOverdue($query)
    {
        return $query->where('next_due_date', '<', now())
                    ->where('status', 'active');
    }

    /**
     * Scope to get bills with reminders enabled.
     * 
     * Usage: Bill::withReminders()->get()
     */
    public function scopeWithReminders($query)
    {
        return $query->where('reminder_enabled', true);
    }

    /**
     * Scope to get bills with auto-pay enabled.
     * 
     * Usage: Bill::withAutoPay()->get()
     */
    public function scopeWithAutoPay($query)
    {
        return $query->where('auto_pay_enabled', true);
    }

    /**
     * Scope to get bills that need first reminders today.
     * 
     * Usage: Bill::needingFirstReminder()->get()
     */
    public function scopeNeedingFirstReminder($query)
    {
        return $query->where('reminder_enabled', true)
                    ->whereRaw('DATE_SUB(next_due_date, INTERVAL reminder_days_before DAY) = CURDATE()');
    }

    /**
     * Scope to get bills that need second reminders today.
     * 
     * Usage: Bill::needingSecondReminder()->get()
     */
    public function scopeNeedingSecondReminder($query)
    {
        return $query->where('second_reminder_enabled', true)
                    ->whereRaw('DATE_SUB(next_due_date, INTERVAL second_reminder_days_before DAY) = CURDATE()');
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Get days until bill is due.
     * 
     * Usage: $bill->days_until_due
     */
    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->next_due_date, false);
    }

    /**
     * Check if bill is overdue.
     * 
     * Usage: $bill->is_overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->next_due_date < now() && $this->status === 'active';
    }

    /**
     * Check if bill is due today.
     * 
     * Usage: $bill->is_due_today
     */
    public function getIsDueTodayAttribute(): bool
    {
        return $this->next_due_date->isToday();
    }

    /**
     * Check if bill is due this week.
     * 
     * Usage: $bill->is_due_this_week
     */
    public function getIsDueThisWeekAttribute(): bool
    {
        return $this->next_due_date <= now()->endOfWeek();
    }

    /**
     * Get the frequency label.
     * 
     * Usage: $bill->frequency_label
     */
    public function getFrequencyLabelAttribute(): string
    {
        return match($this->frequency) {
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
     * Get the estimated amount for variable bills.
     * 
     * Usage: $bill->estimated_amount
     */
    public function getEstimatedAmountAttribute(): float
    {
        if ($this->is_fixed_amount) {
            return $this->amount;
        }
        
        // Use average if available, otherwise use midpoint of min/max range
        if ($this->average_amount > 0) {
            return $this->average_amount;
        }
        
        if ($this->minimum_amount && $this->maximum_amount) {
            return ($this->minimum_amount + $this->maximum_amount) / 2;
        }
        
        return $this->amount;
    }

    /**
     * Get formatted amount.
     * 
     * Usage: $bill->formatted_amount
     */
    public function getFormattedAmountAttribute(): string
    {
        if ($this->is_fixed_amount) {
            return '$' . number_format($this->amount, 2);
        }
        
        if ($this->minimum_amount && $this->maximum_amount) {
            return '$' . number_format($this->minimum_amount, 2) . ' - $' . number_format($this->maximum_amount, 2);
        }
        
        return '~$' . number_format($this->estimated_amount, 2);
    }

    /**
     * Get monthly equivalent amount.
     * 
     * Usage: $bill->monthly_amount
     */
    public function getMonthlyAmountAttribute(): float
    {
        $amount = $this->estimated_amount;
        
        return match($this->frequency) {
            'weekly' => $amount * 4.33, // Average weeks per month
            'bi_weekly' => $amount * 2.17, // Average bi-weeks per month
            'monthly' => $amount,
            'quarterly' => $amount / 3,
            'semi_annual' => $amount / 6,
            'annual' => $amount / 12,
            'custom' => $this->frequency_interval ? $amount * (30 / $this->frequency_interval) : $amount,
            default => $amount
        };
    }

    /**
     * Get average payment amount based on history.
     * 
     * Usage: $bill->calculated_average
     */
    public function getCalculatedAverageAttribute(): float
    {
        if ($this->payment_count <= 0) {
            return $this->amount;
        }
        
        return $this->total_paid / $this->payment_count;
    }

    // ========================================
    // BILL MANAGEMENT METHODS
    // ========================================

    /**
     * Mark bill as paid and update next due date.
     */
    public function markAsPaid(float $amount, Carbon $paidDate = null): void
    {
        $paidDate = $paidDate ?? now();
        
        // Update payment history
        $this->last_paid_date = $paidDate;
        $this->last_paid_amount = $amount;
        $this->total_paid += $amount;
        $this->payment_count++;
        
        // Update average amount for variable bills
        if (!$this->is_fixed_amount) {
            $this->average_amount = $this->calculated_average;
        }
        
        // Calculate next due date
        $this->calculateNextDueDate();
        
        // Reset missed payments counter
        $this->missed_payments = 0;
        
        $this->save();
        
        // Create transaction record
        $this->createPaymentTransaction($amount, $paidDate);
    }

    /**
     * Mark bill as missed payment.
     */
    public function markAsMissed(): void
    {
        $this->missed_payments++;
        
        // Calculate next due date (bill is still due, just overdue)
        $this->calculateNextDueDate();
        
        $this->save();
        
        // You could trigger a notification here
    }

    /**
     * Calculate the next due date based on frequency.
     */
    public function calculateNextDueDate(): void
    {
        $currentDue = $this->next_due_date;
        
        $nextDue = match($this->frequency) {
            'weekly' => $currentDue->addWeek(),
            'bi_weekly' => $currentDue->addWeeks(2),
            'monthly' => $currentDue->addMonth(),
            'quarterly' => $currentDue->addMonths(3),
            'semi_annual' => $currentDue->addMonths(6),
            'annual' => $currentDue->addYear(),
            'custom' => $this->frequency_interval ? $currentDue->addDays($this->frequency_interval) : $currentDue->addMonth(),
            default => $currentDue->addMonth()
        };
        
        $this->next_due_date = $nextDue;
    }

    /**
     * Create a transaction record for this bill payment.
     */
    protected function createPaymentTransaction(float $amount, Carbon $paidDate): Transaction
    {
        return Transaction::create([
            'user_id' => $this->user_id,
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'description' => "Payment: {$this->name}",
            'amount' => $amount,
            'type' => 'expense',
            'transaction_date' => $paidDate,
            'payee' => $this->payee,
            'reference_number' => $this->reference_number,
            'status' => 'cleared',
            'notes' => "Bill payment - {$this->frequency_label}",
        ]);
    }

    /**
     * Process automatic payment.
     */
    public function processAutoPay(): bool
    {
        if (!$this->auto_pay_enabled || !$this->auto_pay_amount || $this->status !== 'active') {
            return false;
        }
        
        // Check if payment is due
        if (!$this->is_due_today) {
            return false;
        }
        
        // Check if account has sufficient funds (for non-credit accounts)
        if ($this->account && $this->account->type !== 'credit') {
            if ($this->account->balance < $this->auto_pay_amount) {
                // Insufficient funds - could trigger notification
                return false;
            }
        }
        
        try {
            // Mark as paid
            $this->markAsPaid($this->auto_pay_amount);
            
            // Add note about auto-pay
            $this->notes = ($this->notes ?? '') . "\n[" . now()->format('Y-m-d') . "] Auto-paid: $" . number_format($this->auto_pay_amount, 2);
            $this->save();
            
            return true;
        } catch (\Exception $e) {
            // Log error and continue
            return false;
        }
    }

    /**
     * Update the bill amount and recalculate averages.
     */
    public function updateAmount(float $newAmount, bool $updateAverage = true): void
    {
        $this->amount = $newAmount;
        
        if ($updateAverage && !$this->is_fixed_amount && $this->payment_count > 0) {
            // Recalculate average including the new amount
            $this->average_amount = (($this->calculated_average * $this->payment_count) + $newAmount) / ($this->payment_count + 1);
        }
        
        $this->save();
    }

    /**
     * Pause the bill temporarily.
     */
    public function pauseBill(string $reason = null): void
    {
        $this->status = 'paused';
        
        if ($reason) {
            $this->notes = ($this->notes ?? '') . "\n[" . now()->format('Y-m-d') . "] Paused: " . $reason;
        }
        
        $this->save();
    }

    /**
     * Resume a paused bill.
     */
    public function resumeBill(): void
    {
        $this->status = 'active';
        
        // If bill was overdue while paused, update next due date
        if ($this->next_due_date < now()) {
            $this->calculateNextDueDate();
        }
        
        $this->save();
    }

    /**
     * Cancel the bill permanently.
     */
    public function cancelBill(string $reason = null): void
    {
        $this->status = 'cancelled';
        
        if ($reason) {
            $this->notes = ($this->notes ?? '') . "\n[" . now()->format('Y-m-d') . "] Cancelled: " . $reason;
        }
        
        $this->save();
    }

    // ========================================
    // REMINDER METHODS
    // ========================================

    /**
     * Check if first reminder should be sent today.
     */
    public function shouldSendFirstReminder(): bool
    {
        if (!$this->reminder_enabled || $this->status !== 'active') {
            return false;
        }
        
        $reminderDate = $this->next_due_date->copy()->subDays($this->reminder_days_before);
        
        return $reminderDate->isToday();
    }

    /**
     * Check if second reminder should be sent today.
     */
    public function shouldSendSecondReminder(): bool
    {
        if (!$this->second_reminder_enabled || $this->status !== 'active') {
            return false;
        }
        
        $reminderDate = $this->next_due_date->copy()->subDays($this->second_reminder_days_before);
        
        return $reminderDate->isToday();
    }

    /**
     * Get reminder message for notifications.
     */
    public function getReminderMessage(bool $isSecondReminder = false): string
    {
        $prefix = $isSecondReminder ? 'Final reminder' : 'Reminder';
        $amount = $this->formatted_amount;
        $daysUntil = $this->days_until_due;
        
        if ($daysUntil == 0) {
            return "{$prefix}: {$this->name} bill ({$amount}) is due today!";
        } elseif ($daysUntil == 1) {
            return "{$prefix}: {$this->name} bill ({$amount}) is due tomorrow.";
        } else {
            return "{$prefix}: {$this->name} bill ({$amount}) is due in {$daysUntil} days.";
        }
    }

    // ========================================
    // STATIC HELPER METHODS
    // ========================================

    /**
     * Get bills that need reminders today.
     */
    public static function getNeedingReminders(): Collection
    {
        $firstReminders = static::needingFirstReminder()->get();
        $secondReminders = static::needingSecondReminder()->get();
        
        return $firstReminders->merge($secondReminders)->unique('id');
    }

    /**
     * Get bills that need auto-pay processing today.
     */
    public static function getNeedingAutoPay(): \Illuminate\Database\Eloquent\Collection
    {
        return static::withAutoPay()
                    ->where('next_due_date', '<=', now())
                    ->get();
    }

    /**
     * Get user's monthly bill summary.
     */
    public static function getMonthlySummary(User $user): array
    {
        $bills = $user->bills()->active()->get();
        
        return [
            'total_bills' => $bills->count(),
            'total_monthly_amount' => $bills->sum('monthly_amount'),
            'fixed_bills' => $bills->where('is_fixed_amount', true)->count(),
            'variable_bills' => $bills->where('is_fixed_amount', false)->count(),
            'auto_pay_bills' => $bills->where('auto_pay_enabled', true)->count(),
            'overdue_bills' => $bills->where('is_overdue', true)->count(),
            'due_this_week' => $bills->where('is_due_this_week', true)->count(),
            'average_payment_amount' => $bills->avg('calculated_average'),
            'total_paid_this_year' => $user->bills()
                                          ->whereYear('last_paid_date', now()->year)
                                          ->sum('total_paid'),
        ];
    }

    /**
     * Create a standard monthly bill.
     */
    public static function createMonthlyBill(
        User $user,
        string $name,
        string $payee,
        float $amount,
        int $dueDay,
        Category $category,
        Account $account = null
    ): Bill {
        return static::create([
            'user_id' => $user->id,
            'account_id' => $account?->id,
            'category_id' => $category->id,
            'name' => $name,
            'payee' => $payee,
            'amount' => $amount,
            'is_fixed_amount' => true,
            'frequency' => 'monthly',
            'due_day' => $dueDay,
            'next_due_date' => now()->day($dueDay),
            'reminder_enabled' => true,
            'reminder_days_before' => 3,
        ]);
    }

    /**
     * Import bills from bank transaction patterns.
     */
    public static function suggestBillsFromTransactions(User $user, int $months = 6): Collection
    {
        // This would analyze recurring transaction patterns to suggest bills
        // Implementation would involve complex pattern recognition
        // For now, return empty collection
        return collect();
    }
}