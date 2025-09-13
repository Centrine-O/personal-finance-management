<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [
        
        /**
         * Synchronous Queue (No Queue)
         * 
         * Jobs run immediately in the same request.
         * Only use this in development or for testing.
         * Not recommended for production as it can slow down requests.
         */
        'sync' => [
            'driver' => 'sync',
        ],

        /**
         * Database Queue
         * 
         * Uses your database to store queued jobs.
         * Good for small to medium applications.
         * Easy to set up and monitor through database queries.
         */
        'database' => [
            'driver' => 'database',
            'table' => 'jobs', // Table to store job data
            'queue' => 'default', // Default queue name
            'retry_after' => 90, // Seconds before job is considered failed and retried
            'after_commit' => false, // Whether to dispatch job after database transaction commits
        ],

        /**
         * Redis Queue
         * 
         * High-performance queue system using Redis.
         * Excellent for high-volume applications.
         * Provides fast job processing and good monitoring capabilities.
         */
        'redis' => [
            'driver' => 'redis',
            'connection' => env('QUEUE_REDIS_CONNECTION', 'default'),
            'queue' => env('QUEUE_REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null, // Seconds to block waiting for a job
            'after_commit' => false,
        ],

        /**
         * Amazon SQS Queue
         * 
         * Fully managed queue service from AWS.
         * Great for cloud-based applications with high reliability requirements.
         */
        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        /**
         * Beanstalkd Queue
         * 
         * Simple, fast work queue.
         * Good performance for medium-scale applications.
         */
        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => 0,
            'after_commit' => false,
        ],

        /**
         * Null Queue Driver
         * 
         * Jobs are discarded immediately.
         * Useful for disabling queues in certain environments.
         */
        'null' => [
            'driver' => 'null',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Personal Finance App Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Custom queue settings specific to our personal finance application.
    | We organize jobs by type and priority for better performance.
    |
    */

    'finance_app' => [
        /**
         * Queue Names and Their Purposes
         * 
         * Different types of jobs should use different queues
         * so we can prioritize and monitor them separately.
         */
        'queues' => [
            // High priority - Security and authentication emails
            'auth' => [
                'name' => 'auth',
                'priority' => 10, // Highest priority
                'retry_after' => 60, // Quick retry for urgent emails
                'max_attempts' => 5, // More attempts for critical emails
            ],

            // High priority - Email notifications
            'emails' => [
                'name' => 'emails',
                'priority' => 9,
                'retry_after' => 90,
                'max_attempts' => 3,
            ],

            // Medium priority - Financial calculations and data processing
            'financial' => [
                'name' => 'financial',
                'priority' => 7,
                'retry_after' => 300, // 5 minutes
                'max_attempts' => 3,
            ],

            // Medium priority - Bill reminders and budget alerts
            'notifications' => [
                'name' => 'notifications',
                'priority' => 6,
                'retry_after' => 600, // 10 minutes
                'max_attempts' => 2,
            ],

            // Low priority - Data imports and exports
            'data' => [
                'name' => 'data',
                'priority' => 5,
                'retry_after' => 900, // 15 minutes
                'max_attempts' => 2,
            ],

            // Low priority - Report generation
            'reports' => [
                'name' => 'reports',
                'priority' => 4,
                'retry_after' => 1800, // 30 minutes
                'max_attempts' => 2,
            ],

            // Lowest priority - Marketing and non-essential emails
            'marketing' => [
                'name' => 'marketing',
                'priority' => 3,
                'retry_after' => 3600, // 1 hour
                'max_attempts' => 1,
            ],

            // Background tasks - Cleanup, analytics, etc.
            'background' => [
                'name' => 'background',
                'priority' => 2,
                'retry_after' => 7200, // 2 hours
                'max_attempts' => 1,
            ],
        ],

        /**
         * Worker Configuration
         * 
         * Settings for queue workers that process jobs.
         */
        'workers' => [
            // Number of workers to run for each queue
            'counts' => [
                'auth' => env('QUEUE_AUTH_WORKERS', 2),
                'emails' => env('QUEUE_EMAIL_WORKERS', 3),
                'financial' => env('QUEUE_FINANCIAL_WORKERS', 2),
                'notifications' => env('QUEUE_NOTIFICATION_WORKERS', 2),
                'data' => env('QUEUE_DATA_WORKERS', 1),
                'reports' => env('QUEUE_REPORT_WORKERS', 1),
                'marketing' => env('QUEUE_MARKETING_WORKERS', 1),
                'background' => env('QUEUE_BACKGROUND_WORKERS', 1),
            ],

            // Memory limits for different types of workers
            'memory_limits' => [
                'auth' => '128M',
                'emails' => '256M',
                'financial' => '512M',
                'data' => '1024M',
                'reports' => '1024M',
                'default' => '256M',
            ],

            // Timeout settings for different job types
            'timeouts' => [
                'auth' => 60, // 1 minute - auth jobs should be fast
                'emails' => 120, // 2 minutes
                'financial' => 300, // 5 minutes
                'data' => 1800, // 30 minutes for large imports
                'reports' => 3600, // 1 hour for complex reports
                'default' => 300, // 5 minutes default
            ],

            // Sleep settings when no jobs are available
            'sleep' => [
                'auth' => 1, // Check very frequently for urgent jobs
                'emails' => 2,
                'financial' => 3,
                'default' => 3,
            ],

            // Maximum number of jobs to process before restarting worker
            'max_jobs' => [
                'memory_intensive' => 10, // Restart frequently for data/report jobs
                'default' => 100,
            ],
        ],

        /**
         * Monitoring and Alerts
         * 
         * Configuration for monitoring queue health and performance.
         */
        'monitoring' => [
            // Log slow jobs (jobs that take longer than expected)
            'log_slow_jobs' => env('QUEUE_LOG_SLOW_JOBS', true),
            'slow_job_threshold' => [
                'auth' => 30, // 30 seconds
                'emails' => 60, // 1 minute
                'financial' => 120, // 2 minutes
                'default' => 300, // 5 minutes
            ],

            // Alert when queue depth exceeds thresholds
            'queue_depth_alerts' => [
                'auth' => 10, // Alert if more than 10 auth jobs queued
                'emails' => 50,
                'financial' => 100,
                'default' => 500,
            ],

            // Failed job handling
            'failed_job_retention' => env('QUEUE_FAILED_JOB_RETENTION', 7), // days
            'alert_on_failed_jobs' => env('QUEUE_ALERT_FAILED_JOBS', true),
        ],

        /**
         * Security Settings
         * 
         * Queue security configuration for financial applications.
         */
        'security' => [
            // Encrypt sensitive job data
            'encrypt_payloads' => env('QUEUE_ENCRYPT_PAYLOADS', true),
            
            // Jobs that should always be encrypted
            'always_encrypt' => [
                'auth',
                'emails',
                'financial',
            ],

            // Maximum job payload size to prevent memory issues
            'max_payload_size' => env('QUEUE_MAX_PAYLOAD_SIZE', '10MB'),

            // IP restrictions for queue workers (if applicable)
            'allowed_ips' => env('QUEUE_ALLOWED_IPS', null),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment-Specific Overrides
    |--------------------------------------------------------------------------
    |
    | Different queue settings for different environments.
    |
    */

    'environment_overrides' => [
        'local' => [
            // Use database queue in local development
            'default_connection' => 'database',
            'worker_count' => 1,
            'log_all_jobs' => true,
        ],

        'testing' => [
            // Use sync queue for faster tests
            'default_connection' => 'sync',
            'disable_failed_job_alerts' => true,
        ],

        'production' => [
            // Use Redis for better performance in production
            'default_connection' => 'redis',
            'enable_monitoring' => true,
            'strict_timeouts' => true,
        ],
    ],

];