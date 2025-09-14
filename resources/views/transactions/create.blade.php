{{-- 
    Transaction Creation Form View
    This view provides a comprehensive form for creating new financial transactions.
    It supports income, expense, and transfer transactions with dynamic form sections.
--}}

{{-- Extend the main application layout --}}
@extends('layouts.app')

{{-- Set the page title --}}
@section('title', 'Add Transaction')

{{-- Main content section --}}
@section('content')
<div class="max-w-2xl mx-auto" x-data="transactionForm()">
    {{-- Page Header Section --}}
    <div class="mb-8">
        {{-- Breadcrumb Navigation --}}
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
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
                        <a href="{{ route('transactions.index') }}" class="ml-1 text-sm font-medium text-finance-gray-700 hover:text-finance-blue-600 md:ml-2">Transactions</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-finance-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-finance-gray-500 md:ml-2">Add Transaction</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Page Title and Description --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-finance-gray-900">Add New Transaction</h1>
                <p class="mt-1 text-sm text-finance-gray-600">
                    Record a new financial transaction for your accounts.
                </p>
            </div>
            {{-- Cancel button --}}
            <a href="{{ route('transactions.index') }}" class="inline-flex items-center px-4 py-2 border border-finance-gray-300 rounded-md shadow-sm text-sm font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                Cancel
            </a>
        </div>
    </div>

    {{-- Transaction Type Selection --}}
    <div class="bg-white shadow-sm rounded-lg mb-6">
        <div class="p-6">
            <h3 class="text-lg font-medium text-finance-gray-900 mb-4">Transaction Type</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Income Option --}}
                <div class="relative">
                    <input type="radio" 
                           name="transaction_type_select" 
                           id="type_income" 
                           value="income"
                           x-model="transactionType"
                           class="sr-only peer">
                    <label for="type_income" 
                           class="flex items-center p-4 border-2 rounded-lg cursor-pointer peer-checked:border-finance-green-500 peer-checked:bg-finance-green-50 border-finance-gray-200 hover:border-finance-gray-300">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-finance-green-500 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-finance-gray-900">Income</div>
                                <div class="text-sm text-finance-gray-500">Money coming in</div>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Expense Option --}}
                <div class="relative">
                    <input type="radio" 
                           name="transaction_type_select" 
                           id="type_expense" 
                           value="expense"
                           x-model="transactionType"
                           class="sr-only peer">
                    <label for="type_expense" 
                           class="flex items-center p-4 border-2 rounded-lg cursor-pointer peer-checked:border-finance-red-500 peer-checked:bg-finance-red-50 border-finance-gray-200 hover:border-finance-gray-300">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-finance-red-500 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-finance-gray-900">Expense</div>
                                <div class="text-sm text-finance-gray-500">Money going out</div>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Transfer Option --}}
                <div class="relative">
                    <input type="radio" 
                           name="transaction_type_select" 
                           id="type_transfer" 
                           value="transfer"
                           x-model="transactionType"
                           class="sr-only peer">
                    <label for="type_transfer" 
                           class="flex items-center p-4 border-2 rounded-lg cursor-pointer peer-checked:border-finance-blue-500 peer-checked:bg-finance-blue-50 border-finance-gray-200 hover:border-finance-gray-300">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-finance-blue-500 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-finance-gray-900">Transfer</div>
                                <div class="text-sm text-finance-gray-500">Between accounts</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Transaction Form --}}
    <div class="bg-white shadow-sm rounded-lg" x-show="transactionType">
        <div class="p-6">
            <form method="POST" action="{{ route('transactions.store') }}" id="transactionForm">
                @csrf {{-- Laravel CSRF protection token --}}

                {{-- Hidden input for transaction type --}}
                <input type="hidden" name="type" x-model="transactionType">

                {{-- Basic Transaction Information --}}
                <div class="space-y-6">
                    <div class="border-b border-finance-gray-200 pb-6">
                        <h3 class="text-lg font-medium leading-6 text-finance-gray-900 mb-4">
                            <span x-text="transactionType === 'income' ? 'Income Details' : (transactionType === 'expense' ? 'Expense Details' : 'Transfer Details')"></span>
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Transaction Description --}}
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Description <span class="text-finance-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="description" 
                                       id="description"
                                       value="{{ old('description') }}"
                                       required
                                       maxlength="255"
                                       class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('description') border-finance-red-300 text-finance-red-900 placeholder-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror"
                                       :placeholder="transactionType === 'income' ? 'e.g., Salary, Freelance Payment, Gift' : (transactionType === 'expense' ? 'e.g., Groceries, Gas, Restaurant' : 'e.g., Transfer to Savings')">
                                @error('description')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Transaction Amount --}}
                            <div>
                                <label for="amount" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Amount <span class="text-finance-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-finance-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" 
                                           name="amount" 
                                           id="amount"
                                           value="{{ old('amount') }}"
                                           step="0.01"
                                           min="0.01"
                                           max="999999999.99"
                                           required
                                           class="block w-full pl-7 pr-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('amount') border-finance-red-300 text-finance-red-900 placeholder-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror"
                                           placeholder="0.00">
                                </div>
                                @error('amount')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Transaction Date --}}
                            <div>
                                <label for="transaction_date" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Date <span class="text-finance-red-500">*</span>
                                </label>
                                <input type="date" 
                                       name="transaction_date" 
                                       id="transaction_date"
                                       value="{{ old('transaction_date', date('Y-m-d')) }}"
                                       required
                                       class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('transaction_date') border-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror">
                                @error('transaction_date')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Account Selection Section --}}
                    <div class="border-b border-finance-gray-200 pb-6">
                        <h3 class="text-lg font-medium leading-6 text-finance-gray-900 mb-4">Account Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- From Account (for all transaction types) --}}
                            <div>
                                <label for="account_id" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    <span x-text="transactionType === 'income' ? 'Deposit To Account' : (transactionType === 'expense' ? 'From Account' : 'From Account')"></span> 
                                    <span class="text-finance-red-500">*</span>
                                </label>
                                <select name="account_id" 
                                        id="account_id"
                                        required
                                        class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('account_id') border-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror">
                                    <option value="">Select account...</option>
                                    @foreach(Auth::user()->accounts()->active()->get() as $account)
                                        <option value="{{ $account->id }}" {{ old('account_id', request('account')) == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ Auth::user()->formatCurrency($account->balance, $account->currency) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Transfer To Account (only for transfers) --}}
                            <div x-show="transactionType === 'transfer'">
                                <label for="transfer_to_account_id" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    To Account <span class="text-finance-red-500">*</span>
                                </label>
                                <select name="transfer_to_account_id" 
                                        id="transfer_to_account_id"
                                        :required="transactionType === 'transfer'"
                                        class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('transfer_to_account_id') border-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror">
                                    <option value="">Select destination account...</option>
                                    @foreach(Auth::user()->accounts()->active()->get() as $account)
                                        <option value="{{ $account->id }}" {{ old('transfer_to_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ Auth::user()->formatCurrency($account->balance, $account->currency) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('transfer_to_account_id')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Category Section (not for transfers) --}}
                    <div x-show="transactionType !== 'transfer'" class="border-b border-finance-gray-200 pb-6">
                        <h3 class="text-lg font-medium leading-6 text-finance-gray-900 mb-4">Category & Details</h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            {{-- Category Selection --}}
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Category
                                </label>
                                <select name="category_id" 
                                        id="category_id"
                                        class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('category_id') border-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror">
                                    <option value="">Select category (optional)...</option>
                                    @foreach(Auth::user()->categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-finance-gray-500">
                                    Categories help organize and track your spending patterns.
                                </p>
                            </div>

                            {{-- Notes Field --}}
                            <div>
                                <label for="notes" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Notes
                                </label>
                                <textarea name="notes" 
                                          id="notes"
                                          rows="3"
                                          maxlength="1000"
                                          class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('notes') border-finance-red-300 text-finance-red-900 placeholder-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror"
                                          placeholder="Additional details about this transaction...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Advanced Options Section --}}
                    <div class="border-b border-finance-gray-200 pb-6" x-data="{ showAdvanced: false }">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium leading-6 text-finance-gray-900">Advanced Options</h3>
                            <button type="button" 
                                    @click="showAdvanced = !showAdvanced"
                                    class="text-sm font-medium text-finance-blue-600 hover:text-finance-blue-500">
                                <span x-show="!showAdvanced">Show Advanced</span>
                                <span x-show="showAdvanced">Hide Advanced</span>
                            </button>
                        </div>
                        
                        <div x-show="showAdvanced" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Reference Number --}}
                            <div>
                                <label for="reference_number" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Reference Number
                                </label>
                                <input type="text" 
                                       name="reference_number" 
                                       id="reference_number"
                                       value="{{ old('reference_number') }}"
                                       maxlength="255"
                                       class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('reference_number') border-finance-red-300 text-finance-red-900 placeholder-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror"
                                       placeholder="Check #, Invoice #, etc.">
                                @error('reference_number')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tags --}}
                            <div>
                                <label for="tags" class="block text-sm font-medium text-finance-gray-700 mb-2">
                                    Tags
                                </label>
                                <input type="text" 
                                       name="tags" 
                                       id="tags"
                                       value="{{ old('tags') }}"
                                       class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm @error('tags') border-finance-red-300 text-finance-red-900 placeholder-finance-red-300 focus:ring-finance-red-500 focus:border-finance-red-500 @enderror"
                                       placeholder="business, tax-deductible, vacation">
                                @error('tags')
                                    <p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-finance-gray-500">
                                    Separate multiple tags with commas.
                                </p>
                            </div>

                            {{-- Recurring Transaction Checkbox --}}
                            <div class="md:col-span-2">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" 
                                               name="is_recurring" 
                                               id="is_recurring"
                                               value="1"
                                               {{ old('is_recurring') ? 'checked' : '' }}
                                               class="focus:ring-finance-blue-500 h-4 w-4 text-finance-blue-600 border-finance-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="is_recurring" class="font-medium text-finance-gray-700">
                                            This is a recurring transaction
                                        </label>
                                        <p class="text-finance-gray-500">
                                            Mark this transaction as recurring for automatic scheduling (feature coming soon).
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end space-x-3 pt-6">
                        {{-- Cancel Button --}}
                        <a href="{{ route('transactions.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-finance-gray-300 rounded-md shadow-sm text-sm font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                            Cancel
                        </a>
                        
                        {{-- Submit Button --}}
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2"
                                :class="transactionType === 'income' ? 'bg-finance-green-600 hover:bg-finance-green-700 focus:ring-finance-green-500' : (transactionType === 'expense' ? 'bg-finance-red-600 hover:bg-finance-red-700 focus:ring-finance-red-500' : 'bg-finance-blue-600 hover:bg-finance-blue-700 focus:ring-finance-blue-500')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span x-text="'Add ' + (transactionType.charAt(0).toUpperCase() + transactionType.slice(1))"></span>
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
    function transactionForm() {
        return {
            transactionType: '{{ old('type', request('type', '')) }}',
            
            init() {
                // Set default transaction type based on URL parameter or old input
                if (!this.transactionType && '{{ request('type') }}') {
                    this.transactionType = '{{ request('type') }}';
                }
                
                // Focus on description field when transaction type is selected
                this.$watch('transactionType', (value) => {
                    if (value) {
                        this.$nextTick(() => {
                            document.getElementById('description').focus();
                        });
                    }
                });
            }
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Format amount input as user types
        const amountInput = document.getElementById('amount');
        amountInput.addEventListener('input', function() {
            let value = this.value;
            
            // Remove any non-numeric characters except decimal point
            value = value.replace(/[^0-9.]/g, '');
            
            // Ensure only one decimal point
            const decimalCount = (value.match(/\./g) || []).length;
            if (decimalCount > 1) {
                value = value.replace(/\.+$/, '');
            }
            
            this.value = value;
        });

        // Prevent selecting the same account for transfers
        const fromAccountSelect = document.getElementById('account_id');
        const toAccountSelect = document.getElementById('transfer_to_account_id');
        
        function updateAccountOptions() {
            const fromValue = fromAccountSelect.value;
            const toValue = toAccountSelect.value;
            
            // Disable the selected "from" account in the "to" dropdown
            Array.from(toAccountSelect.options).forEach(option => {
                if (option.value && option.value === fromValue) {
                    option.disabled = true;
                    option.textContent = option.textContent.replace(' (Same account)', '') + ' (Same account)';
                } else {
                    option.disabled = false;
                    option.textContent = option.textContent.replace(' (Same account)', '');
                }
            });
            
            // If the selected "to" account is the same as "from", clear it
            if (toValue === fromValue) {
                toAccountSelect.value = '';
            }
        }
        
        fromAccountSelect.addEventListener('change', updateAccountOptions);
        toAccountSelect.addEventListener('change', updateAccountOptions);
        
        // Initial check
        updateAccountOptions();

        // Auto-suggest categories based on description
        const descriptionInput = document.getElementById('description');
        const categorySelect = document.getElementById('category_id');
        
        // Simple category suggestions based on keywords
        const categoryKeywords = {
            @foreach(Auth::user()->categories as $category)
                '{{ strtolower($category->name) }}': {{ $category->id }},
            @endforeach
        };
        
        descriptionInput.addEventListener('input', function() {
            const description = this.value.toLowerCase();
            
            // Only suggest if no category is selected
            if (!categorySelect.value) {
                for (const [keyword, categoryId] of Object.entries(categoryKeywords)) {
                    if (description.includes(keyword)) {
                        categorySelect.value = categoryId;
                        break;
                    }
                }
            }
        });

        // Handle form submission with loading state
        const form = document.getElementById('transactionForm');
        form.addEventListener('submit', function() {
            const submitButton = form.querySelector('button[type="submit"]');
            const originalContent = submitButton.innerHTML;
            
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;
            
            // Re-enable button after 5 seconds as a fallback
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalContent;
            }, 5000);
        });
    });
</script>
@endpush
@endsection