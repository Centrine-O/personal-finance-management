<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        /**
         * Web Guard - Session-based authentication
         * 
         * Used for web browser authentication with sessions and cookies.
         * This is the primary authentication method for our web application.
         * 
         * Features:
         * - Session-based authentication
         * - CSRF protection
         * - Remember me functionality
         * - Cookie-based persistence
         */
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        /**
         * API Guard - Token-based authentication
         * 
         * Used for API authentication with Laravel Sanctum.
         * This is for mobile apps, SPAs, and external API access.
         * 
         * Features:
         * - Token-based authentication
         * - Multiple device support
         * - Token expiration and rotation
         * - Granular permissions
         */
        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
            'hash' => false,
        ],

        /**
         * Admin Guard - Separate authentication for admin users
         * 
         * Optional: If we implement admin functionality, we can use
         * a separate guard with additional security requirements.
         */
        // 'admin' => [
        //     'driver' => 'session',
        //     'provider' => 'admins',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        /**
         * Primary Users Provider
         * 
         * Uses our User model for authentication.
         * This handles all regular user authentication.
         */
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        /**
         * Database-based provider (alternative)
         * 
         * If you prefer to use database queries instead of Eloquent,
         * you can uncomment and configure this provider.
         */
        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],

        /**
         * Admin Users Provider (optional)
         * 
         * If we implement separate admin users, we could use this.
         */
        // 'admins' => [
        //     'driver' => 'eloquent',
        //     'model' => App\Models\Admin::class,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        /**
         * User Password Resets
         * 
         * Configuration for regular user password resets.
         * 
         * Security considerations for financial apps:
         * - Shorter expiry time (60 minutes instead of default)
         * - Throttling to prevent spam
         * - Custom email templates with security warnings
         */
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60, // 60 minutes (shorter for financial security)
            'throttle' => 60, // 1 minute between requests
        ],

        /**
         * Admin Password Resets (optional)
         * 
         * If we implement admin users, they get even stricter settings.
         */
        // 'admins' => [
        //     'provider' => 'admins',
        //     'table' => 'admin_password_reset_tokens',
        //     'expire' => 30, // 30 minutes for admin accounts
        //     'throttle' => 300, // 5 minutes between requests
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | times out and the user is prompted to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    | For financial applications, we use a shorter timeout for security.
    |
    */

    'password_timeout' => 1800, // 30 minutes instead of 3 hours

    /*
    |--------------------------------------------------------------------------
    | Custom Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Additional configuration specific to our personal finance application.
    | These settings control various security and authentication behaviors.
    |
    */

    'finance_security' => [
        /**
         * Account Lockout Settings
         * 
         * Controls when and how user accounts get locked for security.
         */
        'lockout' => [
            'max_attempts' => 5, // Maximum failed login attempts
            'lockout_duration' => 15, // Minutes to lock account
            'reset_attempts_after' => 60, // Reset counter after X minutes
        ],

        /**
         * Session Security Settings
         * 
         * Enhanced session security for financial data protection.
         */
        'session' => [
            'lifetime' => 120, // Session lifetime in minutes
            'idle_timeout' => 30, // Auto-logout after X minutes of inactivity
            'regenerate_on_auth' => true, // Regenerate session ID on login
            'strict_ip_check' => false, // Reject if IP changes mid-session
        ],

        /**
         * Two-Factor Authentication Settings
         * 
         * Configuration for 2FA when we implement it.
         */
        'two_factor' => [
            'enabled' => false, // Enable 2FA system-wide
            'required_for_high_value' => true, // Require for accounts >$100k
            'backup_codes_count' => 8, // Number of backup codes to generate
            'window' => 1, // TOTP time window tolerance
        ],

        /**
         * Email Verification Settings
         * 
         * Email verification configuration for financial security.
         */
        'email_verification' => [
            'required' => true, // Require email verification
            'expire' => 60, // Verification link expires in minutes
            'max_attempts' => 3, // Max verification emails per hour
            'restrict_unverified' => true, // Block unverified users from financial features
        ],

        /**
         * API Authentication Settings
         * 
         * Settings for API token authentication.
         */
        'api' => [
            'token_expiry' => null, // API tokens don't expire by default
            'max_tokens_per_user' => 10, // Maximum tokens per user
            'token_name_required' => true, // Require device names for tokens
            'revoke_on_password_reset' => true, // Revoke all tokens on password reset
        ],

        /**
         * Security Monitoring
         * 
         * Settings for security event monitoring and logging.
         */
        'monitoring' => [
            'log_failed_logins' => true,
            'log_successful_logins' => true,
            'log_lockouts' => true,
            'log_password_resets' => true,
            'alert_on_suspicious_activity' => true,
            'track_login_locations' => true,
        ],

        /**
         * Rate Limiting
         * 
         * Enhanced rate limiting for security endpoints.
         */
        'rate_limits' => [
            'login_attempts' => '5,1', // 5 attempts per minute
            'registration' => '3,10', // 3 registrations per 10 minutes
            'password_reset' => '3,10', // 3 reset requests per 10 minutes
            'email_verification' => '6,60', // 6 verification emails per hour
        ],

        /**
         * Password Requirements
         * 
         * Enhanced password requirements for financial security.
         */
        'password_requirements' => [
            'min_length' => 8,
            'require_mixed_case' => true,
            'require_numbers' => true,
            'require_symbols' => true,
            'check_compromised' => true,
            'history_count' => 5, // Remember last 5 passwords
            'max_age_days' => 90, // Force password change every 90 days
        ],

        /**
         * Device Management
         * 
         * Settings for tracking and managing user devices.
         */
        'device_management' => [
            'track_devices' => true,
            'max_devices' => 5, // Maximum concurrent logged-in devices
            'require_verification_new_device' => true,
            'auto_logout_old_sessions' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment-Specific Overrides
    |--------------------------------------------------------------------------
    |
    | Different security settings for different environments.
    |
    */

    'environment_overrides' => [
        'local' => [
            // Relaxed settings for development
            'lockout' => [
                'max_attempts' => 10,
                'lockout_duration' => 5,
            ],
            'session' => [
                'idle_timeout' => 480, // 8 hours for development
            ],
            'rate_limits' => [
                'login_attempts' => '20,1',
            ],
        ],

        'testing' => [
            // Very relaxed settings for testing
            'lockout' => [
                'max_attempts' => 100,
                'lockout_duration' => 1,
            ],
            'email_verification' => [
                'required' => false,
            ],
        ],

        'production' => [
            // Strict settings for production
            'session' => [
                'idle_timeout' => 15, // 15 minutes idle timeout
                'strict_ip_check' => true,
            ],
            'two_factor' => [
                'required_for_high_value' => true,
            ],
            'monitoring' => [
                'alert_on_suspicious_activity' => true,
            ],
        ],
    ],

];