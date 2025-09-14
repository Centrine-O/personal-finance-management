{{--
Password Reset Form Template
===========================

This template creates the password reset form where users can set a new password
using the secure token they received via email.

Key Features:
- Secure token validation
- Strong password requirements with real-time validation
- Password confirmation matching
- Security-focused messaging
- Clear instructions and feedback
- Password strength indicator

Security considerations:
- CSRF protection
- Token validation (handled by controller)
- Strong password requirements
- Rate limiting protection
- Clear security messaging
--}}

@extends('layouts.auth')

{{-- Page Title and Meta --}}
@section('title', 'Set New Password')
@section('subtitle', 'Password Reset')
@section('description', 'Set a new secure password for your personal finance management account.')

{{-- Additional Head Content --}}
@push('head')
    <meta name="keywords" content="password reset, new password, account security">
@endpush

{{-- Main Content --}}
@section('content')
<div x-data="passwordResetForm()">
    {{-- Header Message --}}
    <div class="text-center mb-6">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-finance-green-100 mb-4">
            <svg class="h-6 w-6 text-finance-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        
        <h3 class="text-lg font-medium text-finance-gray-900 mb-2">Set Your New Password</h3>
        <p class="text-sm text-finance-gray-600">
            Choose a strong password to secure your financial data.
        </p>
        
        {{-- User Email Display --}}
        @if(request('email'))
        <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-finance-blue-100 text-finance-blue-800">
            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
            </svg>
            {{ request('email') }}
        </div>
        @endif
    </div>

    {{-- Password Reset Form --}}
    <form method="POST" action="{{ route('password.update') }}" class="space-y-6" novalidate>
        @csrf
        
        {{-- Hidden Fields --}}
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ request('email') }}">
        
        {{-- New Password Field --}}
        <div>
            <label for="password" class="block text-sm font-medium text-finance-gray-700 mb-2">
                New Password <span class="text-finance-red-500">*</span>
            </label>
            <div class="relative" x-data="{ showPassword: false }">
                <input 
                    id="password" 
                    name="password" 
                    :type="showPassword ? 'text' : 'password'" 
                    autocomplete="new-password" 
                    required 
                    autofocus
                    x-model="formData.password"
                    @input="checkPasswordStrength()"
                    class="appearance-none relative block w-full px-3 py-3 pr-12 border @error('password') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 focus:z-10 sm:text-sm transition-colors duration-200"
                    placeholder="Enter your new password">
                
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
            
            {{-- Password Strength Indicator --}}
            <div x-show="formData.password" class="mt-2" style="display: none;">
                <div class="flex items-center space-x-1">
                    <div class="flex-1 bg-finance-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-300" 
                             :class="getPasswordStrengthColor()" 
                             :style="`width: ${passwordStrength}%`"></div>
                    </div>
                    <span class="text-xs font-medium" :class="getPasswordStrengthTextColor()" x-text="getPasswordStrengthText()"></span>
                </div>
                <div class="mt-1 text-xs text-finance-gray-600">
                    Password must contain: 8+ characters, uppercase, lowercase, number, and symbol
                </div>
            </div>
            
            {{-- Password Error Message --}}
            @error('password')
                <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password Field --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-finance-gray-700 mb-2">
                Confirm New Password <span class="text-finance-red-500">*</span>
            </label>
            <div class="relative" x-data="{ showConfirmPassword: false }">
                <input 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    :type="showConfirmPassword ? 'text' : 'password'" 
                    autocomplete="new-password" 
                    required 
                    x-model="formData.password_confirmation"
                    class="appearance-none relative block w-full px-3 py-3 pr-12 border @error('password_confirmation') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 focus:z-10 sm:text-sm transition-colors duration-200"
                    placeholder="Confirm your new password">
                
                {{-- Password Visibility Toggle --}}
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <button 
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="text-finance-gray-400 hover:text-finance-gray-600 focus:outline-none focus:text-finance-gray-600 transition-colors duration-200">
                        <svg x-show="!showConfirmPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showConfirmPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414l-1.414-1.414m4.242 4.242l1.414 1.414M14.122 14.122l1.414 1.414" />
                        </svg>
                    </button>
                </div>
            </div>
            
            {{-- Password Match Indicator --}}
            <div x-show="formData.password_confirmation && formData.password" class="mt-1" style="display: none;">
                <div x-show="formData.password === formData.password_confirmation" class="flex items-center text-sm text-finance-green-600">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Passwords match
                </div>
                <div x-show="formData.password !== formData.password_confirmation" class="flex items-center text-sm text-finance-red-600">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Passwords don't match
                </div>
            </div>
            
            {{-- Confirm Password Error Message --}}
            @error('password_confirmation')
                <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit Button --}}
        <div>
            <button 
                type="submit" 
                :disabled="!canSubmit()"
                :class="canSubmit() ? 'bg-finance-green-600 hover:bg-finance-green-700' : 'bg-finance-gray-400 cursor-not-allowed'"
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-green-500 transition-colors duration-200">
                
                {{-- Lock Icon --}}
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5" :class="canSubmit() ? 'text-finance-green-500 group-hover:text-finance-green-400' : 'text-finance-gray-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                
                Update Password
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

    {{-- Password Requirements --}}
    <div class="mt-8 p-4 bg-finance-blue-50 rounded-lg border border-finance-blue-200">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-finance-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-finance-blue-800">Password Requirements</h4>
                <div class="mt-1 text-xs text-finance-blue-700">
                    <ul class="space-y-1">
                        <li class="flex items-center">
                            <span :class="hasMinLength() ? 'text-finance-green-600' : 'text-finance-gray-400'">
                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            At least 8 characters long
                        </li>
                        <li class="flex items-center">
                            <span :class="hasUppercase() ? 'text-finance-green-600' : 'text-finance-gray-400'">
                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            Contains uppercase letters (A-Z)
                        </li>
                        <li class="flex items-center">
                            <span :class="hasLowercase() ? 'text-finance-green-600' : 'text-finance-gray-400'">
                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            Contains lowercase letters (a-z)
                        </li>
                        <li class="flex items-center">
                            <span :class="hasNumbers() ? 'text-finance-green-600' : 'text-finance-gray-400'">
                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            Contains numbers (0-9)
                        </li>
                        <li class="flex items-center">
                            <span :class="hasSymbols() ? 'text-finance-green-600' : 'text-finance-gray-400'">
                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            Contains special characters (!@#$%^&*)
                        </li>
                    </ul>
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
                <h4 class="text-sm font-medium text-finance-green-800">Security Tips</h4>
                <div class="mt-1 text-xs text-finance-green-700 space-y-1">
                    <p>• Use a unique password that you don't use elsewhere</p>
                    <p>• Consider using a password manager for better security</p>
                    <p>• This password reset link expires in 60 minutes</p>
                    <p>• You'll be automatically signed in after resetting</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- JavaScript for Password Validation --}}
@push('scripts')
<script>
function passwordResetForm() {
    return {
        passwordStrength: 0,
        formData: {
            password: '',
            password_confirmation: ''
        },
        
        canSubmit() {
            return this.formData.password && 
                   this.formData.password_confirmation && 
                   this.formData.password === this.formData.password_confirmation &&
                   this.passwordStrength >= 80;
        },
        
        checkPasswordStrength() {
            const password = this.formData.password;
            let score = 0;
            
            if (password.length >= 8) score += 20;
            if (/[a-z]/.test(password)) score += 20;
            if (/[A-Z]/.test(password)) score += 20;
            if (/[0-9]/.test(password)) score += 20;
            if (/[^A-Za-z0-9]/.test(password)) score += 20;
            
            this.passwordStrength = score;
        },
        
        getPasswordStrengthColor() {
            if (this.passwordStrength < 40) return 'bg-finance-red-500';
            if (this.passwordStrength < 60) return 'bg-yellow-500';
            if (this.passwordStrength < 80) return 'bg-blue-500';
            return 'bg-finance-green-500';
        },
        
        getPasswordStrengthTextColor() {
            if (this.passwordStrength < 40) return 'text-finance-red-600';
            if (this.passwordStrength < 60) return 'text-yellow-600';
            if (this.passwordStrength < 80) return 'text-blue-600';
            return 'text-finance-green-600';
        },
        
        getPasswordStrengthText() {
            if (this.passwordStrength < 40) return 'Weak';
            if (this.passwordStrength < 60) return 'Fair';
            if (this.passwordStrength < 80) return 'Good';
            return 'Strong';
        },
        
        hasMinLength() {
            return this.formData.password.length >= 8;
        },
        
        hasUppercase() {
            return /[A-Z]/.test(this.formData.password);
        },
        
        hasLowercase() {
            return /[a-z]/.test(this.formData.password);
        },
        
        hasNumbers() {
            return /[0-9]/.test(this.formData.password);
        },
        
        hasSymbols() {
            return /[^A-Za-z0-9]/.test(this.formData.password);
        }
    }
}
</script>
@endpush