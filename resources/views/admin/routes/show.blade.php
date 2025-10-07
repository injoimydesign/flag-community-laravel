@extends('layouts.admin')

@section('title', 'Route Details - Admin Dashboard')

@section('page-title', 'Route Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $route->name }}</h1>
            <p class="text-gray-600 mt-1">
                <span class="capitalize">{{ $route->type }}</span> Route
                @if($route->assignedUser)
                    • Assigned to {{ $route->assignedUser->name }}
                @endif
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.routes.edit', $route) }}" class="btn btn-secondary">
                Edit Route
            </a>
            @if($route->status === 'planned')
                <form action="{{ route('admin.routes.start', $route) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">Start Route</button>
                </form>
            @elseif($route->status === 'in_progress')
                <form action="{{ route('admin.routes.complete', $route) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Complete Route</button>
                </form>
            @endif
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-6">
        <span class="px-3 py-1 rounded-full text-sm font-medium
            @if($route->status === 'planned') bg-blue-100 text-blue-800
            @elseif($route->status === 'in_progress') bg-yellow-100 text-yellow-800
            @elseif($route->status === 'completed') bg-green-100 text-green-800
            @else bg-gray-100 text-gray-800
            @endif">
            {{ ucfirst(str_replace('_', ' ', $route->status)) }}
        </span>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Holiday Filter -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Filter by Holiday (Optional)</h2>
        <p class="text-sm text-gray-600 mb-4">Use this filter to view only customers with a specific holiday. You can add customers to the route without selecting a holiday.</p>
        <form method="GET" action="{{ route('admin.routes.show', $route) }}" class="flex items-end space-x-4">
            <div class="flex-1">
                <label for="holiday_id" class="block text-sm font-medium text-gray-700 mb-2">Select Holiday to Filter View</label>
                <select name="holiday_id" id="holiday_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- All Customers (No Filter) --</option>
                    @foreach($holidays as $holiday)
                        <option value="{{ $holiday->id }}" {{ $selectedHolidayId == $holiday->id ? 'selected' : '' }}>
                            {{ $holiday->name }} - {{ $holiday->date->format('M d, Y') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                Apply Filter
            </button>
            @if($selectedHolidayId)
                <a href="{{ route('admin.routes.show', $route) }}" class="btn btn-secondary">
                    Clear Filter
                </a>
            @endif
        </form>
    </div>

    <!-- Route Management Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Available Customers -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Available Customers</h2>
            <p class="text-sm text-gray-600 mb-4">
                @if($selectedHolidayId)
                    Showing customers with the selected holiday. <a href="{{ route('admin.routes.show', $route) }}" class="text-indigo-600 hover:underline">Show all customers</a>
                @else
                    Showing all available customers. Click to add to route.
                @endif
            </p>
            <div id="available-placements" class="space-y-3 max-h-96 overflow-y-auto">
                <p class="text-gray-500 text-sm">Loading available customers...</p>
            </div>
        </div>

        <!-- Route Customers -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-lg font-semibold">Route Customers ({{ $customersWithHolidays->count() }})</h2>
                    @if($selectedHolidayId)
                        <p class="text-sm text-gray-500">Filtered view - <a href="{{ route('admin.routes.show', $route) }}" class="text-indigo-600 hover:underline">show all</a></p>
                    @endif
                </div>
                @if($customersWithHolidays->count() > 1)
                    <button onclick="optimizeRoute()" class="btn btn-sm btn-secondary">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Optimize Route
                    </button>
                @endif
            </div>
            <div id="route-placements" class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($customersWithHolidays as $item)
                    <div class="placement-item p-3 bg-gray-50 rounded border" data-user-id="{{ $item['user']->id }}">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <p class="font-medium">{{ $item['user']->name }}</p>
                                <p class="text-sm text-gray-600">{{ $item['user']->full_address }}</p>
                            </div>
                            <button onclick="removePlacement({{ $item['user']->id }})" class="text-red-600 hover:text-red-800 ml-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach($item['holidays'] as $holiday)
                                <span class="text-xs px-2 py-1 rounded-full {{ $selectedHolidayId == $holiday['id'] ? 'bg-indigo-100 text-indigo-800 font-medium' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $holiday['name'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">
                        @if($selectedHolidayId)
                            No customers with the selected holiday on this route. <a href="{{ route('admin.routes.show', $route) }}" class="text-indigo-600 hover:underline">Clear filter to see all customers</a>
                        @else
                            No customers added to this route yet.
                        @endif
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Google Maps Section -->
    @if($customersWithHolidays->count() > 0)
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Route Map & Directions</h2>
                <button onclick="getDirections()" class="btn btn-secondary">
                    Get Turn-by-Turn Directions
                </button>
            </div>

            <!-- Map Container -->
            <div id="map" class="w-full h-96 rounded-lg border mb-4"></div>

            <!-- Directions Panel -->
            <div id="directions-panel" class="hidden">
                <h3 class="text-md font-semibold mb-3">Turn-by-Turn Directions</h3>
                <div id="directions-content" class="space-y-4"></div>
            </div>
        </div>
    @endif

    <!-- Route Notes -->
    @if($route->notes)
        <div class="bg-white shadow rounded-lg p-6 mt-6">
            <h2 class="text-lg font-semibold mb-3">Notes</h2>
            <p class="text-gray-700">{{ $route->notes }}</p>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places"></script>
<script>
let map;
let directionsService;
let directionsRenderer;
const routeId = {{ $route->id }};
const holidayId = {{ $selectedHolidayId ?? 'null' }};

// Initialize map
function initMap() {
    if (!document.getElementById('map')) return;

    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: { lat: 30.6280, lng: -96.3344 }
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);
}

// Load available placements
function loadAvailablePlacements() {
    const container = document.getElementById('available-placements');

    fetch(`/admin/routes/${routeId}/available-placements`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Available customers data:', data); // Debug log

            if (!Array.isArray(data)) {
                console.error('Expected array, got:', typeof data, data);
                container.innerHTML = '<p class="text-red-500 text-sm">Invalid data format received.</p>';
                return;
            }

            if (data.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm">No available customers. All customers are already on this route.</p>';
                return;
            }

            // Filter by holiday on client side if holiday is selected
            let filteredData = data;
            if (holidayId) {
                filteredData = data.filter(customer =>
                    customer.holidays && Array.isArray(customer.holidays) &&
                    customer.holidays.some(holiday => holiday.id == holidayId)
                );
            }

            if (filteredData.length === 0 && holidayId) {
                container.innerHTML = '<p class="text-gray-500 text-sm">No customers with the selected holiday available. <a href="' + window.location.pathname + '" class="text-indigo-600 hover:underline">Show all customers</a></p>';
                return;
            }

            container.innerHTML = filteredData.map(customer => {
                // Ensure all required fields exist
                const userId = customer.user_id || '';
                const name = customer.name || 'Unknown';
                const address = customer.address || 'No address';
                const holidays = customer.holidays || [];
                const holidayCount = customer.holiday_count || holidays.length;

                return `
                <div class="p-3 bg-gray-50 rounded border hover:bg-gray-100 cursor-pointer transition" onclick="addPlacement(${userId})">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <p class="font-medium">${name}</p>
                            <p class="text-sm text-gray-600">${address}</p>
                        </div>
                        <button class="text-green-600 hover:text-green-800 ml-2" type="button">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-1 mt-2">
                        ${holidays.map(holiday => `
                            <span class="text-xs px-2 py-1 rounded-full ${holiday.id == holidayId ? 'bg-indigo-100 text-indigo-800 font-medium' : 'bg-gray-100 text-gray-700'}" title="${holiday.date || ''}">
                                ${holiday.name || 'Unknown Holiday'}
                            </span>
                        `).join('')}
                    </div>
                    <p class="text-xs text-gray-500 mt-2">${holidayCount} holiday${holidayCount !== 1 ? 's' : ''}</p>
                </div>
            `}).join('');
        })
        .catch(error => {
            console.error('Error loading placements:', error);
            container.innerHTML =
                `<p class="text-red-500 text-sm">Error loading customers: ${error.message}</p>
                 <button onclick="loadAvailablePlacements()" class="text-indigo-600 hover:underline text-sm mt-2">Retry</button>`;
        });
}

// Add placement to route
function addPlacement(userId) {
    fetch(`/admin/routes/${routeId}/add-placement`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Remove placement from route
function removePlacement(userId) {
    if (!confirm('Remove this placement from the route?')) return;

    fetch(`/admin/routes/${routeId}/remove-placement`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Optimize route using Google Maps
function optimizeRoute() {
    if (!confirm('Optimize this route for minimum travel time?')) return;

    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin">⟳</span> Optimizing...';

    fetch(`/admin/routes/${routeId}/optimize`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Route optimized successfully!');
            location.reload();
        } else {
            alert('Error optimizing route: ' + (data.error || 'Unknown error'));
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Optimize Route';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error optimizing route');
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Optimize Route';
    });
}

// Get turn-by-turn directions
function getDirections() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin">⟳</span> Loading...';

    fetch(`/admin/routes/${routeId}/directions`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayDirections(data.directions);
                btn.disabled = false;
                btn.innerHTML = 'Get Turn-by-Turn Directions';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.disabled = false;
            btn.innerHTML = 'Get Turn-by-Turn Directions';
        });
}

// Display directions
function displayDirections(directions) {
    const panel = document.getElementById('directions-panel');
    const content = document.getElementById('directions-content');

    panel.classList.remove('hidden');

    let html = `
        <div class="bg-blue-50 p-4 rounded-lg mb-4">
            <p class="font-semibold">Total Distance: ${directions.total_distance}</p>
            <p class="font-semibold">Estimated Time: ${directions.total_duration}</p>
        </div>
    `;

    directions.stops.forEach((stop, index) => {
        html += `
            <div class="border-l-4 border-indigo-500 pl-4 mb-4">
                <h4 class="font-semibold text-lg mb-2">
                    Stop ${stop.stop_number}: ${stop.customer_name}
                </h4>
                <p class="text-sm text-gray-600 mb-2">${stop.address}</p>
                <p class="text-sm mb-2">
                    <span class="font-medium">Distance:</span> ${stop.distance} •
                    <span class="font-medium">Duration:</span> ${stop.duration}
                </p>
                <div class="space-y-1">
                    ${stop.steps.map((step, i) => `
                        <p class="text-sm pl-4">
                            <span class="font-medium">${i + 1}.</span> ${step.instruction}
                            <span class="text-gray-500">(${step.distance})</span>
                        </p>
                    `).join('')}
                </div>
            </div>
        `;
    });

    content.innerHTML = html;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Always load available placements (no holiday required)
    loadAvailablePlacements();

    // Initialize map if customers exist
    if (document.getElementById('map')) {
        initMap();
    }
});
</script>
@endpush
@endsection
