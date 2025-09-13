<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Email Verification Controller
 * 
 * Handles email verification for our personal finance application.
 * Email verification is crucial for financial apps to ensure:
 * - Security notifications can be sent reliably
 * - Password reset functionality works
 * - User owns the email address they registered with
 * - Compliance with financial regulations
 * 
 * Security Features:
 * - Signed URL verification to prevent tampering
 * - Rate limiting to prevent abuse
 * - Comprehensive audit logging
 * - Account status checking
 * - Automatic redirect after verification
 */
class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     * 
     * After email verification, users can access their full dashboard
     */
    protected string $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify'); // Signed URL for security
        $this->middleware('throttle:6,1')->only('verify', 'resend'); // Rate limiting
    }

    /**
     * Show the email verification notice.
     * 
     * This page is shown to users who need to verify their email address
     */
    public function show(Request $request)
    {
        // If already verified, redirect to dashboard
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectPath())
                   ->with('info', 'Your email is already verified.');
        }

        return view('auth.verify', [
            'title' => 'Verify Your Email Address',
            'subtitle' => 'Check your email for a verification link',
            'user' => $request->user(),
        ]);
    }

    /**
     * Mark the authenticated user's email address as verified.
     * 
     * This method is called when users click the verification link in their email
     */
    public function verify(Request $request)
    {
        // Get the user ID from the signed URL
        $userId = $request->route('id');
        $user = User::findOrFail($userId);

        // Verify the URL signature and hash
        if (! URL::hasValidSignature($request)) {
            Log::warning('Invalid email verification signature', [
                'user_id' => $userId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            throw new AuthorizationException('Invalid verification link.');
        }

        // Check if the authenticated user matches the URL user
        if ($request->user()->getKey() !== $user->getKey()) {
            Log::warning('Email verification attempt for different user', [
                'authenticated_user' => $request->user()->id,
                'url_user' => $userId,
                'ip' => $request->ip(),
            ]);

            throw new AuthorizationException('You can only verify your own email address.');
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            Log::info('Already verified email verification attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Email address is already verified.',
                    'verified' => true,
                ]);
            }

            return redirect($this->redirectPath())
                   ->with('info', 'Your email address is already verified.');
        }

        // Perform account status checks
        $statusCheck = $this->checkAccountStatus($user, $request);
        if ($statusCheck !== true) {
            return $statusCheck;
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            // Log successful verification
            Log::info('Email successfully verified', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Fire the verified event
            event(new Verified($user));

            // Unlock account if it was locked due to unverified email
            if ($user->isLocked()) {
                $user->unlockAccount();
                Log::info('Account unlocked after email verification', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Email verified successfully! You now have full access to your account.',
                'verified' => true,
                'redirect_url' => $this->redirectPath(),
            ]);
        }

        return redirect($this->redirectPath())
               ->with('success', 'Email verified successfully! You now have full access to your personal finance dashboard.')
               ->with('email_verified', true);
    }

    /**
     * Resend the email verification notification.
     * 
     * This allows users to request a new verification email if they didn't receive it
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Email address is already verified.',
                    'verified' => true,
                ], 200);
            }

            return redirect($this->redirectPath())
                   ->with('info', 'Your email address is already verified.');
        }

        // Perform account status checks
        $statusCheck = $this->checkAccountStatus($user, $request);
        if ($statusCheck !== true) {
            return $statusCheck;
        }

        // Check rate limiting - prevent spam
        $recentSends = \Illuminate\Support\Facades\Cache::get("verification_emails:{$user->id}", 0);
        if ($recentSends >= 3) {
            Log::warning('Too many verification email requests', [
                'user_id' => $user->id,
                'email' => $user->email,
                'recent_sends' => $recentSends,
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many verification emails sent. Please wait before requesting another.',
                    'error' => 'rate_limited',
                ], 429);
            }

            return back()->withErrors([
                'email' => 'Too many verification emails sent. Please wait a few minutes before requesting another.',
            ]);
        }

        // Send the verification email
        $user->sendEmailVerificationNotification();

        // Track the send in cache (expires after 10 minutes)
        $newCount = $recentSends + 1;
        \Illuminate\Support\Facades\Cache::put("verification_emails:{$user->id}", $newCount, 600);

        // Log the resend request
        Log::info('Email verification resent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'send_count' => $newCount,
            'ip' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Verification email sent! Please check your email and click the verification link.',
                'email_sent' => true,
            ]);
        }

        return back()->with('success', 'Verification email sent! Please check your email and click the verification link.');
    }

    /**
     * Check account status before verification operations
     */
    protected function checkAccountStatus(User $user, Request $request)
    {
        // Check if account is suspended
        if ($user->status === 'suspended') {
            Log::warning('Email verification attempted on suspended account', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Account suspended. Please contact support.',
                    'error' => 'account_suspended',
                ], 403);
            }

            return redirect()->route('account.suspended')
                   ->with('account_status', 'suspended');
        }

        // Check if account is inactive
        if ($user->status === 'inactive') {
            Log::info('Email verification attempted on inactive account', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Account inactive. Please contact support to reactivate.',
                    'error' => 'account_inactive',
                ], 403);
            }

            return redirect()->route('account.inactive')
                   ->with('account_status', 'inactive');
        }

        return true;
    }

    /**
     * Get verification status for API clients
     */
    public function status(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'email_verified' => $user->hasVerifiedEmail(),
            'email' => $user->email,
            'verification_sent_at' => $user->email_verified_at,
            'can_resend' => !$user->hasVerifiedEmail(),
            'verification_url' => $user->hasVerifiedEmail() ? null : route('verification.notice'),
        ]);
    }

    /**
     * Update email address (requires re-verification)
     * 
     * This allows users to change their email address but requires
     * verification of the new address
     */
    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email,' . $request->user()->id,
            ],
            'password' => [
                'required',
                'string',
                'current_password', // Must enter current password for security
            ],
        ]);

        $user = $request->user();
        $oldEmail = $user->email;
        $newEmail = strtolower($request->email);

        // Log the email change attempt
        Log::info('Email change initiated', [
            'user_id' => $user->id,
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
            'ip' => $request->ip(),
        ]);

        // Update the email and reset verification
        $user->update([
            'email' => $newEmail,
            'email_verified_at' => null, // Require re-verification
        ]);

        // Send verification email to new address
        $user->sendEmailVerificationNotification();

        // Log the successful change
        Log::info('Email changed successfully', [
            'user_id' => $user->id,
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
            'ip' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Email updated successfully. Please verify your new email address.',
                'new_email' => $newEmail,
                'verification_required' => true,
            ]);
        }

        return redirect()->route('verification.notice')
               ->with('success', 'Email updated successfully. Please check your new email address for a verification link.')
               ->with('email_changed', true);
    }

    /**
     * Get the redirect path after verification
     */
    protected function redirectPath(): string
    {
        return $this->redirectTo;
    }
}