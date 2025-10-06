@extends('layouts.admin')

@section('title', 'Create Route - Admin Dashboard')

@section('page-title', 'Create New Route')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create New Route</h1>
            <p class="text-gray-600 mt-1">Create a universal route that can be used for all holidays</p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Create Form -->
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('admin.routes.store') }}" method="POST">
                @csrf

                <!-- Route Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Route Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="e.g., North Route, Downtown Route"
                        required
                    >
                    <p class="text-sm text-gray-500 mt-1">
                        This route will be used across all holidays. Choose a geographic or organizational name.
                    </p>
                </div>

                <!-- Route Type -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Route Type <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="type"
                        id="type"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                        <option value="">-- Select Type --</option>
                        <option value="placement" {{ old('type') === 'placement' ? 'selected' : '' }}>Placement</option>
                        <option value="removal" {{ old('type') === 'removal' ? 'selected' : '' }}>Removal</option>
                    </select>
                    <p class="text-sm text-gray-500 mt-1">
                        Placement routes are for putting flags out, removal routes are for taking them down.
                    </p>
                </div>

                <!-- Assigned Driver -->
                <div class="mb-6">
                    <label for="assigned_user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Assign Driver (Optional)
                    </label>
                    <select
                        name="assigned_user_id"
                        id="assigned_user_id"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">-- Unassigned --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ old('assigned_user_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-500 mt-1">
                        You can assign a driver now or later.
                    </p>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="4"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Add any special instructions or notes about this route..."
                    >{{ old('notes') }}</textarea>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">About Universal Routes</p>
                            <p>This route can be used for multiple holidays throughout the year. After creating the route, you'll be able to:</p>
                            <ul class="list-disc list-inside mt-2 ml-4">
                                <li>Add customer placements to the route</li>
                                <li>Filter placements by holiday</li>
                                <li>Optimize route order using Google Maps</li>
                                <li>Get turn-by-turn directions</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Create Route
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
