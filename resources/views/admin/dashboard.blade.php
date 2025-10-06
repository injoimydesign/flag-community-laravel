@extends('layouts.admin')

@section('title', 'Admin Dashboard - Flags Across Our Community')

@section('header')
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Welcome back, {{ Auth::user()->first_name }}. Here's what's happening today.
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Export Data
            </button>
            <button type="button" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Generate Reports
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Total Customers -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Customers</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_customers']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.customers.index') }}" class="font-medium text-cyan-700 hover:text-cyan-900">
                        View all customers
                    </a>
                </div>
            </div>
        </div>

        <!-- Active Subscriptions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Subscriptions</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['active_subscriptions']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.subscriptions.index') }}" class="font-medium text-cyan-700 hover:text-cyan-900">
                        View all subscriptions
                    </a>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Monthly Revenue</dt>
                            <dd class="text-lg font-medium text-gray-900">${{ number_format($stats['monthly_revenue'] / 100, 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.reports.revenue') }}" class="font-medium text-cyan-700 hover:text-cyan-900">
                        View revenue report
                    </a>
                </div>
            </div>
        </div>

        <!-- Potential Customers -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Potential Customers</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['potential_customers']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.potential-customers.index') }}" class="font-medium text-cyan-700 hover:text-cyan-900">
                        View potential customers
                    </a>
                </div>
            </div>
        </div>

        <!-- Flags Placed This Month -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Flags Placed This Month</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['flags_placed_this_month']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.placements.index') }}" class="font-medium text-cyan-700 hover:text-cyan-900">
                        View all placements
                    </a>
                </div>
            </div>
        </div>

        <!-- Upcoming Placements -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Upcoming Placements (7 days)</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['upcoming_placements']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.placements.index') }}?status=scheduled" class="font-medium text-cyan-700 hover:text-cyan-900">
                        View scheduled placements
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    @if(isset($revenueChart['labels']) && count($revenueChart['labels']) > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Revenue Trend (Last 12 Months)</h3>
            <div class="h-80">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activity and Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Quick Actions Section for Admin Dashboard -->

<div class="bg-white shadow rounded-lg p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Create Subscription -->
        <a href="{{ route('admin.subscriptions.create') }}"
           class="block p-4 border-2 border-indigo-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-all">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">New Subscription</p>
                    <p class="text-xs text-gray-500">Create subscription</p>
                </div>
            </div>
        </a>

        <!-- Add Customer -->
        <a href="{{ route('admin.customers.create') }}"
           class="block p-4 border-2 border-green-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-all">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">New Customer</p>
                    <p class="text-xs text-gray-500">Add customer</p>
                </div>
            </div>
        </a>

        <!-- Create Placement -->
        <a href="{{ route('admin.placements.create') }}"
           class="block p-4 border-2 border-blue-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Create Placement</p>
                    <p class="text-xs text-gray-500">Schedule flag</p>
                </div>
            </div>
        </a>

        <!-- View Reports -->
        <a href="{{ route('admin.reports.index') }}"
           class="block p-4 border-2 border-purple-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-all">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">View Reports</p>
                    <p class="text-xs text-gray-500">Analytics</p>
                </div>
            </div>
        </a>
    </div>
</div>
    </div>

    <!-- Upcoming Tasks -->
    @if($upcomingPlacements->count() > 0 || $overduePlacements->count() > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Upcoming Tasks</h3>

            @if($overduePlacements->count() > 0)
                <div class="mb-6">
                    <h4 class="text-md font-medium text-red-600 mb-2">Overdue Placements</h4>
                    <div class="space-y-2">
                        @foreach($overduePlacements as $placement)
                            <div class="flex items-center justify-between py-2 px-3 bg-red-50 rounded-md">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        @if($placement->subscription && $placement->subscription->user)
                                            {{ $placement->subscription->user->full_name }}
                                        @else
                                            Unknown Customer
                                        @endif
                                        -
                                        @if($placement->holiday)
                                            {{ $placement->holiday->name }}
                                        @else
                                            Unknown Holiday
                                        @endif
                                    </p>
                                    <p class="text-sm text-red-600">
                                        Due: {{ $placement->placement_date->format('M j, Y') }}
                                    </p>
                                </div>
                                <a href="{{ route('admin.placements.show', $placement) }}" class="text-sm font-medium text-red-600 hover:text-red-500">
                                    View →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($upcomingPlacements->count() > 0)
                <div>
                    <h4 class="text-md font-medium text-blue-600 mb-2">Upcoming Placements (Next 7 Days)</h4>
                    <div class="space-y-2">
                        @foreach($upcomingPlacements as $placement)
                            <div class="flex items-center justify-between py-2 px-3 bg-blue-50 rounded-md">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        @if($placement->subscription && $placement->subscription->user)
                                            {{ $placement->subscription->user->full_name }}
                                        @else
                                            Unknown Customer
                                        @endif
                                        -
                                        @if($placement->holiday)
                                            {{ $placement->holiday->name }}
                                        @else
                                            Unknown Holiday
                                        @endif
                                    </p>
                                    <p class="text-sm text-blue-600">
                                        Due: {{ $placement->placement_date->format('M j, Y') }}
                                    </p>
                                </div>
                                <a href="{{ route('admin.placements.show', $placement) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                    View →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Low Inventory Alerts -->
    @if($lowInventoryProducts->count() > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Low Inventory Alerts</h3>
            <div class="space-y-3">
                @foreach($lowInventoryProducts as $product)
                    <div class="flex items-center justify-between py-2 px-3 bg-yellow-50 rounded-md">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                @if($product->flagType)
                                    {{ $product->flagType->name }}
                                @else
                                    Flag Product #{{ $product->id }}
                                @endif
                                @if($product->flagSize)
                                    ({{ $product->flagSize->name }})
                                @endif
                            </p>
                            <p class="text-sm text-yellow-600">
                                {{ $product->current_inventory }} remaining (threshold: {{ $product->low_inventory_threshold }})
                            </p>
                        </div>
                        <a href="{{ route('admin.flag-products.show', $product) }}" class="text-sm font-medium text-yellow-600 hover:text-yellow-500">
                            Restock →
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Upcoming Holidays -->
    @if($upcomingHolidays->count() > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Upcoming Holidays</h3>
            <div class="space-y-3">
                @foreach($upcomingHolidays as $holiday)
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $holiday->name }}</p>
                            <p class="text-sm text-gray-500">{{ $holiday->date->format('F j, Y') }}</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $holiday->date->diffForHumans() }}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.holidays.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    View all holidays →
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

@if(isset($revenueChart['labels']) && count($revenueChart['labels']) > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($revenueChart['labels']),
            datasets: [{
                label: 'Revenue ($)',
                data: @json($revenueChart['data']),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endif
@endsection
