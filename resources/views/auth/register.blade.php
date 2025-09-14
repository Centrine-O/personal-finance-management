{{--
Registration Page Template
=========================

This template creates the registration form for new users to create accounts
in our Personal Finance Management application.

Key Features:
- Multi-step registration process with clear sections
- Strong password requirements with real-time validation
- Personal and financial preference collection
- Terms and privacy policy acceptance
- Clean, user-friendly design
- Progressive disclosure to avoid overwhelming users

Security Features:
- Client-side and server-side validation
- Password strength requirements
- CSRF protection
- Email verification requirement
- Secure form submission
--}}

@extends('layouts.auth')

{{-- Page Title and Meta --}}
@section('title', 'Create Your Account')
@section('subtitle', 'Join Thousands Taking Control of Their Finances')
@section('description', 'Create your free personal finance management account to start tracking expenses, managing budgets, and achieving your financial goals.')

{{-- Additional Head Content --}}
@push('head')
    <meta name="keywords" content="register, sign up, create account, personal finance, free account">
@endpush

{{-- Main Registration Form --}}
@section('content')
<div x-data="registrationForm()">
    {{-- Welcome Message --}}
    <div class="text-center mb-6">
        <h3 class="text-lg font-medium text-finance-gray-900 mb-2">Create Your Free Account</h3>
        <p class="text-sm text-finance-gray-600">
            Join thousands of users who have taken control of their financial future.
        </p>
    </div>

    {{-- Progress Indicator --}}
    <div class="mb-6">
        <div class="flex items-center justify-between text-sm text-finance-gray-500 mb-2">
            <span :class="step >= 1 ? 'text-finance-blue-600 font-medium' : ''">Personal Info</span>
            <span :class="step >= 2 ? 'text-finance-blue-600 font-medium' : ''">Preferences</span>
            <span :class="step >= 3 ? 'text-finance-blue-600 font-medium' : ''">Security</span>
        </div>
        <div class="w-full bg-finance-gray-200 rounded-full h-2">
            <div class="bg-finance-blue-600 h-2 rounded-full transition-all duration-300" 
                 :style="`width: ${(step / 3) * 100}%`"></div>
        </div>
    </div>

    {{-- Registration Form --}}
    <form method="POST" action="{{ route('register') }}" class="space-y-6" novalidate>
        @csrf
        
        {{-- Step 1: Personal Information --}}
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
            <h4 class="text-md font-semibold text-finance-gray-900 mb-4">Personal Information</h4>
            
            {{-- Name Fields --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- First Name --}}
                <div>
                    <label for="first_name" class="block text-sm font-medium text-finance-gray-700 mb-2">
                        First Name <span class="text-finance-red-500">*</span>
                    </label>
                    <input 
                        id="first_name" 
                        name="first_name" 
                        type="text" 
                        autocomplete="given-name" 
                        required 
                        value="{{ old('first_name') }}"
                        x-model="formData.first_name"
                        class="appearance-none relative block w-full px-3 py-3 border @error('first_name') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm"
                        placeholder="Enter your first name">
                    @error('first_name')
                        <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Last Name --}}
                <div>
                    <label for="last_name" class="block text-sm font-medium text-finance-gray-700 mb-2">
                        Last Name <span class="text-finance-red-500">*</span>
                    </label>
                    <input 
                        id="last_name" 
                        name="last_name" 
                        type="text" 
                        autocomplete="family-name" 
                        required 
                        value="{{ old('last_name') }}"
                        x-model="formData.last_name"
                        class="appearance-none relative block w-full px-3 py-3 border @error('last_name') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm"
                        placeholder="Enter your last name">
                    @error('last_name')
                        <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Email Address --}}
            <div class="mt-4">
                <label for="email" class="block text-sm font-medium text-finance-gray-700 mb-2">
                    Email Address <span class="text-finance-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required 
                        value="{{ old('email') }}"
                        x-model="formData.email"
                        @blur="checkEmailAvailability()"
                        class="appearance-none relative block w-full px-3 py-3 pr-12 border @error('email') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm"
                        placeholder="Enter your email address">
                    
                    {{-- Email Status Icon --}}
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg x-show="emailChecking" class="animate-spin h-5 w-5 text-finance-gray-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="emailAvailable && !emailChecking" class="h-5 w-5 text-finance-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg x-show="!emailAvailable && formData.email && !emailChecking" class="h-5 w-5 text-finance-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
                <p x-show="!emailAvailable && formData.email && !emailChecking" class="mt-1 text-sm text-finance-red-600" style="display: none;">
                    This email address is already registered. <a href="{{ route('login') }}" class="font-medium hover:underline">Sign in instead?</a>
                </p>
                @error('email')
                    <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone Number (Optional) --}}
            <div class="mt-4">
                <label for="phone" class="block text-sm font-medium text-finance-gray-700 mb-2">
                    Phone Number <span class="text-finance-gray-400">(Optional)</span>
                </label>
                <input 
                    id="phone" 
                    name="phone" 
                    type="tel" 
                    autocomplete="tel" 
                    value="{{ old('phone') }}"
                    x-model="formData.phone"
                    class="appearance-none relative block w-full px-3 py-3 border @error('phone') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm"
                    placeholder="Enter your phone number">
                @error('phone')
                    <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Step 2: Financial Preferences --}}
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
            <h4 class="text-md font-semibold text-finance-gray-900 mb-4">Financial Preferences</h4>
            
            {{-- Currency and Timezone --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Preferred Currency --}}
                <div>
                    <label for="preferred_currency" class="block text-sm font-medium text-finance-gray-700 mb-2">
                        Preferred Currency
                    </label>
                    <select 
                        id="preferred_currency" 
                        name="preferred_currency" 
                        x-model="formData.preferred_currency"
                        class="appearance-none relative block w-full px-3 py-3 border @error('preferred_currency') border-finance-red-300 @else border-finance-gray-300 @enderror text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                        <option value="USD">USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                        <option value="CAD">CAD - Canadian Dollar</option>
                        <option value="AUD">AUD - Australian Dollar</option>
                        <option value="JPY">JPY - Japanese Yen</option>
                        <option value="CHF">CHF - Swiss Franc</option>
                        <option value="CNY">CNY - Chinese Yuan</option>
                    </select>
                    @error('preferred_currency')
                        <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Timezone --}}
                <div>
                    <label for="timezone" class="block text-sm font-medium text-finance-gray-700 mb-2">
                        Timezone
                    </label>
                    <select 
                        id="timezone" 
                        name="timezone" 
                        x-model="formData.timezone"
                        class="appearance-none relative block w-full px-3 py-3 border @error('timezone') border-finance-red-300 @else border-finance-gray-300 @enderror text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                        <option value="America/New_York">Eastern Time</option>
                        <option value="America/Chicago">Central Time</option>
                        <option value="America/Denver">Mountain Time</option>
                        <option value="America/Los_Angeles">Pacific Time</option>
                        <option value="Europe/London">London</option>
                        <option value="Europe/Paris">Paris</option>
                        <option value="Asia/Tokyo">Tokyo</option>
                        <option value="UTC">UTC</option>
                    </select>
                    @error('timezone')
                        <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Monthly Income (Optional) --}}
            <div class="mt-4">
                <label for="monthly_income" class="block text-sm font-medium text-finance-gray-700 mb-2">
                    Monthly Income <span class="text-finance-gray-400">(Optional - helps us personalize your experience)</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-finance-gray-500 sm:text-sm" x-text="getCurrencySymbol()"></span>
                    </div>
                    <input 
                        id="monthly_income" 
                        name="monthly_income" 
                        type="number" 
                        step="0.01"
                        min="0"
                        value="{{ old('monthly_income') }}"
                        x-model="formData.monthly_income"
                        class="appearance-none relative block w-full pl-8 pr-3 py-3 border @error('monthly_income') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm"
                        placeholder="0.00">
                </div>
                @error('monthly_income')
                    <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notification Preferences --}}
            <div class="mt-6">
                <h5 class="text-sm font-medium text-finance-gray-900 mb-3">Notification Preferences</h5>
                
                <div class="space-y-3">
                    {{-- Budget Alerts --}}
                    <div class="flex items-center">
                        <input 
                            id="budget_alerts_enabled" 
                            name="budget_alerts_enabled" 
                            type="checkbox" 
                            checked="{{ old('budget_alerts_enabled', true) ? 'checked' : '' }}"
                            x-model="formData.budget_alerts_enabled"
                            class="h-4 w-4 text-finance-blue-600 focus:ring-finance-blue-500 border-finance-gray-300 rounded">
                        <label for="budget_alerts_enabled" class="ml-2 block text-sm text-finance-gray-700">
                            Budget overspend alerts
                        </label>
                    </div>

                    {{-- Bill Reminders --}}
                    <div class="flex items-center">
                        <input 
                            id="bill_reminders_enabled" 
                            name="bill_reminders_enabled" 
                            type="checkbox" 
                            checked="{{ old('bill_reminders_enabled', true) ? 'checked' : '' }}"
                            x-model="formData.bill_reminders_enabled"
                            class="h-4 w-4 text-finance-blue-600 focus:ring-finance-blue-500 border-finance-gray-300 rounded">
                        <label for="bill_reminders_enabled" class="ml-2 block text-sm text-finance-gray-700">
                            Bill due reminders
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Security and Terms --}}
        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
            <h4 class="text-md font-semibold text-finance-gray-900 mb-4">Security & Agreement</h4>
            
            {{-- Password Field --}}
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-finance-gray-700 mb-2">
                    Password <span class="text-finance-red-500">*</span>
                </label>
                <div class="relative" x-data="{ showPassword: false }">
                    <input 
                        id="password" 
                        name="password" 
                        :type="showPassword ? 'text' : 'password'" 
                        autocomplete="new-password" 
                        required 
                        x-model="formData.password"
                        @input="checkPasswordStrength()"
                        class="appearance-none relative block w-full px-3 py-3 pr-12 border @error('password') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm"
                        placeholder="Create a strong password">
                    
                    {{-- Password Visibility Toggle --}}
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button 
                            type="button"
                            @click="showPassword = !showPassword"
                            class="text-finance-gray-400 hover:text-finance-gray-600 focus:outline-none">
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
                
                @error('password')
                    <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-finance-gray-700 mb-2">
                    Confirm Password <span class="text-finance-red-500">*</span>
                </label>
                <input 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    type="password" 
                    autocomplete="new-password" 
                    required 
                    x-model="formData.password_confirmation"
                    class="appearance-none relative block w-full px-3 py-3 border @error('password_confirmation') border-finance-red-300 @else border-finance-gray-300 @enderror placeholder-finance-gray-500 text-finance-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm"
                    placeholder="Confirm your password">
                
                {{-- Password Match Indicator --}}
                <div x-show="formData.password_confirmation && formData.password" class="mt-1" style="display: none;">
                    <p x-show="formData.password === formData.password_confirmation" class="text-sm text-finance-green-600">
                        ✓ Passwords match
                    </p>
                    <p x-show="formData.password !== formData.password_confirmation" class="text-sm text-finance-red-600">
                        ✗ Passwords don't match
                    </p>
                </div>
                
                @error('password_confirmation')
                    <p class="mt-1 text-sm text-finance-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Terms and Privacy Agreement --}}
            <div class="space-y-4">
                {{-- Terms of Service --}}
                <div class="flex items-start">
                    <input 
                        id="terms_accepted" 
                        name="terms_accepted" 
                        type="checkbox" 
                        required
                        {{ old('terms_accepted') ? 'checked' : '' }}
                        x-model="formData.terms_accepted"
                        class="h-4 w-4 text-finance-blue-600 focus:ring-finance-blue-500 border-finance-gray-300 rounded mt-1">
                    <label for="terms_accepted" class="ml-2 block text-sm text-finance-gray-700">
                        I agree to the <a href="{{ route('terms') }}" target="_blank" class="text-finance-blue-600 hover:text-finance-blue-500 font-medium">Terms of Service</a> <span class="text-finance-red-500">*</span>
                    </label>
                </div>
                @error('terms_accepted')
                    <p class="text-sm text-finance-red-600">{{ $message }}</p>
                @enderror

                {{-- Privacy Policy --}}
                <div class="flex items-start">
                    <input 
                        id="privacy_accepted" 
                        name="privacy_accepted" 
                        type="checkbox" 
                        required
                        {{ old('privacy_accepted') ? 'checked' : '' }}
                        x-model="formData.privacy_accepted"
                        class="h-4 w-4 text-finance-blue-600 focus:ring-finance-blue-500 border-finance-gray-300 rounded mt-1">
                    <label for="privacy_accepted" class="ml-2 block text-sm text-finance-gray-700">
                        I agree to the <a href="{{ route('privacy') }}" target="_blank" class="text-finance-blue-600 hover:text-finance-blue-500 font-medium">Privacy Policy</a> <span class="text-finance-red-500">*</span>
                    </label>
                </div>
                @error('privacy_accepted')
                    <p class="text-sm text-finance-red-600">{{ $message }}</p>
                @enderror

                {{-- Marketing Emails (Optional) --}}
                <div class="flex items-start">
                    <input 
                        id="marketing_emails" 
                        name="marketing_emails" 
                        type="checkbox"
                        {{ old('marketing_emails') ? 'checked' : '' }}
                        x-model="formData.marketing_emails"
                        class="h-4 w-4 text-finance-blue-600 focus:ring-finance-blue-500 border-finance-gray-300 rounded mt-1">
                    <label for="marketing_emails" class="ml-2 block text-sm text-finance-gray-700">
                        I'd like to receive helpful tips and updates about personal finance management <span class="text-finance-gray-400">(Optional)</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Navigation Buttons --}}
        <div class="flex justify-between pt-6">
            {{-- Back Button --}}
            <button 
                type="button" 
                x-show="step > 1"
                @click="step--"
                class="inline-flex items-center px-4 py-2 border border-finance-gray-300 shadow-sm text-sm font-medium rounded-lg text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </button>

            {{-- Next/Submit Button --}}
            <button 
                type="button" 
                x-show="step < 3"
                @click="nextStep()"
                :disabled="!canProceed()"
                :class="canProceed() ? 'bg-finance-blue-600 hover:bg-finance-blue-700' : 'bg-finance-gray-400 cursor-not-allowed'"
                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                Continue
                <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            {{-- Submit Button --}}
            <button 
                type="submit" 
                x-show="step === 3"
                :disabled="!canSubmit()"
                :class="canSubmit() ? 'bg-finance-green-600 hover:bg-finance-green-700' : 'bg-finance-gray-400 cursor-not-allowed'"
                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-green-500"
                style="display: none;">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Create Account
            </button>
        </div>

        {{-- Sign In Link --}}
        <div class="text-center pt-4 border-t border-finance-gray-200">
            <span class="text-sm text-finance-gray-600">
                Already have an account? 
                <a href="{{ route('login') }}" class="font-medium text-finance-blue-600 hover:text-finance-blue-500">
                    Sign in here
                </a>
            </span>
        </div>
    </form>
