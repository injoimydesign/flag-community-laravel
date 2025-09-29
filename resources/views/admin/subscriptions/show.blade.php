@extends('layouts.admin')

@section('title', 'Subscription Details - Admin Dashboard')

@section('page-title', 'Subscription Details')

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

    <!-- Subscription Overview -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Subscription #{{ $subscription->id }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Created on {{ $subscription->created_at->format('F j, Y') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    @php
                        $statusColors = [
                            'active' => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                        ];
                    @endphp
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($subscription->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer Information -->
                <div class="col-span-2">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Customer Information</h4>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Customer Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('admin.customers.show', $subscription->user) }}" class="text-indigo-600 hover:text-indigo-900">
                            {{ $subscription->user->name ?? 'N/A' }}
                        </a>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->user->email ?? 'N/A' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->user->phone ?? 'N/A' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->user->address ?? 'N/A' }}</dd>
                </div>

                <!-- Subscription Details -->
                <div class="col-span-2 pt-4 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Subscription Details</h4>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Flag Product</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->flagProduct->name ?? 'N/A' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Billing Frequency</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($subscription->billing_frequency ?? 'N/A') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-semibold">${{ number_format($subscription->total_amount / 100, 2) }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Next Billing Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $subscription->next_billing_date ? $subscription->next_billing_date->format('F j, Y') : 'N/A' }}
                    </dd>
                </div>

                @if($subscription->cancelled_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Cancelled At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->cancelled_at->format('F j, Y g:i A') }}</dd>
                </div>
                @endif

                @if($subscription->stripe_subscription_id)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Stripe Subscription ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $subscription->stripe_subscription_id }}</dd>
                </div>
                @endif

                @if($subscription->placement_instructions)
                <div class="col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Placement Instructions</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->placement_instructions }}</dd>
                </div>
                @endif

                @if($subscription->notes)
                <div class="col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $subscription->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.subscriptions.edit', $subscription) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Subscription
                    </a>

                    @if($subscription->status === 'active')
                    <form method="POST" action="{{ route('admin.subscriptions.cancel', $subscription) }}" 
                          onsubmit="return confirm('Are you sure you want to cancel this subscription?');">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel Subscription
                        </button>
                    </form>
                    @endif

                    @if($subscription->status === 'cancelled')
                    <form method="POST" action="{{ route('admin.subscriptions.reactivate', $subscription) }}"
                          onsubmit="return confirm('Are you sure you want to reactivate this subscription?');">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Reactivate Subscription
                        </button>
                    </form>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.subscriptions.destroy', $subscription) }}"
                      onsubmit="return confirm('Are you sure you want to delete this subscription? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 011-1h2a1 1 0 011 1v3M4 7h16" />