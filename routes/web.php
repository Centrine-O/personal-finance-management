<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
| Routes for Personal Finance Management System:
| - Authentication routes (login, register, password reset, email verification)
| - Dashboard routes (financial overview, accounts, transactions)
| - Financial management routes (budgets, goals, bills)
| - Settings and profile routes
|
*/

// ========================================
// PUBLIC ROUTES (Guest Access)
// ========================================

/**
 * Home page - landing page for visitors
 * Shows app features and encourages sign up
 */
Route::get('/', function () {
    return view('welcome', [
        'title' => 'Personal Finance Manager',
        'subtitle' => 'Take control of your financial future',
    ]);
})->name('home');

/**
 * About page - information about the app
 */
Route::get('/about', function () {
    return view('about', [
        'title' => 'About Us',
        'subtitle' => 'Your trusted financial companion',
    ]);
})->name('about');

/**
 * Privacy Policy page - legal requirement
 */
Route::get('/privacy', function () {
    return view('legal.privacy', [
        'title' => 'Privacy Policy',
    ]);
})->name('privacy');

/**
 * Terms of Service page - legal requirement
 */
Route::get('/terms', function () {
    return view('legal.terms', [
        'title' => 'Terms of Service',
    ]);
})->name('terms');

// ========================================
// AUTHENTICATION ROUTES
// ========================================

/**
 * Registration Routes
 * Handles user account creation
 */
Route::middleware('guest')->group(function () {
    // Show registration form
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
         ->name('register');
    
    // Process registration
    Route::post('/register', [RegisterController::class, 'register'])
         ->name('register.submit');
    
    // Check email availability (AJAX)
    Route::post('/register/check-email', [RegisterController::class, 'checkEmail'])
         ->name('register.check-email');
});

/**
 * Login Routes
 * Handles user authentication
 */
Route::middleware('guest')->group(function () {
    // Show login form
    Route::get('/login', [LoginController::class, 'showLoginForm'])
         ->name('login');
    
    // Process login
    Route::post('/login', [LoginController::class, 'login'])
         ->name('login.submit');
});

/**
 * Password Reset Routes
 * Handles forgotten password recovery
 */
Route::middleware('guest')->group(function () {
    // Show "forgot password" form
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
         ->name('password.request');
    
    // Send password reset email
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
         ->name('password.email');
    
    // Show password reset form (with token)
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
         ->name('password.reset');
    
    // Process password reset
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])
         ->name('password.update');
    
    // Cancel password reset (optional)
    Route::post('/password/cancel', [ForgotPasswordController::class, 'cancelReset'])
         ->name('password.cancel');
    
    // Check reset status (AJAX)
    Route::post('/password/status', [ForgotPasswordController::class, 'checkResetStatus'])
         ->name('password.status');
});

/**
 * Email Verification Routes
 * Handles email address verification
 */
Route::middleware('auth')->group(function () {
    // Show email verification notice
    Route::get('/email/verify', [VerificationController::class, 'show'])
         ->name('verification.notice');
    
    // Process email verification (from email link)
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
         ->middleware('signed')
         ->name('verification.verify');
    
    // Resend verification email
    Route::post('/email/resend', [VerificationController::class, 'resend'])
         ->middleware('throttle:6,1')
         ->name('verification.resend');
    
    // Get verification status (AJAX)
    Route::get('/email/status', [VerificationController::class, 'status'])
         ->name('verification.status');
    
    // Update email address (requires re-verification)
    Route::post('/email/update', [VerificationController::class, 'updateEmail'])
         ->name('verification.update-email');
});

/**
 * Logout Route
 * Handles user logout
 */
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])
         ->name('logout');
});

// ========================================
// ACCOUNT STATUS PAGES
// ========================================

/**
 * Account Suspended Page
 * Shown when user account is suspended
 */
Route::get('/account/suspended', function () {
    return view('auth.account-suspended', [
        'title' => 'Account Suspended',
        'subtitle' => 'Your account access has been restricted',
    ]);
})->name('account.suspended');

/**
 * Account Inactive Page
 * Shown when user account is inactive
 */
Route::get('/account/inactive', function () {
    return view('auth.account-inactive', [
        'title' => 'Account Inactive',
        'subtitle' => 'Reactivate your account to continue',
    ]);
})->name('account.inactive');

// ========================================
// AUTHENTICATED ROUTES
// ========================================

/**
 * Dashboard Route
 * Main financial overview after login
 */
Route::middleware(['auth', 'verified', 'account.status'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        return view('dashboard', [
            'title' => 'Financial Dashboard',
            'subtitle' => 'Welcome back, ' . $user->first_name,
            'user' => $user,
        ]);
    })->name('dashboard');
});

/**
 * Profile and Settings Routes
 * User account management
 */
