{{-- 
    Account Detail View
    This view displays comprehensive information about a specific account,
    including recent transactions, balance history, and account management options.
--}}

{{-- Extend the main application layout --}}
@extends('layouts.app')

{{-- Set the dynamic page title with account name --}}
@section('title', $account->name . ' - Account Details')

{{-- Main content section --}}
@section('content')
<div class="max-w-7xl mx-auto">
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
                        <span class="ml-1 text-sm font-medium text-finance-gray-500 md:ml-2">{{ $account->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Page Title and Actions --}}
        <div class="flex items-start justify-between">
            <div class="min-w-0 flex-1">
                {{-- Account Name and Type --}}
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-finance-gray-900 truncate">
                        {{ $account->name }}
                    </h1>
                    {{-- Account Status Badge --}}
                    <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $account->is_active ? 'bg-finance-green-100 text-finance-green-800' : 'bg-finance-red-100 text-finance-red-800' }}">
                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                
                {{-- Account Metadata --}}
                <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:space-x-6">
                    <div class="mt-2 flex items-center text-sm text-finance-gray-500">
                        {{-- Account Type --}}
                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-finance-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        {{ \App\Models\Account::getAccountTypes()[$account->type] ?? ucfirst($account->type) }}
                    </div>
                    
                    @if($account->institution_name)
                        <div class="mt-2 flex items-center text-sm text-finance-gray-500">
                            {{-- Institution --}}
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-finance-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            {{ $account->institution_name }}
                        </div>
                    @endif
                    
                    {{-- Created Date --}}
                    <div class="mt-2 flex items-center text-sm text-finance-gray-500">
                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-finance-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Created {{ $account->created_at->format('M j, Y') }}
                    </div>
                </div>
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex space-x-3">
                {{-- Edit Account Button --}}
                <a href="{{ route('accounts.edit', $account) }}" class="inline-flex items-center px-4 py-2 border border-finance-gray-300 rounded-md shadow-sm text-sm font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Account
                </a>
                
                {{-- Add Transaction Button --}}
                <a href="{{ route('transactions.create', ['account' => $account->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Transaction
                </a>
            </div>
        </div>
    </div>

    {{-- Account Overview Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Current Balance Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-finance-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Current Balance</dt>
                            <dd class="text-lg font-semibold {{ $account->balance >= 0 ? 'text-finance-green-600' : 'text-finance-red-600' }}">
                                {{ Auth::user()->formatCurrency($account->balance, $account->currency) }}
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
                        <div class="w-8 h-8 bg-finance-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Total Transactions</dt>
                            <dd class="text-lg font-semibold text-finance-gray-900">
                                {{ number_format($account->transactions_count ?? $account->transactions->count()) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Last Activity Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-finance-yellow-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Last Activity</dt>
                            <dd class="text-lg font-semibold text-finance-gray-900">
                                @if($account->transactions->isNotEmpty())
                                    {{ $account->transactions->first()->created_at->diffForHumans() }}
                                @else
                                    No transactions yet
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Transactions Section --}}
        <div class="lg:col-span-2">
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-5 border-b border-finance-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-finance-gray-900">Recent Transactions</h3>
                        <a href="{{ route('transactions.index', ['account' => $account->id]) }}" class="text-sm font-medium text-finance-blue-600 hover:text-finance-blue-500">
                            View all transactions
                        </a>
                    </div>
                </div>
                
                <div class="divide-y divide-finance-gray-200">
                    @forelse($account->transactions()->latest()->take(10)->get() as $transaction)
                        <div class="px-6 py-4 hover:bg-finance-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center min-w-0 flex-1">
                                    {{-- Transaction Type Icon --}}
                                    <div class="flex-shrink-0 mr-4">
                                        @if($transaction->type === 'income')
                                            <div class="w-8 h-8 bg-finance-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-finance-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </div>
                                        @elseif($transaction->type === 'expense')
                                            <div class="w-8 h-8 bg-finance-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-finance-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-finance-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-finance-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                                <div class="flex items-center mt-1 text-xs text-finance-gray-500">
                                                    <span class="truncate">
                                                        {{ $transaction->category ? $transaction->category->name : 'Uncategorized' }}
                                                    </span>
                                                    <span class="mx-1">•</span>
                                                    <span>{{ $transaction->transaction_date->format('M j, Y') }}</span>
                                                </div>
                                            </div>
                                            
                                            {{-- Transaction Amount --}}
                                            <div class="ml-4 text-right">
                                                <p class="text-sm font-semibold {{ $transaction->type === 'income' ? 'text-finance-green-600' : ($transaction->type === 'expense' ? 'text-finance-red-600' : 'text-finance-gray-900') }}">
                                                    {{ $transaction->type === 'expense' ? '-' : '+' }}{{ Auth::user()->formatCurrency($transaction->amount, $account->currency) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Empty State for No Transactions --}}
                        <div class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-finance-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-finance-gray-900">No transactions yet</h3>
                            <p class="mt-1 text-sm text-finance-gray-500">Get started by creating your first transaction for this account.</p>
                            <div class="mt-6">
                                <a href="{{ route('transactions.create', ['account' => $account->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add First Transaction
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Account Information Sidebar --}}
        <div class="space-y-6">
            {{-- Account Details Card --}}
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-5 border-b border-finance-gray-200">
                    <h3 class="text-lg font-medium text-finance-gray-900">Account Information</h3>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    {{-- Account Type --}}
                    <div>
                        <dt class="text-sm font-medium text-finance-gray-500">Type</dt>
                        <dd class="mt-1 text-sm text-finance-gray-900">
                            {{ \App\Models\Account::getAccountTypes()[$account->type] ?? ucfirst($account->type) }}
                        </dd>
                    </div>
                    
                    {{-- Institution Name --}}
                    @if($account->institution_name)
                        <div>
                            <dt class="text-sm font-medium text-finance-gray-500">Institution</dt>
                            <dd class="mt-1 text-sm text-finance-gray-900">{{ $account->institution_name }}</dd>
                        </div>
                    @endif
                    
                    {{-- Account Number --}}
                    @if($account->account_number)
                        <div>
                            <dt class="text-sm font-medium text-finance-gray-500">Account Number</dt>
                            <dd class="mt-1 text-sm text-finance-gray-900">****{{ substr($account->account_number, -4) }}</dd>
                        </div>
                    @endif
                    
                    {{-- Currency --}}
                    <div>
                        <dt class="text-sm font-medium text-finance-gray-500">Currency</dt>
                        <dd class="mt-1 text-sm text-finance-gray-900">{{ $account->currency }}</dd>
                    </div>
                    
                    {{-- Status --}}
                    <div>
                        <dt class="text-sm font-medium text-finance-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $account->is_active ? 'bg-finance-green-100 text-finance-green-800' : 'bg-finance-red-100 text-finance-red-800' }}">
                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    
                    {{-- Description --}}
                    @if($account->description)
                        <div>
                            <dt class="text-sm font-medium text-finance-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-finance-gray-900">{{ $account->description }}</dd>
                        </div>
                    @endif
                    
                    {{-- Created Date --}}
                    <div>
                        <dt class="text-sm font-medium text-finance-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-finance-gray-900">{{ $account->created_at->format('M j, Y \a\t g:i A') }}</dd>
                    </div>
                    
                    {{-- Last Updated --}}
                    @if($account->updated_at->ne($account->created_at))
                        <div>
                            <dt class="text-sm font-medium text-finance-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-finance-gray-900">{{ $account->updated_at->format('M j, Y \a\t g:i A') }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-5 border-b border-finance-gray-200">
                    <h3 class="text-lg font-medium text-finance-gray-900">Quick Actions</h3>
                </div>
                
                <div class="px-6 py-4 space-y-3">
                    {{-- Add Income Transaction --}}
                    <a href="{{ route('transactions.create', ['account' => $account->id, 'type' => 'income']) }}" class="w-full inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-green-600 hover:bg-finance-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-green-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Income
                    </a>
                    
                    {{-- Add Expense Transaction --}}
                    <a href="{{ route('transactions.create', ['account' => $account->id, 'type' => 'expense']) }}" class="w-full inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-red-600 hover:bg-finance-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-red-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                        Add Expense
                    </a>
                    
                    {{-- Transfer Money --}}
                    <a href="{{ route('transactions.create', ['account' => $account->id, 'type' => 'transfer']) }}" class="w-full inline-flex items-center px-4 py-2 border border-finance-gray-300 rounded-md shadow-sm text-sm font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Transfer Money
                    </a>
                    
                    {{-- View All Transactions --}}
                    <a href="{{ route('transactions.index', ['account' => $account->id]) }}" class="w-full inline-flex items-center px-4 py-2 border border-finance-gray-300 rounded-md shadow-sm text-sm font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        All Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection