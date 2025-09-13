<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Registration Controller
 * 
 * Handles user registration for our personal finance application.
 * This is the entry point for new users to create their accounts.
 * 
 * Security Features:
 * - Strong password validation
 * - Email uniqueness checking
 * - Rate limiting to prevent spam registration
 * - Automatic email verification setup
 * - Secure password hashing
 * - Input sanitization and validation
 */
class RegisterController extends Controller
{
    /**
     * Where to redirect users after registration.
     * 
     * After successful registration, users are redirected here.
     * We redirect to email verification since financial apps require verified emails.
     */
    protected string $redirectTo = '/email/verify';

    /**
     * Create a new controller instance.
     * 
     * We apply the 'guest' middleware to ensure only non-authenticated users
     * can access registration routes.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the registration form.
     * 
     * This displays the user registration page with the signup form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'title' => 'Create Your Account',
            'subtitle' => 'Start managing your finances today',
        ]);
    }

    /**
     * Handle a registration request for the application.
     * 
     * This is the main registration logic that:
     * 1. Validates the input data
     * 2. Creates the user account
     * 3. Logs them in
     * 4. Sends verification email
     * 5. Redirects to appropriate page
     */
    public function register(Request $request)
    {
        // Validate the registration data
        $this->validator($request->all())->validate();

        // Create the user account
        $user = $this->create($request->all());

        // Log the registration attempt for security audit
        Log::info('User registration attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Fire the Registered event (triggers email verification, default categories, etc.)
        event(new Registered($user));

        // Automatically log the user in after successful registration
        Auth::login($user);

        // Log successful registration
        Log::info('User successfully registered and logged in', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        // Handle different response types
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Registration successful! Please verify your email address.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'email_verified' => false,
                ],
                'redirect_url' => $this->redirectTo,
            ], 201);
        }

        // For web requests, redirect with success message
        return redirect($this->redirectTo)
               ->with('success', 'Welcome! Please check your email to verify your account.')
               ->with('registered_email', $user->email);
    }

    /**
     * Get a validator for an incoming registration request.
     * 
     * This defines all the validation rules for user registration.
     * Strong validation is crucial for financial applications!
     */
    protected function validator(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, [
            // First name validation
            'first_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\'\.]+$/', // Only letters, spaces, hyphens, apostrophes, periods
            ],
            
            // Last name validation
            'last_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\'\.]+$/',
            ],
            
            // Email validation
            'email' => [
                'required',
                'string',
                'email:rfc,dns', // Strict email validation with DNS checking
                'max:255',
                'unique:users,email', // Ensure email is unique
            ],
            
            // Password validation with strict rules for financial security
            'password' => [
                'required',
                'confirmed', // Must match password_confirmation field
                Password::min(8) // Minimum 8 characters
                    ->letters() // Must contain letters
                    ->mixedCase() // Must contain both upper and lower case
                    ->numbers() // Must contain numbers
                    ->symbols() // Must contain symbols
                    ->uncompromised(), // Check against known compromised passwords
            ],
            
            // Phone number (optional but validated if provided)
            'phone' => [
                'nullable',
                'string',
                'regex:/^[\+]?[1-9][\d\s\-\(\)]{7,15}$/', // Basic international phone format
            ],
            
            // Date of birth (optional but useful for financial planning)
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today', // Must be in the past
                'after:' . now()->subYears(120)->toDateString(), // Reasonable age limit
            ],
            
            // Timezone (optional, defaults to UTC)
            'timezone' => [
                'nullable',
                'string',
                'timezone', // Must be a valid timezone
            ],
            
            // Currency preference (optional, defaults to USD)
            'preferred_currency' => [
                'nullable',
                'string',
                'size:3', // Must be 3-character currency code
                'regex:/^[A-Z]{3}$/', // Must be uppercase letters
            ],
            
            // Terms of service acceptance (required for legal compliance)
            'terms_accepted' => [
                'required',
                'accepted', // Must be true, 1, 'yes', 'on'
            ],
            
            // Privacy policy acceptance
            'privacy_accepted' => [
                'required',
                'accepted',
            ],
            
        ], [
            // Custom error messages for better user experience
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, and apostrophes.',
            'email.unique' => 'This email address is already registered. Please use a different email or try logging in.',
            'phone.regex' => 'Please enter a valid phone number.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'date_of_birth.after' => 'Please enter a valid date of birth.',
            'preferred_currency.regex' => 'Currency must be a valid 3-letter code (e.g., USD, EUR, GBP).',
            'terms_accepted.accepted' => 'You must accept the Terms of Service to create an account.',
            'privacy_accepted.accepted' => 'You must accept the Privacy Policy to create an account.',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     * 
     * This method creates the actual user record in the database
     * with properly hashed password and default settings.
     */
    protected function create(array $data): User
    {
        // Prepare user data with secure defaults
        $userData = [
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => strtolower(trim($data['email'])), // Normalize email to lowercase
            'password' => Hash::make($data['password']), // Hash the password securely
            
            // Optional fields with defaults
            'phone' => isset($data['phone']) ? trim($data['phone']) : null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'timezone' => $data['timezone'] ?? config('app.timezone', 'UTC'),
            'preferred_currency' => $data['preferred_currency'] ?? 'USD',
            'locale' => app()->getLocale(),
            
            // Financial settings with sensible defaults
            'budget_alerts_enabled' => true, // Enable budget alerts by default
            'bill_reminders_enabled' => true, // Enable bill reminders by default
            'bill_reminder_days' => 3, // Remind 3 days before bills are due
            'low_balance_threshold' => 100.00, // Alert when account balance < $100
            
            // Account status
            'status' => 'active', // New accounts are active by default
        ];

        // Create and return the user
        return User::create($userData);
    }

    /**
     * Handle registration via API
     * 
     * This method is specifically for API registration requests
     * (like from mobile apps).
     */
    public function apiRegister(Request $request)
    {
        // Use the same validation and creation logic
        $this->validator($request->all())->validate();
        $user = $this->create($request->all());

        // Fire the registration event
        event(new Registered($user));

        // For API, we don't automatically log the user in
        // They need to log in separately (or we could issue a token)

        return response()->json([
            'message' => 'Registration successful! Please check your email to verify your account.',
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'email_verified' => false,
            ],
            'next_step' => 'Please verify your email address before logging in.',
        ], 201);
    }

    /**
     * Check if an email is already registered
     * 
     * This is useful for frontend validation to show immediate feedback
     * when users are typing their email address.
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = User::where('email', strtolower($request->email))->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists 
                ? 'This email is already registered.' 
                : 'This email is available.',
        ]);
    }

    /**
     * Get registration statistics (for admin/monitoring)
     * 
     * This helps track registration trends and identify any issues.
     */
    public function getRegistrationStats()
    {
        // Only allow admin access (you'd check permissions here)
        // if (!auth()->user()?->isAdmin()) {
        //     abort(403);
        // }

        $stats = [
            'total_users' => User::count(),
            'users_today' => User::whereDate('created_at', today())->count(),
            'users_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
            'active_users' => User::where('status', 'active')->count(),
        ];

        return response()->json($stats);
    }
}