<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "postmark", "log",
    |            "array", "failover", "roundrobin"
    |
    */

    'mailers' => [
        /**
         * SMTP Configuration
         * 
         * Standard SMTP configuration for most email providers.
         * Common providers include Gmail, SendGrid, Mailgun, etc.
         */
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        /**
         * Amazon SES Configuration
         * 
         * Amazon Simple Email Service for production applications.
         * Provides high deliverability and detailed analytics.
         */
        'ses' => [
            'transport' => 'ses',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        ],

        /**
         * Postmark Configuration
         * 
         * Postmark is excellent for transactional emails with
         * high deliverability rates, perfect for financial apps.
         */
        'postmark' => [
            'transport' => 'postmark',
            'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // You can add custom headers here
        ],

        /**
         * Mailgun Configuration
         * 
         * Mailgun provides powerful email APIs with good analytics.
         */
        'mailgun' => [
            'transport' => 'mailgun',
            'client' => [
                'timeout' => 5,
            ],
        ],

        /**
         * Log Driver
         * 
         * For development - emails are written to log files instead
         * of being actually sent. Useful for testing email content.
         */
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        /**
         * Array Driver
         * 
         * For testing - emails are stored in memory and can be
         * retrieved in tests to verify content and recipients.
         */
        'array' => [
            'transport' => 'array',
        ],

        /**
         * Failover Configuration
         * 
         * For production reliability - if the primary mailer fails,
         * it will automatically try the backup mailers in order.
         */
        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'postmark',
                'ses',
                'smtp',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@personalfinance.local'),
        'name' => env('MAIL_FROM_NAME', 'Personal Finance Manager'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Personal Finance App Email Configuration
    |--------------------------------------------------------------------------
    |
    | Custom email settings specific to our personal finance application.
    | These settings control various aspects of email functionality.
    |
    */

    'finance_app' => [
        /**
         * Email Addresses for Different Types
         * 
         * Different types of emails should come from appropriate addresses
         * to build trust and allow users to filter emails properly.
         */
        'addresses' => [
            // Transactional emails (login, password reset, verification)
            'auth' => [
                'address' => env('MAIL_AUTH_ADDRESS', 'auth@personalfinance.local'),
                'name' => env('MAIL_AUTH_NAME', 'Personal Finance Security'),
            ],

            // Security alerts and warnings
            'security' => [
                'address' => env('MAIL_SECURITY_ADDRESS', 'security@personalfinance.local'),
                'name' => env('MAIL_SECURITY_NAME', 'Personal Finance Security Team'),
            ],

            // Financial notifications (budget alerts, bill reminders)
            'finance' => [
                'address' => env('MAIL_FINANCE_ADDRESS', 'finance@personalfinance.local'),
                'name' => env('MAIL_FINANCE_NAME', 'Personal Finance Notifications'),
            ],

            // Marketing and promotional emails
            'marketing' => [
                'address' => env('MAIL_MARKETING_ADDRESS', 'news@personalfinance.local'),
                'name' => env('MAIL_MARKETING_NAME', 'Personal Finance Updates'),
            ],

            // Support and customer service
            'support' => [
                'address' => env('MAIL_SUPPORT_ADDRESS', 'support@personalfinance.local'),
                'name' => env('MAIL_SUPPORT_NAME', 'Personal Finance Support'),
            ],
        ],

        /**
         * Email Templates and Styling
         * 
         * Configuration for email appearance and branding.
         */
        'branding' => [
            // Primary brand color for buttons and headers
            'primary_color' => env('MAIL_PRIMARY_COLOR', '#2563eb'),
            
            // Secondary color for accents
            'secondary_color' => env('MAIL_SECONDARY_COLOR', '#16a34a'),
            
            // Warning color for security alerts
            'warning_color' => env('MAIL_WARNING_COLOR', '#dc2626'),
            
            // Logo URL for email headers
            'logo_url' => env('MAIL_LOGO_URL', null),
            
            // Company information
            'company_name' => env('MAIL_COMPANY_NAME', 'Personal Finance Manager'),
            'company_address' => env('MAIL_COMPANY_ADDRESS', ''),
        ],

        /**
         * Email Delivery Settings
         * 
         * Settings that control how and when emails are sent.
         */
        'delivery' => [
            // Queue emails for better performance
            'use_queue' => env('MAIL_USE_QUEUE', true),
            
            // Queue connection for email jobs
            'queue_connection' => env('MAIL_QUEUE_CONNECTION', 'redis'),
            
            // Queue name for email jobs
            'queue_name' => env('MAIL_QUEUE_NAME', 'emails'),
            
            // Delay between sending emails (in seconds) to avoid rate limits
            'send_delay' => env('MAIL_SEND_DELAY', 0),
            
            // Maximum number of email sending attempts
            'max_attempts' => env('MAIL_MAX_ATTEMPTS', 3),
            
            // Timeout for email sending (in seconds)
            'timeout' => env('MAIL_TIMEOUT', 30),
        ],

        /**
         * Security Settings for Email
         * 
         * Email security configuration for financial applications.
         */
        'security' => [
            // Encrypt email content in queue (recommended for financial data)
            'encrypt_queue' => env('MAIL_ENCRYPT_QUEUE', true),
            
            // Sign emails with DKIM (helps with deliverability)
            'dkim_enabled' => env('MAIL_DKIM_ENABLED', false),
            'dkim_domain' => env('MAIL_DKIM_DOMAIN', null),
            'dkim_selector' => env('MAIL_DKIM_SELECTOR', 'default'),
            
            // SPF and DMARC settings (configured at DNS level)
            'spf_enabled' => env('MAIL_SPF_ENABLED', false),
            'dmarc_enabled' => env('MAIL_DMARC_ENABLED', false),
            
            // Track email opens and clicks for security monitoring
            'track_opens' => env('MAIL_TRACK_OPENS', false),
            'track_clicks' => env('MAIL_TRACK_CLICKS', false),
            
            // Rate limiting for email sending
            'rate_limit' => [
                'auth_emails' => env('MAIL_AUTH_RATE_LIMIT', '10,1'), // 10 per minute
                'security_emails' => env('MAIL_SECURITY_RATE_LIMIT', '5,1'), // 5 per minute
                'finance_emails' => env('MAIL_FINANCE_RATE_LIMIT', '20,1'), // 20 per minute
                'marketing_emails' => env('MAIL_MARKETING_RATE_LIMIT', '50,60'), // 50 per hour
            ],
        ],

        /**
         * Development Settings
         * 
         * Settings specific to development environment.
         */
        'development' => [
            // Override all email addresses in development
            'override_to' => env('MAIL_DEV_OVERRIDE_TO', null),
            
            // Log all emails in development
            'log_emails' => env('MAIL_DEV_LOG', true),
            
            // Prefix subject lines in development
            'subject_prefix' => env('MAIL_DEV_PREFIX', '[DEV] '),
            
            // Disable actual email sending in testing
            'disable_sending' => env('MAIL_DEV_DISABLE', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => env('MAIL_MARKDOWN_THEME', 'default'),

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Validation and Verification
    |--------------------------------------------------------------------------
    |
    | Settings for email address validation and verification processes.
    | Important for maintaining data quality in financial applications.
    |
    */

    'validation' => [
        // Validate email addresses against DNS records
        'dns_check' => env('MAIL_DNS_CHECK', true),
        
        // Block disposable email services
        'block_disposable' => env('MAIL_BLOCK_DISPOSABLE', true),
        
        // List of blocked domains
        'blocked_domains' => [
            '10minutemail.com',
            'tempmail.org',
            'guerrillamail.com',
            'mailinator.com',
            // Add more as needed
        ],
        
        // Verification link expiration (in minutes)
        'verification_expiry' => env('MAIL_VERIFICATION_EXPIRY', 1440), // 24 hours
        
        // Maximum verification emails per hour
        'verification_throttle' => env('MAIL_VERIFICATION_THROTTLE', 6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Analytics and Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for tracking email performance and delivery.
    |
    */

    'analytics' => [
        // Enable email delivery tracking
        'track_delivery' => env('MAIL_TRACK_DELIVERY', true),
        
        // Log email events for analysis
        'log_events' => env('MAIL_LOG_EVENTS', true),
        
        // Integration with analytics services
        'google_analytics' => env('MAIL_GOOGLE_ANALYTICS', null),
        
        // Webhook URLs for email events
        'webhooks' => [
            'delivery' => env('MAIL_WEBHOOK_DELIVERY', null),
            'open' => env('MAIL_WEBHOOK_OPEN', null),
            'click' => env('MAIL_WEBHOOK_CLICK', null),
            'bounce' => env('MAIL_WEBHOOK_BOUNCE', null),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Configuration
    |--------------------------------------------------------------------------
    |
    | These settings are maintained for backward compatibility.
    |
    */

    // Support email for contact forms and help
    'support_address' => env('MAIL_SUPPORT_ADDRESS', 'support@personalfinance.local'),
    'security_address' => env('MAIL_SECURITY_ADDRESS', 'security@personalfinance.local'),

];