</div>
@endsection

{{-- JavaScript for Form Functionality --}}
@push('scripts')
<script>
function registrationForm() {
    return {
        step: 1,
        emailChecking: false,
        emailAvailable: true,
        passwordStrength: 0,
        formData: {
            first_name: '{{ old('first_name') }}',
            last_name: '{{ old('last_name') }}',
            email: '{{ old('email') }}',
            phone: '{{ old('phone') }}',
            preferred_currency: '{{ old('preferred_currency', 'USD') }}',
            timezone: '{{ old('timezone', 'UTC') }}',
            monthly_income: '{{ old('monthly_income') }}',
            budget_alerts_enabled: {{ old('budget_alerts_enabled', 'true') }},
            bill_reminders_enabled: {{ old('bill_reminders_enabled', 'true') }},
            password: '',
            password_confirmation: '',
            terms_accepted: {{ old('terms_accepted', 'false') }},
            privacy_accepted: {{ old('privacy_accepted', 'false') }},
            marketing_emails: {{ old('marketing_emails', 'false') }}
        },
        
        canProceed() {
            if (this.step === 1) {
                return this.formData.first_name && 
                       this.formData.last_name && 
                       this.formData.email && 
                       this.emailAvailable;
            }
            return true;
        },
        
        canSubmit() {
            return this.formData.password && 
                   this.formData.password_confirmation && 
                   this.formData.password === this.formData.password_confirmation &&
                   this.passwordStrength >= 80 &&
                   this.formData.terms_accepted && 
                   this.formData.privacy_accepted;
        },
        
        nextStep() {
            if (this.canProceed() && this.step < 3) {
                this.step++;
            }
        },
        
        getCurrencySymbol() {
            const symbols = {
                'USD': '$', 'EUR': '€', 'GBP': '£', 'CAD': 'C$',
                'AUD': 'A$', 'JPY': '¥', 'CHF': 'CHF', 'CNY': '¥'
            };
            return symbols[this.formData.preferred_currency] || '$';
        },
        
        async checkEmailAvailability() {
            if (!this.formData.email || !this.isValidEmail(this.formData.email)) {
                return;
            }
            
            this.emailChecking = true;
            
            try {
                const response = await fetch('{{ route('api.auth.check-email') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.Laravel.csrfToken
                    },
                    body: JSON.stringify({ email: this.formData.email })
                });
                
                const data = await response.json();
                this.emailAvailable = data.available;
            } catch (error) {
                console.error('Error checking email availability:', error);
                this.emailAvailable = true; // Assume available on error
            }
            
            this.emailChecking = false;
        },
        
        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
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
        }
    }
}
</script>
@endpush