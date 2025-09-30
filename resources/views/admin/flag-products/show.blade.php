@extends('layouts.admin')

@section('title', 'Flag Product Details - Admin Dashboard')

@section('page-title', 'Flag Product Details')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.flag-products.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Flag Products
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Details -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ $flagProduct->flagType->name }} - {{ $flagProduct->flagSize->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $flagProduct->flagSize->dimensions }}</p>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $flagProduct->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $flagProduct->active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Pricing -->
                        <div class="md:col-span-2 pb-4 border-b border-gray-200">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Pricing</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">One-Time Price</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($flagProduct->one_time_price / 100, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Annual Subscription</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($flagProduct->annual_subscription_price / 100, 2) }}</dd>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory -->
                        <div class="md:col-span-2 pb-4 border-b border-gray-200">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Inventory</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Current Stock</dt>
                                    <dd class="mt-1 text-lg font-semibold {{ isset($flagProduct->current_inventory) && $flagProduct->current_inventory <= ($flagProduct->low_inventory_threshold ?? 10) ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $flagProduct->current_inventory ?? $flagProduct->inventory_count ?? 0 }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Low Stock Alert</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $flagProduct->low_inventory_threshold ?? $flagProduct->min_inventory_alert ?? 10 }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Cost Per Unit</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">
                                        @if(isset($flagProduct->cost_per_unit))
                                            ${{ number_format($flagProduct->cost_per_unit / 100, 2) }}
                                        @else
                                            <span class="text-gray-400">Not set</span>
                                        @endif
                                    </dd>
                                </div>
                            </div>
                        </div>

                        <!-- Product Information -->
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Flag Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagProduct->flagType->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Category</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($flagProduct->flagType->category) }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Size</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagProduct->flagSize->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dimensions</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagProduct->flagSize->dimensions }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagProduct->created_at->format('M j, Y') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagProduct->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>

                        @if(isset($flagProduct->stripe_price_id_onetime) || isset($flagProduct->stripe_price_id_annual))
                        <div class="md:col-span-2 pt-4 border-t border-gray-200">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Stripe Integration</h4>
                            <div class="space-y-2">
                                @if(isset($flagProduct->stripe_price_id_onetime))
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">One-Time Price ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $flagProduct->stripe_price_id_onetime }}</dd>
                                </div>
                                @endif
                                @if(isset($flagProduct->stripe_price_id_annual))
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Annual Price ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $flagProduct->stripe_price_id_annual }}</dd>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('admin.flag-products.edit', $flagProduct) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Product
                        </a>

                        <button @click="showAdjustInventory = true"
                                class="inline-flex items-center px-4 py-2 border border-indigo-300 rounded-md shadow-sm text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Adjust Inventory
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.flag-products.destroy', $flagProduct) }}"
                          onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 011-1h2a1 1 0 011 1v3M4 7h16" />
                            </svg>
                            Delete Product
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Inventory Adjustments -->
            @if(isset($recentAdjustments) && $recentAdjustments->count() > 0)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Inventory Adjustments</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adjusted By</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentAdjustments as $adjustment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $adjustment->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ ucfirst($adjustment->adjustment_type) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium {{ $adjustment->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $adjustment->quantity > 0 ? '+' : '' }}{{ $adjustment->quantity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $adjustment->reason ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $adjustment->adjustedBy->name ?? 'System' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Stats Sidebar -->
        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
                <dl class="space-y-4">
                    @if(isset($stats['active_subscriptions']))
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Active Subscriptions</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['active_subscriptions'] }}</dd>
                    </div>
                    @endif

                    @if(isset($stats['total_placements']))
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Placements</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_placements'] }}</dd>
                    </div>
                    @endif

                    @if(isset($stats['inventory_value']))
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Inventory Value</dt>
                        <dd class="mt-1 text-2xl font-semibold text-green-600">${{ number_format($stats['inventory_value'], 2) }}</dd>
                    </div>
                    @endif

                    @if(isset($stats['monthly_usage']))
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Monthly Usage</dt>
                        <dd class="mt-1 text-2xl font-semibold text-blue-600">{{ $stats['monthly_usage'] }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.flag-types.show', $flagProduct->flagType) }}"
                       class="block w-full text-center px-4 py-2 border border-indigo-300 rounded-md text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        View Flag Type
                    </a>
                    <a href="{{ route('admin.flag-products.index') }}"
                       class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        View All Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productDetail', () => ({
        showAdjustInventory: false
    }))
})
</script>
@endsection
