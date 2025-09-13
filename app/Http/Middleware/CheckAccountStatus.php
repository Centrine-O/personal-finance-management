<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check Account Status Middleware
 * 
 * This middleware ensures that users with locked, suspended, or inactive accounts
 * cannot access protected routes even if they have valid sessions.
 * 
 * Security Features:
 * - Blocks locked accounts (too many failed login attempts)
 * - Blocks suspended accounts (admin action)
 * - Blocks inactive accounts (user deactivated)
 * - Forces logout for invalid account states
 * - Redirects to appropriate error pages with helpful messages
 */
class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Check if account is locked due to failed login attempts
        if ($user->isLocked()) {
            return $this->handleLockedAccount($request, $user);
        }

        // Check account status
        switch ($user->status) {
            case 'suspended':
                return $this->handleSuspendedAccount($request, $user);
            
            case 'inactive':
                return $this->handleInactiveAccount($request, $user);
            
            case 'active':
                // Account is active, allow access
                return $next($request);
            
            default:
                // Unknown status - treat as suspended for security
                return $this->handleSuspendedAccount($request, $user);
        }
    }

    /**
     * Handle locked account (too many failed login attempts)
     */
    private function handleLockedAccount(Request $request, $user): Response
    {
        // Log the attempt to access with locked account
        \Illuminate\Support\Facades\Log::warning('Locked account attempted access', [
            'user_id' => $user->id,
            'email' => $user->email,
            'locked_until' => $user->locked_until,
            'ip' => $request->ip(),
            'route' => $request->route()?->getName(),
        ]);

        // Logout the user to clear their session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Calculate time remaining until unlock
        $minutesRemaining = now()->diffInMinutes($user->locked_until);

        // Handle API requests differently
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Account temporarily locked due to too many failed login attempts.',
                'error' => 'account_locked',
                'unlock_time' => $user->locked_until->toISOString(),
                'minutes_remaining' => $minutesRemaining,
            ], 423); // 423 Locked status code
        }

        // For web requests, redirect to login with error message
        return redirect()->route('login')
                        ->withErrors(['email' => "Your account is temporarily locked due to too many failed login attempts. Please try again in {$minutesRemaining} minutes."])
                        ->with('account_status', 'locked')
                        ->with('unlock_time', $user->locked_until);
    }

    /**
     * Handle suspended account (admin action)
     */
    private function handleSuspendedAccount(Request $request, $user): Response
    {
        // Log the attempt to access with suspended account
        \Illuminate\Support\Facades\Log::warning('Suspended account attempted access', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'route' => $request->route()?->getName(),
        ]);

        // Logout the user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Handle API requests
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your account has been suspended. Please contact support for assistance.',
                'error' => 'account_suspended',
                'support_email' => config('app.support_email', 'support@personalfinance.local'),
            ], 403); // 403 Forbidden
        }

        // For web requests, redirect to a suspension notice page
        return redirect()->route('account.suspended')
                        ->with('account_status', 'suspended');
    }

    /**
     * Handle inactive account (user deactivated)
     */
    private function handleInactiveAccount(Request $request, $user): Response
    {
        // Log the attempt
        \Illuminate\Support\Facades\Log::info('Inactive account attempted access', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'route' => $request->route()?->getName(),
        ]);

        // Logout the user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Handle API requests
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your account is inactive. Please contact support to reactivate your account.',
                'error' => 'account_inactive',
                'support_email' => config('app.support_email', 'support@personalfinance.local'),
            ], 403);
        }

        // For web requests, redirect to reactivation page
        return redirect()->route('account.inactive')
                        ->with('account_status', 'inactive')
                        ->with('user_email', $user->email);
    }
}