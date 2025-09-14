{{--
Email Verification Page Template
===============================

This template creates the email verification page where users are directed
after registration to verify their email address before accessing the application.

Key Features:
- Clear instructions for email verification
- Resend verification email functionality
- Logout option
- Security-focused messaging
- User-friendly design with status updates

Security considerations:
- Email verification required for account security
- Rate limiting on resend requests
- Clear communication about verification status
- Professional appearance that builds trust
--}}

@extends('layouts.auth')

{{-- Page Title and Meta --}}
@section('title', 'Verify Your Email Address')
@section('subtitle', 'Account Verification Required')
@section('description', 'Please verify your email address to complete your account setup and access your personal finance dashboard.')

{{-- Additional Head Content --}}
@push('head')
    <meta name="keywords" content="email verification, account verification, confirm email">
@endpush

{{-- Main Content --}}
@section('content')
<div x-data="emailVerificationForm()">
    {{-- Header Message --}}
    <div class="text-center mb-6">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-finance-blue-100 mb-4">
            <svg class="h-8 w-8 text-finance-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
            </svg>
        </div>
        
        <h3 class="text-lg font-medium text-finance-gray-900 mb-2">Check Your Email</h3>
        <p class="text-sm text-finance-gray-600 mb-4">
            We've sent a verification link to your email address. Please click the link to verify your account and start managing your finances securely.
        </p>
        
        {{-- User Email Display --}}
        @auth
        <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-finance-blue-100 text-finance-blue-800">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
            </svg>
            {{ Auth::user()->email }}
        </div>
        @endauth
    </div>

    {{-- Action Buttons --}}
    <div class="space-y-4">
        {{-- Resend Verification Email --}}
        <form method="POST" action="{{ route('verification.send') }}" x-ref="resendForm">
            @csrf
            <button 
                type="submit" 
                :disabled="cooldownActive"
                :class="cooldownActive ? 'bg-finance-gray-400 cursor-not-allowed' : 'bg-finance-blue-600 hover:bg-finance-blue-700'"
                @click="startCooldown()"
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500 transition-colors duration-200">
                
                {{-- Send Icon --}}
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5" :class="cooldownActive ? 'text-finance-gray-300' : 'text-finance-blue-500 group-hover:text-finance-blue-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </span>
                
                <span x-show="!cooldownActive">
                    Resend Verification Email
                </span>
                <span x-show="cooldownActive" style="display: none;">
                    Resend Available in <span x-text="cooldownTime"></span>s
                </span>
            </button>
        </form>

        {{-- Check Email Button --}}
        <button 
            @click="checkVerificationStatus()"
            :disabled="checkingStatus"
            class="group relative w-full flex justify-center py-3 px-4 border border-finance-gray-300 shadow-sm text-sm font-medium rounded-lg text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500 transition-colors duration-200">
            
            {{-- Check Icon --}}
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                <svg x-show="!checkingStatus" class="h-5 w-5 text-finance-gray-400 group-hover:text-finance-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg x-show="checkingStatus" class="animate-spin h-5 w-5 text-finance-gray-400" fill="none" viewBox="0 0 24 24" style="display: none;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </span>
            
            <span x-show="!checkingStatus">I've Verified My Email</span>
            <span x-show="checkingStatus" style="display: none;">Checking...</span>
        </button>
        
        {{-- Sign Out Button --}}
        <div class="text-center">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button 
                    type="submit" 
                    class="inline-flex items-center text-sm text-finance-gray-500 hover:text-finance-gray-700 font-medium transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Sign Out
                </button>
            </form>
        </div>
    </div>

    {{-- Instructions --}}
    <div class="mt-8 p-4 bg-finance-blue-50 rounded-lg border border-finance-blue-200">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-finance-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-finance-blue-800">What to do next:</h4>
                <div class="mt-1 text-xs text-finance-blue-700 space-y-1">
                    <p>1. Check your email inbox for a verification message</p>
                    <p>2. Click the "Verify Email Address" button in the email</p>
                    <p>3. You'll be redirected back to your dashboard automatically</p>
                    <p>4. If you don't see the email, check your spam/junk folder</p>
                    <p>5. The verification link expires in 24 hours</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Troubleshooting --}}
    <div class="mt-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-yellow-800">Having trouble?</h4>
                <div class="mt-1 text-xs text-yellow-700 space-y-1">
                    <p>• Make sure to check your spam/junk folder</p>
                    <p>• Add our email address to your contacts</p>
                    <p>• Wait a few minutes - emails can be delayed</p>
                    <p>• Try resending the verification email</p>
                    <p>• Contact support if you continue having issues</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Security Notice --}}
    <div class="mt-4 p-4 bg-finance-green-50 rounded-lg border border-finance-green-200">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-finance-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-finance-green-800">Why verify your email?</h4>
                <div class="mt-1 text-xs text-finance-green-700 space-y-1">
                    <p>• Protects your financial data with secure communications</p>
                    <p>• Enables password recovery if you forget your login</p>
                    <p>• Ensures you receive important security notifications</p>
                    <p>• Allows us to send budget alerts and bill reminders</p>
                    <p>• Required by financial regulations for account security</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Support Contact --}}
    <div class="mt-6 text-center">
        <p class="text-xs text-finance-gray-500">
            Still need help? Contact our support team at 
            <a href="mailto:{{ config('mail.support_address', 'support@personalfinance.local') }}" 
               class="text-finance-blue-600 hover:text-finance-blue-500 font-medium">
                {{ config('mail.support_address', 'support@personalfinance.local') }}
            </a>
        </p>
    </div>
