{{-- 
    Budgets Index View
    This view displays all user budgets with their progress, spending status,
    and provides comprehensive budget management functionality.
--}}

{{-- Extend the main application layout --}}
@extends('layouts.app')

{{-- Set the page title --}}
@section('title', 'Budgets')

{{-- Main content section --}}
@section('content')
<div class="max-w-7xl mx-auto">
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
                        <span class="ml-1 text-sm font-medium text-finance-gray-500 md:ml-2">Budgets</span>
                    </div>
                </li>
            </ol>
        </nav>

        {{-- Page Title and Actions --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-finance-gray-900">Budget Management</h1>
                <p class="mt-1 text-sm text-finance-gray-600">
                    Track your spending against budgets to stay on top of your financial goals.
                </p>
            </div>
            
            {{-- Add Budget Button --}}
            <a href="{{ route('budgets.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Budget
            </a>
        </div>
    </div>

    {{-- Budget Overview Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        {{-- Total Budget Amount --}}
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
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Total Budget</dt>
                            <dd class="text-lg font-semibold text-finance-blue-600">
                                {{ Auth::user()->formatCurrency($budgetSummary['total_budget'] ?? 0) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Spent --}}
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
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Total Spent</dt>
                            <dd class="text-lg font-semibold text-finance-red-600">
                                {{ Auth::user()->formatCurrency($budgetSummary['total_spent'] ?? 0) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Remaining Budget --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-finance-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Remaining</dt>
                            <dd class="text-lg font-semibold {{ ($budgetSummary['remaining'] ?? 0) >= 0 ? 'text-finance-green-600' : 'text-finance-red-600' }}">
                                {{ Auth::user()->formatCurrency($budgetSummary['remaining'] ?? 0) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Budgets Count --}}
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
                            <dt class="text-sm font-medium text-finance-gray-500 truncate">Active Budgets</dt>
                            <dd class="text-lg font-semibold text-finance-gray-900">
                                {{ $budgets->where('is_active', true)->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Budget Performance Alerts --}}
    @php
        $overBudgetCount = $budgets->filter(function($budget) { 
            return $budget->spent_amount > $budget->amount && $budget->is_active; 
        })->count();
        $nearBudgetCount = $budgets->filter(function($budget) { 
            $percentage = $budget->amount > 0 ? ($budget->spent_amount / $budget->amount) * 100 : 0;
            return $percentage >= 80 && $percentage <= 100 && $budget->is_active; 
        })->count();
    @endphp

    @if($overBudgetCount > 0 || $nearBudgetCount > 0)
        <div class="mb-6">
            @if($overBudgetCount > 0)
                <div class="bg-finance-red-50 border-l-4 border-finance-red-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-finance-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-finance-red-800">
                                Budget Alert: {{ $overBudgetCount }} {{ Str::plural('budget', $overBudgetCount) }} over limit
                            </h3>
                            <p class="mt-2 text-sm text-finance-red-700">
                                You have exceeded the spending limit for {{ $overBudgetCount }} of your budgets. Consider reviewing your spending or adjusting your budget amounts.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if($nearBudgetCount > 0)
                <div class="bg-finance-yellow-50 border-l-4 border-finance-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-finance-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-finance-yellow-800">
                                Budget Warning: {{ $nearBudgetCount }} {{ Str::plural('budget', $nearBudgetCount) }} approaching limit
                            </h3>
                            <p class="mt-2 text-sm text-finance-yellow-700">
                                You're close to reaching the spending limit for {{ $nearBudgetCount }} of your budgets. Monitor your spending carefully.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Budgets List Section --}}
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        {{-- Section Header --}}
        <div class="px-6 py-4 border-b border-finance-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-finance-gray-900">Your Budgets</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-finance-gray-500">{{ $budgets->count() }} total</span>
                </div>
            </div>
        </div>

        {{-- Budgets Grid --}}
        @if($budgets->count() > 0)
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($budgets as $budget)
                        @php
                            $percentage = $budget->amount > 0 ? min(($budget->spent_amount / $budget->amount) * 100, 100) : 0;
                            $isOverBudget = $budget->spent_amount > $budget->amount;
                            $isNearBudget = $percentage >= 80 && $percentage <= 100;
                            $daysRemaining = now()->diffInDays($budget->end_date, false);
                            $progressColor = $isOverBudget ? 'bg-finance-red-500' : ($isNearBudget ? 'bg-finance-yellow-500' : 'bg-finance-green-500');
                        @endphp
                        
                        <div class="bg-white border border-finance-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200">
                            {{-- Budget Header --}}
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center min-w-0 flex-1">
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-lg font-semibold text-finance-gray-900 truncate">
                                            {{ $budget->name }}
                                        </h4>
                                        <div class="flex items-center mt-1 text-sm text-finance-gray-500">
                                            @if($budget->category)
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                                <span>{{ $budget->category->name }}</span>
                                            @else
                                                <span class="italic">All categories</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Budget Status Badge --}}
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $budget->is_active ? 'bg-finance-green-100 text-finance-green-800' : 'bg-finance-gray-100 text-finance-gray-800' }}">
                                    {{ $budget->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            {{-- Budget Progress --}}
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-finance-gray-700">
                                        Spent: {{ Auth::user()->formatCurrency($budget->spent_amount) }}
                                    </span>
                                    <span class="text-sm text-finance-gray-500">
                                        of {{ Auth::user()->formatCurrency($budget->amount) }}
                                    </span>
                                </div>
                                
                                {{-- Progress Bar --}}
                                <div class="w-full bg-finance-gray-200 rounded-full h-2">
                                    <div class="{{ $progressColor }} h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                
                                {{-- Progress Percentage --}}
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs {{ $isOverBudget ? 'text-finance-red-600 font-semibold' : ($isNearBudget ? 'text-finance-yellow-600' : 'text-finance-green-600') }}">
                                        {{ number_format($percentage, 1) }}% used
                                    </span>
                                    @if($isOverBudget)
                                        <span class="text-xs text-finance-red-600 font-semibold">
                                            {{ Auth::user()->formatCurrency($budget->spent_amount - $budget->amount) }} over
                                        </span>
                                    @else
                                        <span class="text-xs text-finance-gray-500">
                                            {{ Auth::user()->formatCurrency($budget->amount - $budget->spent_amount) }} left
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Budget Period Info --}}
                            <div class="mb-4 text-xs text-finance-gray-500">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $budget->start_date->format('M j') }} - {{ $budget->end_date->format('M j, Y') }}
                                    </span>
                                    <span class="flex items-center">
                                        @if($daysRemaining > 0)
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $daysRemaining }} {{ Str::plural('day', $daysRemaining) }} left
                                        @elseif($daysRemaining === 0)
                                            <span class="text-finance-yellow-600 font-medium">Ends today</span>
                                        @else
                                            <span class="text-finance-red-600 font-medium">Expired</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            {{-- Budget Description --}}
                            @if($budget->description)
                                <p class="text-sm text-finance-gray-600 mb-4 line-clamp-2">
                                    {{ $budget->description }}
                                </p>
                            @endif

                            {{-- Budget Actions --}}
                            <div class="flex items-center justify-between pt-4 border-t border-finance-gray-100">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('budgets.show', $budget) }}" class="text-finance-blue-600 hover:text-finance-blue-500 text-sm font-medium">
                                        View Details
                                    </a>
                                    <span class="text-finance-gray-300">•</span>
                                    <a href="{{ route('budgets.edit', $budget) }}" class="text-finance-gray-600 hover:text-finance-gray-500 text-sm">
                                        Edit
                                    </a>
                                </div>
                                
                                {{-- Quick Add Transaction --}}
                                <a href="{{ route('transactions.create', ['category' => $budget->category_id, 'type' => 'expense']) }}" 
                                   class="inline-flex items-center px-2 py-1 border border-finance-gray-300 rounded text-xs font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Expense
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-finance-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-finance-gray-900">No budgets yet</h3>
                <p class="mt-1 text-sm text-finance-gray-500">
                    Get started by creating your first budget to track and control your spending.
                </p>
                <div class="mt-6">
                    <a href="{{ route('budgets.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-finance-blue-600 hover:bg-finance-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-finance-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Your First Budget
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection