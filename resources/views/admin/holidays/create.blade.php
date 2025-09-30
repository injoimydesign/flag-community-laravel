@extends('layouts.admin')

@section('title', 'Create Holiday - Admin Dashboard')

@section('page-title', 'Create Holiday')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.holidays.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Holidays
        </a>
    </div>

    <!-- Create Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Create New Holiday</h3>
            <p class="mt-1 text-sm text-gray-500">Add a new holiday to the system</p>
        </div>

        <form method="POST" action="{{ route('admin.holidays.store') }}">
            @csrf

            <div class="px-6 py-4 space-y-6">
                <!-- Basic Information -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Holiday Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name') }}"
                                   required
                                   placeholder="e.g., Memorial Day"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-300 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date -->
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">
                                Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="date" 
                                   id="date" 
                                   value="{{ old('date') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('date') border-red-300 @enderror">
                            @error('date')
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
                                      rows="3"
                                      placeholder="Brief description of the holiday"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Placement Schedule -->
                <div class="pt-6 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Placement Schedule</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Placement Days Before -->
                        <div>
                            <label for="placement_days_before" class="block text-sm font-medium text-gray-700">
                                Days Before to Place Flags <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="placement_days_before" 
                                   id="placement_days_before" 
                                   value="{{ old('placement_days_before', 1) }}"
                                   required
                                   min="0"
                                   max="30"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('placement_days_before') border-red-300 @enderror">
                            @error('placement_days_before')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">How many days before the holiday to place flags</p>
                        </div>

                        <!-- Removal Days After -->
                        <div>
                            <label for="removal_days_after" class="block text-sm font-medium text-gray-700">
                                Days After to Remove Flags <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="removal_days_after" 
                                   id="removal_days_after" 
                                   value="{{ old('removal_days_after', 1) }}"
                                   required
                                   min="0"
                                   max="30"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('removal_days_after') border-red-300 @enderror">
                            @error('removal_days_after')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">How many days after the holiday to remove flags</p>
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="pt-6 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Settings</h4>
                    <div class="space-y-4">
                        <!-- Active -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       name="active" 
                                       id="active" 
                                       value="1"
                                       {{ old('active', true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </div>
                            <div class="ml-3">
                                <label for="active" class="text-sm font-medium text-gray-900">
                                    Active
                                </label>
                                <p class="text-sm text-gray-500">Holiday will be visible and available for flag placements</p>
                            </div>
                        </div>

                        <!-- Recurring -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" 
                                       name="recurring" 
                                       id="recurring" 
                                       value="1"
                                       {{ old('recurring', true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </div>
                            <div class="ml-3">
                                <label for="recurring" class="text-sm font-medium text-gray-900">
                                    Recurring Annually
                                </label>
                                <p class="text-sm text-gray-500">Holiday repeats every year on the same date</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <a href="{{ route('admin.holidays.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create Holiday
                </button>
            </div>
        </form>
    </div>
</div>
@endsection