</div>
@endsection

{{-- JavaScript for Email Verification Functionality --}}
@push('scripts')
<script>
function emailVerificationForm() {
    return {
        cooldownActive: false,
        cooldownTime: 60,
        checkingStatus: false,
        
        startCooldown() {
            if (this.cooldownActive) return;
            
            this.cooldownActive = true;
            this.cooldownTime = 60;
            
            const interval = setInterval(() => {
                this.cooldownTime--;
                
                if (this.cooldownTime <= 0) {
                    this.cooldownActive = false;
                    clearInterval(interval);
                }
            }, 1000);
        },
        
        async checkVerificationStatus() {
            this.checkingStatus = true;
            
            try {
                const response = await fetch('{{ route('api.verification.status') }}', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.Laravel.csrfToken,
                        'Authorization': 'Bearer ' + (localStorage.getItem('api_token') || '')
                    },
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (data.verified) {
                    // Email is verified, redirect to dashboard
                    window.location.href = '{{ route('dashboard') }}';
                } else {
                    // Still not verified
                    this.showMessage('Your email is not yet verified. Please check your inbox and click the verification link.', 'warning');
                }
            } catch (error) {
                console.error('Error checking verification status:', error);
                this.showMessage('Unable to check verification status. Please try refreshing the page.', 'error');
            }
            
            this.checkingStatus = false;
        },
        
        showMessage(text, type) {
            // Create a temporary message element
            const messageDiv = document.createElement('div');
            messageDiv.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-sm ${this.getMessageClasses(type)}`;
            messageDiv.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        ${this.getMessageIcon(type)}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">${text}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" 
                                class="inline-flex text-gray-400 hover:text-gray-600">
                            <span class="sr-only">Close</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(messageDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (messageDiv.parentElement) {
                    messageDiv.remove();
                }
            }, 5000);
        },
        
        getMessageClasses(type) {
            switch (type) {
                case 'success':
                    return 'bg-finance-green-50 border border-finance-green-200 text-finance-green-800';
                case 'warning':
                    return 'bg-yellow-50 border border-yellow-200 text-yellow-800';
                case 'error':
                    return 'bg-finance-red-50 border border-finance-red-200 text-finance-red-800';
                default:
                    return 'bg-finance-blue-50 border border-finance-blue-200 text-finance-blue-800';
            }
        },
        
        getMessageIcon(type) {
            const iconClass = "h-5 w-5";
            
            switch (type) {
                case 'success':
                    return `<svg class="${iconClass} text-finance-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>`;
                case 'warning':
                    return `<svg class="${iconClass} text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>`;
                case 'error':
                    return `<svg class="${iconClass} text-finance-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>`;
                default:
                    return `<svg class="${iconClass} text-finance-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>`;
            }
        }
    }
}
</script>
@endpush