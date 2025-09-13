<?php

namespace App\Models;

// Import necessary Laravel classes
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 * 
 * Represents a user account in our personal finance system.
 * This model handles authentication, user preferences, and relationships
 * to all financial data.
 * 
 * Key Features:
 * - Authentication (login, password, email verification)
 * - Financial preferences (currency, timezone)
 * - Relationships to all financial entities
 * - Security features (2FA, failed login tracking)
 * - Data formatting and calculations
 */
class User extends Authenticatable implements MustVerifyEmail
{
    // Laravel traits for additional functionality
    use HasApiTokens;        // For API authentication tokens
    use HasFactory;          // For generating test data
    use Notifiable;          // For sending notifications (emails, SMS)
    use SoftDeletes;         // For soft delete functionality

    /**
     * The attributes that are mass assignable.
     * 
     * Mass assignment is when you pass an array to create() or update().
     * Only these fields can be set this way for security.
     * 
     * Example: User::create($request->only(['first_name', 'last_name', 'email']))
     */
    protected $fillable = [
        // Basic user information
        'first_name',
        'last_name',
        'email',
        'password',
        
        // Profile information
        'avatar',
        'phone',
        'date_of_birth',
        
        // Personal finance preferences
        'preferred_currency',
        'timezone',
        'locale',
        'monthly_income',
        
        // Notification settings
        'budget_alerts_enabled',
        'bill_reminders_enabled',
        'bill_reminder_days',
        'low_balance_threshold',
        
        // Account status
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * 
     * These fields won't be included when the model is converted to JSON
     * (like in API responses). Important for security!
     */
    protected $hidden = [
        'password',                    // Never expose password hashes
        'remember_token',             // Session security token
        'two_factor_backup_codes',    // 2FA backup codes
    ];

    /**
     * The attributes that should be cast.
     * 
     * Casting automatically converts database values to PHP types.
     * This makes working with data much easier!
     */
    protected $casts = [
        // Convert string timestamps to Carbon date objects
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'date_of_birth' => 'date',
        
        // Convert string to boolean
        'budget_alerts_enabled' => 'boolean',
        'bill_reminders_enabled' => 'boolean',
        'two_factor_enabled' => 'boolean',
        
        // Convert JSON strings to PHP arrays
        'two_factor_backup_codes' => 'array',
        
        // Convert string numbers to floats
        'monthly_income' => 'decimal:2',
        'low_balance_threshold' => 'decimal:2',
        
        // Convert string numbers to integers
        'failed_login_attempts' => 'integer',
        'bill_reminder_days' => 'integer',
    ];

    /**
     * Get the user's full name.
     * 
     * Accessor methods start with "get" and end with "Attribute".
     * This creates a virtual attribute called "full_name".
     * 
     * Usage: $user->full_name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's initials.
     * 
     * Useful for avatar placeholders when no image is uploaded.
     * 
     * Usage: $user->initials
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * Get the user's avatar URL.
     * 
     * Returns either the uploaded avatar or a default placeholder.
     * 
     * Usage: $user->avatar_url
     */
    public function getAvatarUrlAttribute(): string
    {
        // If user has uploaded an avatar, return the storage URL
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Otherwise, return a default avatar (using UI Avatars service)
        return "https://ui-avatars.com/api/?name={$this->full_name}&background=6366f1&color=white&size=100";
    }

    /**
     * Check if the user's account is locked due to too many failed login attempts.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Lock the user's account for a specified number of minutes.
     * 
     * This is called when there are too many failed login attempts.
     */
    public function lockAccount(int $minutes = 15): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * Unlock the user's account and reset failed login attempts.
     */
    public function unlockAccount(): void
    {
        $this->update([
            'locked_until' => null,
            'failed_login_attempts' => 0,
        ]);
    }

    /**
     * Increment the failed login attempts counter.
     * 
     * If too many attempts, lock the account.
     */
    public function incrementFailedLoginAttempts(): void
    {
        $attempts = $this->failed_login_attempts + 1;
        
        // If 5 or more failed attempts, lock the account
        if ($attempts >= 5) {
            $this->lockAccount();
        } else {
            $this->update(['failed_login_attempts' => $attempts]);
        }
    }

    /**
     * Reset failed login attempts (called on successful login).
     */
    public function resetFailedLoginAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    /**
     * Get all accounts belonging to this user.
     * 
     * One user can have many accounts (checking, savings, credit cards, etc.)
     * This is a "One-to-Many" relationship.
     * 
     * Usage: $user->accounts
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get only active accounts.
     * 
     * Usage: $user->activeAccounts
     */
    public function activeAccounts(): HasMany
    {
        return $this->accounts()->where('is_active', true);
    }

    /**
     * Get all transactions for this user.
     * 
     * Usage: $user->transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all categories created by this user.
     * 
     * Note: Users also have access to system categories (where user_id is null)
     * 
     * Usage: $user->categories
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get all budgets for this user.
     * 
     * Usage: $user->budgets
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get the currently active budget.
     * 
     * Usage: $user->activeBudget
     */
    public function activeBudget()
    {
        return $this->budgets()
                    ->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->first();
    }

    /**
     * Get all financial goals for this user.
     * 
     * Usage: $user->goals
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Get all bills for this user.
     * 
     * Usage: $user->bills
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get all recurring transactions for this user.
     * 
     * Usage: $user->recurringTransactions
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    // ========================================
    // FINANCIAL CALCULATIONS
    // ========================================

    /**
     * Calculate the user's total net worth.
     * 
     * Net worth = Assets - Liabilities
     * Assets: Checking, Savings, Investment accounts
     * Liabilities: Credit cards, loans
     */
    public function calculateNetWorth(): float
    {
        return $this->activeAccounts->sum(function ($account) {
            // For asset accounts, add the balance
            if (in_array($account->type, ['checking', 'savings', 'investment', 'cash'])) {
                return $account->balance;
            }
            
            // For liability accounts, subtract the balance (debt)
            if (in_array($account->type, ['credit', 'loan'])) {
                return -$account->balance;
            }
            
            return 0;
        });
    }

    /**
     * Get total income for a specific month.
     */
    public function getMonthlyIncome(Carbon $month): float
    {
        return $this->transactions()
                    ->where('type', 'income')
                    ->whereYear('transaction_date', $month->year)
                    ->whereMonth('transaction_date', $month->month)
                    ->sum('amount');
    }

    /**
     * Get total expenses for a specific month.
     */
    public function getMonthlyExpenses(Carbon $month): float
    {
        return $this->transactions()
                    ->where('type', 'expense')
                    ->whereYear('transaction_date', $month->year)
                    ->whereMonth('transaction_date', $month->month)
                    ->sum('amount');
    }

    /**
     * Check if user is within their budget for the current month.
     */
    public function isWithinBudget(): bool
    {
        $activeBudget = $this->activeBudget();
        
        if (!$activeBudget) {
            return true; // No budget means no limit
        }

        $currentExpenses = $this->getMonthlyExpenses(now());
        
        return $currentExpenses <= $activeBudget->planned_expenses;
    }

    /**
     * Get accounts that have low balances (below threshold).
     */
    public function getLowBalanceAccounts()
    {
        return $this->activeAccounts()
                    ->where('balance', '<', $this->low_balance_threshold)
                    ->where('type', '!=', 'credit') // Don't alert for credit cards
                    ->get();
    }

    // ========================================
    // NOTIFICATION METHODS
    // ========================================

    /**
     * Send email verification notification using our custom template.
     * 
     * This method overrides Laravel's default email verification notification
     * to use our custom template that's specifically designed for financial
     * applications with enhanced security messaging.
     * 
     * Called automatically when:
     * - User registers and needs email verification
     * - User requests new verification email
     * - Email address is changed and needs re-verification
     */
    public function sendEmailVerificationNotification(): void
    {
        // Use our custom email verification notification class
        // instead of Laravel's default one
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }

    /**
     * Send password reset notification using our custom template.
     * 
     * This method overrides Laravel's default password reset notification
     * to use our custom template with enhanced security warnings and
     * professional design appropriate for financial applications.
     * 
     * @param string $token The password reset token
     */
    public function sendPasswordResetNotification($token): void
    {
        // Use our custom password reset notification class
        // with enhanced security messaging for financial apps
        $this->notify(new \App\Notifications\CustomResetPassword($token));
    }

    /**
     * Send a security alert notification.
     * 
     * This method sends notifications for various security events like:
     * - Suspicious login attempts
     * - Password changes
     * - Email address changes
     * - Account lockouts
     * 
     * @param string $event The security event type
     * @param array $context Additional context data
     */
    public function sendSecurityAlert(string $event, array $context = []): void
    {
        // Log the security event for audit purposes
        \Illuminate\Support\Facades\Log::info('Security alert sent', [
            'user_id' => $this->id,
            'event' => $event,
            'context' => $context,
            'timestamp' => now(),
        ]);

        // In a full implementation, you would create a SecurityAlert notification
        // For now, we'll just log it. Example usage:
        // $this->notify(new \App\Notifications\SecurityAlert($event, $context));
    }

    /**
     * Send a financial alert notification.
     * 
     * This method handles financial notifications like:
     * - Budget overspending alerts
     * - Low account balance warnings
     * - Bill due reminders
     * - Goal milestone achievements
     * 
     * @param string $type The alert type
     * @param array $data Alert-specific data
     */
    public function sendFinancialAlert(string $type, array $data = []): void
    {
        // Only send alerts if user has notifications enabled
        if (!$this->budget_alerts_enabled && $type === 'budget') {
            return;
        }

        if (!$this->bill_reminders_enabled && $type === 'bill') {
            return;
        }

        // Log the financial alert
        \Illuminate\Support\Facades\Log::info('Financial alert sent', [
            'user_id' => $this->id,
            'alert_type' => $type,
            'data' => $data,
            'timestamp' => now(),
        ]);

        // In a full implementation, you would create specific notification classes
        // Example: $this->notify(new \App\Notifications\BudgetAlert($data));
    }

    /**
     * Get the user's notification preferences.
     * 
     * Returns an array of the user's notification settings for easy checking
     * before sending any notifications.
     * 
     * @return array
     */
    public function getNotificationPreferences(): array
    {
        return [
            'budget_alerts' => $this->budget_alerts_enabled,
            'bill_reminders' => $this->bill_reminders_enabled,
            'bill_reminder_days' => $this->bill_reminder_days,
            'marketing_emails' => $this->marketing_emails,
            'email_verified' => $this->hasVerifiedEmail(),
        ];
    }

    /**
     * Check if user should receive a specific type of notification.
     * 
     * This method checks user preferences and account status to determine
     * if a notification should be sent.
     * 
     * @param string $type The notification type
     * @return bool
     */
    public function shouldReceiveNotification(string $type): bool
    {
        // Don't send notifications to inactive or suspended accounts
        if ($this->status !== 'active') {
            return false;
        }

        // Check specific notification preferences
        switch ($type) {
            case 'budget':
                return $this->budget_alerts_enabled;
            
            case 'bill':
                return $this->bill_reminders_enabled;
            
            case 'marketing':
                return $this->marketing_emails;
            
            case 'security':
                return true; // Always send security notifications
            
            case 'account':
                return true; // Always send account-related notifications
            
            default:
                return false;
        }
    }
}