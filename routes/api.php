<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| API Routes for Personal Finance Management System:
| - Authentication endpoints (register, login, password reset)
| - Financial data endpoints (accounts, transactions, budgets)
| - Real-time data endpoints for mobile apps
| - Webhook endpoints for bank integrations
|
| All API routes are prefixed with /api and return JSON responses
|
*/

// ========================================
// API INFORMATION AND HEALTH CHECKS
// ========================================

/**
 * API Information Endpoint
 * Returns basic API information and version
 */
Route::get('/', function () {
    return response()->json([
        'name' => 'Personal Finance Management API',
        'version' => '1.0.0',
        'description' => 'RESTful API for personal finance management',
        'documentation' => url('/api/documentation'),
        'status' => 'operational',
        'timestamp' => now()->toISOString(),
    ]);
})->name('api.info');

/**
 * API Health Check Endpoint
 * Used by load balancers and monitoring systems
 */
Route::get('/health', function () {
    $checks = [
        'database' => 'ok',
        'redis' => 'ok', 
        'storage' => 'ok',
    ];

    try {
        // Check database connectivity
        \Illuminate\Support\Facades\DB::connection()->getPdo();
    } catch (\Exception $e) {
        $checks['database'] = 'failed';
    }

    try {
        // Check Redis connectivity
        \Illuminate\Support\Facades\Cache::get('health_check', 'ok');
    } catch (\Exception $e) {
        $checks['redis'] = 'failed';
    }

    $healthy = !in_array('failed', $checks);
    $status = $healthy ? 200 : 503;

    return response()->json([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'timestamp' => now()->toISOString(),
    ], $status);
})->name('api.health');

// ========================================
// AUTHENTICATION API ROUTES
// ========================================

/**
 * API Authentication Routes (Public)
 * These endpoints don't require authentication
 */
Route::prefix('auth')->name('api.auth.')->group(function () {
    
    /**
     * User Registration API
     */
    Route::post('/register', [RegisterController::class, 'apiRegister'])
         ->name('register');
    
    /**
     * Check Email Availability
     * For real-time form validation
     */
    Route::post('/check-email', [RegisterController::class, 'checkEmail'])
         ->name('check-email');
    
    /**
     * User Login API
     * Returns access token for API authentication
     */
    Route::post('/login', [LoginController::class, 'apiLogin'])
         ->name('login');
    
    /**
     * Password Reset Request API
     * Send password reset email
     */
    Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail'])
         ->middleware('throttle:5,1')
         ->name('password.forgot');
    
    /**
     * Check Password Reset Status
     */
    Route::post('/password/status', [ForgotPasswordController::class, 'checkResetStatus'])
         ->name('password.status');
    
    /**
     * Password Reset API
     * Reset password using token
     */
    Route::post('/password/reset', [ResetPasswordController::class, 'apiReset'])
         ->middleware('throttle:5,1')
         ->name('password.reset');
    
    /**
     * Check Reset Token Validity
     */
    Route::post('/password/check-token', [ResetPasswordController::class, 'checkToken'])
         ->name('password.check-token');
});

// ========================================
// AUTHENTICATED API ROUTES
// ========================================

/**
 * Protected API Routes
 * These routes require API token authentication
 */
