<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure Email Is Verified Middleware
 * 
 * This middleware ensures that users have verified their email address
 * before accessing financial features. This is crucial for security!
 * 
 * Why email verification matters for finance apps:
 * - Prevents unauthorized access to financial data
 * - Ensures we can send important security notifications
 * - Required for password reset functionality
 * - Builds trust with users about security practices
 */
class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $redirectToRoute = null): Response
    {
        // If user is not authenticated, let other middleware handle it
        if (!$request->user()) {
            return $next($request);
        }

        // Check if user model implements MustVerifyEmail interface
        if (!$request->user() instanceof MustVerifyEmail) {
            return $next($request);
        }

        // Check if email is already verified
        if ($request->user()->hasVerifiedEmail()) {
            return $next($request);
        }

        // Email is not verified - handle the response

        // For API requests, return JSON error
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your email address is not verified.',
                'error' => 'email_not_verified',
                'verification_url' => $this->getVerificationUrl($request),
                'instructions' => 'Please check your email and click the verification link to access your financial data.',
            ], 409); // 409 Conflict status code
        }

        // For web requests, redirect to email verification notice
        return $redirectToRoute && $request->route() && $request->route()->getName() !== $redirectToRoute
            ? redirect()->guest(route($redirectToRoute))
            : Redirect::guest(
                URL::route($redirectToRoute ?: 'verification.notice')
            );
    }

    /**
     * Get the email verification URL
     */
    private function getVerificationUrl(Request $request): string
    {
        // If this is an API request, return the API verification endpoint
        if ($request->expectsJson()) {
            return route('verification.send');
        }

        // For web requests, return the verification notice page
        return route('verification.notice');
    }
}