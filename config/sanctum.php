<?php

use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    | For our personal finance app, we include domains that should have
    | stateful authentication (like our main web app domain).
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort(),
        env('FRONTEND_URL') ? ','.parse_url(env('FRONTEND_URL'), PHP_URL_HOST) : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request. If none of these guards
    | are able to authenticate the request, Sanctum will use the bearer
    | token that's present on an incoming request for authentication.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens do
    | not expire. This won't tweak the lifetime of first-party sessions.
    |
    | For financial applications, we might want tokens to expire for security.
    | Set to null for no expiration, or a number of minutes for expiration.
    |
    */

    'expiration' => env('SANCTUM_EXPIRATION', null), // No expiration by default

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Sanctum can prefix new tokens in order to take advantage of numerous
    | security scanning initiatives maintained by open source platforms
    | that notify developers if they commit tokens into repositories.
    |
    | See: https://docs.github.com/en/code-security/secret-scanning/about-secret-scanning
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum you may need to
    | customize some of the middleware Sanctum uses while processing the
    | request. You may change the middleware listed below as required.
    |
    */

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
        'validate_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Personal Finance API Configuration
    |--------------------------------------------------------------------------
    |
    | Custom configuration for our personal finance application's API
    | authentication and security requirements.
    |
    */

    'finance_api' => [
        /**
         * Token Configuration
         * 
         * Settings specific to API tokens for financial data access
         */
        'tokens' => [
            // Maximum number of active tokens per user
            'max_per_user' => env('SANCTUM_MAX_TOKENS_PER_USER', 10),
            
            // Require device names for all tokens
            'require_device_name' => true,
            
            // Default token abilities (permissions)
            'default_abilities' => [
                'read-user',
                'read-accounts',
                'read-transactions',
                'read-budgets',
                'read-goals',
                'read-bills',
            ],
            
            // Sensitive abilities that require additional verification
            'sensitive_abilities' => [
                'write-accounts',
                'write-transactions',
                'delete-data',
                'export-data',
                'admin-access',
            ],
            
            // Token name patterns for different client types
            'name_patterns' => [
                'mobile' => 'Mobile App - %s',
                'web' => 'Web App - %s',
                'api' => 'API Client - %s',
                'integration' => 'Integration - %s',
            ],
        ],

        /**
         * Security Settings
         * 
         * Enhanced security settings for financial API access
         */
        'security' => [
            // Revoke all tokens when user changes password
            'revoke_on_password_change' => true,
            
            // Revoke all tokens when user changes email
            'revoke_on_email_change' => true,
            
            // Maximum idle time before token is considered stale (minutes)
            'max_idle_time' => env('SANCTUM_MAX_IDLE_TIME', 10080), // 7 days
            
            // Log all API token usage for security auditing
            'log_token_usage' => env('SANCTUM_LOG_USAGE', true),
            
            // Require IP validation for tokens (bind token to IP)
            'ip_validation' => env('SANCTUM_IP_VALIDATION', false),
            
            // Rate limiting for API endpoints
            'rate_limit' => [
                'read_operations' => env('SANCTUM_READ_LIMIT', '1000,60'), // 1000 per hour
                'write_operations' => env('SANCTUM_WRITE_LIMIT', '100,60'), // 100 per hour
                'sensitive_operations' => env('SANCTUM_SENSITIVE_LIMIT', '10,60'), // 10 per hour
            ],
        ],

        /**
         * Mobile App Configuration
         * 
         * Specific settings for mobile application API access
         */
        'mobile' => [
            // Longer token expiration for mobile apps
            'token_expiration' => env('SANCTUM_MOBILE_EXPIRATION', 43200), // 30 days
            
            // Mobile-specific abilities
            'abilities' => [
                'read-user',
                'update-user-profile',
                'read-accounts',
                'read-transactions',
                'create-transactions',
                'read-budgets',
                'read-goals',
                'update-goals',
                'read-bills',
                'mobile-notifications',
            ],
            
            // Require device registration for mobile tokens
            'require_device_registration' => true,
            
            // Maximum number of mobile devices per user
            'max_devices' => 3,
        ],

        /**
         * Third-Party Integration Configuration
         * 
         * Settings for third-party service integrations (bank APIs, etc.)
         */
        'integrations' => [
            // Special token type for bank integrations
            'bank_integration_expiration' => env('SANCTUM_BANK_INTEGRATION_EXPIRATION', 1440), // 24 hours
            
            // Integration-specific abilities
            'abilities' => [
                'read-accounts',
                'create-transactions',
                'update-account-balances',
                'webhook-access',
            ],
            
            // Require IP whitelist for integration tokens
            'require_ip_whitelist' => true,
            
            // Webhook verification settings
            'webhook_verification' => [
                'require_signature' => true,
                'signature_header' => 'X-Webhook-Signature',
                'tolerance' => 300, // 5 minutes
            ],
        ],

        /**
         * Development and Testing Configuration
         * 
         * Settings for development and testing environments
         */
        'development' => [
            // Longer token expiration for development
            'token_expiration' => null, // Never expire in development
            
            // Relaxed rate limiting
            'rate_limit' => [
                'read_operations' => '10000,60',
                'write_operations' => '1000,60',
                'sensitive_operations' => '100,60',
            ],
            
            // Disable some security features for easier testing
            'ip_validation' => false,
            'require_device_registration' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Abilities
    |--------------------------------------------------------------------------
    |
    | Define the available token abilities for our personal finance API.
    | These control what actions can be performed with different tokens.
    |
    */

    'abilities' => [
        // User management abilities
        'read-user' => 'Read user profile information',
        'update-user-profile' => 'Update user profile information',
        'update-user-preferences' => 'Update user preferences and settings',
        'delete-user-account' => 'Delete user account (destructive)',

        // Account management abilities
        'read-accounts' => 'Read account information and balances',
        'create-accounts' => 'Create new accounts',
        'update-accounts' => 'Update account information',
        'delete-accounts' => 'Delete accounts (destructive)',

        // Transaction management abilities
        'read-transactions' => 'Read transaction history',
        'create-transactions' => 'Create new transactions',
        'update-transactions' => 'Update existing transactions',
        'delete-transactions' => 'Delete transactions (destructive)',

        // Budget management abilities
        'read-budgets' => 'Read budget information',
        'create-budgets' => 'Create new budgets',
        'update-budgets' => 'Update existing budgets',
        'delete-budgets' => 'Delete budgets (destructive)',

        // Goal management abilities
        'read-goals' => 'Read financial goals',
        'create-goals' => 'Create new financial goals',
        'update-goals' => 'Update existing goals',
        'delete-goals' => 'Delete goals (destructive)',

        // Bill management abilities
        'read-bills' => 'Read bill information',
        'create-bills' => 'Create new bills',
        'update-bills' => 'Update existing bills',
        'delete-bills' => 'Delete bills (destructive)',

        // Category management abilities
        'read-categories' => 'Read transaction categories',
        'create-categories' => 'Create new categories',
        'update-categories' => 'Update existing categories',
        'delete-categories' => 'Delete categories (destructive)',

        // Reporting and analytics abilities
        'read-reports' => 'Access financial reports and analytics',
        'export-data' => 'Export financial data',

        // Integration abilities
        'webhook-access' => 'Receive webhook notifications',
        'bank-integration' => 'Access bank integration features',

        // Mobile-specific abilities
        'mobile-notifications' => 'Send push notifications to mobile devices',
        'mobile-sync' => 'Sync data with mobile applications',

        // Administrative abilities
        'admin-access' => 'Administrative access to system features',
        'user-management' => 'Manage other user accounts',
        'system-settings' => 'Modify system-wide settings',

        // Security abilities
        'security-audit' => 'Access security logs and audit trails',
        'token-management' => 'Manage API tokens for other users',
    ],

];