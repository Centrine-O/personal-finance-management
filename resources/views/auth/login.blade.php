{{--
Login Page Template
==================

This template creates the login form for our Personal Finance Management application.
It extends the auth layout and provides a secure, user-friendly login experience.

Key Features:
- Clean, professional design
- Security-focused messaging
- Form validation and error handling
- Remember me functionality
- Password visibility toggle
- Forgot password link
- Registration link for new users

Security considerations:
- CSRF protection
- Client-side validation
- Rate limiting protection
- Secure form submission
--}}

@extends('layouts.auth')

{{-- Page Title and Meta --}}
@section('title', 'Sign In to Your Account')
@section('subtitle', 'Welcome Back')
@section('description', 'Sign in to your personal finance management account to track expenses, manage budgets, and achieve your financial goals.')

{{-- Additional Head Content --}}
@push('head')
    <meta name="keywords" content="login, sign in, personal finance, account access">
@endpush

{{-- Main Login Form --}}
@section('content')
<div>
    {{-- Welcome Message --}}
    <div class="text-center mb-6">
        <h3 class="text-lg font-medium text-finance-gray-900 mb-2">Welcome back!</h3>
        <p class="text-sm text-finance-gray-600">
            Sign in to your account to access your financial dashboard.
        </p>
    </div>

    {{-- Login Form --}}
    <form method="POST" action="{{ route('login') }}" class="space-y-6" novalidate>
        @csrf
        
        {{-- Email Address Field --}}
        <div>
            <label for="email" class="block text-sm font-medium text-finance-gray-700 mb-2">
                Email Address
            </label>
            <div class="relative">
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    autocomplete="email" 
                    required 
                    value="{{ old('email') }}"
                    class="appearance-none relative block w-full px-3 py-3 border @error('email') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 focus:z-10 sm:text-sm transition-colors duration-200"
                    placeholder="Enter your email address">
                
                {{-- Email Icon --}}
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-finance-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
            </div>
            
            {{-- Email Error Message --}}
            @error('email')
                <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password Field --}}
        <div>
            <label for="password" class="block text-sm font-medium text-finance-gray-700 mb-2">
                Password
            </label>
            <div class="relative" x-data="{ showPassword: false }">
                <input 
                    id="password" 
                    name="password" 
                    :type="showPassword ? 'text' : 'password'" 
                    autocomplete="current-password" 
                    required 
                    class="appearance-none relative block w-full px-3 py-3 pr-12 border @error('password') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 focus:z-10 sm:text-sm transition-colors duration-200"
                    placeholder="Enter your password">
                
                {{-- Password Visibility Toggle --}}
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <button 
                        type="button"
                        @click="showPassword = !showPassword"
                        class="text-finance-gray-400 hover:text-finance-gray-600 focus:outline-none focus:text-finance-gray-600 transition-colors duration-200"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'">
                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414l-1.414-1.414m4.242 4.242l1.414 1.414M14.122 14.122l1.414 1.414" />
                        </svg>
                    </button>
                </div>
            </div>
            
            {{-- Password Error Message --}}
            @error('password')
                <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember Me and Forgot Password --}}
        <div class="flex items-center justify-between">
            {{-- Remember Me Checkbox --}}
            <div class="flex items-center">
                <input 
                    id="remember" 
                    name="remember" 
                    type="checkbox" 
                    {{ old('remember') ? 'checked' : '' }}
                    class="h-4 w-4 text-finance-blue-600 focus:ring-finance-blue-500 border-finance-gray-300 rounded">
                <label for="remember" class="ml-2 block text-sm text-finance-gray-700">
                    Keep me signed in
                </label>
            </div>

            {{-- Forgot Password Link --}}
            <div class="text-sm">
                <a href="{{ route('password.request') }}" 
                   class="text-finance-blue-600 hover:text-finance-blue-500 font-medium transition-colors duration-200">
                    Forgot your password?
                </a>
            </div>
        </div>

        {{-- Submit Button --}}
        <div>
            <button 
                type="submit" 
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500 transition-colors duration-200">
                
                {{-- Lock Icon --}}
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-finance-blue-500 group-hover:text-finance-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                
                Sign In to Your Account
            </button>
        </div>

        {{-- Divider --}}
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-finance-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-finance-gray-500">New to {{ config('app.name') }}?</span>
            </div>
        </div>

        {{-- Register Link --}}
        <div class="text-center">
            <a href="{{ route('register') }}" 
               class="inline-flex items-center px-4 py-2 border border-finance-gray-300 shadow-sm text-sm font-medium rounded-lg text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Create Your Free Account
            </a>
        </div>
    </form>

    {{-- Security Notice --}}
    <div class="mt-8 p-4 bg-finance-blue-50 rounded-lg border border-finance-blue-200">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-finance-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-finance-blue-800">Security Notice</h4>
                <div class="mt-1 text-xs text-finance-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Your login attempts are monitored for security</li>
                        <li>We use bank-level encryption to protect your data</li>
                        <li>Never share your login credentials with anyone</li>
                        <li>Always log out from shared or public computers</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Additional JavaScript --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Email validation
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    // Real-time email validation
    emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && !isValidEmail(email)) {
            this.classList.add('border-finance-red-300');
            this.classList.remove('border-finance-gray-300');
        } else {
            this.classList.remove('border-finance-red-300');
            this.classList.add('border-finance-gray-300');
        }
    });
    
    // Password strength indicator (optional)
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        if (password.length > 0 && password.length < 6) {
            this.classList.add('border-finance-red-300');
            this.classList.remove('border-finance-gray-300');
        } else {
            this.classList.remove('border-finance-red-300');
            this.classList.add('border-finance-gray-300');
        }
    });
    
    // Email validation helper function
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Caps lock detection for password field
    passwordInput.addEventListener('keyup', function(event) {
        if (event.getModifierState && event.getModifierState('CapsLock')) {
            showCapsLockWarning();
        } else {
            hideCapsLockWarning();
        }
    });
    
    function showCapsLockWarning() {
        let warning = document.getElementById('caps-lock-warning');
        if (!warning) {
            warning = document.createElement('p');
            warning.id = 'caps-lock-warning';
            warning.className = 'mt-1 text-sm text-yellow-600';
            warning.innerHTML = '⚠️ Caps Lock is on';
            passwordInput.parentNode.appendChild(warning);
        }
    }
    
    function hideCapsLockWarning() {
        const warning = document.getElementById('caps-lock-warning');
        if (warning) {
            warning.remove();
        }
    }
});
</script>
@endpush