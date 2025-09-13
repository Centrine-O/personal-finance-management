<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * Forgot Password Controller
 * 
 * Handles password reset email sending for our personal finance application.
 * This is the first step in the password reset process.
 * 
 * Security Features:
 * - Rate limiting to prevent email spam
 * - Account status checking before sending emails
 * - Comprehensive logging for security audit
 * - Generic responses to prevent email enumeration
 * - Token expiration for security
 */
class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
        
        // Add rate limiting specifically for password reset requests
        $this->middleware('throttle:5,1')->only('sendResetLinkEmail'); // 5 attempts per minute
    }

    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email', [
            'title' => 'Reset Your Password',
            'subtitle' => 'Enter your email to receive a password reset link',
        ]);
    }

    /**
     * Send a reset link to the given user.
     * 
     * This method overrides the default behavior to add security checks
     * and comprehensive logging for financial application security.
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email
        $this->validateEmail($request);

        $email = strtolower($request->email);

        // Find the user (but don't reveal if they exist or not)
        $user = User::where('email', $email)->first();

        // Log the password reset request
        Log::info('Password reset requested', [
            'email' => $email,
            'user_exists' => $user !== null,
            'user_id' => $user?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Perform security checks if user exists
        if ($user) {
            $securityCheck = $this->performSecurityChecks($user, $request);
            if ($securityCheck !== true) {
                return $securityCheck;
            }
        }

        // We send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        // Always return the same response to prevent user enumeration
        // This prevents attackers from knowing if an email exists or not
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'If that email address exists in our system, you will receive a password reset link shortly.',
                'status' => 'reset_link_sent',
            ]);
        }

        return back()->with('status', 
            'If that email address exists in our system, you will receive a password reset link shortly.'
        );
    }

    /**
     * Validate the email for the given request.
     */
    protected function validateEmail(Request $request): void
    {
        $request->validate([
            'email' => [
                'required',
                'string',
                'email:rfc,dns', // Strict email validation
                'max:255',
            ],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address is too long.',
        ]);
    }

    /**
     * Get the needed authentication credentials from the request.
     */
    protected function credentials(Request $request): array
    {
        return [
            'email' => strtolower($request->email),
        ];
    }

    /**
     * Perform security checks before sending password reset email
     */
    protected function performSecurityChecks(User $user, Request $request)
    {
        // Check if account is suspended
        if ($user->status === 'suspended') {
            Log::warning('Password reset attempted on suspended account', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            // Don't reveal account is suspended - return generic message
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'If that email address exists in our system, you will receive a password reset link shortly.',
                    'status' => 'reset_link_sent',
                ]);
            }

            return back()->with('status', 
                'If that email address exists in our system, you will receive a password reset link shortly.'
            );
        }

        // Check if account is locked (but still allow password reset)
        if ($user->isLocked()) {
            Log::info('Password reset requested for locked account', [
                'user_id' => $user->id,
                'email' => $user->email,
                'locked_until' => $user->locked_until,
                'ip' => $request->ip(),
            ]);

            // Allow password reset for locked accounts
            // This is actually helpful for users who forgot their password
        }

        // Check if too many recent password reset requests from this user
        $recentResetAttempts = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();

        if ($recentResetAttempts >= 3) {
            Log::warning('Too many password reset attempts from user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many password reset attempts. Please wait before requesting another.',
                    'error' => 'too_many_attempts',
                ], 429);
            }

            throw ValidationException::withMessages([
                'email' => ['Too many password reset attempts. Please wait a few minutes before requesting another.'],
            ]);
        }

        // All security checks passed
        return true;
    }

    /**
     * Get the broker to be used during password reset.
     */
    public function broker(): \Illuminate\Contracts\Auth\PasswordBroker
    {
        return Password::broker();
    }

    /**
     * Get the response for a successful password reset link.
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Password reset link sent successfully.',
                'status' => 'reset_link_sent',
            ]);
        }

        return back()->with('status', trans($response));
    }

    /**
     * Get the response for a failed password reset link.
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        // Log the failure (but don't reveal why it failed)
        Log::warning('Password reset link send failed', [
            'email' => $request->email,
            'response' => $response,
            'ip' => $request->ip(),
        ]);

        // Always return generic message to prevent enumeration
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'If that email address exists in our system, you will receive a password reset link shortly.',
                'status' => 'reset_link_sent',
            ]);
        }

        return back()->with('status', 
            'If that email address exists in our system, you will receive a password reset link shortly.'
        );
    }

    /**
     * Check password reset status (for API clients)
     */
    public function checkResetStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $hasRecentReset = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', strtolower($request->email))
            ->where('created_at', '>', now()->subHour())
            ->exists();

        return response()->json([
            'has_recent_reset_request' => $hasRecentReset,
            'message' => $hasRecentReset 
                ? 'A password reset request was sent recently.' 
                : 'No recent password reset request found.',
        ]);
    }

    /**
     * Cancel a password reset request
     */
    public function cancelReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower($request->email);
        $user = User::where('email', $email)->first();

        if ($user) {
            // Delete any pending password reset tokens
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            Log::info('Password reset cancelled', [
                'user_id' => $user->id,
                'email' => $email,
                'ip' => $request->ip(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Password reset request cancelled.',
            ]);
        }

        return redirect()->route('login')
               ->with('success', 'Password reset request cancelled.');
    }
}