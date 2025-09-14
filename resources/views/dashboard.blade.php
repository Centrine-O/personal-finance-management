{{--
Dashboard View Template
======================

This is the main dashboard view for our Personal Finance Management application.
It displays a comprehensive overview of the user's financial status, including:

- Financial overview cards (net worth, income, expenses)
- Account balances and summaries
- Recent transaction activity
- Budget progress and alerts
- Financial goals tracking
- Upcoming bills and reminders
- Personalized insights and recommendations

The design is:
- Responsive for mobile, tablet, and desktop
- Professional and trustworthy for financial data
- Interactive with real-time updates
- Accessible with proper ARIA labels
- Performance optimized with lazy loading

Why this dashboard design matters:
- Users get immediate overview of their financial health
- Critical information is prioritized and easily visible
- Interactive elements encourage engagement
- Professional design builds trust with financial data
- Mobile-friendly design for on-the-go access
--}}

@extends('layouts.app')

{{-- Page Title and Meta --}}
@section('title', 'Dashboard - Financial Overview')

{{-- Additional Head Content --}}
@push('head')
    <meta name="description" content="Your personal finance dashboard with account balances, recent transactions, budget tracking, and financial insights.">
    <meta name="keywords" content="dashboard, financial overview, account balances, budget tracking, personal finance">
@endpush