Route::middleware(['auth', 'verified', 'account.status'])->group(function () {
    // User profile
    Route::get('/profile', function () {
        return view('profile.show', [
            'title' => 'Your Profile',
            'user' => auth()->user(),
        ]);
    })->name('profile.show');
    
    // Edit profile
    Route::get('/profile/edit', function () {
        return view('profile.edit', [
            'title' => 'Edit Profile',
            'user' => auth()->user(),
        ]);
    })->name('profile.edit');
    
    // Account settings
    Route::get('/settings', function () {
        return view('settings.index', [
            'title' => 'Account Settings',
            'user' => auth()->user(),
        ]);
    })->name('settings.index');
    
    // Security settings
    Route::get('/settings/security', function () {
        return view('settings.security', [
            'title' => 'Security Settings',
            'user' => auth()->user(),
        ]);
    })->name('settings.security');
});

/**
 * Financial Management Routes
 * Core application functionality
 */
Route::middleware(['auth', 'verified', 'account.status'])->group(function () {
    // Accounts management
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', function () {
            return view('accounts.index', [
                'title' => 'Your Accounts',
                'accounts' => auth()->user()->accounts()->active()->get(),
            ]);
        })->name('index');
        
        Route::get('/create', function () {
            return view('accounts.create', [
                'title' => 'Add New Account',
            ]);
        })->name('create');
    });
    
    // Transactions management
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', function () {
            return view('transactions.index', [
                'title' => 'Transaction History',
                'transactions' => auth()->user()->transactions()
                    ->with(['account', 'category'])
                    ->orderBy('transaction_date', 'desc')
                    ->take(50)
                    ->get(),
            ]);
        })->name('index');
        
        Route::get('/create', function () {
            return view('transactions.create', [
                'title' => 'Add Transaction',
                'accounts' => auth()->user()->accounts()->active()->get(),
                'categories' => \App\Models\Category::forUser(auth()->id())->active()->get(),
            ]);
        })->name('create');
    });
    
    // Budgets management
    Route::prefix('budgets')->name('budgets.')->group(function () {
        Route::get('/', function () {
            return view('budgets.index', [
                'title' => 'Budget Management',
                'budgets' => auth()->user()->budgets()->orderBy('start_date', 'desc')->get(),
            ]);
        })->name('index');
        
        Route::get('/create', function () {
            return view('budgets.create', [
                'title' => 'Create Budget',
                'categories' => \App\Models\Category::forUser(auth()->id())->budgetable()->get(),
            ]);
        })->name('create');
    });
    
    // Goals management
    Route::prefix('goals')->name('goals.')->group(function () {
        Route::get('/', function () {
            return view('goals.index', [
                'title' => 'Financial Goals',
                'goals' => auth()->user()->goals()->orderBy('priority')->get(),
            ]);
        })->name('index');
        
        Route::get('/create', function () {
            return view('goals.create', [
                'title' => 'Create New Goal',
                'accounts' => auth()->user()->accounts()->active()->get(),
            ]);
        })->name('create');
    });
    
    // Bills management
    Route::prefix('bills')->name('bills.')->group(function () {
        Route::get('/', function () {
            return view('bills.index', [
                'title' => 'Bill Management',
                'bills' => auth()->user()->bills()->active()->orderBy('next_due_date')->get(),
            ]);
        })->name('index');
        
        Route::get('/create', function () {
            return view('bills.create', [
                'title' => 'Add New Bill',
                'accounts' => auth()->user()->accounts()->active()->get(),
                'categories' => \App\Models\Category::forUser(auth()->id())->ofType('expense')->get(),
            ]);
        })->name('create');
    });
    
    // Reports and analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', function () {
            return view('reports.index', [
                'title' => 'Financial Reports',
            ]);
        })->name('index');
        
        Route::get('/spending', function () {
            return view('reports.spending', [
                'title' => 'Spending Analysis',
            ]);
        })->name('spending');
        
        Route::get('/income', function () {
            return view('reports.income', [
                'title' => 'Income Analysis',
            ]);
        })->name('income');
        
        Route::get('/net-worth', function () {
            return view('reports.net-worth', [
                'title' => 'Net Worth Tracking',
                'net_worth' => auth()->user()->calculateNetWorth(),
            ]);
        })->name('net-worth');
    });
});

/**
 * Import/Export Routes
 * Data management functionality
 */
Route::middleware(['auth', 'verified', 'account.status', 'can:export-financial-data'])->group(function () {
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', function () {
            return view('import.index', [
                'title' => 'Import Data',
                'subtitle' => 'Import transactions from banks and other sources',
            ]);
        })->name('index');
    });
    
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/', function () {
            return view('export.index', [
                'title' => 'Export Data',
                'subtitle' => 'Download your financial data',
            ]);
        })->name('index');
    });
});

/**
 * Help and Support Routes
 */
Route::middleware('auth')->group(function () {
    Route::get('/help', function () {
        return view('help.index', [
            'title' => 'Help Center',
        ]);
    })->name('help');
    
    Route::get('/contact', function () {
        return view('help.contact', [
            'title' => 'Contact Support',
        ]);
    })->name('contact');
});

/**
 * Fallback Route
 * Handles 404 errors gracefully
 */
Route::fallback(function () {
    return response()->view('errors.404', [
        'title' => 'Page Not Found',
    ], 404);
});