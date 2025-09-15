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
<div class="space-y-6" x-data="dashboard()">
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
                    <a href="{{ route('admin.customers.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
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
                    <a href="{{ route('admin.subscriptions.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
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
                            <dd class="text-lg font-medium text-gray-900">${{ number_format($stats['monthly_revenue'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.reports.revenue') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        View revenue reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Potential Customers -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <a href="{{ route('admin.potential-customers.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        View waitlist
                    </a>
                </div>
            </div>
        </div>

        <!-- Upcoming Placements -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Upcoming Placements</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['upcoming_placements']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.placements.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        View all placements
                    </a>
                </div>
            </div>
        </div>

        <!-- Flags Placed This Month -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
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
                    <a href="{{ route('admin.placements.index') }}?filter=completed" class="font-medium text-indigo-600 hover:text-indigo-500">
                        View completed placements
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    @if($overduePlacements->count() > 0 || $lowInventoryProducts->count() > 0)
    <div class="space-y-4">
        <!-- Overdue Placements Alert -->
        @if($overduePlacements->count() > 0)
        <div class="bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Overdue Placements</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>{{ $overduePlacements->count() }} flag placements are overdue. Please review and update their status.</p>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.placements.index') }}?filter=overdue" class="text-red-800 font-medium hover:text-red-900">
                            Review overdue placements →
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Low Inventory Alert -->
        @if($lowInventoryProducts->count() > 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Low Inventory Alert</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>{{ $lowInventoryProducts->count() }} flag products have low inventory levels.</p>
                        <ul class="mt-1 list-disc list-inside">
                            @foreach($lowInventoryProducts->take(3) as $product)
                            <li>{{ $product->flagType->name }} ({{ $product->flagSize->name }}): {{ $product->inventory_count }} remaining</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.flag-products.index') }}?filter=low_inventory" class="text-yellow-800 font-medium hover:text-yellow-900">
                            Manage inventory →
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recent Activity -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Activity</h3>
                
                <!-- Activity Tabs -->
                <div class="border-b border-gray-200 mb-4">
                    <nav class="-mb-px flex space-x-8">
                        <button @click="activeTab = 'subscriptions'" 
                                :class="activeTab === 'subscriptions' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            New Subscriptions
                        </button>
                        <button @click="activeTab = 'placements'" 
                                :class="activeTab === 'placements' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Recent Placements
                        </button>
                    </nav>
                </div>

                <!-- Recent Subscriptions -->
                <div x-show="activeTab === 'subscriptions'" x-cloak>
                    <div class="space-y-4">
                        @forelse($recentSubscriptions as $subscription)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $subscription->user->full_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $subscription->type_display }} • {{ $subscription->formatted_total }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ $subscription->created_at->format('M j, Y') }}</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $subscription->status_display }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500 text-sm">No recent subscriptions</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Placements -->
                <div x-show="activeTab === 'placements'" x-cloak>
                    <div class="space-y-4">
                        @forelse($recentPlacements as $placement)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $placement->subscription->user->full_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $placement->holiday->name }} • {{ $placement->flagProduct->flagType->name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ $placement->placed_at->format('M j, Y') }}</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Placed
                                </span>
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500 text-sm">No recent placements</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Tasks -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Upcoming Tasks</h3>
                
                <div class="space-y-4">
                    @forelse($upcomingPlacements as $placement)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $placement->subscription->user->full_name }}</p>
                                <p class="text-sm text-gray-500">{{ $placement->holiday->name }} placement</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-900">{{ $placement->placement_date->format('M j') }}</p>
                            <p class="text-xs text-gray-500">{{ $placement->placement_date->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 text-sm">No upcoming placements</p>
                    @endforelse
                </div>

                @if($upcomingPlacements->count() > 0)
                <div class="mt-4">
                    <a href="{{ route('admin.placements.index') }}" class="text-indigo-600 hover:text-indigo-500 font-medium text-sm">
                        View all upcoming placements →
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Revenue Trend (Last 12 Months)</h3>
            <div class="h-64">
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function dashboard() {
    return {
        activeTab: 'subscriptions',
        
        init() {
            this.initRevenueChart();
        },
        
        initRevenueChart() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($revenueChart['labels']),
                    datasets: [{
                        label: 'Revenue ($)',
                        data: @json($revenueChart['data']),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
    }
}
</script>
@endpush
@endsection