{{-- Main Dashboard Content --}}
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="dashboardApp()">
    
    {{-- Welcome Header --}}
    <div class="mb-8">
        <div class="flex items-center justify-between flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-finance-gray-900">
                    Welcome back, {{ $user->first_name }}! 👋
                </h1>
                <p class="mt-1 text-sm text-finance-gray-600">
                    Here's what's happening with your finances today, {{ now()->format('l, F j, Y') }}
                </p>
            </div>
            
            {{-- Quick Actions --}}
            <div class="flex space-x-3 mt-4 lg:mt-0">
                <button @click="refreshDashboard()" 
                        class="inline-flex items-center px-3 py-2 border border-finance-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-finance-gray-700 bg-white hover:bg-finance-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                    <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': isRefreshing }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
                
                <a href="{{ route('transactions.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Transaction
                </a>
            </div>
        </div>
    </div>

    {{-- Financial Overview Cards --}}
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
                            <dd class="text-lg font-semibold {{ $netWorth >= 0 ? 'text-finance-green-600' : 'text-finance-red-600' }}">
                                {{ $user->formatCurrency($netWorth) }}
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-xs text-finance-gray-500">
                        Total assets minus liabilities
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Income Card --}}
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
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Monthly Income</dt>
                            <dd class="text-lg font-semibold text-finance-green-600">
                                {{ $user->formatCurrency($monthlyIncome) }}
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-xs text-finance-gray-500">
                        {{ now()->format('F Y') }} income
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Expenses Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 bg-finance-red-100 rounded-md flex items-center justify-center">
                            <svg class="h-5 w-5 text-finance-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Monthly Expenses</dt>
                            <dd class="text-lg font-semibold text-finance-red-600">
                                {{ $user->formatCurrency($monthlyExpenses) }}
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-xs text-finance-gray-500">
                        {{ now()->format('F Y') }} spending
                    </div>
                </div>
            </div>
        </div>

        {{-- Savings Rate Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 {{ $savingsRate >= 20 ? 'bg-finance-green-100' : ($savingsRate >= 10 ? 'bg-yellow-100' : 'bg-finance-red-100') }} rounded-md flex items-center justify-center">
                            <svg class="h-5 w-5 {{ $savingsRate >= 20 ? 'text-finance-green-600' : ($savingsRate >= 10 ? 'text-yellow-600' : 'text-finance-red-600') }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Savings Rate</dt>
                            <dd class="text-lg font-semibold {{ $savingsRate >= 20 ? 'text-finance-green-600' : ($savingsRate >= 10 ? 'text-yellow-600' : 'text-finance-red-600') }}">
                                {{ number_format($savingsRate, 1) }}%
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-xs text-finance-gray-500">
                        {{ $user->formatCurrency($monthlySavings) }} saved this month
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts Section --}}
    @if($budgetAlerts->isNotEmpty() || $goalAlerts->isNotEmpty() || $lowBalanceAccounts->isNotEmpty())
    <div class="mb-8">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Attention Required</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($budgetAlerts as $alert)
                                <li>{{ $alert['message'] }}</li>
                            @endforeach
                            @foreach($goalAlerts as $alert)
                                <li>{{ $alert['message'] }}</li>
                            @endforeach
                            @foreach($lowBalanceAccounts as $account)
                                <li>{{ $account->name }} has a low balance: {{ $user->formatCurrency($account->balance) }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Dashboard Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- Recent Transactions --}}
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-finance-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-finance-gray-900">Recent Transactions</h3>
                        <a href="{{ route('transactions.index') }}" class="text-sm text-finance-blue-600 hover:text-finance-blue-500 font-medium">
                            View all
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-finance-gray-200">
                    @forelse($recentTransactions as $transaction)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full {{ $transaction->type === 'income' ? 'bg-finance-green-100' : 'bg-finance-red-100' }} flex items-center justify-center">
                                            @if($transaction->type === 'income')
                                                <svg class="h-5 w-5 text-finance-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5 text-finance-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-finance-gray-900">
                                            {{ $transaction->description }}
                                        </div>
                                        <div class="text-xs text-finance-gray-500">
                                            {{ $transaction->account->name }} • {{ $transaction->category->name }} • {{ $transaction->transaction_date->format('M j') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-sm font-semibold {{ $transaction->type === 'income' ? 'text-finance-green-600' : 'text-finance-red-600' }}">
                                    {{ $transaction->formatted_amount }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-finance-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-finance-gray-900">No transactions yet</h3>
                            <p class="mt-1 text-sm text-finance-gray-500">Get started by adding your first transaction.</p>
                            <div class="mt-6">
                                <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-finance-blue-600 hover:bg-finance-blue-700">
                                    Add Transaction
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Budget Progress --}}
            @if($budgetProgress)
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-finance-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-finance-gray-900">Budget Progress</h3>
                        <a href="{{ route('budgets.show', $budgetProgress['budget']) }}" class="text-sm text-finance-blue-600 hover:text-finance-blue-500 font-medium">
                            View details
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        {{-- Income Progress --}}
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-finance-gray-900">Income</span>
                                <span class="text-finance-gray-500">
                                    {{ $user->formatCurrency($budgetProgress['budget']->actual_income) }} of {{ $user->formatCurrency($budgetProgress['budget']->planned_income) }}
                                </span>
                            </div>
                            <div class="mt-2">
                                <div class="bg-finance-gray-200 rounded-full h-2">
                                    <div class="bg-finance-green-600 h-2 rounded-full transition-all duration-300" style="width: {{ min($budgetProgress['income_progress'], 100) }}%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Expense Progress --}}
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-finance-gray-900">Expenses</span>
                                <span class="text-finance-gray-500">
                                    {{ $user->formatCurrency($budgetProgress['budget']->actual_expenses) }} of {{ $user->formatCurrency($budgetProgress['budget']->planned_expenses) }}
                                </span>
                            </div>
                            <div class="mt-2">
                                <div class="bg-finance-gray-200 rounded-full h-2">
                                    <div class="{{ $budgetProgress['expense_progress'] > 100 ? 'bg-finance-red-600' : ($budgetProgress['expense_progress'] > 80 ? 'bg-yellow-500' : 'bg-finance-blue-600') }} h-2 rounded-full transition-all duration-300" style="width: {{ min($budgetProgress['expense_progress'], 100) }}%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Remaining Budget --}}
                        <div class="bg-finance-gray-50 rounded-lg p-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold {{ $budgetProgress['remaining_budget'] >= 0 ? 'text-finance-green-600' : 'text-finance-red-600' }}">
                                    {{ $user->formatCurrency($budgetProgress['remaining_budget']) }}
                                </div>
                                <div class="text-sm text-finance-gray-500 mt-1">
                                    {{ $budgetProgress['remaining_budget'] >= 0 ? 'Remaining in budget' : 'Over budget' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Financial Insights --}}
            @if($insights->isNotEmpty())
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-finance-gray-200">
                    <h3 class="text-lg font-medium text-finance-gray-900">Financial Insights</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($insights as $insight)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 {{ $insight['type'] === 'success' ? 'bg-finance-green-100' : ($insight['type'] === 'warning' ? 'bg-yellow-100' : 'bg-finance-blue-100') }} rounded-full flex items-center justify-center">
                                        <svg class="h-4 w-4 {{ $insight['type'] === 'success' ? 'text-finance-green-600' : ($insight['type'] === 'warning' ? 'text-yellow-600' : 'text-finance-blue-600') }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            @if($insight['icon'] === 'check-circle')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            @elseif($insight['icon'] === 'exclamation-triangle')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            @endif
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-finance-gray-900">{{ $insight['title'] }}</h4>
                                    <p class="text-sm text-finance-gray-600 mt-1">{{ $insight['message'] }}</p>
                                    <p class="text-xs text-finance-gray-500 mt-2">💡 {{ $insight['action'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column --}}
        <div class="space-y-8">
            
            {{-- Account Summary --}}
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-finance-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-finance-gray-900">Accounts</h3>
                        <a href="{{ route('accounts.index') }}" class="text-sm text-finance-blue-600 hover:text-finance-blue-500 font-medium">
                            View all
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach(['checking', 'savings', 'investment', 'credit'] as $accountType)
                            @if($accountsByType->has($accountType))
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="h-2 w-2 {{ $accountType === 'checking' ? 'bg-blue-500' : ($accountType === 'savings' ? 'bg-green-500' : ($accountType === 'investment' ? 'bg-purple-500' : 'bg-red-500')) }} rounded-full mr-3"></div>
                                        <span class="text-sm font-medium text-finance-gray-900 capitalize">{{ $accountType }}</span>
                                        <span class="text-xs text-finance-gray-500 ml-1">({{ $accountsByType[$accountType]->count() }})</span>
                                    </div>
                                    <span class="text-sm font-semibold {{ $accountType === 'credit' ? 'text-finance-red-600' : 'text-finance-gray-900' }}">
                                        {{ $user->formatCurrency($accountSummary[$accountType]) }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Financial Goals --}}
            @if($activeGoals->isNotEmpty())
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-finance-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-finance-gray-900">Active Goals</h3>
                        <a href="{{ route('goals.index') }}" class="text-sm text-finance-blue-600 hover:text-finance-blue-500 font-medium">
                            View all
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-finance-gray-200">
                    @foreach($activeGoals->take(3) as $goal)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-finance-gray-900">{{ $goal->name }}</span>
                                <span class="text-xs text-finance-gray-500">{{ $goal->target_date->format('M Y') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs text-finance-gray-600 mb-2">
                                <span>{{ $user->formatCurrency($goal->current_amount) }}</span>
                                <span>{{ $user->formatCurrency($goal->target_amount) }}</span>
                            </div>
                            <div class="bg-finance-gray-200 rounded-full h-2">
                                <div class="bg-finance-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ min(($goal->current_amount / max($goal->target_amount, 1)) * 100, 100) }}%"></div>
                            </div>
                            <div class="text-xs text-finance-gray-500 mt-1">
                                {{ number_format(($goal->current_amount / max($goal->target_amount, 1)) * 100, 1) }}% complete
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Upcoming Bills --}}
            @if($upcomingBills->isNotEmpty())
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-finance-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-finance-gray-900">Upcoming Bills</h3>
                        <a href="{{ route('bills.index') }}" class="text-sm text-finance-blue-600 hover:text-finance-blue-500 font-medium">
                            View all
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-finance-gray-200">
                    @foreach($upcomingBills as $bill)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-finance-gray-900">{{ $bill->name }}</div>
                                    <div class="text-xs text-finance-gray-500">
                                        Due {{ $bill->next_due_date->format('M j') }} 
                                        @if($bill->next_due_date->isToday())
                                            <span class="text-finance-red-600 font-medium">(Today)</span>
                                        @elseif($bill->next_due_date->isTomorrow())
                                            <span class="text-yellow-600 font-medium">(Tomorrow)</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-finance-red-600">
                                    {{ $user->formatCurrency($bill->amount) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quick Stats --}}
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-finance-gray-200">
                    <h3 class="text-lg font-medium text-finance-gray-900">Quick Stats</h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-finance-gray-500">Total Accounts</dt>
                            <dd class="text-sm font-medium text-finance-gray-900">{{ $quickStats['total_accounts'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-finance-gray-500">Transactions This Month</dt>
                            <dd class="text-sm font-medium text-finance-gray-900">{{ $quickStats['total_transactions_this_month'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-finance-gray-500">Largest Expense</dt>
                            <dd class="text-sm font-medium text-finance-gray-900">{{ $user->formatCurrency($quickStats['largest_expense_this_month']) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-finance-gray-500">Daily Average Spending</dt>
                            <dd class="text-sm font-medium text-finance-gray-900">{{ $user->formatCurrency($quickStats['average_daily_spending']) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Dashboard JavaScript --}}
@push('scripts')
<script>
function dashboardApp() {
    return {
        isRefreshing: false,
        
        async refreshDashboard() {
            this.isRefreshing = true;
            
            try {
                // In a real application, this would make an AJAX call to refresh data
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Show success message
                this.showNotification('Dashboard refreshed successfully!', 'success');
            } catch (error) {
                console.error('Error refreshing dashboard:', error);
                this.showNotification('Failed to refresh dashboard. Please try again.', 'error');
            }
            
            this.isRefreshing = false;
        },
        
        showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${this.getNotificationClasses(type)}`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <span class="mr-2">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remove after 3 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 3000);
        },
        
        getNotificationClasses(type) {
            switch (type) {
                case 'success':
                    return 'bg-finance-green-100 border border-finance-green-200 text-finance-green-800';
                case 'error':
                    return 'bg-finance-red-100 border border-finance-red-200 text-finance-red-800';
                default:
                    return 'bg-finance-blue-100 border border-finance-blue-200 text-finance-blue-800';
            }
        }
    }
}

// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    // In a real application, you might want to refresh specific components
    console.log('Auto-refresh would happen here');
}, 300000);
</script>
@endpush