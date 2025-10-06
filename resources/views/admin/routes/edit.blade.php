@extends('layouts.admin')

@section('title', 'Edit Route - Admin Dashboard')

@section('page-title', 'Edit Route')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Route</h1>
            <p class="text-gray-600 mt-1">Update route information</p>
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

        <!-- Edit Form -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <form action="{{ route('admin.routes.update', $route) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Route Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Route Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $route->name) }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
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
                        <option value="placement" {{ old('type', $route->type) === 'placement' ? 'selected' : '' }}>
                            Placement
                        </option>
                        <option value="removal" {{ old('type', $route->type) === 'removal' ? 'selected' : '' }}>
                            Removal
                        </option>
                    </select>
                </div>

                <!-- Assigned Driver -->
                <div class="mb-6">
                    <label for="assigned_user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Assign Driver
                    </label>
                    <select
                        name="assigned_user_id"
                        id="assigned_user_id"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">-- Unassigned --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}"
                                {{ old('assigned_user_id', $route->assigned_user_id) == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="status"
                        id="status"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    >
                        <option value="planned" {{ old('status', $route->status) === 'planned' ? 'selected' : '' }}>
                            Planned
                        </option>
                        <option value="in_progress" {{ old('status', $route->status) === 'in_progress' ? 'selected' : '' }}>
                            In Progress
                        </option>
                        <option value="completed" {{ old('status', $route->status) === 'completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                        <option value="cancelled" {{ old('status', $route->status) === 'cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes
                    </label>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="4"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Add any special instructions or notes..."
                    >{{ old('notes', $route->notes) }}</textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between">
                    <button
                        type="button"
                        onclick="confirmDelete()"
                        class="btn btn-danger"
                    >
                        Delete Route
                    </button>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.routes.show', $route) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Update Route
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Delete Form (hidden) -->
        <form id="delete-form" action="{{ route('admin.routes.destroy', $route) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <!-- Route Information -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-medium text-blue-900 mb-2">Route Information</h3>
            <div class="text-sm text-blue-800 space-y-1">
                <p><strong>Total Stops:</strong> {{ $route->total_stops }}</p>
                <p><strong>Created:</strong> {{ $route->created_at->format('M d, Y g:i A') }}</p>
                <p><strong>Last Updated:</strong> {{ $route->updated_at->format('M d, Y g:i A') }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this route? This action cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush
@endsection
