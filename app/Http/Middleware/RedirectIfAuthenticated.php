<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect If Authenticated Middleware
 * 
 * This middleware redirects users who are already logged in away from
 * authentication pages (like login, register, password reset).
 * 
 * Why this is needed:
 * - Prevents logged-in users from seeing login forms
 * - Improves UX by redirecting to dashboard
 * - Prevents confusion and duplicate sessions
 */
class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Check each authentication guard
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            // If user is authenticated in this guard
            if (Auth::guard($guard)->check()) {
                // Redirect them to the appropriate dashboard
                return redirect($this->getRedirectPath($guard));
            }
        }

        // User is not authenticated, allow them to proceed to auth pages
        return $next($request);
    }

    /**
     * Get the redirect path based on the guard
     * 
     * Different guards might redirect to different places
     */
    private function getRedirectPath(string $guard = null): string
    {
        switch ($guard) {
            case 'admin':
                return '/admin/dashboard';
            case 'api':
                // API users shouldn't hit web routes, but just in case
                return '/dashboard';
            default:
                // Default web guard - redirect to main dashboard
                return '/dashboard';
        }
    }
}