Route::middleware('auth:sanctum')->group(function () {
    
    /**
     * Current User Information
     */
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
                'avatar_url' => $user->avatar_url,
                'preferred_currency' => $user->preferred_currency,
                'timezone' => $user->timezone,
                'status' => $user->status,
                'created_at' => $user->created_at->toISOString(),
            ],
        ]);
    })->name('api.user');
    
    /**
     * User Logout API
     * Revoke current access token
     */
    Route::post('/auth/logout', function (Request $request) {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    })->name('api.auth.logout');
    
    /**
     * Logout from All Devices
     * Revoke all user's tokens
     */
    Route::post('/auth/logout-all', function (Request $request) {
        // Revoke all tokens for this user
        $request->user()->tokens()->delete();
        
        return response()->json([
            'message' => 'Successfully logged out from all devices.',
        ]);
    })->name('api.auth.logout-all');
    
    // ========================================
    // EMAIL VERIFICATION API ROUTES
    // ========================================
    
    /**
     * Email Verification Status
     */
    Route::get('/email/verification-status', [VerificationController::class, 'status'])
         ->name('api.verification.status');
    
    /**
     * Resend Email Verification
     */
    Route::post('/email/resend-verification', [VerificationController::class, 'resend'])
         ->middleware('throttle:6,1')
         ->name('api.verification.resend');
    
    /**
     * Update Email Address
     * Requires password confirmation
     */
    Route::post('/email/update', [VerificationController::class, 'updateEmail'])
         ->name('api.verification.update');
    
    // ========================================
    // FINANCIAL DATA API ROUTES
    // ========================================
    
    /**
     * Dashboard Summary API
     * Financial overview for mobile dashboard
     */
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        
        return response()->json([
            'summary' => [
                'net_worth' => $user->calculateNetWorth(),
                'monthly_income' => $user->getMonthlyIncome(now()),
                'monthly_expenses' => $user->getMonthlyExpenses(now()),
                'account_count' => $user->activeAccounts()->count(),
                'recent_transactions' => $user->transactions()
                    ->with(['account:id,name', 'category:id,name'])
                    ->orderBy('transaction_date', 'desc')
                    ->take(10)
                    ->get()
                    ->map(function ($transaction) {
                        return [
                            'id' => $transaction->id,
                            'description' => $transaction->description,
                            'amount' => $transaction->signed_amount,
                            'formatted_amount' => $transaction->formatted_amount,
                            'date' => $transaction->transaction_date->toDateString(),
                            'account' => $transaction->account->name,
                            'category' => $transaction->category->name,
                        ];
                    }),
            ],
        ]);
    })->middleware('can:access-financial-data')
      ->name('api.dashboard');
    
    /**
     * Accounts API
     */
    Route::prefix('accounts')->name('api.accounts.')->group(function () {
        Route::get('/', function (Request $request) {
            $accounts = $request->user()->activeAccounts()
                ->orderBy('sort_order')
                ->get()
                ->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'name' => $account->name,
                        'type' => $account->type,
                        'balance' => $account->balance,
                        'formatted_balance' => $account->formatted_balance,
                        'currency' => $account->currency,
                        'color' => $account->color,
                        'icon' => $account->icon,
                        'is_asset' => $account->is_asset,
                        'updated_at' => $account->updated_at->toISOString(),
                    ];
                });
            
            return response()->json(['accounts' => $accounts]);
        })->name('index');
        
        Route::get('/{account}', function (Request $request, \App\Models\Account $account) {
            // Ensure user owns this account
            if ($account->user_id !== $request->user()->id) {
                abort(403);
            }
            
            return response()->json([
                'account' => [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'balance' => $account->balance,
                    'formatted_balance' => $account->formatted_balance,
                    'currency' => $account->currency,
                    'institution_name' => $account->institution_name,
                    'account_number_last4' => $account->account_number_last4,
                    'is_active' => $account->is_active,
                    'created_at' => $account->created_at->toISOString(),
                    'updated_at' => $account->updated_at->toISOString(),
                    'recent_transactions' => $account->recentTransactions()
                        ->with(['category:id,name'])
                        ->take(10)
                        ->get()
                        ->map(function ($transaction) {
                            return [
                                'id' => $transaction->id,
                                'description' => $transaction->description,
                                'amount' => $transaction->signed_amount,
                                'formatted_amount' => $transaction->formatted_amount,
                                'date' => $transaction->transaction_date->toDateString(),
                                'category' => $transaction->category->name,
                            ];
                        }),
                ],
            ]);
        })->name('show');
    });
    
    /**
     * Transactions API
     */
    Route::prefix('transactions')->name('api.transactions.')->group(function () {
        Route::get('/', function (Request $request) {
            $query = $request->user()->transactions()
                ->with(['account:id,name', 'category:id,name'])
                ->orderBy('transaction_date', 'desc');
            
            // Apply filters
            if ($request->has('account_id')) {
                $query->where('account_id', $request->account_id);
            }
            
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            if ($request->has('from_date')) {
                $query->where('transaction_date', '>=', $request->from_date);
            }
            
            if ($request->has('to_date')) {
                $query->where('transaction_date', '<=', $request->to_date);
            }
            
            $perPage = min($request->get('per_page', 50), 100); // Max 100 per page
            $transactions = $query->paginate($perPage);
            
            return response()->json([
                'transactions' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                ],
            ]);
        })->name('index');
    });
    
    /**
     * Categories API
     */
    Route::get('/categories', function (Request $request) {
        $categories = \App\Models\Category::forUser($request->user()->id)
            ->active()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->type,
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'is_system' => $category->is_system,
                ];
            });
        
        return response()->json(['categories' => $categories]);
    })->name('api.categories.index');
    
    /**
     * Budget Summary API
     */
    Route::get('/budgets/current', function (Request $request) {
        $budget = $request->user()->activeBudget();
        
        if (!$budget) {
            return response()->json(['budget' => null]);
        }
        
        return response()->json([
            'budget' => [
                'id' => $budget->id,
                'name' => $budget->name,
                'start_date' => $budget->start_date->toDateString(),
                'end_date' => $budget->end_date->toDateString(),
                'progress_percentage' => $budget->progress_percentage,
                'planned_income' => $budget->planned_income,
                'actual_income' => $budget->actual_income,
                'planned_expenses' => $budget->planned_expenses,
                'actual_expenses' => $budget->actual_expenses,
                'remaining_budget' => $budget->remaining_budget,
                'is_overspent' => $budget->is_overspent,
                'categories' => $budget->budgetCategories()
                    ->with('category:id,name,color')
                    ->get()
                    ->map(function ($budgetCategory) {
                        return [
                            'category_name' => $budgetCategory->category->name,
                            'category_color' => $budgetCategory->category->color,
                            'allocated' => $budgetCategory->allocated_amount,
                            'spent' => $budgetCategory->spent_amount,
                            'remaining' => $budgetCategory->remaining_amount,
                            'usage_percentage' => $budgetCategory->usage_percentage,
                        ];
                    }),
            ],
        ]);
    })->name('api.budgets.current');
});

