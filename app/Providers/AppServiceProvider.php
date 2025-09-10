<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;

/**
 * Main Application Service Provider
 * 
 * This is where we configure our application's core services.
 * Service Providers are the central place to configure your application.
 * Think of this as the "configuration hub" for our app.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * 
     * This method is called early in the application lifecycle.
     * Use this to bind things into the service container.
     * The container is Laravel's dependency injection system.
     */
    public function register(): void
    {
        // Register custom services here
        
        // Example: Register a custom finance calculation service
        // $this->app->singleton('finance.calculator', function ($app) {
        //     return new \App\Services\FinanceCalculatorService();
        // });
        
        // In development, we might want additional debugging tools
        if ($this->app->environment('local')) {
            // Register development-only services
            // Example: Laravel Telescope for debugging
            if (class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
                $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
                $this->app->register(\App\Providers\TelescopeServiceProvider::class);
            }
        }
    }

    /**
     * Bootstrap any application services.
     * 
     * This method is called after all providers are registered.
     * Use this to perform actions that depend on other services.
     */
    public function boot(): void
    {
        // Database Configuration
        // Set default string length for MySQL compatibility
        // This prevents issues with older MySQL versions
        Schema::defaultStringLength(191);
        
        // Model Configuration for better development experience
        if ($this->app->environment('local')) {
            // Prevent lazy loading (helps catch N+1 query problems early)
            // This will throw an exception if you accidentally trigger lazy loading
            Model::preventLazyLoading();
            
            // Prevent accessing missing attributes (catches typos in model attributes)
            Model::preventAccessingMissingAttributes();
            
            // Prevent silently discarding fillable attributes
            // This helps catch when you're trying to mass-assign non-fillable attributes
            Model::preventSilentlyDiscardingAttributes();
        }
        
        // Asset Configuration
        // Configure Vite for frontend asset compilation
        Vite::prefetch(concurrency: 3);
        
        // Global View Data
        // Share common data with all views
        view()->share([
            'appName' => config('app.name'),
            'appVersion' => config('app.version', '1.0.0'),
        ]);
        
        // Custom Validation Rules
        // We'll add custom validation rules for financial data
        $this->registerCustomValidationRules();
        
        // Custom Blade Directives
        // Add helpful directives for our views
        $this->registerBladeDirectives();
    }
    
    /**
     * Register custom validation rules specific to personal finance
     * 
     * These rules help validate financial data properly
     */
    private function registerCustomValidationRules(): void
    {
        // Validation rule for currency amounts
        // Ensures amounts are valid monetary values (positive, max 2 decimal places)
        \Illuminate\Support\Facades\Validator::extend('currency', function ($attribute, $value, $parameters, $validator) {
            // Check if value is numeric and has max 2 decimal places
            if (!is_numeric($value)) {
                return false;
            }
            
            // Convert to string and check decimal places
            $parts = explode('.', (string) $value);
            if (count($parts) > 2) {
                return false; // More than one decimal point
            }
            
            if (count($parts) === 2 && strlen($parts[1]) > 2) {
                return false; // More than 2 decimal places
            }
            
            // Check if positive (financial amounts should be positive)
            return $value >= 0;
        });
        
        // Validation rule for budget periods
        // Ensures budget period is one of: weekly, monthly, yearly
        \Illuminate\Support\Facades\Validator::extend('budget_period', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, ['weekly', 'monthly', 'yearly']);
        });
        
        // Validation rule for account types
        // Ensures account type is valid for our system
        \Illuminate\Support\Facades\Validator::extend('account_type', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, ['checking', 'savings', 'credit', 'investment', 'loan']);
        });
    }
    
    /**
     * Register custom Blade directives for our views
     * 
     * These make it easier to work with financial data in our templates
     */
    private function registerBladeDirectives(): void
    {
        // Blade directive to format currency
        // Usage in views: @currency($amount)
        \Illuminate\Support\Facades\Blade::directive('currency', function ($expression) {
            return "<?php echo '$' . number_format($expression, 2); ?>";
        });
        
        // Blade directive to format percentages
        // Usage in views: @percentage($rate)
        \Illuminate\Support\Facades\Blade::directive('percentage', function ($expression) {
            return "<?php echo number_format($expression * 100, 2) . '%'; ?>";
        });
        
        // Blade directive for financial status badges
        // Usage in views: @financialStatus($status)
        \Illuminate\Support\Facades\Blade::directive('financialStatus', function ($expression) {
            return "<?php 
                \$status = $expression;
                \$class = match(\$status) {
                    'on_track' => 'bg-green-100 text-green-800',
                    'at_risk' => 'bg-yellow-100 text-yellow-800',
                    'over_budget' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };
                echo '<span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . \$class . '\">' . ucwords(str_replace('_', ' ', \$status)) . '</span>';
            ?>";
        });
    }
}