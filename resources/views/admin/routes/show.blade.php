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

    <!-- Route Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Number of Stops -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Stops</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-stops">{{ $customersWithHolidays->count() }}</p>
                </div>
            </div>
        </div>

        <!-- US Flags -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">US Flags</p>
                    <p class="text-2xl font-bold text-gray-900" id="us-flags-count">{{ $flagCounts['us'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Military Flags -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Military Flags</p>
                    <p class="text-2xl font-bold text-gray-900" id="military-flags-count">{{ $flagCounts['military'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Estimated Time -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Est. Time</p>
                    <p class="text-2xl font-bold text-gray-900" id="estimated-time">{{ $estimatedTime ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer List -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Route Customers ({{ $customersWithHolidays->count() }})</h2>

        @if($customersWithHolidays->count() > 0)
            <div class="space-y-4">
                @foreach($customersWithHolidays as $index => $item)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition">
                        <div class="flex items-start">
                            <!-- Stop Number -->
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-semibold text-sm">
                                    {{ $index + 1 }}
                                </span>
                            </div>

                            <!-- Customer Details -->
                            <div class="ml-4 flex-1">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $item['user']->name }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ $item['user']->full_address }}
                                        </p>
                                        @if($item['user']->phone)
                                            <p class="text-sm text-gray-600 mt-1">
                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                {{ $item['user']->phone }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Flags Ordered -->
                                <div class="mt-3">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Flags Ordered:</p>
                                    <div class="space-y-1">
                                        @foreach($item['placements'] as $placement)
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                                </svg>
                                                <span class="font-medium">{{ $placement->quantity ?? 1 }}x {{ $placement->flagProduct->display_name ?? 'N/A' }}</span>
                                                <span class="mx-2">•</span>
                                                <span>{{ $placement->holiday->name ?? 'N/A' }}</span>
                                                @if($placement->placement_date)
                                                    <span class="mx-2">•</span>
                                                    <span>{{ $placement->placement_date->format('M d, Y') }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Order Notes -->
                                @if($item['subscription']->special_instructions)
                                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                        <p class="text-sm font-medium text-yellow-800 mb-1">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Notes:
                                        </p>
                                        <p class="text-sm text-yellow-700">{{ $item['subscription']->special_instructions }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-8">No customers added to this route yet.</p>
        @endif
    </div>

    <!-- Google Maps Section -->
    @if($customersWithHolidays->count() > 0)
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Route Map</h2>
            </div>

            <!-- Map Container -->
            <div id="map" class="w-full h-96 rounded-lg border mb-4"></div>

            <!-- Starting Address Input -->
            <div class="mb-4">
                <label for="starting-address" class="block text-sm font-medium text-gray-700 mb-2">
                    Starting Address for Directions
                </label>
                <div class="flex gap-2">
                    <input
                        type="text"
                        id="starting-address"
                        value="15531 Gladeridge Dr, Houston, TX"
                        class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Enter starting address"
                    >
                    <button onclick="getDirections()" class="btn btn-primary">
                        Get Directions
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Default: 15531 Gladeridge Dr, Houston, TX</p>
            </div>

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
            <h2 class="text-lg font-semibold mb-3">Route Notes</h2>
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
const baseUrl = '{{ url('/') }}';

// Build URL helper
function buildUrl(path) {
    path = path.replace(/^\//, '');
    return `${baseUrl}/${path}`;
}

// Initialize map
function initMap() {
    if (!document.getElementById('map')) return;

    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: { lat: 29.7604, lng: -95.3698 } // Houston
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);

    // Add markers for all stops
    addRouteMarkers();
}

// Add markers for all stops
function addRouteMarkers() {
    @foreach($customersWithHolidays as $index => $item)
        new google.maps.Marker({
            position: {
                lat: {{ $item['user']->latitude ?? 29.7604 }},
                lng: {{ $item['user']->longitude ?? -95.3698 }}
            },
            map: map,
            label: '{{ $index + 1 }}',
            title: '{{ $item['user']->name }}'
        });
    @endforeach
}

// Get turn-by-turn directions with custom starting address
function getDirections() {
    const startAddress = document.getElementById('starting-address').value.trim();

    if (!startAddress) {
        alert('Please enter a starting address');
        return;
    }

    const btn = event.target;
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin">⟳</span> Loading...';

    const url = buildUrl(`admin/routes/${routeId}/directions`) + `?start=${encodeURIComponent(startAddress)}`;

    console.log('Fetching directions from:', url);

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Directions data:', data);

        if (data.success && data.directions) {
            displayDirections(data.directions);
        } else {
            throw new Error(data.error || 'Failed to get directions');
        }
    })
    .catch(error => {
        console.error('Error getting directions:', error);
        alert('Error getting directions: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    });
}

// Display directions
function displayDirections(directions) {
    const panel = document.getElementById('directions-panel');
    const content = document.getElementById('directions-content');

    if (!panel || !content) return;

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
    if (document.getElementById('map')) {
        initMap();
    }
});
</script>
@endpush
@endsection
