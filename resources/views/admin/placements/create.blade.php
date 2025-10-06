@extends('layouts.admin')

@section('title', 'Create Flag Placement - Admin Dashboard')

@section('page-title', 'Create New Flag Placement')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.placements.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Placements
        </a>
    </div>

    <!-- Create Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Create New Flag Placement</h3>
            <p class="mt-1 text-sm text-gray-500">Schedule a flag placement for a customer</p>
        </div>

        <form method="POST" action="{{ route('admin.placements.store') }}" x-data="placementForm()">
            @csrf

            <div class="px-6 py-4 space-y-6">
                <!-- Subscription Selection -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Subscription Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="subscription_id" class="block text-sm font-medium text-gray-700">
                                Subscription <span class="text-red-500">*</span>
                            </label>
                            <select name="subscription_id" id="subscription_id" required
                                    x-model="subscriptionId"
                                    @change="fetchSubscriptionDetails()"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('subscription_id') border-red-300 @enderror">
                                <option value="">-- Select a Subscription --</option>
                                @foreach($subscriptions as $subscription)
                                    <option value="{{ $subscription->id }}"
                                            data-customer="{{ $subscription->user->name ?? 'N/A' }}"
                                            data-address="{{ $subscription->user->address ?? '' }}"
                                            data-city="{{ $subscription->user->city ?? '' }}"
                                            data-state="{{ $subscription->user->state ?? '' }}"
                                            data-zip="{{ $subscription->user->zip_code ?? '' }}"
                                            data-product="{{ $subscription->flagProduct->display_name ?? 'N/A' }}">
                                        #{{ $subscription->id }} - {{ $subscription->user->name ?? 'N/A' }} ({{ $subscription->flagProduct->display_name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('subscription_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="subscriptionId" class="bg-gray-50 p-4 rounded-md">
                            <h5 class="text-sm font-medium text-gray-700 mb-2">Subscription Info</h5>
                            <p class="text-sm text-gray-600"><strong>Customer:</strong> <span x-text="customerName"></span></p>
                            <p class="text-sm text-gray-600"><strong>Product:</strong> <span x-text="productName"></span></p>
                            <p class="text-sm text-gray-600" x-show="customerAddress"><strong>Address:</strong> <span x-text="customerAddress"></span></p>
                        </div>
                    </div>
                </div>

                <!-- Holiday and Dates -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Placement Schedule</h4>

                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">
                                    Creating placements for ALL active holidays
                                </h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>This will create placements for all {{ count($holidays) }} active holidays in the system. Each holiday will use the same address.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="hidden" name="create_all_holidays" value="1">

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('status') border-red-300 @enderror">
                                <option value="scheduled" selected>Scheduled</option>
                                <option value="placed">Placed</option>
                                <option value="removed">Removed</option>
                                <option value="skipped">Skipped</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Holidays to Create
                            </label>
                            <div class="mt-1 bg-gray-50 p-3 rounded-md border border-gray-200 max-h-40 overflow-y-auto">
                                <ul class="text-sm text-gray-700 space-y-1">
                                    @foreach($holidays as $holiday)
                                        <li class="flex items-center">
                                            <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $holiday->name }}@if($holiday->date) - {{ $holiday->date->format('M j, Y') }}@endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Placement Address -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-md font-medium text-gray-900">Placement Address</h4>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="useSubscriptionAddress" @change="toggleAddress()" class="mr-2">
                            <span class="text-sm text-gray-700">Use subscription address</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="placement_address" class="block text-sm font-medium text-gray-700">
                                Street Address
                            </label>
                            <input type="text" name="placement_address" id="placement_address"
                                   x-model="placementAddress"
                                   :readonly="useSubscriptionAddress"
                                   value="{{ old('placement_address') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('placement_address') border-red-300 @enderror">
                            @error('placement_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="placement_city" class="block text-sm font-medium text-gray-700">
                                City
                            </label>
                            <input type="text" name="placement_city" id="placement_city"
                                   x-model="placementCity"
                                   :readonly="useSubscriptionAddress"
                                   value="{{ old('placement_city') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('placement_city') border-red-300 @enderror">
                            @error('placement_city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="placement_state" class="block text-sm font-medium text-gray-700">
                                State
                            </label>
                            <input type="text" name="placement_state" id="placement_state"
                                   x-model="placementState"
                                   :readonly="useSubscriptionAddress"
                                   value="{{ old('placement_state') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('placement_state') border-red-300 @enderror">
                            @error('placement_state')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="placement_zip_code" class="block text-sm font-medium text-gray-700">
                                ZIP Code
                            </label>
                            <input type="text" name="placement_zip_code" id="placement_zip_code"
                                   x-model="placementZipCode"
                                   :readonly="useSubscriptionAddress"
                                   value="{{ old('placement_zip_code') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('placement_zip_code') border-red-300 @enderror">
                            @error('placement_zip_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Placement Notes
                    </label>
                    <textarea id="notes" name="notes" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-300 @enderror"
                              placeholder="Add any special notes or instructions for this placement...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <a href="{{ route('admin.placements.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create Placement
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function placementForm() {
    return {
        subscriptionId: '',
        customerName: '',
        productName: '',
        customerAddress: '',
        useSubscriptionAddress: true,
        placementAddress: '',
        placementCity: '',
        placementState: '',
        placementZipCode: '',
        subscriptionAddressData: {},

        fetchSubscriptionDetails() {
            const select = document.getElementById('subscription_id');
            const option = select.options[select.selectedIndex];
            if (option && option.value) {
                this.customerName = option.getAttribute('data-customer');
                this.productName = option.getAttribute('data-product');
                const address = option.getAttribute('data-address');
                const city = option.getAttribute('data-city');
                const state = option.getAttribute('data-state');
                const zip = option.getAttribute('data-zip');

                this.subscriptionAddressData = {
                    address: address,
                    city: city,
                    state: state,
                    zip: zip
                };

                this.customerAddress = `${address}, ${city}, ${state} ${zip}`;

                if (this.useSubscriptionAddress) {
                    this.updatePlacementAddress();
                }
            } else {
                this.customerName = '';
                this.productName = '';
                this.customerAddress = '';
                this.subscriptionAddressData = {};
            }
        },

        toggleAddress() {
            if (this.useSubscriptionAddress) {
                this.updatePlacementAddress();
            }
        },

        updatePlacementAddress() {
            this.placementAddress = this.subscriptionAddressData.address || '';
            this.placementCity = this.subscriptionAddressData.city || '';
            this.placementState = this.subscriptionAddressData.state || '';
            this.placementZipCode = this.subscriptionAddressData.zip || '';
        }
    }
}
</script>
@endsection
