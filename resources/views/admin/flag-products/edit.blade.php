@extends('layouts.admin')

@section('title', 'Edit Flag Product - Admin Dashboard')

@section('page-title', 'Edit Flag Product')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.flag-products.show', $flagProduct) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Product Details
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Edit Flag Product</h3>
            <p class="mt-1 text-sm text-gray-500">Update flag product pricing and settings</p>
        </div>

        <form method="POST" action="{{ route('admin.flag-products.update', $flagProduct) }}">
            @csrf
            @method('PUT')

            <div class="px-6 py-4 space-y-6">
                <!-- Product Configuration -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Product Configuration</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Flag Type -->
                        <div>
                            <label for="flag_type_id" class="block text-sm font-medium text-gray-700">
                                Flag Type <span class="text-red-500">*</span>
                            </label>
                            <select name="flag_type_id"
                                    id="flag_type_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('flag_type_id') border-red-300 @enderror">
                                <option value="">Select a flag type</option>
                                @foreach($flagTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('flag_type_id', $flagProduct->flag_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('flag_type_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Flag Size -->
                        <div>
                            <label for="flag_size_id" class="block text-sm font-medium text-gray-700">
                                Flag Size <span class="text-red-500">*</span>
                            </label>
                            <select name="flag_size_id"
                                    id="flag_size_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('flag_size_id') border-red-300 @enderror">
                                <option value="">Select a size</option>
                                @foreach($flagSizes as $size)
                                    <option value="{{ $size->id }}" {{ old('flag_size_id', $flagProduct->flag_size_id) == $size->id ? 'selected' : '' }}>
                                        {{ $size->name }} ({{ $size->dimensions }})
                                    </option>
                                @endforeach
                            </select>
                            @error('flag_size_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Pricing</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- One-Time Price -->
                        <div>
                            <label for="one_time_price" class="block text-sm font-medium text-gray-700">
                                One-Time Purchase Price <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number"
                                       name="one_time_price"
                                       id="one_time_price"
                                       step="0.01"
                                       min="0"
                                       value="{{ old('one_time_price', number_format($flagProduct->one_time_price / 100, 2, '.', '')) }}"
                                       required
                                       class="pl-7 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('one_time_price') border-red-300 @enderror">
                            </div>
                            @error('one_time_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Annual Subscription Price -->
                        <div>
                            <label for="annual_subscription_price" class="block text-sm font-medium text-gray-700">
                                Annual Subscription Price <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number"
                                       name="annual_subscription_price"
                                       id="annual_subscription_price"
                                       step="0.01"
                                       min="0"
                                       value="{{ old('annual_subscription_price', number_format($flagProduct->annual_subscription_price / 100, 2, '.', '')) }}"
                                       required
                                       class="pl-7 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('annual_subscription_price') border-red-300 @enderror">
                            </div>
                            @error('annual_subscription_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cost Per Unit -->
                        <div>
                            <label for="cost_per_unit" class="block text-sm font-medium text-gray-700">
                                Cost Per Unit <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number"
                                       name="cost_per_unit"
                                       id="cost_per_unit"
                                       step="0.01"
                                       min="0"
                                       value="{{ old('cost_per_unit', number_format($flagProduct->cost_per_unit / 100, 2, '.', '')) }}"
                                       required
                                       class="pl-7 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('cost_per_unit') border-red-300 @enderror">
                            </div>
                            @error('cost_per_unit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Your cost to purchase/produce this item</p>
                        </div>
                    </div>
                </div>

                <!-- Inventory Settings -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Inventory Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Current Inventory (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Current Inventory
                            </label>
                            <div class="mt-1 px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                                <span class="text-gray-900 font-medium">{{ $flagProduct->current_inventory }}</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">To adjust inventory, use the "Adjust Inventory" button on the product details page</p>
                        </div>

                        <!-- Low Inventory Threshold -->
                        <div>
                            <label for="low_inventory_threshold" class="block text-sm font-medium text-gray-700">
                                Low Inventory Threshold <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="low_inventory_threshold"
                                   id="low_inventory_threshold"
                                   min="0"
                                   value="{{ old('low_inventory_threshold', $flagProduct->low_inventory_threshold) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('low_inventory_threshold') border-red-300 @enderror">
                            @error('low_inventory_threshold')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Alert when inventory falls below this number</p>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Status</h4>
                    <div class="flex items-center">
                        <input type="hidden" name="active" value="0">
                        <input type="checkbox"
                               name="active"
                               id="active"
                               value="1"
                               {{ old('active', $flagProduct->active) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="active" class="ml-2 block text-sm text-gray-900">
                            Active (visible to customers)
                        </label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <a href="{{ route('admin.flag-products.show', $flagProduct) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
