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
            <a href="{{ route('admin.flag-products.export') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export
            </a>
            <a href="{{ route('admin.flag-products.create') }}"
               class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Product
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="flagProducts()">
    <!-- Filters -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form method="GET" class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search products..."
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category" id="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                {{ ucfirst($category) }} Flags
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Size Filter -->
                <div>
                    <label for="size" class="block text-sm font-medium text-gray-700">Size</label>
                    <select name="size" id="size" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Sizes</option>
                        @foreach($flagSizes as $size)
                            <option value="{{ $size->id }}" {{ request('size') == $size->id ? 'selected' : '' }}>
                                {{ $size->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="active" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="active" id="active" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Products</option>
                        <option value="true" {{ request('active') === 'true' ? 'selected' : '' }}>Active Only</option>
                        <option value="false" {{ request('active') === 'false' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="sm:col-span-2 lg:col-span-4 flex items-end space-x-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.flag-products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Clear
                    </a>

                    <!-- Quick Filters -->
                    <div class="hidden lg:flex items-center space-x-2 ml-6">
                        <span class="text-sm text-gray-500">Quick filters:</span>
                        <a href="{{ route('admin.flag-products.index', ['filter' => 'low_inventory']) }}"
                           class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ request('filter') === 'low_inventory' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                            Low Inventory
                        </a>
                        <a href="{{ route('admin.flag-products.index', ['filter' => 'out_of_stock']) }}"
                           class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ request('filter') === 'out_of_stock' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                            Out of Stock
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <!-- Bulk Actions -->
            <div x-show="selectedProducts.length > 0" x-cloak class="mb-4 p-4 bg-indigo-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-2 text-sm font-medium text-indigo-900">
                            <span x-text="selectedProducts.length"></span> products selected
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="bulkUpdateInventory()"
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
                                    <div class="font-medium">${{ number_format($product->annual_subscription_price, 2) }}/year</div>
                                    <div class="text-gray-500">${{ number_format($product->one_time_price, 2) }} one-time</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-sm font-medium text-gray-900">{{ $product->inventory_count }}</span>
                                    @if($product->hasLowInventory())
                                        <svg class="ml-1 w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    @elseif($product->inventory_count === 0)
                                        <svg class="ml-1 w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">Alert at {{ $product->min_inventory_alert }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button @click="toggleProductStatus({{ $product->id }})"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer {{ $product->active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                                    {{ $product->active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('admin.flag-products.show', $product) }}"
                                       class="text-indigo-600 hover:text-indigo-900">
                                        View
                                    </a>
                                    <a href="{{ route('admin.flag-products.edit', $product) }}"
                                       class="text-indigo-600 hover:text-indigo-900">
                                        Edit
                                    </a>
                                    <button @click="adjustInventory({{ $product->id }})"
                                            class="text-indigo-600 hover:text-indigo-900">
                                        Adjust Stock
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 21V9l8-4" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new flag product.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('admin.flag-products.create') }}"
                                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
    </div>

    <!-- Inventory Adjustment Modal -->
    <div x-show="showInventoryModal"
     x-cloak
     class="fixed z-10 inset-0 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showInventoryModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
             aria-hidden="true"
             @click="showInventoryModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showInventoryModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

            <form @submit.prevent="submitInventoryAdjustment()">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 21V9l8-4" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Adjust Inventory
                            </h3>
                            <div class="mt-4 space-y-4">
                                <!-- Adjustment Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Adjustment Type <span class="text-red-500">*</span>
                                    </label>
                                    <div class="space-y-2">
                                        <label class="inline-flex items-center">
                                            <input type="radio"
                                                   x-model="inventoryForm.adjustment_type"
                                                   value="increase"
                                                   class="form-radio text-indigo-600">
                                            <span class="ml-2 text-sm text-gray-700">Increase (Add stock)</span>
                                        </label>
                                        <label class="inline-flex items-center ml-6">
                                            <input type="radio"
                                                   x-model="inventoryForm.adjustment_type"
                                                   value="decrease"
                                                   class="form-radio text-indigo-600">
                                            <span class="ml-2 text-sm text-gray-700">Decrease (Remove stock)</span>
                                        </label>
                                        <label class="inline-flex items-center ml-6">
                                            <input type="radio"
                                                   x-model="inventoryForm.adjustment_type"
                                                   value="set"
                                                   class="form-radio text-indigo-600">
                                            <span class="ml-2 text-sm text-gray-700">Set (Set exact amount)</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number"
                                           x-model="inventoryForm.quantity"
                                           min="1"
                                           placeholder="Enter quantity"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                           required>
                                    <p class="mt-1 text-xs text-gray-500">
                                        <span x-show="inventoryForm.adjustment_type === 'increase'">Amount to add to current inventory</span>
                                        <span x-show="inventoryForm.adjustment_type === 'decrease'">Amount to remove from current inventory</span>
                                        <span x-show="inventoryForm.adjustment_type === 'set'">New total inventory amount</span>
                                    </p>
                                </div>

                                <!-- Reason -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Reason <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           x-model="inventoryForm.reason"
                                           placeholder="e.g., Restock from supplier, Damaged goods, Physical count adjustment"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                           required>
                                    <p class="mt-1 text-xs text-gray-500">Provide a brief explanation for this adjustment</p>
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
                    <button type="button"
                            @click="showInventoryModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
</div>

@push('scripts')
<script>
function flagProducts() {
    return {
        selectedProducts: [],
        showInventoryModal: false,
        inventoryProductId: null,
        inventoryForm: {
            adjustment_type: 'increase',  // CHANGED: Use adjustment_type instead of just 'adjustment'
            quantity: '',                 // CHANGED: Use quantity instead of 'adjustment'
            reason: '',                   // Keep this but make it required text
            customReason: ''
        },

        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedProducts = @json($products->pluck('id')->toArray());
            } else {
                this.selectedProducts = [];
            }
        },

        toggleProductStatus(productId) {
            fetch(`/admin/flag-products/${productId}/toggle-active`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status');
            });
        },

        adjustInventory(productId) {
            this.inventoryProductId = productId;
            this.inventoryForm = {
                adjustment_type: 'increase',
                quantity: '',
                reason: '',
                customReason: ''
            };
            this.showInventoryModal = true;
        },

        submitInventoryAdjustment() {
            // FIXED: Send the correct field names that match the controller validation
            const formData = {
                adjustment_type: this.inventoryForm.adjustment_type,
                quantity: parseInt(this.inventoryForm.quantity),
                reason: this.inventoryForm.reason || this.inventoryForm.customReason
            };

            // Validation
            if (!formData.quantity || formData.quantity < 1) {
                alert('Please enter a valid quantity (minimum 1)');
                return;
            }

            if (!formData.reason || formData.reason.trim() === '') {
                alert('Please provide a reason for this adjustment');
                return;
            }

            fetch(`/admin/flag-products/${this.inventoryProductId}/adjust-inventory`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Server error');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.showInventoryModal = false;
                    location.reload();
                } else {
                    alert(data.message || 'Error adjusting inventory');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adjusting inventory: ' + error.message);
            });
        },

        bulkUpdateInventory() {
            // This would open a bulk inventory update modal
            alert('Bulk inventory update functionality would be implemented here');
        }
    }
}
</script>
@endpush
@endsection
