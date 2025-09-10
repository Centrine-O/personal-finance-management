<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Goal Model
 * 
 * Represents savings goals and financial objectives.
 * Examples: Emergency fund, vacation, house down payment, retirement
 * 
 * Key Features:
 * - Target amount and target date tracking
 * - Progress calculation and visualization
 * - Automatic contribution scheduling
 * - Milestone notifications
 * - Linked to specific accounts (where goal money is saved)
 * - Priority-based goal management
 */
class Goal extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'account_id',
        'name',
        'description',
        'type',
        'target_amount',
        'current_amount',
        'target_date',
        'start_date',
        'required_monthly_contribution',
        'required_weekly_contribution',
        'progress_percentage',
        'priority',
        'status',
        'color',
        'icon',
        'auto_contribute',
        'auto_contribute_amount',
        'auto_contribute_frequency',
        'auto_contribute_day',
        'progress_notifications',
        'reminder_notifications',
        'milestone_notifications',
        'milestones_reached',
        'completed_at',
        'excess_amount',
        'notes',
        'motivation_note',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'required_monthly_contribution' => 'decimal:2',
        'required_weekly_contribution' => 'decimal:2',
        'auto_contribute_amount' => 'decimal:2',
        'excess_amount' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'priority' => 'integer',
        'auto_contribute_day' => 'integer',
        'target_date' => 'date',
        'start_date' => 'date',
        'completed_at' => 'datetime',
        'auto_contribute' => 'boolean',
        'progress_notifications' => 'boolean',
        'reminder_notifications' => 'boolean',
        'milestone_notifications' => 'array',
        'milestones_reached' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When creating or updating, recalculate progress and requirements
        static::saving(function ($goal) {
            $goal->calculateProgress();
            $goal->calculateRequiredContributions();
        });
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the user who owns this goal.
     * 
     * Usage: $goal->user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account where goal funds are saved.
     * 
     * Usage: $goal->account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // ========================================
    // QUERY SCOPES
    // ========================================

    /**
     * Scope to get active goals.
     * 
     * Usage: Goal::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get completed goals.
     * 
     * Usage: Goal::completed()->get()
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get goals by type.
     * 
     * Usage: Goal::ofType('emergency_fund')->get()
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get goals by priority.
     * 
     * Usage: Goal::byPriority(1)->get()
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get goals due soon.
     * 
     * Usage: Goal::dueSoon(30)->get() // Due within 30 days
     */
    public function scopeDueSoon($query, $days = 30)
    {
        return $query->where('target_date', '<=', now()->addDays($days))
                    ->where('status', 'active');
    }

    /**
     * Scope to get goals with auto-contribution enabled.
     * 
     * Usage: Goal::autoContributing()->get()
     */
    public function scopeAutoContributing($query)
    {
        return $query->where('auto_contribute', true)
                    ->where('status', 'active');
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Get the amount needed to reach the goal.
     * 
     * Usage: $goal->amount_needed
     */
    public function getAmountNeededAttribute(): float
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * Get days remaining until target date.
     * 
     * Usage: $goal->days_remaining
     */
    public function getDaysRemainingAttribute(): int
    {
        return max(0, now()->diffInDays($this->target_date, false));
    }

    /**
     * Get weeks remaining until target date.
     * 
     * Usage: $goal->weeks_remaining
     */
    public function getWeeksRemainingAttribute(): int
    {
        return max(0, (int) ceil($this->days_remaining / 7));
    }

    /**
     * Get months remaining until target date.
     * 
     * Usage: $goal->months_remaining
     */
    public function getMonthsRemainingAttribute(): int
    {
        return max(0, now()->diffInMonths($this->target_date, false));
    }

    /**
     * Check if goal is overdue.
     * 
     * Usage: $goal->is_overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->target_date < now() && $this->status === 'active';
    }

    /**
     * Check if goal is completed.
     * 
     * Usage: $goal->is_completed
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }

    /**
     * Check if goal is on track based on time elapsed.
     * 
     * Usage: $goal->is_on_track
     */
    public function getIsOnTrackAttribute(): bool
    {
        $totalDays = $this->start_date->diffInDays($this->target_date);
        $daysPassed = $this->start_date->diffInDays(now());
        
        if ($totalDays <= 0) {
            return true;
        }
        
        $expectedProgress = min(100, ($daysPassed / $totalDays) * 100);
        
        // Allow 10% variance
        return $this->progress_percentage >= ($expectedProgress - 10);
    }

    /**
     * Get priority label.
     * 
     * Usage: $goal->priority_label
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

    /**
     * Get goal type label.
     * 
     * Usage: $goal->type_label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'emergency_fund' => 'Emergency Fund',
            'vacation' => 'Vacation',
            'house' => 'House',
            'car' => 'Car',
            'retirement' => 'Retirement',
            'education' => 'Education',
            'debt_payoff' => 'Debt Payoff',
            'investment' => 'Investment',
            'other' => 'Other',
            default => 'Other'
        };
    }

    /**
     * Get formatted target amount.
     * 
     * Usage: $goal->formatted_target_amount
     */
    public function getFormattedTargetAmountAttribute(): string
    {
        return '$' . number_format($this->target_amount, 2);
    }

    /**
     * Get formatted current amount.
     * 
     * Usage: $goal->formatted_current_amount
     */
    public function getFormattedCurrentAmountAttribute(): string
    {
        return '$' . number_format($this->current_amount, 2);
    }

    /**
     * Get formatted amount needed.
     * 
     * Usage: $goal->formatted_amount_needed
     */
    public function getFormattedAmountNeededAttribute(): string
    {
        return '$' . number_format($this->amount_needed, 2);
    }

    // ========================================
    // CALCULATION METHODS
    // ========================================

    /**
     * Calculate and update progress percentage.
     */
    public function calculateProgress(): void
    {
        if ($this->target_amount <= 0) {
            $this->progress_percentage = 0;
            return;
        }
        
        $this->progress_percentage = min(100, ($this->current_amount / $this->target_amount) * 100);
    }

    /**
     * Calculate required contributions to meet goal.
     */
    public function calculateRequiredContributions(): void
    {
        $amountNeeded = $this->amount_needed;
        
        if ($amountNeeded <= 0) {
            $this->required_monthly_contribution = 0;
            $this->required_weekly_contribution = 0;
            return;
        }
        
        $monthsRemaining = max(1, $this->months_remaining);
        $weeksRemaining = max(1, $this->weeks_remaining);
        
        $this->required_monthly_contribution = $amountNeeded / $monthsRemaining;
        $this->required_weekly_contribution = $amountNeeded / $weeksRemaining;
    }

    /**
     * Add amount to goal and check for completion.
     */
    public function addAmount(float $amount, string $source = 'manual'): void
    {
        $this->current_amount += $amount;
        $this->calculateProgress();
        
        // Check for milestone notifications
        $this->checkMilestones();
        
        // Check if goal is completed
        if ($this->is_completed && $this->status !== 'completed') {
            $this->completeGoal();
        }
        
        $this->save();
        
        // Log the contribution
        // You could create a separate GoalContribution model to track this
    }

    /**
     * Check and trigger milestone notifications.
     */
    protected function checkMilestones(): void
    {
        $milestones = $this->milestone_notifications ?? [];
        $reached = $this->milestones_reached ?? [];
        
        foreach ($milestones as $milestone) {
            if ($this->progress_percentage >= $milestone && !in_array($milestone, $reached)) {
                $reached[] = $milestone;
                
                // Trigger milestone notification
                // You could dispatch an event here for notification handling
            }
        }
        
        $this->milestones_reached = $reached;
    }

    /**
     * Complete the goal.
     */
    public function completeGoal(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        
        // Calculate excess amount if any
        if ($this->current_amount > $this->target_amount) {
            $this->excess_amount = $this->current_amount - $this->target_amount;
        }
        
        $this->save();
        
        // Trigger completion notification
        // You could dispatch an event here
    }

    /**
     * Reset goal to start over.
     */
    public function resetGoal(): void
    {
        $this->current_amount = 0;
        $this->progress_percentage = 0;
        $this->status = 'active';
        $this->completed_at = null;
        $this->excess_amount = 0;
        $this->milestones_reached = [];
        
        $this->calculateRequiredContributions();
        $this->save();
    }

    /**
     * Pause the goal.
     */
    public function pauseGoal(string $reason = null): void
    {
        $this->status = 'paused';
        
        if ($reason) {
            $this->notes = ($this->notes ?? '') . "\n[" . now()->format('Y-m-d') . "] Paused: " . $reason;
        }
        
        $this->save();
    }

    /**
     * Resume a paused goal.
     */
    public function resumeGoal(): void
    {
        $this->status = 'active';
        $this->calculateRequiredContributions();
        $this->save();
    }

    /**
     * Update target date and recalculate contributions.
     */
    public function updateTargetDate(Carbon $newDate): void
    {
        $this->target_date = $newDate;
        $this->calculateRequiredContributions();
        $this->save();
    }

    /**
     * Update target amount and recalculate contributions.
     */
    public function updateTargetAmount(float $newAmount): void
    {
        $this->target_amount = $newAmount;
        $this->calculateProgress();
        $this->calculateRequiredContributions();
        $this->save();
    }

    // ========================================
    // AUTO-CONTRIBUTION METHODS
    // ========================================

    /**
     * Process automatic contribution.
     */
    public function processAutoContribution(): bool
    {
        if (!$this->auto_contribute || !$this->auto_contribute_amount || $this->status !== 'active') {
            return false;
        }
        
        // Check if it's time for contribution based on frequency and day
        if (!$this->isContributionDue()) {
            return false;
        }
        
        try {
            // Create transfer transaction from linked account
            if ($this->account) {
                Transaction::create([
                    'user_id' => $this->user_id,
                    'account_id' => $this->account_id,
                    'category_id' => Category::getSavingsCategory()->id,
                    'description' => "Auto contribution to {$this->name}",
                    'amount' => $this->auto_contribute_amount,
                    'type' => 'expense', // Treating as expense from main account
                    'transaction_date' => now(),
                    'status' => 'cleared',
                    'notes' => "Automatic goal contribution",
                ]);
            }
            
            // Add amount to goal
            $this->addAmount($this->auto_contribute_amount, 'auto');
            
            return true;
        } catch (\Exception $e) {
            // Log error and continue
            return false;
        }
    }

    /**
     * Check if auto-contribution is due today.
     */
    protected function isContributionDue(): bool
    {
        if (!$this->auto_contribute_frequency || !$this->auto_contribute_day) {
            return false;
        }
        
        $today = now();
        
        return match($this->auto_contribute_frequency) {
            'weekly' => $today->dayOfWeek == $this->auto_contribute_day,
            'bi_weekly' => $today->dayOfWeek == $this->auto_contribute_day && $today->weekOfYear % 2 == 0,
            'monthly' => $today->day == $this->auto_contribute_day,
            'quarterly' => $today->day == $this->auto_contribute_day && in_array($today->month, [1, 4, 7, 10]),
            default => false
        };
    }

    // ========================================
    // STATIC HELPER METHODS
    // ========================================

    /**
     * Get goals that need auto-contribution processing.
     */
    public static function getNeedingAutoContribution(): \Illuminate\Database\Eloquent\Collection
    {
        return static::autoContributing()
                    ->get()
                    ->filter(function ($goal) {
                        return $goal->isContributionDue();
                    });
    }

    /**
     * Get user's goal summary.
     */
    public static function getGoalSummary(User $user): array
    {
        $goals = $user->goals()->active()->get();
        
        return [
            'total_goals' => $goals->count(),
            'total_target_amount' => $goals->sum('target_amount'),
            'total_current_amount' => $goals->sum('current_amount'),
            'total_amount_needed' => $goals->sum('amount_needed'),
            'average_progress' => $goals->avg('progress_percentage'),
            'goals_on_track' => $goals->where('is_on_track', true)->count(),
            'overdue_goals' => $goals->where('is_overdue', true)->count(),
            'completed_this_year' => $user->goals()
                                          ->where('status', 'completed')
                                          ->whereYear('completed_at', now()->year)
                                          ->count(),
        ];
    }

    /**
     * Create a standard emergency fund goal.
     */
    public static function createEmergencyFund(User $user, float $monthlyExpenses, int $months = 6): Goal
    {
        $targetAmount = $monthlyExpenses * $months;
        
        return static::create([
            'user_id' => $user->id,
            'name' => 'Emergency Fund',
            'description' => "{$months} months of expenses for financial security",
            'type' => 'emergency_fund',
            'target_amount' => $targetAmount,
            'target_date' => now()->addYear(),
            'start_date' => now(),
            'priority' => 1, // High priority
            'color' => '#EF4444', // Red for urgency
            'icon' => 'shield-check',
            'motivation_note' => 'Financial peace of mind for unexpected expenses',
        ]);
    }
}