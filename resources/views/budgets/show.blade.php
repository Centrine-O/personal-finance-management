{{-- Budget Detail View --}}
@extends('layouts.app')
@section('title', $budget->name . ' - Budget Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <nav class="flex mb-4">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="{{ route('dashboard') }}" class="text-finance-blue-600">Dashboard</a></li>
                <li><a href="{{ route('budgets.index') }}" class="text-finance-blue-600">Budgets</a></li>
                <li><span class="text-finance-gray-500">{{ $budget->name }}</span></li>
            </ol>
        </nav>
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-finance-gray-900">{{ $budget->name }}</h1>
                <p class="mt-1 text-sm text-finance-gray-600">{{ $budget->start_date->format('M j, Y') }} - {{ $budget->end_date->format('M j, Y') }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('budgets.edit', $budget) }}" class="px-4 py-2 border border-finance-gray-300 rounded-md text-sm font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50">Edit Budget</a>
                <a href="{{ route('transactions.create', ['category' => $budget->category_id, 'type' => 'expense']) }}" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-blue-600 hover:bg-finance-blue-700">Add Expense</a>
            </div>
        </div>
    </div>

    @php
        $percentage = $budget->amount > 0 ? min(($budget->spent_amount / $budget->amount) * 100, 100) : 0;
        $isOverBudget = $budget->spent_amount > $budget->amount;
        $remaining = $budget->amount - $budget->spent_amount;
        $daysRemaining = now()->diffInDays($budget->end_date, false);
    @endphp

    {{-- Budget Overview Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-finance-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-finance-gray-500">Budget Amount</dt>
                        <dd class="text-lg font-semibold text-finance-blue-600">{{ Auth::user()->formatCurrency($budget->amount) }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-finance-red-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-finance-gray-500">Spent</dt>
                        <dd class="text-lg font-semibold text-finance-red-600">{{ Auth::user()->formatCurrency($budget->spent_amount) }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 {{ $remaining >= 0 ? 'bg-finance-green-500' : 'bg-finance-red-500' }} rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-finance-gray-500">{{ $remaining >= 0 ? 'Remaining' : 'Over Budget' }}</dt>
                        <dd class="text-lg font-semibold {{ $remaining >= 0 ? 'text-finance-green-600' : 'text-finance-red-600' }}">{{ Auth::user()->formatCurrency(abs($remaining)) }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-finance-gray-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-finance-gray-500">Days Left</dt>
                        <dd class="text-lg font-semibold text-finance-gray-900">{{ max(0, $daysRemaining) }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Section --}}
    <div class="bg-white shadow-sm rounded-lg mb-6">
        <div class="p-6">
            <h3 class="text-lg font-medium text-finance-gray-900 mb-4">Budget Progress</h3>
            <div class="mb-4">
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-finance-gray-700">{{ number_format($percentage, 1) }}% of budget used</span>
                    <span class="text-sm text-finance-gray-500">{{ Auth::user()->formatCurrency($budget->spent_amount) }} / {{ Auth::user()->formatCurrency($budget->amount) }}</span>
                </div>
                <div class="w-full bg-finance-gray-200 rounded-full h-4">
                    <div class="{{ $isOverBudget ? 'bg-finance-red-500' : ($percentage >= 80 ? 'bg-finance-yellow-500' : 'bg-finance-green-500') }} h-4 rounded-full transition-all duration-300" style="width: {{ min($percentage, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Transactions --}}
        <div class="lg:col-span-2 bg-white shadow-sm rounded-lg">
            <div class="px-6 py-5 border-b border-finance-gray-200">
                <h3 class="text-lg font-medium text-finance-gray-900">Recent Transactions</h3>
            </div>
            <div class="divide-y divide-finance-gray-200">
                @forelse($budget->getRecentTransactions() as $transaction)
                    <div class="px-6 py-4 hover:bg-finance-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-finance-gray-900">{{ $transaction->description }}</p>
                                <p class="text-xs text-finance-gray-500">{{ $transaction->transaction_date->format('M j, Y') }} • {{ $transaction->account->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-finance-red-600">-{{ Auth::user()->formatCurrency($transaction->amount) }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm text-finance-gray-500">No transactions yet for this budget period.</p>
                        <a href="{{ route('transactions.create', ['category' => $budget->category_id, 'type' => 'expense']) }}" class="mt-2 text-sm text-finance-blue-600 hover:text-finance-blue-500">Add your first expense</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Budget Info Sidebar --}}
        <div class="space-y-6">
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-5 border-b border-finance-gray-200">
                    <h3 class="text-lg font-medium text-finance-gray-900">Budget Details</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-finance-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-finance-gray-900">{{ $budget->category ? $budget->category->name : 'All Categories' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-finance-gray-500">Period</dt>
                        <dd class="mt-1 text-sm text-finance-gray-900">{{ $budget->start_date->format('M j, Y') }} - {{ $budget->end_date->format('M j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-finance-gray-500">Status</dt>
                        <dd class="mt-1"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $budget->is_active ? 'bg-finance-green-100 text-finance-green-800' : 'bg-finance-gray-100 text-finance-gray-800' }}">{{ $budget->is_active ? 'Active' : 'Inactive' }}</span></dd>
                    </div>
                    @if($budget->description)
                        <div>
                            <dt class="text-sm font-medium text-finance-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-finance-gray-900">{{ $budget->description }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection