@extends('layouts.admin')

@section('title', 'Placement Details - Admin Dashboard')

@section('page-title', 'Placement Details')

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

    <!-- Placement Overview -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Placement #{{ $placement->id }}</h3>
                    <p class="mt-1 text-sm text-gray-500">Created on {{ $placement->created_at->format('F j, Y') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    @php
                        $statusColors = [
                            'scheduled' => 'bg-yellow-100 text-yellow-800',
                            'placed' => 'bg-green-100 text-green-800',
                            'removed' => 'bg-gray-100 text-gray-800',
                            'skipped' => 'bg-red-100 text-red-800',
                        ];
                    @endphp
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusColors[$placement->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($placement->status) }}
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
                        @if($placement->subscription && $placement->subscription->user)
                            <a href="{{ route('admin.customers.show', $placement->subscription->user) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $placement->subscription->user->name }}
                            </a>
                        @else
                            N/A
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $placement->subscription->user->email ?? 'N/A' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $placement->subscription->user->phone ?? 'N/A' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Subscription</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($placement->subscription)
                            <a href="{{ route('admin.subscriptions.show', $placement->subscription) }}" class="text-indigo-600 hover:text-indigo-900">
                                #{{ $placement->subscription->id }}
                            </a>
                        @else
                            N/A
                        @endif
                    </dd>
                </div>

                <!-- Placement Details -->
                <div class="col-span-2 pt-4 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Placement Details</h4>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Holidays</dt>
                    <!--<dd class="mt-1 text-sm text-gray-900">
                        @if($placement->holiday)
                            <a href="{{ route('admin.holidays.show', $placement->holiday) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $placement->holiday->name }}
                            </a>
                        @else
                            N/A
                        @endif
                    </dd>-->
                    <dd>{{ $placement->holiday_names }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Flag Product</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $placement->flagProduct->display_name ?? 'N/A' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Placement Date</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $placement->placement_date->format('F j, Y') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Removal Date</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $placement->removal_date ? $placement->removal_date->format('F j, Y') : 'N/A' }}</dd>
                </div>

                <!-- Placement Address -->
                @if($placement->placement_address)
                <div class="col-span-2 pt-4 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Placement Address</h4>
                </div>

                <div class="col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $placement->full_placement_address }}</dd>
                </div>
                @endif

                <!-- Status Information -->
                <div class="col-span-2 pt-4 border-t border-gray-200">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Status Information</h4>
                </div>

                @if($placement->placed_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Placed At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $placement->placed_at->format('F j, Y g:i A') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Placed By</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $placement->placedByUser->name ?? 'N/A' }}</dd>
                </div>
                @endif

                @if($placement->removed_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Removed At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $placement->removed_at->format('F j, Y g:i A') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Removed By</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $placement->removedByUser->name ?? 'N/A' }}</dd>
                </div>
                @endif

                @if($placement->skipped_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Skipped At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $placement->skipped_at->format('F j, Y g:i A') }}</dd>
                </div>

                @if($placement->skip_reason)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Skip Reason</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $placement->skip_reason }}</dd>
                </div>
                @endif
                @endif

                @if($placement->notes)
                <div class="col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $placement->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                @if($placement->status === 'scheduled')
                    <form method="POST" action="{{ route('admin.placements.place', $placement) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Mark as Placed
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.placements.skip', $placement) }}" class="inline">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Are you sure you want to skip this placement?')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Skip Placement
                        </button>
                    </form>
                @endif

                @if($placement->status === 'placed')
                    <form method="POST" action="{{ route('admin.placements.remove', $placement) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Mark as Removed
                        </button>
                    </form>
                @endif
            </div>

            <a href="{{ route('admin.placements.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back to Placements
            </a>
        </div>
    </div>
</div>
@endsection
