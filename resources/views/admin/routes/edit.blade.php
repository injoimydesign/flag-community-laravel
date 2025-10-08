@extends('layouts.admin')

@section('title', 'Edit Route - Admin Dashboard')

@section('page-title', 'Edit Route')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Route</h1>
            <p class="text-gray-600 mt-1">Update route information and manage customers</p>
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

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form - Left Column (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Edit Form -->
                <div class="bg-white shadow rounded-lg p-6">
                    <form action="{{ route('admin.routes.update', $route) }}" method="POST" id="route-form">
                        @csrf
                        @method('PUT')

                        <!-- Hidden field to store customer order -->
                        <input type="hidden" name="customer_order" id="customer_order" value="{{ json_encode($route->customer_order ?? []) }}">

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
                                <option value="delivery" {{ old('type', $route->type) === 'delivery' ? 'selected' : '' }}>
                                    Delivery
                                </option>
                            </select>
                        </div>

                        <!-- Assigned User -->
                        <div class="mb-6">
                            <label for="assigned_user_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Assign To
                            </label>
                            <select
                                name="assigned_user_id"
                                id="assigned_user_id"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">-- Unassigned --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_user_id', $route->assigned_user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
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

                <!-- Route Information -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-medium text-blue-900 mb-2">Route Information</h3>
                    <div class="text-sm text-blue-800 space-y-1">
                        <p><strong>Total Stops:</strong> <span id="total-stops">{{ $route->total_stops }}</span></p>
                        <p><strong>Created:</strong> {{ $route->created_at->format('M d, Y g:i A') }}</p>
                        <p><strong>Last Updated:</strong> {{ $route->updated_at->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Route Customers - Right Column (1/3) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Current Customers on Route -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4">Customers on Route (<span id="customer-count">{{ count($route->customer_order ?? []) }}</span>)</h2>
                    <div id="route-customers" class="space-y-2 max-h-96 overflow-y-auto">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Available Customers -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-semibold mb-4">Available Customers</h2>
                    <p class="text-sm text-gray-600 mb-4">Click to add to route</p>
                    <div id="available-customers" class="space-y-2 max-h-96 overflow-y-auto">
                        <p class="text-gray-500 text-sm">Loading...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Form (hidden) -->
        <form id="delete-form" action="{{ route('admin.routes.destroy', $route) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

@push('scripts')
<script>
const routeId = {{ $route->id }};
const baseUrl = '{{ url('/') }}';
let customerOrder = {!! json_encode($route->customer_order ?? []) !!};

// Build URL helper
function buildUrl(path) {
    path = path.replace(/^\//, '');
    return `${baseUrl}/${path}`;
}

// Update customer order in hidden field
function updateCustomerOrderField() {
    document.getElementById('customer_order').value = JSON.stringify(customerOrder);
    document.getElementById('customer-count').textContent = customerOrder.length;
    document.getElementById('total-stops').textContent = customerOrder.length;
}

// Load customers already on route
function loadRouteCustomers() {
    const container = document.getElementById('route-customers');

    if (customerOrder.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm">No customers added yet</p>';
        return;
    }

    // Fetch customer details for IDs in customerOrder
    fetch(buildUrl(`admin/routes/${routeId}/available-placements`))
        .then(response => response.json())
        .then(allCustomers => {
            // Get all customers (both on route and available)
            const url = buildUrl(`admin/routes/${routeId}/available-placements`);

            // We need to get customer details from somewhere
            // For now, render based on IDs
            container.innerHTML = customerOrder.map((customerId, index) => `
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded border">
                    <div class="flex items-center">
                        <span class="text-gray-500 mr-2">${index + 1}.</span>
                        <span class="text-sm">Customer ID: ${customerId}</span>
                    </div>
                    <button
                        onclick="removeFromRoute(${customerId})"
                        class="text-red-600 hover:text-red-800"
                        type="button">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            `).join('');
        });
}

// Load available customers
function loadAvailableCustomers() {
    const container = document.getElementById('available-customers');
    container.innerHTML = '<p class="text-gray-500 text-sm">Loading...</p>';

    const url = buildUrl(`admin/routes/${routeId}/available-placements`);

    fetch(url, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    })
    .then(data => {
        // Filter out customers already on route
        const available = data.filter(customer => !customerOrder.includes(customer.user_id));

        if (available.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm">No available customers</p>';
            return;
        }

        container.innerHTML = available.map(customer => `
            <div class="p-2 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer transition"
                 onclick="addToRoute(${customer.user_id}, '${customer.name.replace(/'/g, "\\'")}')"
                 title="Click to add to route">
                <p class="font-medium text-sm">${customer.name}</p>
                <p class="text-xs text-gray-600">${customer.address}</p>
                <p class="text-xs text-gray-500 mt-1">${customer.holiday_count} holiday${customer.holiday_count !== 1 ? 's' : ''}</p>
            </div>
        `).join('');
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = '<p class="text-red-500 text-sm">Error loading customers</p>';
    });
}

// Add customer to route
function addToRoute(customerId, customerName) {
    if (!customerOrder.includes(customerId)) {
        customerOrder.push(customerId);
        updateCustomerOrderField();
        loadRouteCustomers();
        loadAvailableCustomers();
    }
}

// Remove customer from route
function removeFromRoute(customerId) {
    customerOrder = customerOrder.filter(id => id !== customerId);
    updateCustomerOrderField();
    loadRouteCustomers();
    loadAvailableCustomers();
}

// Confirm delete
function confirmDelete() {
    if (confirm('Are you sure you want to delete this route? This action cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRouteCustomers();
    loadAvailableCustomers();
});
</script>
@endpush
@endsection
