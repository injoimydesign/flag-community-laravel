{{-- resources/views/admin/flag-products/show.blade.php --}}
{{-- COMPLETE implementation with working DELETE button --}}

@extends('layouts.admin')

@section('title', 'Flag Product Details - Admin Dashboard')

@section('page-title', 'Flag Product Details')

@section('content')
<div class="space-y-6" x-data="productDetail()">
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
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <!-- Pricing Info -->
                        <div>
                            <dt class="text-sm font-medium text-gray-500">One-Time Purchase Price</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($flagProduct->one_time_price / 100, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Annual Subscription Price</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($flagProduct->annual_subscription_price / 100, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Cost Per Unit</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($flagProduct->cost_per_unit / 100, 2) }}</dd>
                        </div>

                        <!-- Inventory Info -->
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Current Inventory</dt>
                            <dd class="mt-1">
                                <span class="text-lg font-semibold {{ $flagProduct->current_inventory <= $flagProduct->low_inventory_threshold ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $flagProduct->current_inventory }}
                                </span>
                                @if($flagProduct->current_inventory <= $flagProduct->low_inventory_threshold)
                                    <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Low Stock
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Low Inventory Threshold</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $flagProduct->low_inventory_threshold }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Inventory Value</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($stats['inventory_value'], 2) }}</dd>
                        </div>

                        <!-- Dates -->
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagProduct->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagProduct->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
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

                    {{-- FIXED: Complete delete form with proper closing tags and button --}}
                    <form method="POST" action="{{ route('admin.flag-products.destroy', $flagProduct) }}"
                          onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                @if($stats['active_subscriptions'] > 0) disabled title="Cannot delete - has active subscriptions" @endif>
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 011-1h2a1 1 0 011 1v3M4 7h16" />
                            </svg>
                            Delete Product
                        </button>
                    </form>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Usage Statistics</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Active Subscriptions</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['active_subscriptions'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Placements</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_placements'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Monthly Usage</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['monthly_usage'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Inventory Value</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($stats['inventory_value'], 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Recent Inventory Adjustments -->
            @if($recentAdjustments->count() > 0)
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Recent Inventory Adjustments</h3>
                    <a href="{{ route('admin.flag-products.inventory-history', $flagProduct) }}"
                       class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">By</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentAdjustments as $adjustment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $adjustment->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $adjustment->adjustment_type === 'increase' ? 'bg-green-100 text-green-800' :
                                           ($adjustment->adjustment_type === 'decrease' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ ucfirst($adjustment->adjustment_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium
                                    {{ $adjustment->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $adjustment->quantity > 0 ? '+' : '' }}{{ $adjustment->quantity }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $adjustment->reason }}
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

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Info</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $flagProduct->flagType->category === 'us' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($flagProduct->flagType->category) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Flag Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('admin.flag-types.show', $flagProduct->flagType) }}"
                               class="text-indigo-600 hover:text-indigo-900">
                                {{ $flagProduct->flagType->name }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Size</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $flagProduct->flagSize->name }} ({{ $flagProduct->flagSize->dimensions }})</dd>
                    </div>
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <button @click="showAdjustInventory = true"
                            class="block w-full text-center px-4 py-2 border border-indigo-300 rounded-md text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Adjust Inventory
                    </button>
                    <a href="{{ route('admin.flag-products.inventory-history', $flagProduct) }}"
                       class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        View Inventory History
                    </a>
                    <form method="POST" action="{{ route('admin.flag-products.duplicate', $flagProduct) }}">
                        @csrf
                        <button type="submit"
                                class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Duplicate Product
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Adjust Inventory Modal -->
    <div x-show="showAdjustInventory" x-cloak class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showAdjustInventory" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showAdjustInventory = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showAdjustInventory" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="POST" action="{{ route('admin.flag-products.adjust-inventory', $flagProduct) }}">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Adjust Inventory
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Current Inventory</label>
                                        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $flagProduct->current_inventory }}</p>
                                    </div>
                                    <div>
                                        <label for="adjustment_type" class="block text-sm font-medium text-gray-700">Adjustment Type</label>
                                        <select name="adjustment_type" id="adjustment_type" required
                                                class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="increase">Add to Inventory</option>
                                            <option value="decrease">Remove from Inventory</option>
                                            <option value="set">Set Inventory To</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                        <input type="number" name="quantity" id="quantity" min="0" required
                                               class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                                        <textarea name="reason" id="reason" rows="3" required
                                                  class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Adjust Inventory
                        </button>
                        <button type="button" @click="showAdjustInventory = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function productDetail() {
    return {
        showAdjustInventory: false
    }
}
</script>
@endpush
@endsection
