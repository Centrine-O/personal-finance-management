{{-- Categories Index View --}}
@extends('layouts.app')
@section('title', 'Categories')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <nav class="flex mb-4">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="{{ route('dashboard') }}" class="text-finance-blue-600">Dashboard</a></li>
                <li><span class="text-finance-gray-500">Categories</span></li>
            </ol>
        </nav>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-finance-gray-900">Categories</h1>
                <p class="mt-1 text-sm text-finance-gray-600">Organize your transactions with categories for better tracking.</p>
            </div>
            <a href="{{ route('categories.create') }}" class="px-4 py-2 bg-finance-blue-600 text-white rounded-md hover:bg-finance-blue-700">Add Category</a>
        </div>
    </div>

    {{-- Category Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-finance-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-finance-gray-500">Total Categories</dt>
                        <dd class="text-lg font-semibold text-finance-gray-900">{{ $categories->count() }}</dd>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-finance-green-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <dt class="text-sm font-medium text-finance-gray-500">Active Categories</dt>
                        <dd class="text-lg font-semibold text-finance-green-600">{{ $categories->where('is_active', true)->count() }}</dd>
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
                        <dt class="text-sm font-medium text-finance-gray-500">Parent Categories</dt>
                        <dd class="text-lg font-semibold text-finance-purple-600">{{ $categories->whereNull('parent_id')->count() }}</dd>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Categories List --}}
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-finance-gray-200">
            <h3 class="text-lg font-medium text-finance-gray-900">All Categories</h3>
        </div>

        @if($categories->count() > 0)
            <div class="divide-y divide-finance-gray-200">
                @foreach($categories->whereNull('parent_id') as $category)
                    {{-- Parent Category --}}
                    <div class="px-6 py-4 hover:bg-finance-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $category->color ?? '#6B7280' }}"></div>
                                <div>
                                    <h4 class="text-sm font-medium text-finance-gray-900">{{ $category->name }}</h4>
                                    @if($category->description)
                                        <p class="text-xs text-finance-gray-500">{{ $category->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="text-xs text-finance-gray-500">{{ $category->transactions_count ?? 0 }} transactions</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-finance-green-100 text-finance-green-800' : 'bg-finance-gray-100 text-finance-gray-800' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <div class="flex space-x-2">
                                    <a href="{{ route('categories.show', $category) }}" class="text-finance-gray-400 hover:text-finance-blue-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('categories.edit', $category) }}" class="text-finance-gray-400 hover:text-finance-blue-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Subcategories --}}
                    @foreach($category->children as $subcategory)
                        <div class="px-6 py-3 pl-12 bg-finance-gray-25 hover:bg-finance-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 rounded-full mr-3" style="background-color: {{ $subcategory->color ?? '#9CA3AF' }}"></div>
                                    <div>
                                        <h5 class="text-sm text-finance-gray-800">{{ $subcategory->name }}</h5>
                                        @if($subcategory->description)
                                            <p class="text-xs text-finance-gray-500">{{ $subcategory->description }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="text-xs text-finance-gray-500">{{ $subcategory->transactions_count ?? 0 }} transactions</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $subcategory->is_active ? 'bg-finance-green-100 text-finance-green-800' : 'bg-finance-gray-100 text-finance-gray-800' }}">
                                        {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('categories.show', $subcategory) }}" class="text-finance-gray-400 hover:text-finance-blue-600">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('categories.edit', $subcategory) }}" class="text-finance-gray-400 hover:text-finance-blue-600">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-finance-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-finance-gray-900">No categories yet</h3>
                <p class="mt-1 text-sm text-finance-gray-500">Get started by creating your first category to organize transactions.</p>
                <div class="mt-6">
                    <a href="{{ route('categories.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-finance-blue-600 hover:bg-finance-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Category
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection