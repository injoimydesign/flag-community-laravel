@extends('layouts.admin')

@section('title', 'Edit Flag Type - Admin Dashboard')

@section('page-title', 'Edit Flag Type')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.flag-types.show', $flagType) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Flag Type Details
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Edit Flag Type: {{ $flagType->name }}</h3>
            <p class="mt-1 text-sm text-gray-500">Update flag type information and settings</p>
        </div>

        <form method="POST" action="{{ route('admin.flag-types.update', $flagType) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

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
                                   value="{{ old('name', $flagType->name) }}"
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
                                <option value="us" {{ old('category', $flagType->category) === 'us' ? 'selected' : '' }}>US Flags</option>
                                <option value="military" {{ old('category', $flagType->category) === 'military' ? 'selected' : '' }}>Military Flags</option>
                                <option value="state" {{ old('category', $flagType->category) === 'state' ? 'selected' : '' }}>State Flags</option>
                                <option value="other" {{ old('category', $flagType->category) === 'other' ? 'selected' : '' }}>Other</option>
                                @if(isset($categories))
                                    @foreach($categories as $cat)
                                        @if(!in_array($cat, ['us', 'military', 'state', 'other']))
                                            <option value="{{ $cat }}" {{ old('category', $flagType->category) === $cat ? 'selected' : '' }}>
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
                                   value="{{ old('sort_order', $flagType->sort_order ?? 0) }}"
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
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-300 @enderror">{{ old('description', $flagType->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Current Images -->
                @if($flagType->image_path || $flagType->image_url)
                <div class="pt-6 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Current Image</h4>
                    <div class="flex items-center space-x-4">
                        <img src="{{ $flagType->image_path ? asset('storage/' . $flagType->image_path) : $flagType->image_url }}" 
                             alt="{{ $flagType->name }}" 
                             class="h-32 w-48 object-cover rounded-lg border border-gray-300">
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">Upload a new image below to replace this one</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Files -->
                <div class="pt-6 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">{{ $flagType->image_path || $flagType->image_url ? 'Update' : 'Upload' }} Images & Files</h4>
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
                                @if($flagType->design_file_path)
                                    <span class="text-green-600">(File exists)</span>
                                @endif
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
                            @if($flagType->design_file_path)
                                <a href="{{ asset('storage/' . $flagType->design_file_path) }}" 
                                   target="_blank"
                                   class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download Current File
                                </a>
                            @endif
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
                                   {{ old('active', $flagType->active) ? 'checked' : '' }}
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
                                   {{ old('featured', $flagType->featured) ? 'checked' : '' }}
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
                <a href="{{ route('admin.flag-types.show', $flagType) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Flag Type
                </button>
            </div>
        </form>
    </div>
</div>
@endsection