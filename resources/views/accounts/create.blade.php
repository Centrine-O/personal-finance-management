{{-- 
    Account Creation Form View
    This view provides a comprehensive form for creating new financial accounts.
    It includes all necessary fields with proper validation and user guidance.
--}}

{{-- Extend the main application layout --}}
@extends('layouts.app')

{{-- Set the page title --}}
@section('title', 'Create New Account')

{{-- Main content section --}}
@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Page Header Section --}}
    <div class="mb-8">
        {{-- Breadcrumb Navigation --}}
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    {{-- Dashboard link with home icon --}}
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-finance-gray-700 hover:text-finance-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    {{-- Breadcrumb separator --}}
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-finance-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        {{-- Accounts link --}}
                        <a href="{{ route('accounts.index') }}" class="ml-1 text-sm font-medium text-finance-gray-700 hover:text-finance-blue-600 md:ml-2">Accounts</a>
                    </div>
                </li>
                <li>
                    {{-- Current page indicator --}}
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-finance-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-finance-gray-500 md:ml-2">Create Account</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Page Title and Description --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-finance-gray-900">Create New Account</h1>
                <p class="mt-1 text-sm text-finance-gray-600">
                    Add a new financial account to track your money and transactions.
                </p>
            </div>
            {{-- Cancel button that goes back to accounts list --}}
            <a href="{{ route('accounts.index') }}" class="inline-flex items-center px-4 py-2 border border-finance-gray-300 rounded-md shadow-sm text-sm font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                Cancel
            </a>
        </div>
    </div>

    {{-- Main Form Card --}}
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            {{-- Account Creation Form --}}
            <form method="POST" action="{{ route('accounts.store') }}" id="accountForm">
                @csrf {{-- Laravel CSRF protection token --}}

                {{-- Account Basic Information Section --}}
                <div class="space-y-6">
                    <div class="border-b border-finance-gray-200 pb-6">
                        <h3 class="text-lg font-medium leading-6 text-finance-gray-900 mb-4">
                            Basic Information
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            {{-- Account Name Field --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Account Name <span class="text-finance-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       id="name"
                                       value="{{ old('name') }}"
                                       required
                                       maxlength="255"
                                       class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('name') border-finance-red-300 text-finance-red-900 placeholder-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror"
                                       placeholder="e.g., Chase Checking, Savings Account, Credit Card">
                                {{-- Display validation error for name field --}}
                                @error('name')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-finance-gray-500">
                                    Give your account a descriptive name that you'll recognize.
                                </p>
                            </div>

                            {{-- Account Type Field --}}
                            <div>
                                <label for="type" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Account Type <span class="text-finance-red-500">*</span>
                                </label>
                                <select name="type" 
                                        id="type"
                                        required
                                        class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('type') border-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror">
                                    <option value="">Select account type...</option>
                                    {{-- Loop through available account types from the Account model --}}
                                    @foreach(\App\Models\Account::getAccountTypes() as $key => $label)
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- Display validation error for type field --}}
                                @error('type')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-finance-gray-500">
                                    Choose the type that best matches your account.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Financial Details Section --}}
                    <div class="border-b border-finance-gray-200 pb-6">
                        <h3 class="text-lg font-medium leading-6 text-finance-gray-900 mb-4">
                            Financial Details
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Initial Balance Field --}}
                            <div>
                                <label for="balance" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Current Balance <span class="text-finance-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    {{-- Currency symbol --}}
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-finance-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" 
                                           name="balance" 
                                           id="balance"
                                           value="{{ old('balance', '0.00') }}"
                                           step="0.01"
                                           min="-999999999.99"
                                           max="999999999.99"
                                           required
                                           class="block w-full pl-7 pr-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('balance') border-finance-red-300 text-finance-red-900 placeholder-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror"
                                           placeholder="0.00">
                                </div>
                                {{-- Display validation error for balance field --}}
                                @error('balance')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-finance-gray-500">
                                    Enter the current balance of this account.
                                </p>
                            </div>

                            {{-- Currency Field --}}
                            <div>
                                <label for="currency" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Currency
                                </label>
                                <select name="currency" 
                                        id="currency"
                                        class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('currency') border-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror">
                                    {{-- Common currency options --}}
                                    <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                    <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                    <option value="CAD" {{ old('currency') == 'CAD' ? 'selected' : '' }}>CAD ($)</option>
                                    <option value="AUD" {{ old('currency') == 'AUD' ? 'selected' : '' }}>AUD ($)</option>
                                </select>
                                {{-- Display validation error for currency field --}}
                                @error('currency')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Optional Information Section --}}
                    <div class="border-b border-finance-gray-200 pb-6">
                        <h3 class="text-lg font-medium leading-6 text-finance-gray-900 mb-4">
                            Additional Information
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Institution Name Field --}}
                            <div>
                                <label for="institution_name" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Institution Name
                                </label>
                                <input type="text" 
                                       name="institution_name" 
                                       id="institution_name"
                                       value="{{ old('institution_name') }}"
                                       maxlength="255"
                                       class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('institution_name') border-finance-red-300 text-finance-red-900 placeholder-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror"
                                       placeholder="e.g., Chase Bank, Wells Fargo">
                                {{-- Display validation error for institution_name field --}}
                                @error('institution_name')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Account Number Field --}}
                            <div>
                                <label for="account_number" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Account Number
                                </label>
                                <input type="text" 
                                       name="account_number" 
                                       id="account_number"
                                       value="{{ old('account_number') }}"
                                       maxlength="255"
                                       class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('account_number') border-finance-red-300 text-finance-red-900 placeholder-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror"
                                       placeholder="Last 4 digits for security">
                                {{-- Display validation error for account_number field --}}
                                @error('account_number')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-finance-gray-500">
                                    Optional: For identification purposes only. Never store full account numbers.
                                </p>
                            </div>
                        </div>

                        {{-- Description Field --}}
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description" 
                                      id="description"
                                      rows="3"
                                      maxlength="1000"
                                      class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('description') border-finance-red-300 text-finance-red-900 placeholder-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror"
                                      placeholder="Optional notes about this account...">{{ old('description') }}</textarea>
                            {{-- Display validation error for description field --}}
                            @error('description')
                                <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Account Status Field --}}
                        <div class="mt-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           id="is_active"
                                           value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}
                                           class="focus:ring-finance-blue-500 h-4 w-4 text-finance-blue-600 border-finance-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_active" class="font-medium text-finance-gray-700">
                                        Account is active
                                    </label>
                                    <p class="text-finance-gray-500">
                                        Active accounts appear in your main dashboard and can receive new transactions.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end space-x-3 pt-6">
                        {{-- Cancel Button --}}
                        <a href="{{ route('accounts.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-finance-gray-300 rounded-md shadow-sm text-sm font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                            Cancel
                        </a>
                        
                        {{-- Submit Button --}}
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                            {{-- Plus icon --}}
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Account
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JavaScript for enhanced form interaction --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get form elements for enhanced interaction
        const accountTypeSelect = document.getElementById('type');
        const balanceInput = document.getElementById('balance');
        const institutionInput = document.getElementById('institution_name');
        
        // Add account type change handler to provide contextual guidance
        accountTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            // Provide helpful defaults based on account type
            switch(selectedType) {
                case 'checking':
                    // Checking accounts typically have positive balances
                    if (balanceInput.value === '0.00') {
                        balanceInput.placeholder = 'e.g., 1500.00';
                    }
                    break;
                case 'savings':
                    // Savings accounts typically have positive balances
                    if (balanceInput.value === '0.00') {
                        balanceInput.placeholder = 'e.g., 5000.00';
                    }
                    break;
                case 'credit_card':
                    // Credit cards typically have negative balances (debt)
                    if (balanceInput.value === '0.00') {
                        balanceInput.placeholder = 'e.g., -250.00 (negative for debt)';
                    }
                    break;
                case 'loan':
                    // Loans typically have negative balances (debt owed)
                    if (balanceInput.value === '0.00') {
                        balanceInput.placeholder = 'e.g., -15000.00 (amount owed)';
                    }
                    break;
                default:
                    balanceInput.placeholder = '0.00';
                    break;
            }
        });

        // Format balance input as user types
        balanceInput.addEventListener('input', function() {
            let value = this.value;
            
            // Remove any non-numeric characters except decimal point and minus sign
            value = value.replace(/[^-0-9.]/g, '');
            
            // Ensure only one decimal point
            const decimalCount = (value.match(/\./g) || []).length;
            if (decimalCount > 1) {
                value = value.replace(/\.+$/, '');
            }
            
            // Ensure only one minus sign at the beginning
            if (value.includes('-')) {
                const parts = value.split('-');
                value = '-' + parts.join('');
            }
            
            this.value = value;
        });

        // Auto-focus on first field
        document.getElementById('name').focus();
    });
</script>
@endpush
@endsection