@extends('layouts.admin')

@section('title', 'Create Subscription - Admin Dashboard')

@section('page-title', 'Create New Subscription')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Subscriptions
        </a>
    </div>

    <!-- Create Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Create New Subscription</h3>
            <p class="mt-1 text-sm text-gray-500">Add a new subscription for an existing or new customer</p>
        </div>

        <form method="POST" action="{{ route('admin.subscriptions.store') }}" x-data="subscriptionForm()">
            @csrf

            <div class="px-6 py-4 space-y-6">
                <!-- Customer Selection -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Customer Information</h4>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="radio" name="customer_type" value="existing" x-model="customerType" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Select Existing Customer</span>
                        </label>
                        <label class="flex items-center mt-2">
                            <input type="radio" name="customer_type" value="new" x-model="customerType" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Create New Customer</span>
                        </label>
                    </div>

                    <!-- Existing Customer Selection -->
                    <div x-show="customerType === 'existing'" class="space-y-4">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">
                                Select Customer <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" id="user_id"
                                    x-model="userId"
                                    @change="fetchCustomerAddress()"
                                    :required="customerType === 'existing'"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('user_id') border-red-300 @enderror">
                                <option value="">-- Select a Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-address="{{ $customer->address }}" data-city="{{ $customer->city }}" data-state="{{ $customer->state }}" data-zip="{{ $customer->zip_code }}">
                                        {{ $customer->name }} ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="userId" class="bg-gray-50 p-4 rounded-md">
                            <h5 class="text-sm font-medium text-gray-700 mb-2">Selected Customer Address</h5>
                            <p class="text-sm text-gray-600" x-text="customerAddress"></p>
                        </div>
                    </div>

                    <!-- New Customer Form -->
                    <div x-show="customerType === 'new'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                                   :required="customerType === 'new'"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('first_name') border-red-300 @enderror">
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                                   :required="customerType === 'new'"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('last_name') border-red-300 @enderror">
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   :required="customerType === 'new'"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-300 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">
                                Phone
                            </label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-300 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700">
                                Address <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="address" id="address" value="{{ old('address') }}"
                                   x-model="newCustomerAddress"
                                   :required="customerType === 'new'"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-300 @enderror">
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">
                                City <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="city" id="city" value="{{ old('city') }}"
                                   :required="customerType === 'new'"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('city') border-red-300 @enderror">
                            @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700">
                                State <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="state" id="state" value="{{ old('state') }}"
                                   :required="customerType === 'new'"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('state') border-red-300 @enderror">
                            @error('state')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="zip_code" class="block text-sm font-medium text-gray-700">
                                ZIP Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code') }}"
                                   :required="customerType === 'new'"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('zip_code') border-red-300 @enderror">
                            @error('zip_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Subscription Details -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Subscription Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="flag_product_id" class="block text-sm font-medium text-gray-700">
                                Flag Product <span class="text-red-500">*</span>
                            </label>
                            <select name="flag_product_id" id="flag_product_id" required
                                    x-model="flagProductId"
                                    @change="updatePrice()"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('flag_product_id') border-red-300 @enderror">
                                <option value="">-- Select a Flag Product --</option>
                                @foreach($flagProducts as $product)
                                    <option value="{{ $product->id }}"
                                            data-price="{{ $product->annual_subscription_price }}"
                                            data-name="{{ $product->display_name }}"
                                            {{ old('flag_product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->display_name }} - ${{ number_format($product->annual_subscription_price / 100, 2) }}/year
                                    </option>
                                @endforeach
                            </select>
                            @error('flag_product_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('status') border-red-300 @enderror">
                                <option value="pending">Pending</option>
                                <option value="active" selected>Active</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date"
                                   value="{{ old('start_date', date('Y-m-d')) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_date') border-red-300 @enderror">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_frequency" class="block text-sm font-medium text-gray-700">
                                Billing Frequency <span class="text-red-500">*</span>
                            </label>
                            <select name="billing_frequency" id="billing_frequency" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('billing_frequency') border-red-300 @enderror">
                                <option value="annual" selected>Annual</option>
                                <option value="monthly">Monthly</option>
                            </select>
                            @error('billing_frequency')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2" x-show="flagProductId">
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <h5 class="text-sm font-medium text-blue-900 mb-2">Subscription Summary</h5>
                                <p class="text-sm text-blue-800">
                                    <strong>Product:</strong> <span x-text="selectedProductName"></span><br>
                                    <strong>Total Amount:</strong> $<span x-text="(selectedPrice / 100).toFixed(2)"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Placement Instructions -->
                <div>
                    <label for="placement_instructions" class="block text-sm font-medium text-gray-700">
                        Placement Instructions
                    </label>
                    <textarea id="placement_instructions" name="placement_instructions" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('placement_instructions') border-red-300 @enderror"
                              placeholder="Enter any special instructions for flag placement...">{{ old('placement_instructions') }}</textarea>
                    @error('placement_instructions')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">These instructions will be visible to staff when placing flags.</p>
                </div>

                <!-- Admin Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Admin Notes
                    </label>
                    <textarea id="notes" name="notes" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-300 @enderror"
                              placeholder="Add internal notes about this subscription...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">These notes are only visible to admin users.</p>
                </div>

                <input type="hidden" name="use_address_as_placement" value="1">
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create Subscription
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function subscriptionForm() {
    return {
        customerType: 'existing',
        userId: '',
        customerAddress: '',
        newCustomerAddress: '',
        flagProductId: '',
        selectedPrice: 0,
        selectedProductName: '',

        fetchCustomerAddress() {
            const select = document.getElementById('user_id');
            const option = select.options[select.selectedIndex];
            if (option && option.value) {
                const address = option.getAttribute('data-address');
                const city = option.getAttribute('data-city');
                const state = option.getAttribute('data-state');
                const zip = option.getAttribute('data-zip');
                this.customerAddress = `${address}, ${city}, ${state} ${zip}`;
            } else {
                this.customerAddress = '';
            }
        },

        updatePrice() {
            const select = document.getElementById('flag_product_id');
            const option = select.options[select.selectedIndex];
            if (option && option.value) {
                this.selectedPrice = parseInt(option.getAttribute('data-price'));
                this.selectedProductName = option.getAttribute('data-name');
            } else {
                this.selectedPrice = 0;
                this.selectedProductName = '';
            }
        }
    }
}
</script>
@endsection
