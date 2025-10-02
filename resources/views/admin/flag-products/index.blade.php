{{-- resources/views/admin/flag-products/index.blade.php --}}
{{-- ENHANCED: Added delete buttons in actions column --}}
{{-- FIXED: Changed inventory_count to current_inventory --}}

@extends('layouts.admin')

@section('title', 'Flag Products - Admin Dashboard')

@section('header')
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Flag Products
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Manage flag products, pricing, and inventory levels.
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="{{ route('admin.flag-products.create') }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Product
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="flagProductsManager()">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Products</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['total_products'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Products</dt>
                            <dd class="text-lg font-semibold text-green-600">{{ $stats['active_products'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Low Inventory</dt>
                            <dd class="text-lg font-semibold text-yellow-600">{{ $stats['low_inventory'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Out of Stock</dt>
                            <dd class="text-lg font-semibold text-red-600">{{ $stats['out_of_stock'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Inventory Value</dt>
                            <dd class="text-lg font-semibold text-blue-600">${{ number_format($stats['total_inventory_value'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('admin.flag-products.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Flag Type Filter -->
                <div>
                    <label for="flag_type_id" class="block text-sm font-medium text-gray-700 mb-1">Flag Type</label>
                    <select name="flag_type_id" id="flag_type_id" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Types</option>
                        @foreach($flagTypes as $type)
                            <option value="{{ $type->id }}" {{ request('flag_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Flag Size Filter -->
                <div>
                    <label for="flag_size_id" class="block text-sm font-medium text-gray-700 mb-1">Flag Size</label>
                    <select name="flag_size_id" id="flag_size_id" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Sizes</option>
                        @foreach($flagSizes as $size)
                            <option value="{{ $size->id }}" {{ request('flag_size_id') == $size->id ? 'selected' : '' }}>
                                {{ $size->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Inventory Status Filter -->
                <div>
                    <label for="inventory_status" class="block text-sm font-medium text-gray-700 mb-1">Inventory Status</label>
                    <select name="inventory_status" id="inventory_status" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="in_stock" {{ request('inventory_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_inventory" {{ request('inventory_status') == 'low_inventory' ? 'selected' : '' }}>Low Inventory</option>
                        <option value="out_of_stock" {{ request('inventory_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="md:col-span-4 flex items-center justify-between">
                    <div class="flex space-x-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Apply Filters
                        </button>
                        <a href="{{ route('admin.flag-products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Clear
                        </a>
                    </div>
                    <a href="{{ route('admin.flag-products.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Products</h3>
                <div x-show="selectedProducts.length > 0" x-cloak class="flex items-center space-x-2">
                    <span class="text-sm text-gray-700"><span x-text="selectedProducts.length"></span> selected</span>
                    <div class="flex space-x-2">
                        <button @click="showInventoryModal = true"
                                class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                            Update Inventory
                        </button>
                        <button @click="selectedProducts = []"
                                class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                            Clear Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                            <input type="checkbox" @change="toggleSelectAll($event)"
                                   class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 sm:left-6">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pricing
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Inventory
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50" :class="selectedProducts.includes({{ $product->id }}) ? 'bg-indigo-50' : ''">
                        <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                            <input type="checkbox" value="{{ $product->id }}" x-model="selectedProducts"
                                   class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 sm:left-6">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 flex-shrink-0">
                                    <div class="w-10 h-10 rounded-lg {{ $product->flagType->category === 'us' ? 'bg-blue-100' : 'bg-green-100' }} flex items-center justify-center">
                                        <svg class="w-5 h-5 {{ $product->flagType->category === 'us' ? 'text-blue-600' : 'text-green-600' }}" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $product->flagType->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $product->flagSize->name }} ({{ $product->flagSize->dimensions }})
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->flagType->category === 'us' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($product->flagType->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>
                                <div class="font-medium">${{ number_format($product->annual_subscription_price / 100, 2) }}/year</div>
                                <div class="text-gray-500">${{ number_format($product->one_time_price / 100, 2) }} one-time</div>
                            </div>
                        </td>
                        {{-- FIXED: Changed from inventory_count to current_inventory and min_inventory_alert to low_inventory_threshold --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900">{{ $product->current_inventory }}</span>
                                @if($product->hasLowInventory())
                                    <svg class="ml-1 w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                @elseif($product->current_inventory === 0)
                                    <svg class="ml-1 w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">Alert at {{ $product->low_inventory_threshold }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button @click="toggleProductStatus({{ $product->id }})"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer {{ $product->active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                                {{ $product->active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            {{-- ENHANCED: Added delete button to actions column --}}
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('admin.flag-products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                <a href="{{ route('admin.flag-products.edit', $product) }}" class="text-gray-600 hover:text-gray-900">Edit</a>

                                @if($product->getActiveSubscriptionCount() == 0)
                                <form method="POST"
                                      action="{{ route('admin.flag-products.destroy', $product) }}"
                                      onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </form>
                                @else
                                <span class="text-gray-400 cursor-not-allowed"
                                      title="Cannot delete - has {{ $product->getActiveSubscriptionCount() }} active subscription(s)">
                                    Delete
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating a new flag product.</p>
                                <div class="mt-6">
                                    <a href="{{ route('admin.flag-products.create') }}"
                                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Add Product
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
        <div class="border-t border-gray-200 px-4 py-3 sm:px-6">
            {{ $products->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    <!-- Inventory Adjustment Modal -->
    <div x-show="showInventoryModal" x-cloak class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showInventoryModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showInventoryModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showInventoryModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form @submit.prevent="submitInventoryAdjustment()">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Bulk Inventory Adjustment
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Adjustment Type</label>
                                        <select x-model="adjustmentType" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="add">Add to Inventory</option>
                                            <option value="remove">Remove from Inventory</option>
                                            <option value="set">Set Inventory To</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                        <input type="number" x-model="adjustmentQuantity" min="0" required
                                               class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reason</label>
                                        <textarea x-model="adjustmentReason" rows="3" required
                                                  class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Update Inventory
                        </button>
                        <button type="button" @click="showInventoryModal = false"
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
function flagProductsManager() {
    return {
        selectedProducts: [],
        showInventoryModal: false,
        adjustmentType: 'add',
        adjustmentQuantity: 0,
        adjustmentReason: '',

        toggleSelectAll(event) {
            if (event.target.checked) {
                // Select all product IDs on current page
                this.selectedProducts = Array.from(document.querySelectorAll('tbody input[type="checkbox"]'))
                    .map(cb => parseInt(cb.value));
            } else {
                this.selectedProducts = [];
            }
        },

        async toggleProductStatus(productId) {
            try {
                const response = await fetch(`/admin/flag-products/${productId}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Failed to update product status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating the product status');
            }
        },

        async submitInventoryAdjustment() {
            if (this.selectedProducts.length === 0) {
                alert('Please select at least one product');
                return;
            }

            try {
                const response = await fetch('/admin/flag-products/bulk-update-inventory', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        product_ids: this.selectedProducts,
                        adjustment_type: this.adjustmentType,
                        quantity: this.adjustmentQuantity,
                        reason: this.adjustmentReason
                    })
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Failed to update inventory');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating inventory');
            }
        }
    }
}
</script>
@endpush
@endsection
