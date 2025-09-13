<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login Request Validation
 * 
 * This form request handles authentication with comprehensive security features:
 * - Rate limiting to prevent brute force attacks
 * - Account status checking before login
 * - Failed attempt tracking
 * - IP-based security monitoring
 * 
 * Security Features:
 * - Rate limiting (5 attempts per minute per IP+email combination)
 * - Account lockout after too many failed attempts
 * - Comprehensive logging for security audit
 * - Account status verification
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only non-authenticated users can attempt login
        return !$this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email:rfc', // Basic email validation (no DNS check for login)
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
            // Optional device information for security tracking
            'device_name' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Please enter your password.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Normalize email to lowercase
            'email' => strtolower(trim($this->email ?? '')),
            
            // Ensure remember is boolean
            'remember' => filter_var($this->remember ?? false, FILTER_VALIDATE_BOOLEAN),
            
            // Set device name if not provided (useful for API logins)
            'device_name' => $this->device_name ?? $this->userAgent(),
        ]);
    }

    /**
     * Attempt to authenticate the request's credentials.
     * 
     * This method handles the complete authentication process with
     * security checks and rate limiting.
     */
    public function authenticate(): void
    {
        // Check for rate limiting first
        $this->ensureIsNotRateLimited();

        // Get user to perform pre-login checks
        $user = User::where('email', $this->email)->first();

        // Perform account status checks if user exists
        if ($user) {
            $this->checkAccountStatus($user);
        }

        // Attempt authentication
        if (!Auth::attempt($this->credentials(), $this->boolean('remember'))) {
            // Authentication failed
            $this->handleFailedAuthentication($user);
        }

        // Authentication succeeded - clear rate limiter
        RateLimiter::clear($this->throttleKey());

        // Update user login tracking
        if ($user) {
            $user->resetFailedLoginAttempts();
        }

        // Log successful authentication
        \Illuminate\Support\Facades\Log::info('Successful login', [
            'user_id' => Auth::id(),
            'email' => $this->email,
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ]);
    }

    /**
     * Get the authentication credentials from the request.
     */
    public function credentials(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }

    /**
     * Ensure the login request is not rate limited.
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Log the rate limit hit
        \Illuminate\Support\Facades\Log::warning('Login rate limit exceeded', [
            'email' => $this->email,
            'ip' => $this->ip(),
            'seconds_remaining' => $seconds,
        ]);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Check the user's account status before allowing login.
     */
    protected function checkAccountStatus(User $user): void
    {
        // Check if account is locked due to failed login attempts
        if ($user->isLocked()) {
            $minutesRemaining = now()->diffInMinutes($user->locked_until);

            \Illuminate\Support\Facades\Log::warning('Login attempted on locked account', [
                'user_id' => $user->id,
                'email' => $user->email,
                'locked_until' => $user->locked_until,
                'ip' => $this->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => "Account temporarily locked. Try again in {$minutesRemaining} minutes.",
            ]);
        }

        // Check account status
        switch ($user->status) {
            case 'suspended':
                \Illuminate\Support\Facades\Log::warning('Login attempted on suspended account', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $this->ip(),
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Your account has been suspended. Please contact support for assistance.',
                ]);

            case 'inactive':
                \Illuminate\Support\Facades\Log::info('Login attempted on inactive account', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $this->ip(),
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Your account is inactive. Please contact support to reactivate your account.',
                ]);

            case 'active':
                // Account is active, continue with login
                break;

            default:
                // Unknown status - treat as suspended for security
                throw ValidationException::withMessages([
                    'email' => 'Unable to log in to this account. Please contact support.',
                ]);
        }
    }

    /**
     * Handle failed authentication attempt.
     */
    protected function handleFailedAuthentication(?User $user): void
    {
        // Increment rate limiter
        RateLimiter::hit($this->throttleKey());

        // Track failed attempt for the user account
        if ($user) {
            $user->incrementFailedLoginAttempts();
        }

        // Log failed authentication
        \Illuminate\Support\Facades\Log::warning('Failed login attempt', [
            'email' => $this->email,
            'user_exists' => $user !== null,
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ]);

        // Throw validation exception
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Get the rate limiting throttle key for the given request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
    }

    /**
     * Create an API token for successful login.
     * 
     * This method is used for API authentication after successful login.
     */
    public function createApiToken(User $user, string $deviceName = null): string
    {
        $deviceName = $deviceName ?? $this->device_name ?? 'Unknown Device';
        
        // Revoke existing tokens for this device (optional)
        // $user->tokens()->where('name', $deviceName)->delete();
        
        // Create new token
        $token = $user->createToken($deviceName);
        
        // Log token creation
        \Illuminate\Support\Facades\Log::info('API token created', [
            'user_id' => $user->id,
            'device_name' => $deviceName,
            'ip' => $this->ip(),
        ]);
        
        return $token->plainTextToken;
    }

    /**
     * Get security information for logging.
     */
    public function getSecurityContext(): array
    {
        return [
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $this->session()->getId(),
            'device_name' => $this->device_name,
        ];
    }

    /**
     * Check if this is a suspicious login attempt.
     * 
     * This method can be extended to implement additional security checks
     * like geolocation analysis, device fingerprinting, etc.
     */
    public function isSuspicious(): bool
    {
        // Get user if exists
        $user = User::where('email', $this->email)->first();
        
        if (!$user) {
            return false; // Can't be suspicious if user doesn't exist
        }

        // Check if login is from a new IP address
        if ($user->last_login_ip && $user->last_login_ip !== $this->ip()) {
            return true;
        }

        // Check if it's been a long time since last login
        if ($user->last_login_at && $user->last_login_at->lt(now()->subDays(30))) {
            return true;
        }

        // Check unusual hours (optional)
        $hour = now()->hour;
        if ($hour < 6 || $hour > 23) { // Between 11 PM and 6 AM
            return true;
        }

        return false;
    }

    /**
     * Handle post-authentication actions.
     * 
     * This method can be called after successful authentication to
     * perform additional security actions.
     */
    public function handlePostAuthentication(User $user): void
    {
        // Check if this is a suspicious login
        if ($this->isSuspicious()) {
            // Send security notification email
            // $user->notify(new SuspiciousLoginNotification($this->getSecurityContext()));
            
            // Log suspicious login
            \Illuminate\Support\Facades\Log::warning('Suspicious login detected', [
                'user_id' => $user->id,
                'email' => $user->email,
                'context' => $this->getSecurityContext(),
            ]);
        }

        // Update session security
        $this->session()->regenerate();

        // Store security context in session
        $this->session()->put('login_context', $this->getSecurityContext());
    }
}