<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login Controller
 * 
 * Handles user authentication for our personal finance application.
 * Provides secure login with rate limiting, account status checking,
 * and comprehensive security logging.
 * 
 * Security Features:
 * - Rate limiting to prevent brute force attacks
 * - Account lockout after too many failed attempts
 * - IP-based tracking and logging
 * - Account status verification
 * - Session security management
 * - Remember me functionality
 */
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     * 
     * After successful login, users go to the main dashboard
     * where they can see their financial overview.
     */
    protected string $redirectTo = '/dashboard';

    /**
     * Maximum login attempts before lockout.
     * 
     * After this many failed attempts, the user's account will be
     * temporarily locked for security.
     */
    protected int $maxAttempts = 5;

    /**
     * Number of minutes to lock account after max attempts.
     */
    protected int $decayMinutes = 15;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Show the application's login form.
     */
    public function showLoginForm()
    {
        return view('auth.login', [
            'title' => 'Sign In to Your Account',
            'subtitle' => 'Access your personal finance dashboard',
        ]);
    }

    /**
     * Handle a login request to the application.
     * 
     * This overrides the default Laravel login to add our custom
     * security checks and logging.
     */
    public function login(Request $request)
    {
        // Validate the login request
        $this->validateLogin($request);

        // Check if too many login attempts from this IP
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        // Get user to check account status before attempting login
        $user = User::where('email', $request->email)->first();

        // Check if user exists and account status allows login
        if ($user) {
            $statusCheck = $this->checkAccountStatus($user);
            if ($statusCheck !== true) {
                return $statusCheck; // Return the error response
            }
        }

        // Attempt to authenticate
        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        }

        // Authentication failed - increment attempts
        $this->incrementLoginAttempts($request);

        // Track failed login attempt
        if ($user) {
            $user->incrementFailedLoginAttempts();
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     */
    protected function validateLogin(Request $request): void
    {
        $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'min:1', // At least something entered
            ],
            'remember' => [
                'nullable',
                'boolean',
            ],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Please enter your password.',
        ]);
    }

    /**
     * Check if the user's account status allows login.
     * 
     * Returns true if login is allowed, or a response if blocked.
     */
    protected function checkAccountStatus(User $user)
    {
        // Check if account is locked due to failed login attempts
        if ($user->isLocked()) {
            $minutesRemaining = now()->diffInMinutes($user->locked_until);
            
            Log::warning('Login attempted on locked account', [
                'user_id' => $user->id,
                'email' => $user->email,
                'locked_until' => $user->locked_until,
                'ip' => request()->ip(),
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => "Account temporarily locked. Try again in {$minutesRemaining} minutes.",
                    'error' => 'account_locked',
                    'minutes_remaining' => $minutesRemaining,
                ], 423);
            }

            return back()->withErrors([
                'email' => "Your account is temporarily locked due to too many failed login attempts. Please try again in {$minutesRemaining} minutes.",
            ])->withInput(request()->except('password'));
        }

        // Check account status
        switch ($user->status) {
            case 'suspended':
                Log::warning('Login attempted on suspended account', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => request()->ip(),
                ]);

                if (request()->expectsJson()) {
                    return response()->json([
                        'message' => 'Account suspended. Please contact support.',
                        'error' => 'account_suspended',
                    ], 403);
                }

                return back()->withErrors([
                    'email' => 'Your account has been suspended. Please contact support for assistance.',
                ])->withInput(request()->except('password'));

            case 'inactive':
                Log::info('Login attempted on inactive account', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => request()->ip(),
                ]);

                if (request()->expectsJson()) {
                    return response()->json([
                        'message' => 'Account inactive. Please contact support to reactivate.',
                        'error' => 'account_inactive',
                    ], 403);
                }

                return back()->withErrors([
                    'email' => 'Your account is inactive. Please contact support to reactivate your account.',
                ])->withInput(request()->except('password'));

            case 'active':
                // Account is active, proceed with login
                return true;

            default:
                // Unknown status - treat as suspended
                return $this->checkAccountStatus((object) array_merge($user->toArray(), ['status' => 'suspended']));
        }
    }

    /**
     * Attempt to log the user into the application.
     * 
     * This handles the actual authentication logic.
     */
    protected function attemptLogin(Request $request): bool
    {
        $credentials = $this->credentials($request);

        // Use Laravel's built-in authentication
        $remember = $request->filled('remember');

        return Auth::attempt($credentials, $remember);
    }

    /**
     * Get the needed authorization credentials from the request.
     */
    protected function credentials(Request $request): array
    {
        return [
            'email' => strtolower($request->email), // Normalize email
            'password' => $request->password,
        ];
    }

    /**
     * Send the response after the user was authenticated.
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $user = Auth::user();

        // Update last login tracking
        $user->resetFailedLoginAttempts();

        // Log successful login
        Log::info('User successfully logged in', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($response = $this->authenticated($request, $user)) {
            return $response;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login successful.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'email_verified' => $user->hasVerifiedEmail(),
                ],
                'redirect_url' => $this->redirectPath(),
            ]);
        }

        return redirect()->intended($this->redirectPath())
               ->with('success', 'Welcome back, ' . $user->first_name . '!');
    }

    /**
     * The user has been authenticated.
     * 
     * This method is called after successful authentication.
     * We can add custom logic here if needed.
     */
    protected function authenticated(Request $request, $user)
    {
        // Check if user needs to verify their email
        if (!$user->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please verify your email address to access all features.',
                    'email_verified' => false,
                    'verification_url' => route('verification.notice'),
                ], 200);
            }

            // For web requests, they'll be redirected by the email verification middleware
        }

        // You could add other checks here like:
        // - Force password change if old
        // - Check for required profile completion
        // - Handle first-time login setup
        
        return null; // Continue with default behavior
    }

    /**
     * Get the failed login response instance.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        Log::warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'errors' => [
                    'email' => ['These credentials do not match our records.'],
                ],
            ], 422);
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Log the logout
        Log::info('User logged out', [
            'user_id' => $user?->id,
            'email' => $user?->email,
            'ip' => $request->ip(),
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Successfully logged out.',
            ]);
        }

        return redirect('/')
               ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Get the rate limiting throttle key for the given request.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function checkTooManyFailedAttempts(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), $this->maxAttempts)) {
            return;
        }

        event(new \Illuminate\Auth\Events\Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => [trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ]);
    }

    /**
     * Increment the login attempts for the user.
     */
    protected function incrementLoginAttempts(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), $this->decayMinutes * 60);
    }

    /**
     * Clear the login locks for the given user credentials.
     */
    protected function clearLoginAttempts(Request $request): void
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    /**
     * API Login method for mobile applications
     */
    public function apiLogin(Request $request)
    {
        $this->validateLogin($request);

        $user = User::where('email', strtolower($request->email))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'error' => 'invalid_credentials',
            ], 401);
        }

        // Check account status
        $statusCheck = $this->checkAccountStatus($user);
        if ($statusCheck !== true) {
            return $statusCheck;
        }

        // Create API token for the user
        $token = $user->createToken('mobile-app')->plainTextToken;

        // Update login tracking
        $user->resetFailedLoginAttempts();

        return response()->json([
            'message' => 'Login successful.',
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}