// ========================================
// WEBHOOK ROUTES
// ========================================

/**
 * Webhook Routes for Bank Integrations
 * These routes handle incoming data from financial institutions
 */
Route::prefix('webhooks')->name('api.webhooks.')->group(function () {
    
    /**
     * Bank Transaction Webhook
     * Receives new transactions from bank APIs
     */
    Route::post('/transactions/{provider}', function ($provider) {
        // This would handle incoming transaction data from banks
        // Implementation would depend on the specific bank API
        
        return response()->json([
            'message' => 'Webhook received successfully',
            'provider' => $provider,
        ]);
    })->name('transactions');
    
    /**
     * Account Balance Update Webhook
     * Receives balance updates from bank APIs
     */
    Route::post('/balances/{provider}', function ($provider) {
        // This would handle incoming balance updates from banks
        
        return response()->json([
            'message' => 'Balance update received',
            'provider' => $provider,
        ]);
    })->name('balances');
});

// ========================================
// RATE LIMITED ROUTES
// ========================================

/**
 * API routes with stricter rate limiting for security
 */
Route::middleware(['throttle:30,1'])->group(function () {
    
    /**
     * Export Data API
     * Allows users to export their financial data
     */
    Route::middleware(['auth:sanctum', 'can:export-financial-data'])
         ->get('/export/{format}', function ($format) {
             // This would handle data export in various formats
             
             return response()->json([
                 'message' => 'Export initiated',
                 'format' => $format,
                 'status' => 'processing',
             ]);
         })
         ->where('format', 'csv|json|pdf')
         ->name('api.export');
});

/**
 * API Error Handler
 * Handles API-specific error responses
 */
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found.',
        'error' => 'not_found',
        'documentation' => url('/api/documentation'),
    ], 404);
});