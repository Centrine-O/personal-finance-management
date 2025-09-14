{{-- 
    Transactions Index View
    This view displays a comprehensive list of all transactions with filtering,
    searching, and sorting capabilities. It provides a complete transaction management interface.
--}}

{{-- Extend the main application layout --}}
@extends('layouts.app')

{{-- Set the page title --}}
@section('title', 'Transactions')

{{-- Main content section --}}
@section('content')
<div class="max-w-7xl mx-auto" x-data="transactionFilters()">
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
                        <span class="ml-1 text-sm font-medium text-finance-gray-500 md:ml-2">Transactions</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Page Title and Actions --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-finance-gray-900">Transactions</h1>
                <p class="mt-1 text-sm text-finance-gray-600">
                    Manage and track all your financial transactions across all accounts.
                </p>
            </div>
            
            {{-- Add Transaction Button --}}
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Transaction
            </a>
        </div>
    </div>

    {{-- Summary Cards Section --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        {{-- Total Income Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-finance-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Total Income</dt>
                            <dd class="text-lg font-semibold text-finance-green-600">
                                {{ Auth::user()->formatCurrency($summary['total_income'] ?? 0) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Expenses Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-finance-red-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Total Expenses</dt>
                            <dd class="text-lg font-semibold text-finance-red-600">
                                {{ Auth::user()->formatCurrency($summary['total_expenses'] ?? 0) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Net Income Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-finance-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Net Income</dt>
                            <dd class="text-lg font-semibold {{ ($summary['net_income'] ?? 0) >= 0 ? 'text-finance-green-600' : 'text-finance-red-600' }}">
                                {{ Auth::user()->formatCurrency($summary['net_income'] ?? 0) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transaction Count Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-finance-gray-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Total Transactions</dt>
                            <dd class="text-lg font-semibold text-finance-gray-900">
                                {{ number_format($transactions->total()) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters and Search Section --}}
    <div class="bg-white shadow-sm rounded-lg mb-6">
        <div class="p-6 border-b border-finance-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-finance-gray-900">Filters</h3>
                <button type="button" 
                        @click="showFilters = !showFilters" 
                        class="text-sm font-medium text-finance-blue-600 hover:text-finance-blue-500">
                    <span x-show="!showFilters">Show Filters</span>
                    <span x-show="showFilters">Hide Filters</span>
                </button>
            </div>
            
            {{-- Filter Form --}}
            <div x-show="showFilters" x-transition class="space-y-4">
                <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Search Input --}}
                    <div>
                        <label for="search" class="block text-sm font-medium text-finance-gray-700 mb-1">
                            Search
                        </label>
                        <input type="text" 
                               name="search" 
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Search descriptions..."
                               class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                    </div>
                    
                    {{-- Transaction Type Filter --}}
                    <div>
                        <label for="type" class="block text-sm font-medium text-finance-gray-700 mb-1">
                            Type
                        </label>
                        <select name="type" 
                                id="type"
                                class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                            <option value="">All Types</option>
                            <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                            <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                            <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                    
                    {{-- Account Filter --}}
                    <div>
                        <label for="account_id" class="block text-sm font-medium text-finance-gray-700 mb-1">
                            Account
                        </label>
                        <select name="account_id" 
                                id="account_id"
                                class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                            <option value="">All Accounts</option>
                            @foreach(Auth::user()->accounts as $account)
                                <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Category Filter --}}
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-finance-gray-700 mb-1">
                            Category
                        </label>
                        <select name="category_id" 
                                id="category_id"
                                class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                            <option value="">All Categories</option>
                            @foreach(Auth::user()->categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Date Range Filters --}}
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-finance-gray-700 mb-1">
                            From Date
                        </label>
                        <input type="date" 
                               name="date_from" 
                               id="date_from"
                               value="{{ request('date_from') }}"
                               class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-finance-gray-700 mb-1">
                            To Date
                        </label>
                        <input type="date" 
                               name="date_to" 
                               id="date_to"
                               value="{{ request('date_to') }}"
                               class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                    </div>
                    
                    {{-- Amount Range Filters --}}
                    <div>
                        <label for="amount_min" class="block text-sm font-medium text-finance-gray-700 mb-1">
                            Min Amount
                        </label>
                        <input type="number" 
                               name="amount_min" 
                               id="amount_min"
                               value="{{ request('amount_min') }}"
                               step="0.01"
                               placeholder="0.00"
                               class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="amount_max" class="block text-sm font-medium text-finance-gray-700 mb-1">
                            Max Amount
                        </label>
                        <input type="number" 
                               name="amount_max" 
                               id="amount_max"
                               value="{{ request('amount_max') }}"
                               step="0.01"
                               placeholder="1000.00"
                               class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm placeholder-finance-gray-400 focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                    </div>
                    
                    {{-- Filter Actions --}}
                    <div class="lg:col-span-4 flex items-center justify-between pt-4">
                        <a href="{{ route('transactions.index') }}" class="text-sm font-medium text-finance-gray-600 hover:text-finance-gray-500">
                            Clear Filters
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Transactions Table Section --}}
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        {{-- Table Header --}}
        <div class="px-6 py-4 border-b border-finance-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-finance-gray-900">
                    All Transactions 
                    <span class="text-sm font-normal text-finance-gray-500">
                        ({{ $transactions->total() }} total)
                    </span>
                </h3>
                
                {{-- Sort Options --}}
                <div class="flex items-center space-x-2">
                    <label for="sort" class="text-sm font-medium text-finance-gray-700">Sort by:</label>
                    <select name="sort" 
                            id="sort" 
                            onchange="updateSort(this.value)"
                            class="text-sm border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500">
                        <option value="date_desc" {{ request('sort', 'date_desc') == 'date_desc' ? 'selected' : '' }}>Date (Newest)</option>
                        <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Date (Oldest)</option>
                        <option value="amount_desc" {{ request('sort') == 'amount_desc' ? 'selected' : '' }}>Amount (High to Low)</option>
                        <option value="amount_asc" {{ request('sort') == 'amount_asc' ? 'selected' : '' }}>Amount (Low to High)</option>
                        <option value="description_asc" {{ request('sort') == 'description_asc' ? 'selected' : '' }}>Description (A-Z)</option>
                        <option value="description_desc" {{ request('sort') == 'description_desc' ? 'selected' : '' }}>Description (Z-A)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Transactions List --}}
        @if($transactions->count() > 0)
            <div class="divide-y divide-finance-gray-200">
                @foreach($transactions as $transaction)
                    <div class="px-6 py-4 hover:bg-finance-gray-50">
                        <div class="flex items-center justify-between">
                            {{-- Transaction Info --}}
                            <div class="flex items-center min-w-0 flex-1">
                                {{-- Transaction Type Icon --}}
                                <div class="flex-shrink-0 mr-4">
                                    @if($transaction->type === 'income')
                                        <div class="w-10 h-10 bg-finance-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-finance-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                    @elseif($transaction->type === 'expense')
                                        <div class="w-10 h-10 bg-finance-red-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-finance-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 bg-finance-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-finance-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                
                                {{-- Transaction Details --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-finance-gray-900 truncate">
                                                {{ $transaction->description }}
                                            </p>
                                            <div class="flex items-center mt-1 text-xs text-finance-gray-500 space-x-4">
                                                <span class="flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                    {{ $transaction->account->name }}
                                                </span>
                                                <span class="flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                    </svg>
                                                    {{ $transaction->category ? $transaction->category->name : 'Uncategorized' }}
                                                </span>
                                                <span class="flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    {{ $transaction->transaction_date->format('M j, Y') }}
                                                </span>
                                                @if($transaction->type === 'transfer' && $transaction->transfer_to_account)
                                                    <span class="flex items-center text-finance-blue-600">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                                        </svg>
                                                        to {{ $transaction->transfer_to_account->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        {{-- Transaction Amount and Actions --}}
                                        <div class="flex items-center space-x-4">
                                            {{-- Amount --}}
                                            <div class="text-right">
                                                <p class="text-sm font-semibold {{ $transaction->type === 'income' ? 'text-finance-green-600' : ($transaction->type === 'expense' ? 'text-finance-red-600' : 'text-finance-blue-600') }}">
                                                    @if($transaction->type === 'expense')
                                                        -{{ Auth::user()->formatCurrency($transaction->amount) }}
                                                    @elseif($transaction->type === 'income')
                                                        +{{ Auth::user()->formatCurrency($transaction->amount) }}
                                                    @else
                                                        {{ Auth::user()->formatCurrency($transaction->amount) }}
                                                    @endif
                                                </p>
                                                <p class="text-xs text-finance-gray-500 capitalize">{{ $transaction->type }}</p>
                                            </div>
                                            
                                            {{-- Action Buttons --}}
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('transactions.show', $transaction) }}" class="text-finance-gray-400 hover:text-finance-blue-600">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('transactions.edit', $transaction) }}" class="text-finance-gray-400 hover:text-finance-blue-600">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-finance-gray-200">
                {{ $transactions->appends(request()->query())->links() }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-finance-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-finance-gray-900">No transactions found</h3>
                <p class="mt-1 text-sm text-finance-gray-500">
                    @if(request()->hasAny(['search', 'type', 'account_id', 'category_id', 'date_from', 'date_to', 'amount_min', 'amount_max']))
                        Try adjusting your filters to find what you're looking for.
                    @else
                        Get started by creating your first transaction.
                    @endif
                </p>
                <div class="mt-6">
                    <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add First Transaction
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- JavaScript for enhanced filtering and sorting --}}
@push('scripts')
<script>
    // Alpine.js component for transaction filters
    function transactionFilters() {
        return {
            showFilters: {{ request()->hasAny(['search', 'type', 'account_id', 'category_id', 'date_from', 'date_to', 'amount_min', 'amount_max']) ? 'true' : 'false' }},
        }
    }
    
    // Function to handle sort changes
    function updateSort(sortValue) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', sortValue);
        window.location.href = url.toString();
    }
    
    // Auto-submit form on filter changes (with debounce for text inputs)
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action*="transactions"]');
        const inputs = form.querySelectorAll('select, input[type="date"], input[type="number"]');
        const searchInput = form.querySelector('input[name="search"]');
        
        // Add change listeners to select and date inputs for immediate submission
        inputs.forEach(input => {
            if (input !== searchInput) {
                input.addEventListener('change', () => {
                    form.submit();
                });
            }
        });
        
        // Add debounced search for text input
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (searchInput.value.length === 0 || searchInput.value.length >= 3) {
                        form.submit();
                    }
                }, 500);
            });
        }
    });
</script>
@endpush
@endsection