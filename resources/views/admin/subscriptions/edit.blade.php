@extends('layouts.admin')

@section('title', 'Edit Subscription - Admin Dashboard')

@section('page-title', 'Edit Subscription')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Subscription Details
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Edit Subscription #{{ $subscription->id }}</h3>
            <p class="mt-1 text-sm text-gray-500">Update subscription information and settings</p>
        </div>

        <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}">
            @csrf
            @method('PUT')

            <div class="px-6 py-4 space-y-6">
                <!-- Customer Information (Read-only) -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Customer Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Customer Name</label>
                            <input type="text" 
                                   value="{{ $subscription->user->name ?? 'N/A' }}" 
                                   disabled
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="text" 
                                   value="{{ $subscription->user->email ?? 'N/A' }}" 
                                   disabled
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- Subscription Status -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Subscription Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status
                            </label>
                            <select id="status" 
                                    name="status" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('status') border-red-300 @enderror">
                                <option value="active" {{ old('status', $subscription->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ old('status', $subscription->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="cancelled" {{ old('status', $subscription->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Flag Product</label>
                            <input type="text" 
                                   value="{{ $subscription->flagProduct->name ?? 'N/A' }}" 
                                   disabled
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- Placement Instructions -->
                <div>
                    <label for="placement_instructions" class="block text-sm font-medium text-gray-700">
                        Placement Instructions
                    </label>
                    <textarea id="placement_instructions" 
                              name="placement_instructions" 
                              rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('placement_instructions') border-red-300 @enderror"
                              placeholder="Enter any special instructions for flag placement...">{{ old('placement_instructions', $subscription->placement_instructions) }}</textarea>
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
                    <textarea id="notes" 
                              name="notes" 
                              rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('notes') border-red-300 @enderror"
                              placeholder="Add internal notes about this subscription...">{{ old('notes', $subscription->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">These notes are only visible to admin users.</p>
                </div>

                <!-- Subscription Details (Read-only) -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Subscription Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Billing Frequency</label>
                            <input type="text" 
                                   value="{{ ucfirst($subscription->billing_frequency ?? 'N/A') }}" 
                                   disabled
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="text" 
                                   value="${{ number_format($subscription->total_amount / 100, 2) }}" 
                                   disabled
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Next Billing Date</label>
                            <input type="text" 
                                   value="{{ $subscription->next_billing_date ? $subscription->next_billing_date->format('F j, Y') : 'N/A' }}" 
                                   disabled
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Created Date</label>
                            <input type="text" 
                                   value="{{ $subscription->created_at->format('F j, Y') }}" 
                                   disabled
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <a href="{{ route('admin.subscriptions.show', $subscription) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Subscription
                </button>
            </div>
        </form>
    </div>
</div>
@endsection