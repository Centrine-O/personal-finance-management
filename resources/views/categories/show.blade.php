{{-- Category Detail View --}}
@extends('layouts.app')
@section('title', $category->name . ' - Category Details')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <nav class="flex mb-4">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="{{ route('dashboard') }}" class="text-finance-blue-600">Dashboard</a></li>
                <li><a href="{{ route('categories.index') }}" class="text-finance-blue-600">Categories</a></li>
                <li><span class="text-finance-gray-500">{{ $category->name }}</span></li>
            </ol>
        </nav>
        <div class="flex items-start justify-between">
            <div class="flex items-center">
                <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $category->color ?? '#6B7280' }}"></div>
                <div>
                    <h1 class="text-2xl font-bold text-finance-gray-900">{{ $category->name }}</h1>
                    @if($category->parent)
                        <p class="text-sm text-finance-gray-600">Subcategory of {{ $category->parent->name }}</p>
                    @endif
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('categories.edit', $category) }}" class="px-4 py-2 border border-finance-gray-300 rounded-md text-sm font-medium text-finance-gray-700 bg-white hover:bg-finance-gray-50">Edit Category</a>
                <a href="{{ route('transactions.create', ['category' => $category->id]) }}" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-blue-600 hover:bg-finance-blue-700">Add Transaction</a>
            </div>
        </div>
    </div>

    {{-- Category Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-finance-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-finance-gray-500">Total Transactions</dt>
                        <dd class="text-lg font-semibold text-finance-gray-900">{{ $category->transactions()->count() }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-finance-green-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-finance-gray-500">Total Income</dt>
                        <dd class="text-lg font-semibold text-finance-green-600">{{ Auth::user()->formatCurrency($category->transactions()->where('type', 'income')->sum('amount')) }}</dd>
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
                        <dt class="text-sm font-medium text-finance-gray-500">Total Expenses</dt>
                        <dd class="text-lg font-semibold text-finance-red-600">{{ Auth::user()->formatCurrency($category->transactions()->where('type', 'expense')->sum('amount')) }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-finance-purple-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-finance-gray-500">Subcategories</dt>
                        <dd class="text-lg font-semibold text-finance-purple-600">{{ $category->children()->count() }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Transactions --}}
        <div class="lg:col-span-2 bg-white shadow-sm rounded-lg">
            <div class="px-6 py-5 border-b border-finance-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-finance-gray-900">Recent Transactions</h3>
                    <a href="{{ route('transactions.index', ['category_id' => $category->id]) }}" class="text-sm font-medium text-finance-blue-600 hover:text-finance-blue-500">View all</a>
                </div>
            </div>
            <div class="divide-y divide-finance-gray-200">
                @forelse($category->transactions()->latest()->take(10)->get() as $transaction)
                    <div class="px-6 py-4 hover:bg-finance-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-finance-gray-900">{{ $transaction->description }}</p>
                                <p class="text-xs text-finance-gray-500">{{ $transaction->transaction_date->format('M j, Y') }} • {{ $transaction->account->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold {{ $transaction->type === 'income' ? 'text-finance-green-600' : 'text-finance-red-600' }}">
                                    {{ $transaction->type === 'income' ? '+' : '-' }}{{ Auth::user()->formatCurrency($transaction->amount) }}
                                </p>
                                <p class="text-xs text-finance-gray-500 capitalize">{{ $transaction->type }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm text-finance-gray-500">No transactions in this category yet.</p>
                        <a href="{{ route('transactions.create', ['category' => $category->id]) }}" class="mt-2 text-sm text-finance-blue-600 hover:text-finance-blue-500">Add your first transaction</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Category Info & Subcategories --}}
        <div class="space-y-6">
            {{-- Category Details --}}
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-5 border-b border-finance-gray-200">
                    <h3 class="text-lg font-medium text-finance-gray-900">Category Details</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    @if($category->parent)
                        <div>
                            <dt class="text-sm font-medium text-finance-gray-500">Parent Category</dt>
                            <dd class="mt-1 text-sm text-finance-gray-900">
                                <a href="{{ route('categories.show', $category->parent) }}" class="text-finance-blue-600 hover:text-finance-blue-500">{{ $category->parent->name }}</a>
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-finance-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-finance-green-100 text-finance-green-800' : 'bg-finance-gray-100 text-finance-gray-800' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    @if($category->description)
                        <div>
                            <dt class="text-sm font-medium text-finance-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-finance-gray-900">{{ $category->description }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-finance-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-finance-gray-900">{{ $category->created_at->format('M j, Y') }}</dd>
                    </div>
                </div>
            </div>

            {{-- Subcategories --}}
            @if($category->children()->count() > 0)
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-5 border-b border-finance-gray-200">
                        <h3 class="text-lg font-medium text-finance-gray-900">Subcategories</h3>
                    </div>
                    <div class="divide-y divide-finance-gray-200">
                        @foreach($category->children as $child)
                            <div class="px-6 py-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $child->color ?? '#9CA3AF' }}"></div>
                                        <div>
                                            <h4 class="text-sm font-medium text-finance-gray-900">{{ $child->name }}</h4>
                                            <p class="text-xs text-finance-gray-500">{{ $child->transactions()->count() }} transactions</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('categories.show', $child) }}" class="text-finance-blue-600 hover:text-finance-blue-500 text-sm">View</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection