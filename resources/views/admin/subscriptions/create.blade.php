@extends('layouts.admin')

@section('title', 'Create New Order - Admin Dashboard')

@section('header')
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Create New Order
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Create a new subscription or one-time order for a customer
            </p>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="orderForm()">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Subscriptions
        </a>
    </div>

    <form method="POST" action="{{ route('admin.subscriptions.store') }}">
        @csrf

        <!-- Customer Selection -->
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">1. Customer Information</h3>
            </div>

            <div class="px-6 py-4 space-y-6">
                <!-- Customer Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Customer Type</label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="customer_type" value="existing" x-model="customerType" class="form-radio">
                            <span class="ml-2">Existing Customer</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="customer_type" value="new" x-model="customerType" class="form-radio">
                            <span class="ml-2">New Customer</span>
                        </label>
                    </div>
                    @error('customer_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Existing Customer Selection -->
                <div x-show="customerType === 'existing'" x-cloak>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700">
                        Select Customer <span class="text-red-500">*</span>
                    </label>
                    <select :name="customerType === 'existing' ? 'customer_id' : ''"
                            id="customer_id"
                            class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('customer_id') border-red-300 @enderror"
                            x-model="selectedCustomerId"
                            @change="loadCustomerInfo()"
                            :required="customerType === 'existing'">
                        <option value="">-- Select a Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                    data-email="{{ $customer->email }}"
                                    data-phone="{{ $customer->phone }}"
                                    data-address="{{ $customer->address }}"
                                    data-city="{{ $customer->city }}"
                                    data-state="{{ $customer->state }}"
                                    data-zip="{{ $customer->zip_code }}">
                                {{ $customer->full_name }} - {{ $customer->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <!-- Customer Info Display -->
                    <div x-show="selectedCustomerId" x-cloak class="mt-4 p-4 bg-blue-50 rounded-md">
                        <h4 class="font-medium text-sm text-gray-900 mb-2">Selected Customer Details:</h4>
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                            <div>
                                <dt class="font-medium text-gray-700">Email:</dt>
                                <dd class="text-gray-900" x-text="customerInfo.email"></dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-700">Phone:</dt>
                                <dd class="text-gray-900" x-text="customerInfo.phone || 'N/A'"></dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="font-medium text-gray-700">Address:</dt>
                                <dd class="text-gray-900" x-text="`${customerInfo.address}, ${customerInfo.city}, ${customerInfo.state} ${customerInfo.zip}`"></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- New Customer Form -->
                <div x-show="customerType === 'new'" x-cloak>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('first_name') border-red-300 @enderror">
                            @error('first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('last_name') border-red-300 @enderror">
                            @error('last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-300 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">
                                Phone
                            </label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700">
                                Street Address <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="address" id="address" value="{{ old('address') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-300 @enderror">
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">
                                City <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="city" id="city" value="{{ old('city') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('city') border-red-300 @enderror">
                            @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700">
                                State <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="state" id="state" value="{{ old('state') }}" maxlength="2" placeholder="TX"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('state') border-red-300 @enderror">
                            @error('state')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="zip_code" class="block text-sm font-medium text-gray-700">
                                ZIP Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('zip_code') border-red-300 @enderror">
                            @error('zip_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Account Creation Option -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center">
                            <input type="checkbox" name="create_account" id="create_account" value="1"
                                   x-model="createAccount"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="create_account" class="ml-2 block text-sm text-gray-700">
                                Create customer account (send verification email)
                            </label>
                        </div>

                        <div x-show="createAccount" x-cloak class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password" id="password"
                                       class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                    Confirm Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Details -->
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">2. Subscription Details</h3>
            </div>

            <div class="px-6 py-4 space-y-6">
                <!-- Subscription Type -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">
                            Subscription Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" x-model="subscriptionType" @change="calculateTotal()"
                                class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('type') border-red-300 @enderror">
                            <option value="">-- Select Type --</option>
                            <option value="annual">Annual Subscription</option>
                            <option value="one-time">One-Time Service</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">
                            Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('start_date') border-red-300 @enderror">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Flag Products Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Flag Products <span class="text-red-500">*</span>
                    </label>
                    <div class="border border-gray-300 rounded-md p-4 max-h-64 overflow-y-auto">
                        @foreach($flagProducts as $product)
                        <label class="flex items-start py-2 hover:bg-gray-50 px-2 rounded cursor-pointer">
                            <input type="checkbox"
                                   name="flag_products[]"
                                   value="{{ $product->id }}"
                                   x-model="selectedProducts"
                                   @change="calculateTotal()"
                                   data-annual-price="{{ $product->annual_subscription_price }}"
                                   data-onetime-price="{{ $product->one_time_price }}"
                                   class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <div class="ml-3 flex-1">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $product->flagType->name }} - {{ $product->flagSize->name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $product->flagSize->dimensions }} |
                                    Annual: ${{ number_format($product->annual_subscription_price / 100, 2) }} |
                                    One-Time: ${{ number_format($product->one_time_price / 100, 2) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Inventory: {{ $product->current_inventory }}
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('flag_products')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Total Calculation -->
                <div class="bg-gray-50 p-4 rounded-md">
                    <div class="flex items-center justify-between text-lg font-semibold">
                        <span>Total:</span>
                        <span x-text="'$' + (totalAmount / 100).toFixed(2)"></span>
                    </div>
                </div>

                <!-- Holidays Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select Holidays (leave empty for all holidays)
                    </label>
                    <div class="border border-gray-300 rounded-md p-4 max-h-48 overflow-y-auto">
                        @foreach($holidays as $holiday)
                        <label class="flex items-center py-2 hover:bg-gray-50 px-2 rounded cursor-pointer">
                            <input type="checkbox"
                                   name="selected_holidays[]"
                                   value="{{ $holiday->id }}"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm text-gray-900">
                                {{ $holiday->name }} - {{ \Carbon\Carbon::parse($holiday->date)->format('M j, Y') }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Special Instructions -->
                <div>
                    <label for="special_instructions" class="block text-sm font-medium text-gray-700">
                        Special Instructions
                    </label>
                    <textarea name="special_instructions" id="special_instructions" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Flag placement instructions, special requests, etc.">{{ old('special_instructions') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">3. Payment Information</h3>
            </div>

            <div class="px-6 py-4 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="payment_status" class="block text-sm font-medium text-gray-700">
                            Payment Status <span class="text-red-500">*</span>
                        </label>
                        <select name="payment_status" id="payment_status" x-model="paymentStatus"
                                class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('payment_status') border-red-300 @enderror">
                            <option value="">-- Select Status --</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending Payment</option>
                            <option value="comp">Complimentary (Free)</option>
                        </select>
                        @error('payment_status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="paymentStatus === 'paid'" x-cloak>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700">
                            Payment Method
                        </label>
                        <select name="payment_method" id="payment_method"
                                class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select Method --</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="comp">Complimentary</option>
                        </select>
                    </div>
                </div>

                <div x-show="paymentStatus === 'paid'" x-cloak>
                    <label for="stripe_payment_intent_id" class="block text-sm font-medium text-gray-700">
                        Stripe Payment Intent ID (if applicable)
                    </label>
                    <input type="text" name="stripe_payment_intent_id" id="stripe_payment_intent_id"
                           value="{{ old('stripe_payment_intent_id') }}"
                           placeholder="pi_xxxxxxxxxxxxx"
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Enter Stripe payment intent ID if payment was processed through Stripe</p>
                </div>

                <!-- Admin Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Admin Notes
                    </label>
                    <textarea name="notes" id="notes" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Internal notes about this order...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('admin.subscriptions.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Create Order
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function orderForm() {
    return {
        customerType: '{{ old("customer_type", "existing") }}',
        selectedCustomerId: '{{ old("customer_id") }}',
        customerInfo: {
            email: '',
            phone: '',
            address: '',
            city: '',
            state: '',
            zip: ''
        },
        createAccount: {{ old('create_account', 0) }},
        subscriptionType: '{{ old("type") }}',
        selectedProducts: [],
        totalAmount: 0,
        paymentStatus: '{{ old("payment_status") }}',

        init() {
            // Restore selected products from old input if validation failed
            const oldProducts = @json(old('flag_products', []));
            if (oldProducts.length > 0) {
                this.selectedProducts = oldProducts.map(id => String(id));
                this.$nextTick(() => {
                    this.calculateTotal();
                });
            }

            // Load customer info if customer was selected
            if (this.selectedCustomerId) {
                this.loadCustomerInfo();
            }
        },

        loadCustomerInfo() {
            const select = document.getElementById('customer_id');
            const option = select.options[select.selectedIndex];

            if (option.value) {
                this.customerInfo = {
                    email: option.dataset.email || '',
                    phone: option.dataset.phone || '',
                    address: option.dataset.address || '',
                    city: option.dataset.city || '',
                    state: option.dataset.state || '',
                    zip: option.dataset.zip || ''
                };
            }
        },

        calculateTotal() {
            let total = 0;

            if (!this.subscriptionType) {
                this.totalAmount = 0;
                return;
            }

            const checkboxes = document.querySelectorAll('input[name="flag_products[]"]:checked');

            checkboxes.forEach(checkbox => {
                const price = this.subscriptionType === 'annual'
                    ? parseFloat(checkbox.dataset.annualPrice)
                    : parseFloat(checkbox.dataset.onetimePrice);
                total += price;
            });

            this.totalAmount = total;
        }
    }
}
</script>
@endpush
@endsection
