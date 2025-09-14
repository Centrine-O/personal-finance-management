{{-- 
    Account Edit Form View
    This view provides a comprehensive form for editing existing account information.
    It pre-fills the form with current account data and includes proper validation.
--}}

{{-- Extend the main application layout --}}
@extends('layouts.app')

{{-- Set the dynamic page title --}}
@section('title', 'Edit ' . $account->name)

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
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-finance-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('accounts.index') }}" class="ml-1 text-sm font-medium text-finance-gray-700 hover:text-finance-blue-600 md:ml-2">Accounts</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-finance-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('accounts.show', $account) }}" class="ml-1 text-sm font-medium text-finance-gray-700 hover:text-finance-blue-600 md:ml-2">{{ $account->name }}</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-finance-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-finance-gray-500 md:ml-2">Edit</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Page Title and Description --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-finance-gray-900">Edit Account</h1>
                <p class="mt-1 text-sm text-finance-gray-600">
                    Update the information for "{{ $account->name }}".
                </p>
            </div>
            {{-- Cancel button that goes back to account details --}}
            <a href="{{ route('accounts.show', $account) }}" class="inline-flex items-center px-4 py-2 border border-finance-gray-300 rounded-md shadow-sm text-sm font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                Cancel
            </a>
        </div>
    </div>

    {{-- Main Form Card --}}
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            {{-- Account Edit Form --}}
            <form method="POST" action="{{ route('accounts.update', $account) }}" id="accountEditForm">
                @csrf {{-- Laravel CSRF protection token --}}
                @method('PUT') {{-- HTTP method spoofing for PUT request --}}

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
                                       value="{{ old('name', $account->name) }}"
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
                                        <option value="{{ $key }}" {{ old('type', $account->type) == $key ? 'selected' : '' }}>
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
                        
                        {{-- Warning about balance changes --}}
                        @if($account->transactions()->exists())
                            <div class="mb-6 p-4 bg-finance-yellow-50 border border-finance-yellow-200 rounded-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-finance-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-finance-yellow-800">
                                            Balance Adjustment Notice
                                        </h3>
                                        <div class="mt-2 text-sm text-finance-yellow-700">
                                            <p>
                                                This account has existing transactions. Changing the balance will create an adjustment transaction 
                                                to reconcile the difference between the current calculated balance and your new balance.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Current Balance Field --}}
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
                                           value="{{ old('balance', number_format($account->balance, 2, '.', '')) }}"
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
                                    The current balance of this account.
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
                                    <option value="USD" {{ old('currency', $account->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="EUR" {{ old('currency', $account->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                    <option value="GBP" {{ old('currency', $account->currency) == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                    <option value="CAD" {{ old('currency', $account->currency) == 'CAD' ? 'selected' : '' }}>CAD ($)</option>
                                    <option value="AUD" {{ old('currency', $account->currency) == 'AUD' ? 'selected' : '' }}>AUD ($)</option>
                                </select>
                                {{-- Display validation error for currency field --}}
                                @error('currency')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                                {{-- Warning about currency changes --}}
                                @if($account->transactions()->exists())
                                    <p class="mt-2 text-sm text-finance-yellow-600">
                                        <strong>Warning:</strong> Changing currency affects existing transactions.
                                    </p>
                                @endif
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
                                       value="{{ old('institution_name', $account->institution_name) }}"
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
                                       value="{{ old('account_number', $account->account_number) }}"
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
                                      placeholder="Optional notes about this account...">{{ old('description', $account->description) }}</textarea>
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
                                           {{ old('is_active', $account->is_active) ? 'checked' : '' }}
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
                    <div class="flex items-center justify-between pt-6">
                        {{-- Delete Account Button --}}
                        <button type="button" 
                                onclick="confirmDelete()"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-finance-red-700 bg-finance-red-100 hover:bg-finance-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-red-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Account
                        </button>
                        
                        {{-- Form Action Buttons --}}
                        <div class="flex items-center space-x-3">
                            {{-- Cancel Button --}}
                            <a href="{{ route('accounts.show', $account) }}" 
                               class="inline-flex items-center px-4 py-2 border border-finance-gray-300 rounded-md shadow-sm text-sm font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                                Cancel
                            </a>
                            
                            {{-- Update Button --}}
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                                {{-- Save icon --}}
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                Update Account
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Hidden Delete Form --}}
    <form id="deleteForm" method="POST" action="{{ route('accounts.destroy', $account) }}" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>

{{-- JavaScript for enhanced form interaction and delete confirmation --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get form elements for enhanced interaction
        const accountTypeSelect = document.getElementById('type');
        const balanceInput = document.getElementById('balance');
        const originalBalance = {{ $account->balance }};
        
        // Add account type change handler to provide contextual guidance
        accountTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            // Provide helpful guidance based on account type
            switch(selectedType) {
                case 'credit_card':
                case 'loan':
                    // Credit cards and loans typically have negative balances (debt)
                    if (parseFloat(balanceInput.value) > 0) {
                        balanceInput.placeholder = 'Negative values indicate debt owed';
                    }
                    break;
                case 'checking':
                case 'savings':
                case 'investment':
                    // Asset accounts typically have positive balances
                    if (parseFloat(balanceInput.value) < 0) {
                        balanceInput.placeholder = 'Positive values indicate money you own';
                    }
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

        // Show balance change warning
        balanceInput.addEventListener('change', function() {
            const newBalance = parseFloat(this.value) || 0;
            const difference = newBalance - originalBalance;
            
            if (Math.abs(difference) > 0.01) {
                // Show a subtle indicator that balance will be adjusted
                const existingWarning = document.getElementById('balance-change-warning');
                if (existingWarning) {
                    existingWarning.remove();
                }
                
                const warningDiv = document.createElement('div');
                warningDiv.id = 'balance-change-warning';
                warningDiv.className = 'mt-2 text-sm text-finance-blue-600';
                warningDiv.innerHTML = difference > 0 
                    ? `<strong>+$${Math.abs(difference).toFixed(2)}</strong> adjustment will be recorded.`
                    : `<strong>-$${Math.abs(difference).toFixed(2)}</strong> adjustment will be recorded.`;
                
                this.parentElement.parentElement.appendChild(warningDiv);
            }
        });

        // Auto-focus on first field
        document.getElementById('name').focus();
    });

    // Delete confirmation function
    function confirmDelete() {
        const accountName = '{{ $account->name }}';
        const transactionCount = {{ $account->transactions()->count() }};
        
        let message = `Are you sure you want to delete the account "${accountName}"?`;
        
        if (transactionCount > 0) {
            message += `\n\nThis account has ${transactionCount} transaction(s). All transactions will also be deleted.`;
            message += '\n\nThis action cannot be undone.';
        } else {
            message += '\n\nThis action cannot be undone.';
        }
        
        if (confirm(message)) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endpush
@endsection