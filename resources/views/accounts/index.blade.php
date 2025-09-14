{{--
Accounts Index View
==================

This view displays all user accounts with filtering, sorting, and management options.
It provides a comprehensive overview of the user's financial accounts including
balance summaries, account types, and quick action buttons.

Key Features:
- Account listing with visual cards
- Advanced filtering and search
- Balance summaries and analytics
- Quick action buttons
- Responsive design for all devices
- Account type grouping and organization
--}}

@extends('layouts.app')

@section('title', 'My Accounts')

@push('head')
    <meta name="description" content="Manage your financial accounts, view balances, and track your assets and liabilities.">
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="accountsApp()">
    
    {{-- Page Header --}}
    <div class="mb-8">
        <div class="flex items-center justify-between flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-finance-gray-900">My Accounts</h1>
                <p class="mt-1 text-sm text-finance-gray-600">
                    Manage your financial accounts and track your balances
                </p>
            </div>
            
            {{-- Quick Actions --}}
            <div class="flex space-x-3 mt-4 lg:mt-0">
                <a href="{{ route('accounts.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Account
                </a>
                
                <button @click="toggleFilters()" 
                        class="inline-flex items-center px-3 py-2 border border-finance-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filters
                </button>
            </div>
        </div>
    </div>

    {{-- Account Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Net Worth Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-finance-blue-100 rounded-md flex items-center justify-center">
                            <svg class="h-5 w-5 text-finance-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Net Worth</dt>
                            <dd class="text-lg font-semibold {{ $accountSummary['net_worth'] >= 0 ? 'text-finance-green-600' : 'text-finance-red-600' }}">
                                {{ Auth::user()->formatCurrency($accountSummary['net_worth']) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Balance Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-finance-green-100 rounded-md flex items-center justify-center">
                            <svg class="h-5 w-5 text-finance-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Total Balance</dt>
                            <dd class="text-lg font-semibold text-finance-gray-900">
                                {{ Auth::user()->formatCurrency($accountSummary['total_balance']) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Accounts Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-finance-blue-100 rounded-md flex items-center justify-center">
                            <svg class="h-5 w-5 text-finance-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Active Accounts</dt>
                            <dd class="text-lg font-semibold text-finance-gray-900">
                                {{ $accountSummary['active_accounts'] }}
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-xs text-finance-gray-500">
                        {{ $accountSummary['total_accounts'] }} total accounts
                    </div>
                </div>
            </div>
        </div>

        {{-- Account Types Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-finance-gray-500">Account Types</span>
                </div>
                <div class="space-y-2">
                    @foreach($accountsByType as $type => $info)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="h-2 w-2 {{ $type === 'checking' ? 'bg-blue-500' : ($type === 'savings' ? 'bg-green-500' : ($type === 'credit' ? 'bg-red-500' : 'bg-purple-500')) }} rounded-full mr-2"></div>
                                <span class="text-xs font-medium text-finance-gray-900 capitalize">{{ $type }}</span>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-semibold text-finance-gray-900">{{ $info['count'] }}</div>
                                <div class="text-xs text-finance-gray-500">{{ Auth::user()->formatCurrency($info['total_balance']) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Panel --}}
    <div x-show="showFilters" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="bg-white shadow-sm rounded-lg p-6 mb-6">
        
        <form method="GET" action="{{ route('accounts.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Search --}}
                <div>
                    <label for="search" class="block text-sm font-medium text-finance-gray-700 mb-2">Search</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ $request->search }}"
                           placeholder="Account name, institution..."
                           class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                </div>

                {{-- Account Type Filter --}}
                <div>
                    <label for="type" class="block text-sm font-medium text-finance-gray-700 mb-2">Account Type</label>
                    <select id="type" 
                            name="type" 
                            class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                        <option value="">All Types</option>
                        @foreach($accountTypes as $type => $label)
                            <option value="{{ $type }}" {{ $request->type === $type ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label for="status" class="block text-sm font-medium text-finance-gray-700 mb-2">Status</label>
                    <select id="status" 
                            name="status" 
                            class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ $request->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $request->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                {{-- Sort Options --}}
                <div>
                    <label for="sort" class="block text-sm font-medium text-finance-gray-700 mb-2">Sort By</label>
                    <select id="sort" 
                            name="sort" 
                            class="block w-full px-3 py-2 border border-finance-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-finance-blue-500 focus:border-finance-blue-500 sm:text-sm">
                        <option value="sort_order" {{ $request->sort === 'sort_order' ? 'selected' : '' }}>Custom Order</option>
                        <option value="name" {{ $request->sort === 'name' ? 'selected' : '' }}>Name</option>
                        <option value="type" {{ $request->sort === 'type' ? 'selected' : '' }}>Account Type</option>
                        <option value="balance" {{ $request->sort === 'balance' ? 'selected' : '' }}>Balance</option>
                        <option value="created_at" {{ $request->sort === 'created_at' ? 'selected' : '' }}>Date Created</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                        Apply Filters
                    </button>
                    
                    <a href="{{ route('accounts.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-finance-gray-300 shadow-sm text-sm font-medium rounded-md text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                        Clear Filters
                    </a>
                </div>
                
                <button type="button" 
                        @click="showFilters = false"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-finance-gray-500 hover:text-finance-gray-700">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Close
                </button>
            </div>
        </form>
    </div>

    {{-- Accounts Grid --}}
    @if($accounts->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($accounts as $account)
                <div class="bg-white shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200">
                    {{-- Account Header --}}
                    <div class="p-6 pb-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-lg flex items-center justify-center" style="background-color: {{ $account->color }}20">
                                    <svg class="h-6 w-6" style="color: {{ $account->color }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        @if($account->type === 'checking')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        @elseif($account->type === 'savings')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        @elseif($account->type === 'credit')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        @endif
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-medium text-finance-gray-900">{{ $account->name }}</h3>
                                    <p class="text-sm text-finance-gray-500">
                                        {{ ucfirst($account->type) }}
                                        @if($account->institution_name)
                                            • {{ $account->institution_name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            {{-- Account Status --}}
                            <div class="flex items-center">
                                @if($account->is_active)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-finance-green-100 text-finance-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-finance-gray-100 text-finance-gray-800">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Account Balance --}}
                    <div class="px-6 pb-4">
                        <div class="text-2xl font-bold {{ $account->balance >= 0 ? 'text-finance-green-600' : 'text-finance-red-600' }}">
                            {{ $account->formatted_balance }}
                        </div>
                        @if($account->type === 'credit' && $account->credit_limit)
                            <div class="mt-1 text-sm text-finance-gray-500">
                                Credit limit: {{ Auth::user()->formatCurrency($account->credit_limit) }}
                            </div>
                            <div class="mt-2">
                                <div class="flex items-center justify-between text-xs text-finance-gray-500 mb-1">
                                    <span>Usage</span>
                                    <span>{{ number_format((abs($account->balance) / $account->credit_limit) * 100, 1) }}%</span>
                                </div>
                                <div class="w-full bg-finance-gray-200 rounded-full h-2">
                                    <div class="bg-finance-blue-600 h-2 rounded-full" style="width: {{ min((abs($account->balance) / $account->credit_limit) * 100, 100) }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Account Actions --}}
                    <div class="bg-finance-gray-50 px-6 py-4 rounded-b-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex space-x-3">
                                <a href="{{ route('accounts.show', $account) }}" 
                                   class="text-sm font-medium text-finance-blue-600 hover:text-finance-blue-500">
                                    View Details
                                </a>
                                <a href="{{ route('transactions.create', ['account_id' => $account->id]) }}" 
                                   class="text-sm font-medium text-finance-green-600 hover:text-finance-green-500">
                                    Add Transaction
                                </a>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('accounts.edit', $account) }}" 
                                   class="text-finance-gray-400 hover:text-finance-gray-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        {{ $accounts->links() }}

    @else
        {{-- Empty State --}}
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-finance-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-finance-gray-900">No accounts found</h3>
            <p class="mt-2 text-sm text-finance-gray-500">
                @if($request->anyFilled(['search', 'type', 'status']))
                    Try adjusting your filters or 
                    <a href="{{ route('accounts.index') }}" class="font-medium text-finance-blue-600 hover:text-finance-blue-500">clear all filters</a>
                @else
                    Get started by adding your first account to track your finances.
                @endif
            </p>
            <div class="mt-6">
                <a href="{{ route('accounts.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Your First Account
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function accountsApp() {
    return {
        showFilters: false,
        
        toggleFilters() {
            this.showFilters = !this.showFilters;
        },
    }
}
</script>
@endpush