{{-- Budget Creation Form --}}
@extends('layouts.app')
@section('title', 'Create Budget')

@section('content')
<div class="max-w-2xl mx-auto" x-data="budgetForm()">
    <div class="mb-8">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="{{ route('dashboard') }}" class="text-finance-blue-600">Dashboard</a></li>
                <li><a href="{{ route('budgets.index') }}" class="text-finance-blue-600">Budgets</a></li>
                <li><span class="text-finance-gray-500">Create Budget</span></li>
            </ol>
        </nav>
        <h1 class="text-2xl font-bold text-finance-gray-900">Create New Budget</h1>
    </div>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('budgets.store') }}">
                @csrf
                <div class="space-y-6">
                    {{-- Basic Info --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-finance-gray-700">Budget Name *</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500" value="{{ old('name') }}">
                        @error('name')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label for="amount" class="block text-sm font-medium text-finance-gray-700">Budget Amount *</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-finance-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="amount" id="amount" step="0.01" min="0.01" required class="block w-full pl-7 border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500" value="{{ old('amount') }}">
                        </div>
                        @error('amount')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Category --}}
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-finance-gray-700">Category</label>
                        <select name="category_id" id="category_id" class="mt-1 block w-full border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500">
                            <option value="">All Categories</option>
                            @foreach(Auth::user()->categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Date Range --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-finance-gray-700">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" required class="mt-1 block w-full border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500" value="{{ old('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                            @error('start_date')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-finance-gray-700">End Date *</label>
                            <input type="date" name="end_date" id="end_date" required class="mt-1 block w-full border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500" value="{{ old('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
                            @error('end_date')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-finance-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Active Status --}}
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="focus:ring-finance-blue-500 h-4 w-4 text-finance-blue-600 border-finance-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_active" class="font-medium text-finance-gray-700">Budget is active</label>
                            <p class="text-finance-gray-500">Active budgets will track spending and show progress.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-6 flex justify-end space-x-3">
                    <a href="{{ route('budgets.index') }}" class="px-4 py-2 border border-finance-gray-300 rounded-md text-sm font-medium text-finance-gray-700 hover:bg-finance-gray-50">Cancel</a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-blue-600 hover:bg-finance-blue-700">Create Budget</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function budgetForm() {
        return {
            init() {
                // Auto-set end date when start date changes
                document.getElementById('start_date').addEventListener('change', (e) => {
                    const startDate = new Date(e.target.value);
                    const endDate = new Date(startDate);
                    endDate.setMonth(endDate.getMonth() + 1);
                    endDate.setDate(0); // Last day of the month
                    document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
                });
            }
        }
    }
</script>
@endpush
@endsection