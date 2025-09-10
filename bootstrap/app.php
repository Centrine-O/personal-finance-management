<?php

/*
|--------------------------------------------------------------------------
| Laravel Application Bootstrap
|--------------------------------------------------------------------------
|
| This file is responsible for bootstrapping the Laravel application.
| It creates the application instance and binds important interfaces.
| 
| This is the entry point where Laravel starts up and configures itself.
| Think of this as the "main function" of our Laravel application.
|
*/

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Create a new Laravel application instance
// This sets up the core container and basic services
return Application::configure(basePath: dirname(__DIR__))
    
    // Configure routing
    // This tells Laravel where to find our route files
    ->withRouting(
        // Web routes (for browser requests with sessions, CSRF protection, etc.)
        web: __DIR__.'/../routes/web.php',
        
        // API routes (for mobile apps, SPAs, external integrations)
        api: __DIR__.'/../routes/api.php',
        
        // Console routes (for custom artisan commands)
        commands: __DIR__.'/../routes/console.php',
        
        // Real-time features routes (for WebSocket connections)
        channels: __DIR__.'/../routes/channels.php',
        
        // Health check endpoint (useful for load balancers, monitoring)
        health: '/up',
    )
    
    // Configure middleware (code that runs before/after requests)
    ->withMiddleware(function (Middleware $middleware) {
        
        // Global middleware (runs on every request)
        // These are security and performance middlewares
        $middleware->use([
            // Trust proxies (important for load balancers)
            \Illuminate\Http\Middleware\TrustProxies::class,
            
            // Handle CORS (Cross-Origin Resource Sharing) for API requests
            \Illuminate\Http\Middleware\HandleCors::class,
            
            // Prevent requests during maintenance mode
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            
            // Validate POST data size (prevents huge uploads)
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            
            // Trim whitespace from request data (data cleanup)
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            
            // Convert empty strings to null (database consistency)
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);
        
        // Web middleware group (for browser requests)
        $middleware->group('web', [
            // Session handling (login, cart, flash messages)
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            
            // CSRF protection (prevents malicious form submissions)
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            
            // Route model binding (automatic model injection)
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        
        // API middleware group (for API requests)
        $middleware->group('api', [
            // Rate limiting (prevent API abuse)
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            
            // Route model binding for APIs
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        
        // Custom middleware aliases (shortcuts for route definitions)
        $middleware->alias([
            // Authentication middleware
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            
            // Basic HTTP authentication
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            
            // Authorization middleware
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            
            // Guest middleware (redirect if already logged in)
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            
            // Password confirmation middleware (for sensitive actions)
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            
            // Signed URL middleware (for secure temporary links)
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            
            // Rate limiting middleware
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            
            // Email verification middleware
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    
    // Configure exception handling
    ->withExceptions(function (Exceptions $exceptions) {
        
        // Custom exception reporting
        // You can add logging, error tracking services (like Sentry), etc.
        $exceptions->report(function (Throwable $e) {
            // Log all exceptions for debugging
            // In production, you might send this to error tracking services
            if (app()->environment('production')) {
                // Example: Send to external error tracking
                // Sentry::captureException($e);
            }
        });
        
        // Custom exception rendering
        // This controls how exceptions are displayed to users
        $exceptions->render(function (Throwable $e, $request) {
            
            // For API requests, always return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'error' => class_basename($e),
                    // Only include details in development
                    'details' => app()->environment('local') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ] : null,
                ], 500);
            }
            
            // For web requests, use default Laravel error pages
            return null;
        });
    })
    
    // Create and return the configured application
    ->create();