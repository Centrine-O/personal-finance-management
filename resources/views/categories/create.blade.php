{{-- Category Create Form --}}
@extends('layouts.app')
@section('title', 'Create Category')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <nav class="flex mb-4">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="{{ route('dashboard') }}" class="text-finance-blue-600">Dashboard</a></li>
                <li><a href="{{ route('categories.index') }}" class="text-finance-blue-600">Categories</a></li>
                <li><span class="text-finance-gray-500">Create Category</span></li>
            </ol>
        </nav>
        <h1 class="text-2xl font-bold text-finance-gray-900">Create New Category</h1>
    </div>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                <div class="space-y-6">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-finance-gray-700">Category Name *</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500" value="{{ old('name') }}">
                        @error('name')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Parent Category --}}
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-finance-gray-700">Parent Category</label>
                        <select name="parent_id" id="parent_id" class="mt-1 block w-full border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500">
                            <option value="">None (Top Level Category)</option>
                            @foreach(Auth::user()->categories()->whereNull('parent_id')->get() as $category)
                                <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                        <p class="mt-2 text-sm text-finance-gray-500">Select a parent to create a subcategory, or leave blank for a top-level category.</p>
                    </div>

                    {{-- Color --}}
                    <div>
                        <label for="color" class="block text-sm font-medium text-finance-gray-700">Color</label>
                        <div class="mt-1 flex items-center space-x-3">
                            <input type="color" name="color" id="color" class="h-10 w-16 border border-finance-gray-300 rounded-md" value="{{ old('color', '#3B82F6') }}">
                            <span class="text-sm text-finance-gray-500">Choose a color to identify this category</span>
                        </div>
                        @error('color')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
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
                            <label for="is_active" class="font-medium text-finance-gray-700">Category is active</label>
                            <p class="text-finance-gray-500">Active categories appear in transaction forms and reports.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-6 flex justify-end space-x-3">
                    <a href="{{ route('categories.index') }}" class="px-4 py-2 border border-finance-gray-300 rounded-md text-sm font-medium text-finance-gray-700 hover:bg-finance-gray-50">Cancel</a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-blue-600 hover:bg-finance-blue-700">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection