{{-- Category Edit Form --}}
@extends('layouts.app')
@section('title', 'Edit ' . $category->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <nav class="flex mb-4">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="{{ route('dashboard') }}" class="text-finance-blue-600">Dashboard</a></li>
                <li><a href="{{ route('categories.index') }}" class="text-finance-blue-600">Categories</a></li>
                <li><a href="{{ route('categories.show', $category) }}" class="text-finance-blue-600">{{ $category->name }}</a></li>
                <li><span class="text-finance-gray-500">Edit</span></li>
            </ol>
        </nav>
        <h1 class="text-2xl font-bold text-finance-gray-900">Edit Category</h1>
    </div>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="p-6">
            <form method="POST" action="{{ route('categories.update', $category) }}">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-finance-gray-700">Category Name *</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500" value="{{ old('name', $category->name) }}">
                        @error('name')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Parent Category --}}
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-finance-gray-700">Parent Category</label>
                        <select name="parent_id" id="parent_id" class="mt-1 block w-full border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500">
                            <option value="">None (Top Level Category)</option>
                            @foreach(Auth::user()->categories()->whereNull('parent_id')->where('id', '!=', $category->id)->get() as $availableCategory)
                                <option value="{{ $availableCategory->id }}" {{ old('parent_id', $category->parent_id) == $availableCategory->id ? 'selected' : '' }}>{{ $availableCategory->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Color --}}
                    <div>
                        <label for="color" class="block text-sm font-medium text-finance-gray-700">Color</label>
                        <div class="mt-1 flex items-center space-x-3">
                            <input type="color" name="color" id="color" class="h-10 w-16 border border-finance-gray-300 rounded-md" value="{{ old('color', $category->color ?? '#3B82F6') }}">
                            <span class="text-sm text-finance-gray-500">Choose a color to identify this category</span>
                        </div>
                        @error('color')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-finance-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-finance-gray-300 rounded-md shadow-sm focus:ring-finance-blue-500 focus:border-finance-blue-500">{{ old('description', $category->description) }}</textarea>
                        @error('description')<p class="mt-2 text-sm text-finance-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Active Status --}}
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="focus:ring-finance-blue-500 h-4 w-4 text-finance-blue-600 border-finance-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_active" class="font-medium text-finance-gray-700">Category is active</label>
                            <p class="text-finance-gray-500">Active categories appear in transaction forms and reports.</p>
                        </div>
                    </div>
                </div>

                <div class="pt-6 flex justify-between">
                    <button type="button" onclick="confirmDelete()" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-finance-red-700 bg-finance-red-100 hover:bg-finance-red-200">Delete Category</button>
                    <div class="flex space-x-3">
                        <a href="{{ route('categories.show', $category) }}" class="px-4 py-2 border border-finance-gray-300 rounded-md text-sm font-medium text-finance-gray-700 hover:bg-finance-gray-50">Cancel</a>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-finance-blue-600 hover:bg-finance-blue-700">Update Category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Hidden Delete Form --}}
    <form id="deleteForm" method="POST" action="{{ route('categories.destroy', $category) }}" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
    function confirmDelete() {
        const transactionCount = {{ $category->transactions()->count() }};
        const subcategoryCount = {{ $category->children()->count() }};
        
        let message = `Are you sure you want to delete the category "${{'{{ $category->name }}'}}"?`;
        
        if (transactionCount > 0) {
            message += `\n\nThis category has ${transactionCount} transaction(s). These transactions will be uncategorized.`;
        }
        
        if (subcategoryCount > 0) {
            message += `\n\nThis category has ${subcategoryCount} subcategory(ies) that will become top-level categories.`;
        }
        
        message += '\n\nThis action cannot be undone.';
        
        if (confirm(message)) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endpush
@endsection