{{--
Forgot Password Page Template
============================

This template creates the password reset request form where users can
request a password reset email for their Personal Finance Management account.

Key Features:
- Simple, focused design for password reset
- Clear security messaging
- Email validation
- Rate limiting protection
- User-friendly instructions
- Back to login link

Security considerations:
- CSRF protection
- Rate limiting (handled by middleware)
- No indication of whether email exists (security)
- Clear instructions for users
--}}

@extends('layouts.auth')

{{-- Page Title and Meta --}}
@section('title', 'Reset Your Password')
@section('subtitle', 'Account Recovery')
@section('description', 'Reset your personal finance management account password securely.')

{{-- Additional Head Content --}}
@push('head')
    <meta name="keywords" content="password reset, forgot password, account recovery">
@endpush

{{-- Main Content --}}
@section('content')
<div>
    {{-- Header Message --}}
    <div class="text-center mb-6">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-finance-blue-100 mb-4">
            <svg class="h-6 w-6 text-finance-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-6 6c-3.053 0-5.789-1.604-7.33-4M9 7a2 2 0 012 2m4 0a6 6 0 01-6 6c-3.053 0-5.789-1.604-7.33-4" />
            </svg>
        </div>
        
        <h3 class="text-lg font-medium text-finance-gray-900 mb-2">Forgot your password?</h3>
        <p class="text-sm text-finance-gray-600">
            No worries! Enter your email address and we'll send you a secure link to reset your password.
        </p>
    </div>

    {{-- Password Reset Form --}}
    <form method="POST" action="{{ route('password.email') }}" class="space-y-6" novalidate>
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
                    autofocus
                    value="{{ old('email') }}"
                    class="appearance-none relative block w-full px-3 py-3 pr-12 border @error('email') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 focus:z-10 sm:text-sm transition-colors duration-200"
                    placeholder="Enter the email address for your account">
                
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

        {{-- Submit Button --}}
        <div>
            <button 
                type="submit" 
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500 transition-colors duration-200">
                
                {{-- Send Icon --}}
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-finance-blue-500 group-hover:text-finance-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </span>
                
                Send Password Reset Email
            </button>
        </div>

        {{-- Back to Login Link --}}
        <div class="text-center">
            <a href="{{ route('login') }}" 
               class="inline-flex items-center text-sm text-finance-blue-600 hover:text-finance-blue-500 font-medium transition-colors duration-200">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Sign In
            </a>
        </div>
    </form>

    {{-- Security Information --}}
    <div class="mt-8 p-4 bg-finance-blue-50 rounded-lg border border-finance-blue-200">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-finance-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-finance-blue-800">What happens next?</h4>
                <div class="mt-1 text-xs text-finance-blue-700 space-y-1">
                    <p>• We'll send a secure password reset link to your email</p>
                    <p>• The link will expire in 60 minutes for security</p>
                    <p>• Check your spam folder if you don't see the email</p>
                    <p>• You can request a new link if the first one expires</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Security Notice --}}
    <div class="mt-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-yellow-800">Security Reminder</h4>
                <div class="mt-1 text-xs text-yellow-700 space-y-1">
                    <p>• Never share password reset links with anyone</p>
                    <p>• We will never ask for your password via email</p>
                    <p>• If you didn't request this reset, you can safely ignore the email</p>
                    <p>• Contact support if you have concerns about account security</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Alternative Support --}}
    <div class="mt-6 text-center">
        <p class="text-xs text-finance-gray-500">
            Need help? Contact our support team at 
            <a href="mailto:{{ config('mail.support_address', 'support@personalfinance.local') }}" 
               class="text-finance-blue-600 hover:text-finance-blue-500 font-medium">
                {{ config('mail.support_address', 'support@personalfinance.local') }}
            </a>
        </p>
    </div>
</div>
@endsection

{{-- Additional JavaScript --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const form = document.querySelector('form');
    
    // Email validation
    emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        if (email && !isValidEmail(email)) {
            this.classList.add('border-finance-red-300');
            this.classList.remove('border-finance-gray-300');
            showEmailError('Please enter a valid email address.');
        } else {
            this.classList.remove('border-finance-red-300');
            this.classList.add('border-finance-gray-300');
            hideEmailError();
        }
    });
    
    // Form submission handling
    form.addEventListener('submit', function(e) {
        const email = emailInput.value.trim();
        
        if (!email) {
            e.preventDefault();
            emailInput.focus();
            showEmailError('Email address is required.');
            return;
        }
        
        if (!isValidEmail(email)) {
            e.preventDefault();
            emailInput.focus();
            showEmailError('Please enter a valid email address.');
            return;
        }
        
        // Show loading state
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Sending Reset Email...
            `;
            
            // Reset after 10 seconds if something goes wrong
            setTimeout(function() {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }, 10000);
        }
    });
    
    // Helper functions
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function showEmailError(message) {
        hideEmailError(); // Remove any existing error
        
        const errorElement = document.createElement('p');
        errorElement.className = 'mt-1 text-sm text-finance-red-600';
        errorElement.textContent = message;
        errorElement.id = 'email-error';
        
        emailInput.parentNode.appendChild(errorElement);
    }
    
    function hideEmailError() {
        const existingError = document.getElementById('email-error');
        if (existingError) {
            existingError.remove();
        }
    }
    
    // Auto-focus email field when page loads
    emailInput.focus();
});
</script>
@endpush