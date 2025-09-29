@extends('layouts.admin')

@section('title', 'Create Flag Type - Admin Dashboard')

@section('page-title', 'Create Flag Type')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.flag-types.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Flag Types
        </a>
    </div>

    <!-- Create Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Create New Flag Type</h3>
            <p class="mt-1 text-sm text-gray-500">Add a new flag type to your catalog</p>
        </div>

        <form method="POST" action="{{ route('admin.flag-types.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="px-6 py-4 space-y-6">
                <!-- Basic Information -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-300 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select name="category" 
                                    id="category" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('category') border-red-300 @enderror">
                                <option value="">Select Category</option>
                                <option value="us" {{ old('category') === 'us' ? 'selected' : '' }}>US Flags</option>
                                <option value="military" {{ old('category') === 'military' ? 'selected' : '' }}>Military Flags</option>
                                <option value="state" {{ old('category') === 'state' ? 'selected' : '' }}>State Flags</option>
                                <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other</option>
                                @if(isset($categories))
                                    @foreach($categories as $cat)
                                        @if(!in_array($cat, ['us', 'military', 'state', 'other']))
                                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                                {{ ucfirst($cat) }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700">
                                Sort Order
                            </label>
                            <input type="number" 
                                   name="sort_order" 
                                   id="sort_order" 
                                   value="{{ old('sort_order', 0) }}"
                                   min="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('sort_order') border-red-300 @enderror">
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Lower numbers appear first</p>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Description
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Files -->
                <div class="pt-6 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Images & Files</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Image Upload -->
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700">
                                Flag Image
                            </label>
                            <input type="file" 
                                   name="image" 
                                   id="image" 
                                   accept="image/*"
                                   class="mt-1 block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-md file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100
                                          @error('image') border-red-300 @enderror">
                            @error('image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">JPG, PNG, GIF (max 2MB)</p>
                        </div>

                        <!-- Design File Upload -->
                        <div>
                            <label for="design_file" class="block text-sm font-medium text-gray-700">
                                Design File
                            </label>
                            <input type="file" 
                                   name="design_file" 
                                   id="design_file" 
                                   accept=".pdf,.ai,.psd,.svg"
                                   class="mt-1 block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-md file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100
                                          @error('design_file') border-red-300 @enderror">
                            @error('design_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">PDF, AI, PSD, SVG (max 10MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="pt-6 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Settings</h4>
                    <div class="space-y-4">
                        <!-- Active -->
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="active" 
                                   id="active" 
                                   value="1"
                                   {{ old('active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="active" class="ml-2 block text-sm text-gray-900">
                                Active
                            </label>
                            <p class="ml-2 text-sm text-gray-500">(Flag type will be visible and available)</p>
                        </div>

                        <!-- Featured -->
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="featured" 
                                   id="featured" 
                                   value="1"
                                   {{ old('featured') ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="featured" class="ml-2 block text-sm text-gray-900">
                                Featured
                            </label>
                            <p class="ml-2 text-sm text-gray-500">(Display prominently on homepage)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <a href="{{ route('admin.flag-types.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create Flag Type
                </button>
            </div>
        </form>
    </div>
</div>
@endsection