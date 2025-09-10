<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BudgetCategory Model
 * 
 * This is a pivot model that connects budgets with categories.
 * Each record represents an allocation of money to a specific category within a budget.
 * 
 * Example: "In January 2024 Budget, allocate $500 to Food category"
 * 
 * Key Features:
 * - Tracks allocated vs spent amounts
 * - Calculates usage percentages
 * - Category-specific alert settings
 * - Priority levels for budget planning
 * - Performance tracking and analysis
 */
class BudgetCategory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'budget_categories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'budget_id',
        'category_id',
        'allocated_amount',
        'spent_amount',
        'remaining_amount',
        'previous_period_spent',
        'usage_percentage',
        'alert_on_overspend',
        'alert_threshold',
        'is_fixed_amount',
        'priority',
        'notes',
        'rollover_unused',
        'last_calculated_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'previous_period_spent' => 'decimal:2',
        'usage_percentage' => 'decimal:2',
        'alert_on_overspend' => 'boolean',
        'alert_threshold' => 'integer',
        'is_fixed_amount' => 'boolean',
        'priority' => 'integer',
        'last_calculated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When creating or updating, automatically calculate derived fields
        static::saving(function ($budgetCategory) {
            $budgetCategory->calculateDerivedFields();
        });
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the budget this allocation belongs to.
     * 
     * Usage: $budgetCategory->budget
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the category this allocation is for.
     * 
     * Usage: $budgetCategory->category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope to get allocations that are over budget.
     * 
     * Usage: BudgetCategory::overBudget()->get()
     */
    public function scopeOverBudget($query)
    {
        return $query->whereRaw('spent_amount > allocated_amount');
    }

    /**
     * Scope to get allocations near their limit.
     * 
     * Usage: BudgetCategory::nearLimit(80)->get()
     */
    public function scopeNearLimit($query, $threshold = 80)
    {
        return $query->whereRaw('usage_percentage >= ?', [$threshold])
                    ->whereRaw('spent_amount <= allocated_amount');
    }

    /**
     * Scope to get fixed amount allocations.
     * 
     * Usage: BudgetCategory::fixedAmount()->get()
     */
    public function scopeFixedAmount($query)
    {
        return $query->where('is_fixed_amount', true);
    }

    /**
     * Scope to get allocations by priority.
     * 
     * Usage: BudgetCategory::byPriority(1)->get()
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to order by priority and then by allocated amount.
     * 
     * Usage: BudgetCategory::priorityOrder()->get()
     */
    public function scopePriorityOrder($query)
    {
        return $query->orderBy('priority')
                    ->orderBy('allocated_amount', 'desc');
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Get the variance between allocated and spent amounts.
     * 
     * Positive = under budget, Negative = over budget
     * 
     * Usage: $budgetCategory->variance
     */
    public function getVarianceAttribute(): float
    {
        return $this->allocated_amount - $this->spent_amount;
    }

    /**
     * Check if this allocation is over budget.
     * 
     * Usage: $budgetCategory->is_over_budget
     */
    public function getIsOverBudgetAttribute(): bool
    {
        return $this->spent_amount > $this->allocated_amount;
    }

    /**
     * Check if this allocation is within the alert threshold.
     * 
     * Usage: $budgetCategory->is_at_threshold
     */
    public function getIsAtThresholdAttribute(): bool
    {
        $threshold = $this->alert_threshold ?? $this->budget->alert_percentage ?? 80;
        
        return $this->usage_percentage >= $threshold;
    }

    /**
     * Get the amount remaining before hitting the alert threshold.
     * 
     * Usage: $budgetCategory->amount_until_alert
     */
    public function getAmountUntilAlertAttribute(): float
    {
        $threshold = $this->alert_threshold ?? $this->budget->alert_percentage ?? 80;
        $alertAmount = ($threshold / 100) * $this->allocated_amount;
        
        return max(0, $alertAmount - $this->spent_amount);
    }

    /**
     * Get the daily spending rate needed to stay within budget.
     * 
     * Usage: $budgetCategory->daily_budget_rate
     */
    public function getDailyBudgetRateAttribute(): float
    {
        $remainingDays = max(1, now()->diffInDays($this->budget->end_date));
        
        return $this->remaining_amount / $remainingDays;
    }

    /**
     * Get the current daily spending rate.
     * 
     * Usage: $budgetCategory->current_daily_rate
     */
    public function getCurrentDailyRateAttribute(): float
    {
        $daysPassed = max(1, $this->budget->start_date->diffInDays(now()));
        
        return $this->spent_amount / $daysPassed;
    }

    /**
     * Compare spending to previous period.
     * 
     * Usage: $budgetCategory->previous_period_variance
     */
    public function getPreviousPeriodVarianceAttribute(): ?float
    {
        if (!$this->previous_period_spent) {
            return null;
        }
        
        return $this->spent_amount - $this->previous_period_spent;
    }

    /**
     * Get percentage change from previous period.
     * 
     * Usage: $budgetCategory->previous_period_change_percentage
     */
    public function getPreviousPeriodChangePercentageAttribute(): ?float
    {
        if (!$this->previous_period_spent || $this->previous_period_spent == 0) {
            return null;
        }
        
        return (($this->spent_amount - $this->previous_period_spent) / $this->previous_period_spent) * 100;
    }

    /**
     * Get priority label.
     * 
     * Usage: $budgetCategory->priority_label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            1 => 'Critical',
            2 => 'High',
            3 => 'Medium',
            4 => 'Low',
            5 => 'Optional',
            default => 'Medium'
        };
    }

    // ========================================
    // CALCULATION METHODS
    // ========================================

    /**
     * Calculate and update derived fields.
     */
    public function calculateDerivedFields(): void
    {
        // Calculate remaining amount
        $this->remaining_amount = $this->allocated_amount - $this->spent_amount;
        
        // Calculate usage percentage
        if ($this->allocated_amount > 0) {
            $this->usage_percentage = ($this->spent_amount / $this->allocated_amount) * 100;
        } else {
            $this->usage_percentage = 0;
        }
        
        // Update calculation timestamp
        $this->last_calculated_at = now();
    }

    /**
     * Recalculate spent amount from actual transactions.
     */
    public function recalculateSpentAmount(): void
    {
        // Get all expense transactions in this category for the budget period
        $spentAmount = Transaction::where('category_id', $this->category_id)
            ->where('user_id', $this->budget->user_id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [
                $this->budget->start_date,
                $this->budget->end_date
            ])
            ->sum('amount');

        $this->spent_amount = $spentAmount;
        $this->calculateDerivedFields();
        $this->save();
    }

    /**
     * Calculate previous period spending for comparison.
     */
    public function calculatePreviousPeriodSpending(): void
    {
        // Calculate previous period dates
        $periodLength = $this->budget->start_date->diffInDays($this->budget->end_date) + 1;
        $previousStart = $this->budget->start_date->copy()->subDays($periodLength);
        $previousEnd = $this->budget->start_date->copy()->subDay();

        // Get spending from previous period
        $previousSpent = Transaction::where('category_id', $this->category_id)
            ->where('user_id', $this->budget->user_id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$previousStart, $previousEnd])
            ->sum('amount');

        $this->previous_period_spent = $previousSpent;
        $this->save();
    }

    /**
     * Adjust allocation amount.
     */
    public function adjustAllocation(float $newAmount, string $reason = null): void
    {
        $oldAmount = $this->allocated_amount;
        $this->allocated_amount = $newAmount;
        
        // Recalculate derived fields
        $this->calculateDerivedFields();
        
        // Add note about the adjustment
        if ($reason) {
            $currentNotes = $this->notes ?? '';
            $adjustmentNote = sprintf(
                "[%s] Adjusted from $%.2f to $%.2f: %s",
                now()->format('Y-m-d H:i'),
                $oldAmount,
                $newAmount,
                $reason
            );
            
            $this->notes = $currentNotes ? $currentNotes . "\n" . $adjustmentNote : $adjustmentNote;
        }
        
        $this->save();
        
        // Update budget totals
        $this->budget->updatePlannedTotals();
    }

    /**
     * Transfer unused amount to another category.
     */
    public function transferUnusedTo(BudgetCategory $targetCategory, float $amount = null): float
    {
        // Use remaining amount if no specific amount provided
        $transferAmount = $amount ?? max(0, $this->remaining_amount);
        
        // Can't transfer more than remaining amount
        $transferAmount = min($transferAmount, $this->remaining_amount);
        
        if ($transferAmount <= 0) {
            return 0;
        }
        
        // Adjust allocations
        $this->adjustAllocation(
            $this->allocated_amount - $transferAmount,
            "Transferred $transferAmount to {$targetCategory->category->name}"
        );
        
        $targetCategory->adjustAllocation(
            $targetCategory->allocated_amount + $transferAmount,
            "Received $transferAmount from {$this->category->name}"
        );
        
        return $transferAmount;
    }

    // ========================================
    // ALERT METHODS
    // ========================================

    /**
     * Check if this allocation should trigger an alert.
     */
    public function shouldAlert(): bool
    {
        if (!$this->alert_on_overspend) {
            return false;
        }
        
        // Alert if over budget
        if ($this->is_over_budget) {
            return true;
        }
        
        // Alert if at threshold
        if ($this->is_at_threshold) {
            return true;
        }
        
        return false;
    }

    /**
     * Get alert message for this allocation.
     */
    public function getAlertMessage(): ?string
    {
        if (!$this->shouldAlert()) {
            return null;
        }
        
        $categoryName = $this->category->name;
        $budgetName = $this->budget->name;
        
        if ($this->is_over_budget) {
            $overAmount = $this->spent_amount - $this->allocated_amount;
            return "'{$categoryName}' in '{$budgetName}' is over budget by $" . number_format($overAmount, 2);
        }
        
        if ($this->is_at_threshold) {
            $threshold = $this->alert_threshold ?? $this->budget->alert_percentage ?? 80;
            return "'{$categoryName}' in '{$budgetName}' has reached {$threshold}% of budget";
        }
        
        return null;
    }

    /**
     * Get alert type for styling purposes.
     */
    public function getAlertType(): ?string
    {
        if (!$this->shouldAlert()) {
            return null;
        }
        
        if ($this->is_over_budget) {
            return 'error';
        }
        
        if ($this->is_at_threshold) {
            return 'warning';
        }
        
        return 'info';
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get spending projection for end of budget period.
     */
    public function getSpendingProjection(): array
    {
        $daysPassed = max(1, $this->budget->start_date->diffInDays(now()));
        $totalDays = $this->budget->start_date->diffInDays($this->budget->end_date) + 1;
        $remainingDays = max(0, $totalDays - $daysPassed);
        
        $currentDailyRate = $this->current_daily_rate;
        $projectedTotal = $this->spent_amount + ($currentDailyRate * $remainingDays);
        
        return [
            'current_daily_rate' => $currentDailyRate,
            'projected_total' => $projectedTotal,
            'projected_variance' => $projectedTotal - $this->allocated_amount,
            'days_remaining' => $remainingDays,
            'recommended_daily_rate' => $this->daily_budget_rate,
        ];
    }

    /**
     * Get performance summary for this allocation.
     */
    public function getPerformanceSummary(): array
    {
        return [
            'category' => $this->category->name,
            'allocated' => $this->allocated_amount,
            'spent' => $this->spent_amount,
            'remaining' => $this->remaining_amount,
            'usage_percentage' => $this->usage_percentage,
            'variance' => $this->variance,
            'is_over_budget' => $this->is_over_budget,
            'is_at_threshold' => $this->is_at_threshold,
            'priority' => $this->priority_label,
            'previous_period_comparison' => [
                'previous_spent' => $this->previous_period_spent,
                'variance' => $this->previous_period_variance,
                'change_percentage' => $this->previous_period_change_percentage,
            ],
            'projection' => $this->getSpendingProjection(),
        ];
    }
}