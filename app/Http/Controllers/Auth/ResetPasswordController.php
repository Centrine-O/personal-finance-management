<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * Reset Password Controller
 * 
 * Handles the actual password reset process for our personal finance application.
 * This is the second step after users receive the password reset email.
 * 
 * Security Features:
 * - Strong password validation for financial security
 * - Token validation and expiration checking
 * - Account status verification
 * - Automatic session invalidation
 * - Comprehensive audit logging
 * - Failed attempt tracking
 */
class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     * 
     * After successful password reset, redirect to login page with success message
     */
    protected string $redirectTo = '/login';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
        
        // Rate limit password reset attempts
        $this->middleware('throttle:5,1')->only('reset'); // 5 attempts per minute
    }

    /**
     * Display the password reset view for the given token.
     * 
     * If no token is present, redirect to the link request form.
     */
    public function showResetForm(Request $request, $token = null)
    {
        if (!$token) {
            return redirect()->route('password.request')
                   ->withErrors(['token' => 'Password reset token is required.']);
        }

        // Validate that the token exists and hasn't expired
        $tokenExists = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('token', $token)
            ->where('created_at', '>', now()->subHours(1)) // 1 hour expiration
            ->exists();

        if (!$tokenExists) {
            Log::warning('Invalid or expired password reset token accessed', [
                'token' => $token,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('password.request')
                   ->withErrors(['token' => 'This password reset token is invalid or has expired.'])
                   ->with('token_expired', true);
        }

        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email,
            'title' => 'Reset Your Password',
            'subtitle' => 'Enter your new password below',
        ]);
    }

    /**
     * Reset the given user's password.
     * 
     * This method handles the actual password reset process with comprehensive
     * security checks and logging.
     */
    public function reset(Request $request)
    {
        // Validate the reset request
        $this->validateReset($request);

        // Find the user before attempting reset
        $user = User::where('email', strtolower($request->email))->first();

        if ($user) {
            // Perform pre-reset security checks
            $securityCheck = $this->performPreResetChecks($user, $request);
            if ($securityCheck !== true) {
                return $securityCheck;
            }
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), 
            function ($user, $password) use ($request) {
                $this->resetPassword($user, $password, $request);
            }
        );

        // Return the response based on the result
        return $response == Password::PASSWORD_RESET
                    ? $this->sendResetResponse($request, $response)
                    : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * Validate the password reset request.
     */
    protected function validateReset(Request $request): void
    {
        $request->validate([
            'token' => [
                'required',
                'string',
                'size:64', // Laravel tokens are 64 characters
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
            ],
            'password' => [
                'required',
                'confirmed',
                // Strong password requirements for financial security
                PasswordRule::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ], [
            'token.required' => 'Password reset token is required.',
            'token.size' => 'Invalid password reset token format.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);
    }

    /**
     * Get the password reset credentials from the request.
     */
    protected function credentials(Request $request): array
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    /**
     * Perform security checks before resetting password
     */
    protected function performPreResetChecks(User $user, Request $request)
    {
        // Check if account is suspended
        if ($user->status === 'suspended') {
            Log::warning('Password reset attempted on suspended account', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unable to reset password. Please contact support.',
                    'error' => 'account_suspended',
                ], 403);
            }

            return back()->withErrors([
                'email' => 'Unable to reset password for this account. Please contact support.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        // Check for too many recent reset attempts
        $recentResets = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->where('created_at', '>', now()->subMinutes(10))
            ->count();

        if ($recentResets > 3) {
            Log::warning('Too many password reset attempts', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many password reset attempts. Please wait before trying again.',
                    'error' => 'too_many_attempts',
                ], 429);
            }

            throw ValidationException::withMessages([
                'email' => ['Too many password reset attempts. Please wait a few minutes before trying again.'],
            ]);
        }

        return true;
    }

    /**
     * Reset the given user's password.
     * 
     * This method is called when the password reset is successful.
     * It handles updating the user's password and performing cleanup.
     */
    protected function resetPassword(User $user, string $password, Request $request): void
    {
        // Log the password reset before making changes
        Log::info('Password reset initiated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Update the user's password
        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ]);

        // Unlock account if it was locked (password reset unlocks account)
        if ($user->isLocked()) {
            $user->unlockAccount();
            Log::info('Account unlocked via password reset', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        // Reset failed login attempts
        $user->failed_login_attempts = 0;
        
        // Save the changes
        $user->save();

        // Fire the password reset event
        event(new PasswordReset($user));

        // Invalidate all existing sessions for this user (security measure)
        $this->invalidateUserSessions($user);

        Log::info('Password reset completed successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);
    }

    /**
     * Invalidate all existing sessions for a user
     * 
     * This is a security measure to ensure that if someone had unauthorized
     * access to the account, they are logged out when password is reset.
     */
    protected function invalidateUserSessions(User $user): void
    {
        // This would require additional session management
        // For now, we'll just invalidate API tokens if using Sanctum
        
        if (method_exists($user, 'tokens')) {
            // Revoke all API tokens
            $user->tokens()->delete();
            
            Log::info('All API tokens revoked for user', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        // For web sessions, you'd need to implement additional logic
        // to invalidate sessions stored in database or cache
    }

    /**
     * Get the response for a successful password reset.
     */
    protected function sendResetResponse(Request $request, $response)
    {
        Log::info('Password reset response sent', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'success' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your password has been reset successfully. You can now log in with your new password.',
                'status' => 'password_reset',
                'login_url' => route('login'),
            ]);
        }

        return redirect()->route('login')
               ->with('success', 'Your password has been reset successfully. You can now log in with your new password.')
               ->with('password_reset', true);
    }

    /**
     * Get the response for a failed password reset.
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        Log::warning('Password reset failed', [
            'email' => $request->email,
            'response' => $response,
            'ip' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unable to reset password. Please check your information and try again.',
                'error' => 'reset_failed',
                'details' => trans($response),
            ], 422);
        }

        return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => trans($response)]);
    }

    /**
     * Get the broker to be used during password reset.
     */
    public function broker(): \Illuminate\Contracts\Auth\PasswordBroker
    {
        return Password::broker();
    }

    /**
     * Check if a password reset token is valid (API endpoint)
     */
    public function checkToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $tokenRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', strtolower($request->email))
            ->where('created_at', '>', now()->subHours(1))
            ->first();

        $isValid = $tokenRecord && Hash::check($request->token, $tokenRecord->token);

        return response()->json([
            'valid' => $isValid,
            'expired' => $tokenRecord && now()->diffInHours($tokenRecord->created_at) >= 1,
            'message' => $isValid 
                ? 'Token is valid.' 
                : 'Token is invalid or expired.',
        ]);
    }

    /**
     * API endpoint for password reset
     */
    public function apiReset(Request $request)
    {
        // Same validation and logic as web reset
        return $this->reset($request);
    }
}