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
 * Budget Model
 * 
 * Represents a budget for a specific time period (weekly, monthly, yearly).
 * Budgets help users plan their income and expenses and track performance.
 * 
 * Key Features:
 * - Flexible time periods (weekly, monthly, yearly, custom)
 * - Income and expense planning
 * - Template support for recurring budgets
 * - Performance tracking (planned vs actual)
 * - Approval workflow for shared finances
 * - Rollover settings for unused amounts
 */
class Budget extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'period_type',
        'start_date',
        'end_date',
        'planned_income',
        'actual_income',
        'planned_expenses',
        'actual_expenses',
        'currency',
        'status',
        'is_template',
        'template_name',
        'rollover_unused',
        'deduct_overspent',
        'alert_percentage',
        'weekly_summary',
        'overspend_alerts',
        'created_by',
        'approved_by',
        'approved_at',
        'description',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'planned_income' => 'decimal:2',
        'actual_income' => 'decimal:2',
        'planned_expenses' => 'decimal:2',
        'actual_expenses' => 'decimal:2',
        'is_template' => 'boolean',
        'rollover_unused' => 'boolean',
        'deduct_overspent' => 'boolean',
        'weekly_summary' => 'boolean',
        'overspend_alerts' => 'boolean',
        'alert_percentage' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a budget, set default name if not provided
        static::creating(function ($budget) {
            if (!$budget->name) {
                $budget->name = $budget->generateDefaultName();
            }
        });
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the user who owns this budget.
     * 
     * Usage: $budget->user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created this budget.
     * 
     * Usage: $budget->creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this budget.
     * 
     * Usage: $budget->approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all category allocations for this budget.
     * 
     * Usage: $budget->budgetCategories
     */
    public function budgetCategories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class)
                    ->orderBy('priority')
                    ->orderBy('allocated_amount', 'desc');
    }

    /**
     * Get only expense category allocations.
     * 
     * Usage: $budget->expenseCategories
     */
    public function expenseCategories(): HasMany
    {
        return $this->budgetCategories()
                    ->whereHas('category', function ($query) {
                        $query->where('type', 'expense');
                    });
    }

    /**
     * Get only income category allocations.
     * 
     * Usage: $budget->incomeCategories
     */
    public function incomeCategories(): HasMany
    {
        return $this->budgetCategories()
                    ->whereHas('category', function ($query) {
                        $query->where('type', 'income');
                    });
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope to get active budgets.
     * 
     * Usage: Budget::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get budget templates.
     * 
     * Usage: Budget::templates()->get()
     */
    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    /**
     * Scope to get budgets for a specific period type.
     * 
     * Usage: Budget::ofPeriodType('monthly')->get()
     */
    public function scopeOfPeriodType($query, $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    /**
     * Scope to get current budgets (active during current date).
     * 
     * Usage: Budget::current()->get()
     */
    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        
        return $query->where('status', 'active')
                    ->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Get the budget's planned net income (income - expenses).
     * 
     * Usage: $budget->planned_net_income
     */
    public function getPlannedNetIncomeAttribute(): float
    {
        return $this->planned_income - $this->planned_expenses;
    }

    /**
     * Get the budget's actual net income.
     * 
     * Usage: $budget->actual_net_income
     */
    public function getActualNetIncomeAttribute(): float
    {
        return $this->actual_income - $this->actual_expenses;
    }

    /**
     * Get the budget variance (actual vs planned).
     * 
     * Usage: $budget->net_variance
     */
    public function getNetVarianceAttribute(): float
    {
        return $this->actual_net_income - $this->planned_net_income;
    }

    /**
     * Get the income variance.
     * 
     * Usage: $budget->income_variance
     */
    public function getIncomeVarianceAttribute(): float
    {
        return $this->actual_income - $this->planned_income;
    }

    /**
     * Get the expense variance.
     * 
     * Usage: $budget->expense_variance
     */
    public function getExpenseVarianceAttribute(): float
    {
        return $this->actual_expenses - $this->planned_expenses;
    }

    /**
     * Get budget progress as a percentage.
     * 
     * Usage: $budget->progress_percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        $totalDays = $this->start_date->diffInDays($this->end_date) + 1;
        $daysPassed = $this->start_date->diffInDays(min(now(), $this->end_date)) + 1;
        
        return min(100, ($daysPassed / $totalDays) * 100);
    }

    /**
     * Check if budget is over the alert threshold.
     * 
     * Usage: $budget->is_over_threshold
     */
    public function getIsOverThresholdAttribute(): bool
    {
        if ($this->planned_expenses <= 0) {
            return false;
        }
        
        $percentage = ($this->actual_expenses / $this->planned_expenses) * 100;
        
        return $percentage >= $this->alert_percentage;
    }

    /**
     * Check if budget is overspent.
     * 
     * Usage: $budget->is_overspent
     */
    public function getIsOverspentAttribute(): bool
    {
        return $this->actual_expenses > $this->planned_expenses;
    }

    /**
     * Get remaining budget amount.
     * 
     * Usage: $budget->remaining_budget
     */
    public function getRemainingBudgetAttribute(): float
    {
        return max(0, $this->planned_expenses - $this->actual_expenses);
    }

    /**
     * Get budget duration in days.
     * 
     * Usage: $budget->duration_days
     */
    public function getDurationDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Check if this budget is currently active.
     * 
     * Usage: $budget->is_current
     */
    public function getIsCurrentAttribute(): bool
    {
        $today = now()->toDateString();
        
        return $this->status === 'active' 
            && $this->start_date <= $today 
            && $this->end_date >= $today;
    }

    // ========================================
    // BUDGET OPERATIONS
    // ========================================

    /**
     * Recalculate actual income and expenses from transactions.
     */
    public function recalculateActuals(): void
    {
        // Calculate actual income
        $this->actual_income = $this->user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
            ->sum('amount');

        // Calculate actual expenses  
        $this->actual_expenses = $this->user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
            ->sum('amount');

        $this->save();

        // Recalculate each budget category's spent amount
        $this->budgetCategories->each(function ($budgetCategory) {
            $budgetCategory->recalculateSpentAmount();
        });
    }

    /**
     * Create budget categories from a template or previous budget.
     */
    public function createCategoriesFromTemplate(Budget $templateBudget): void
    {
        foreach ($templateBudget->budgetCategories as $templateCategory) {
            BudgetCategory::create([
                'budget_id' => $this->id,
                'category_id' => $templateCategory->category_id,
                'allocated_amount' => $templateCategory->allocated_amount,
                'priority' => $templateCategory->priority,
                'is_fixed_amount' => $templateCategory->is_fixed_amount,
                'alert_on_overspend' => $templateCategory->alert_on_overspend,
                'alert_threshold' => $templateCategory->alert_threshold,
            ]);
        }

        // Update planned totals
        $this->updatePlannedTotals();
    }

    /**
     * Update planned income and expense totals from category allocations.
     */
    public function updatePlannedTotals(): void
    {
        $this->planned_income = $this->incomeCategories->sum('allocated_amount');
        $this->planned_expenses = $this->expenseCategories->sum('allocated_amount');
        $this->save();
    }

    /**
     * Generate a default budget name based on period.
     */
    protected function generateDefaultName(): string
    {
        switch ($this->period_type) {
            case 'weekly':
                return "Week of " . $this->start_date->format('M j, Y');
            case 'monthly':
                return $this->start_date->format('F Y') . " Budget";
            case 'yearly':
                return $this->start_date->format('Y') . " Annual Budget";
            default:
                return $this->start_date->format('M j') . " - " . $this->end_date->format('M j, Y') . " Budget";
        }
    }

    /**
     * Approve this budget.
     */
    public function approve(User $approver): void
    {
        $this->update([
            'status' => 'active',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Complete this budget (end of period).
     */
    public function complete(): void
    {
        // Final recalculation
        $this->recalculateActuals();
        
        // Mark as completed
        $this->update(['status' => 'completed']);
    }

    /**
     * Create next period's budget from this one.
     */
    public function createNextPeriodBudget(): Budget
    {
        $nextStart = $this->calculateNextPeriodStart();
        $nextEnd = $this->calculateNextPeriodEnd($nextStart);

        $nextBudget = static::create([
            'user_id' => $this->user_id,
            'period_type' => $this->period_type,
            'start_date' => $nextStart,
            'end_date' => $nextEnd,
            'currency' => $this->currency,
            'alert_percentage' => $this->alert_percentage,
            'weekly_summary' => $this->weekly_summary,
            'overspend_alerts' => $this->overspend_alerts,
            'rollover_unused' => $this->rollover_unused,
            'deduct_overspent' => $this->deduct_overspent,
        ]);

        // Copy category allocations
        $nextBudget->createCategoriesFromTemplate($this);

        // Handle rollover amounts if enabled
        if ($this->rollover_unused) {
            $this->handleRollovers($nextBudget);
        }

        return $nextBudget;
    }

    /**
     * Calculate the start date for the next budget period.
     */
    protected function calculateNextPeriodStart(): Carbon
    {
        switch ($this->period_type) {
            case 'weekly':
                return $this->end_date->copy()->addDay();
            case 'monthly':
                return $this->start_date->copy()->addMonth();
            case 'yearly':
                return $this->start_date->copy()->addYear();
            default:
                return $this->end_date->copy()->addDay();
        }
    }

    /**
     * Calculate the end date for the next budget period.
     */
    protected function calculateNextPeriodEnd(Carbon $startDate): Carbon
    {
        switch ($this->period_type) {
            case 'weekly':
                return $startDate->copy()->addDays(6);
            case 'monthly':
                return $startDate->copy()->endOfMonth();
            case 'yearly':
                return $startDate->copy()->endOfYear();
            default:
                return $startDate->copy()->addDays($this->duration_days - 1);
        }
    }

    /**
     * Handle rollover amounts to next budget.
     */
    protected function handleRollovers(Budget $nextBudget): void
    {
        foreach ($this->budgetCategories as $currentCategory) {
            $nextCategory = $nextBudget->budgetCategories()
                                     ->where('category_id', $currentCategory->category_id)
                                     ->first();

            if (!$nextCategory) {
                continue;
            }

            // Calculate unused amount (if any)
            $unusedAmount = max(0, $currentCategory->allocated_amount - $currentCategory->spent_amount);
            
            if ($unusedAmount > 0 && $currentCategory->rollover_unused !== false) {
                $nextCategory->increment('allocated_amount', $unusedAmount);
            }

            // Handle overspent amounts if deduct_overspent is enabled
            if ($this->deduct_overspent) {
                $overspentAmount = max(0, $currentCategory->spent_amount - $currentCategory->allocated_amount);
                
                if ($overspentAmount > 0) {
                    $nextCategory->decrement('allocated_amount', min($nextCategory->allocated_amount, $overspentAmount));
                }
            }
        }

        // Update next budget's planned totals
        $nextBudget->updatePlannedTotals();
    }

    /**
     * Get budget performance summary.
     */
    public function getPerformanceSummary(): array
    {
        return [
            'period' => [
                'start_date' => $this->start_date->toDateString(),
                'end_date' => $this->end_date->toDateString(),
                'duration_days' => $this->duration_days,
                'progress_percentage' => $this->progress_percentage,
            ],
            'income' => [
                'planned' => $this->planned_income,
                'actual' => $this->actual_income,
                'variance' => $this->income_variance,
                'percentage' => $this->planned_income > 0 ? ($this->actual_income / $this->planned_income) * 100 : 0,
            ],
            'expenses' => [
                'planned' => $this->planned_expenses,
                'actual' => $this->actual_expenses,
                'variance' => $this->expense_variance,
                'percentage' => $this->planned_expenses > 0 ? ($this->actual_expenses / $this->planned_expenses) * 100 : 0,
                'remaining' => $this->remaining_budget,
            ],
            'net' => [
                'planned' => $this->planned_net_income,
                'actual' => $this->actual_net_income,
                'variance' => $this->net_variance,
            ],
            'status' => [
                'is_over_threshold' => $this->is_over_threshold,
                'is_overspent' => $this->is_overspent,
                'alert_percentage' => $this->alert_percentage,
            ],
        ];
    }

    /**
     * Get categories that are over budget.
     */
    public function getOverBudgetCategories(): Collection
    {
        return $this->budgetCategories()
                    ->whereRaw('spent_amount > allocated_amount')
                    ->with('category')
                    ->get();
    }

    /**
     * Get categories that are near their budget limit.
     */
    public function getNearLimitCategories(int $threshold = 80): Collection
    {
        return $this->budgetCategories()
                    ->whereRaw('(spent_amount / allocated_amount) * 100 >= ?', [$threshold])
                    ->whereRaw('spent_amount <= allocated_amount')
                    ->with('category')
                    ->get();
    }
}