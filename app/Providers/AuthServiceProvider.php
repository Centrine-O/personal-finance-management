<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Authentication Service Provider
 * 
 * This provider configures authentication-related services for our personal finance app.
 * It handles permissions, policies, and custom authentication behavior.
 * 
 * Key Responsibilities:
 * - Define user permissions and policies
 * - Customize email verification and password reset emails
 * - Set up authentication guards and drivers
 * - Configure security policies for financial data
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     * 
     * Policies define who can perform what actions on which models.
     * This is crucial for financial data security!
     * 
     * Format: Model::class => Policy::class
     */
    protected $policies = [
        // User::class => UserPolicy::class,
        // Account::class => AccountPolicy::class,
        // Transaction::class => TransactionPolicy::class,
        // Budget::class => BudgetPolicy::class,
        // Goal::class => GoalPolicy::class,
        // Bill::class => BillPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     * 
     * This method runs when the application boots up.
     * We use it to define custom permissions and authentication behavior.
     */
    public function boot(): void
    {
        // Register all policies defined above
        $this->registerPolicies();

        // ========================================
        // CUSTOM GATES (PERMISSIONS)
        // ========================================
        
        /**
         * Gate to check if user can access financial data
         * 
         * This is a global permission check for accessing any financial information.
         * Users must have verified email and active account status.
         */
        Gate::define('access-financial-data', function (User $user) {
            // User must have verified email
            if (!$user->hasVerifiedEmail()) {
                return false;
            }
            
            // User account must be active
            if ($user->status !== 'active') {
                return false;
            }
            
            // Account must not be locked
            if ($user->isLocked()) {
                return false;
            }
            
            return true;
        });

        /**
         * Gate to check if user can manage account settings
         * 
         * This controls access to sensitive account management features.
         */
        Gate::define('manage-account-settings', function (User $user) {
            // Must have basic financial data access
            if (!Gate::forUser($user)->allows('access-financial-data')) {
                return false;
            }
            
            // Additional security: check if recent login (within last 30 minutes)
            if ($user->last_login_at && $user->last_login_at->lt(now()->subMinutes(30))) {
                return true;
            }
            
            return false;
        });

        /**
         * Gate to check if user can export financial data
         * 
         * Exporting financial data is sensitive - add extra security checks.
         */
        Gate::define('export-financial-data', function (User $user) {
            // Must have basic access
            if (!Gate::forUser($user)->allows('access-financial-data')) {
                return false;
            }
            
            // Must have recent login (within last 15 minutes for exports)
            if ($user->last_login_at && $user->last_login_at->gte(now()->subMinutes(15))) {
                return true;
            }
            
            return false;
        });

        /**
         * Gate for administrative functions (future feature)
         * 
         * For when we add admin users who can help with support.
         */
        Gate::define('admin-access', function (User $user) {
            // For now, no admin users - but structure is ready
            return false;
            
            // Future implementation might check:
            // return $user->hasRole('admin') && $user->two_factor_enabled;
        });

        // ========================================
        // CUSTOMIZE AUTHENTICATION EMAILS
        // ========================================

        /**
         * Customize the email verification notification
         * 
         * This changes the email users receive when they need to verify their email address.
         * We customize it to match our personal finance app branding.
         */
        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Verify Your Personal Finance Account')
                ->greeting('Welcome to Personal Finance Manager!')
                ->line('Please click the button below to verify your email address and start managing your finances.')
                ->action('Verify Email Address', $url)
                ->line('If you did not create an account, no further action is required.')
                ->line('This verification link will expire in 60 minutes for security.')
                ->salutation('Best regards, The Personal Finance Team');
        });

        /**
         * Customize the password reset notification
         * 
         * This changes the email users receive when they request a password reset.
         * We make it clear this is for their financial account security.
         */
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Reset Your Personal Finance Account Password')
                ->greeting('Hello ' . $notifiable->first_name . '!')
                ->line('You are receiving this email because we received a password reset request for your account.')
                ->line('Your financial data security is our priority. Please reset your password promptly.')
                ->action('Reset Password', $url)
                ->line('This password reset link will expire in 60 minutes.')
                ->line('If you did not request a password reset, please contact our support team immediately.')
                ->salutation('Stay secure, The Personal Finance Team');
        });

        // ========================================
        // AUTHENTICATION EVENTS
        // ========================================

        /**
         * Listen for successful login attempts
         * 
         * When a user successfully logs in, we update their login tracking
         * and reset failed attempt counters.
         */
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                $user = $event->user;
                
                // Reset failed login attempts on successful login
                $user->resetFailedLoginAttempts();
                
                // Log successful login for security audit
                \Illuminate\Support\Facades\Log::info('User logged in', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        );

        /**
         * Listen for failed login attempts
         * 
         * When a login fails, we track it for security purposes
         * and potentially lock the account.
         */
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Failed::class,
            function ($event) {
                // If we have a user (meaning email was valid but password wrong)
                if ($event->user) {
                    $event->user->incrementFailedLoginAttempts();
                }
                
                // Log failed login attempt for security monitoring
                \Illuminate\Support\Facades\Log::warning('Failed login attempt', [
                    'email' => $event->credentials['email'] ?? 'unknown',
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        );

        /**
         * Listen for user registration events
         * 
         * When a new user registers, we can set up their default financial data.
         */
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Registered::class,
            function ($event) {
                $user = $event->user;
                
                // Create default categories for the new user
                $this->createDefaultCategoriesForUser($user);
                
                // Create welcome transactions/examples (optional)
                // $this->createWelcomeDataForUser($user);
                
                // Log user registration
                \Illuminate\Support\Facades\Log::info('New user registered', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => request()->ip(),
                ]);
            }
        );

        /**
         * Listen for email verification events
         * 
         * When a user verifies their email, we can unlock full features.
         */
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Verified::class,
            function ($event) {
                $user = $event->user;
                
                // Log email verification
                \Illuminate\Support\Facades\Log::info('User verified email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
                
                // Send welcome email with getting started guide
                // $user->notify(new WelcomeNotification());
            }
        );
    }

    /**
     * Create default categories for a new user
     * 
     * When someone signs up, we give them a starter set of categories
     * so they can immediately start tracking their finances.
     */
    private function createDefaultCategoriesForUser(User $user): void
    {
        $defaultCategories = [
            // Income categories
            ['name' => 'Salary', 'type' => 'income', 'icon' => 'currency-dollar', 'color' => '#10B981'],
            ['name' => 'Freelance', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#3B82F6'],
            ['name' => 'Investment Income', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#8B5CF6'],
            
            // Essential expense categories
            ['name' => 'Housing', 'type' => 'expense', 'icon' => 'home', 'color' => '#EF4444'],
            ['name' => 'Transportation', 'type' => 'expense', 'icon' => 'truck', 'color' => '#F59E0B'],
            ['name' => 'Food & Dining', 'type' => 'expense', 'icon' => 'utensils', 'color' => '#84CC16'],
            ['name' => 'Utilities', 'type' => 'expense', 'icon' => 'lightning-bolt', 'color' => '#06B6D4'],
            ['name' => 'Healthcare', 'type' => 'expense', 'icon' => 'heart', 'color' => '#EC4899'],
            ['name' => 'Insurance', 'type' => 'expense', 'icon' => 'shield-check', 'color' => '#6366F1'],
            ['name' => 'Entertainment', 'type' => 'expense', 'icon' => 'film', 'color' => '#A855F7'],
            ['name' => 'Shopping', 'type' => 'expense', 'icon' => 'shopping-bag', 'color' => '#F43F5E'],
            ['name' => 'Savings', 'type' => 'expense', 'icon' => 'savings', 'color' => '#059669'],
            
            // Transfer category (special)
            ['name' => 'Transfer', 'type' => 'transfer', 'icon' => 'arrow-right', 'color' => '#6B7280'],
        ];

        foreach ($defaultCategories as $categoryData) {
            \App\Models\Category::create(array_merge($categoryData, [
                'user_id' => $user->id,
                'is_active' => true,
                'is_budgetable' => true,
            ]));
        }
    }

    /**
     * Create welcome data for new users (optional)
     * 
     * This could include sample transactions, a starter budget, or goals
     * to help users understand how the system works.
     */
    private function createWelcomeDataForUser(User $user): void
    {
        // For now, we'll skip this - but you could add:
        // - Sample transactions showing how to track expenses
        // - A starter emergency fund goal
        // - Example budget template
        
        // Example:
        // Goal::createEmergencyFund($user, 3000); // $3k emergency fund
    }

    /**
     * Get authentication guard configurations
     * 
     * This method can be used to dynamically configure authentication guards
     * if we need different authentication methods for different parts of the app.
     */
    public function getAuthGuards(): array
    {
        return [
            // Web guard for browser sessions
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
            
            // API guard for mobile apps and API access
            'api' => [
                'driver' => 'sanctum',
                'provider' => 'users',
                'hash' => false,
            ],
        ];
    }

    /**
     * Check if two-factor authentication should be required
     * 
     * For a financial app, we might want to require 2FA for certain actions
     * or for users with high account balances.
     */
    public function shouldRequireTwoFactor(User $user, string $action = null): bool
    {
        // For now, 2FA is optional
        // But you could implement logic like:
        
        // Always require 2FA for high-value accounts
        $netWorth = $user->calculateNetWorth();
        if ($netWorth > 100000) { // $100k+ net worth
            return true;
        }
        
        // Require 2FA for sensitive actions
        $sensitiveActions = ['export-data', 'delete-account', 'change-email'];
        if (in_array($action, $sensitiveActions)) {
            return true;
        }
        
        return false;
    }
}