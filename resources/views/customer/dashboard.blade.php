@extends('layouts.app')

@section('title', 'My Dashboard - Flags Across Our Community')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ Auth::user()->first_name }}!</h1>
            <p class="mt-2 text-gray-600">Manage your flag subscription and view upcoming placements.</p>
        </div>

        @if($activeSubscription)
        <!-- Active Subscription Summary -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Active Subscription</h3>
                        <div class="mt-2 max-w-xl text-sm text-gray-500">
                            <p>{{ $activeSubscription->type_display }} • Expires {{ $activeSubscription->end_date->format('F j, Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600">{{ $activeSubscription->formatted_total }}</div>
                        @if($activeSubscription->isExpiringSoon(30))
                            <div class="text-sm text-yellow-600 font-medium">Expires in {{ $activeSubscription->remaining_days }} days</div>
                        @endif
                    </div>
                </div>

                <!-- Subscription Details -->
                <div class="mt-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($activeSubscription->items as $item)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $item->flagProduct->flagType->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->flagProduct->flagSize->name }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('customer.subscription') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        View Details
                    </a>
                    @if($activeSubscription->type === 'annual' && $activeSubscription->isExpiringSoon(60))
                    <a href="{{ route('customer.renew-subscription') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Renew Subscription
                    </a>
                    @endif
                    <button onclick="document.getElementById('instructions-form').classList.toggle('hidden')" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Update Instructions
                    </button>
                </div>

                <!-- Special Instructions Form (Hidden by default) -->
                <form id="instructions-form" class="hidden mt-4" method="POST" action="{{ route('customer.update-instructions') }}">
                    @csrf
                    <div>
                        <label for="special_instructions" class="block text-sm font-medium text-gray-700">Special Instructions</label>
                        <textarea name="special_instructions" id="special_instructions" rows="3" 
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                  placeholder="Any special instructions for flag placement...">{{ $activeSubscription->special_instructions }}</textarea>
                    </div>
                    <div class="mt-3 flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('instructions-form').classList.add('hidden')" 
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-3 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Update Instructions
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Upcoming Placements -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Upcoming Flag Placements</h3>
                        
                        @if($upcomingPlacements->count() > 0)
                        <div class="space-y-4">
                            @foreach($upcomingPlacements as $placement)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <h4 class="text-lg font-medium text-gray-900">{{ $placement->holiday->name }}</h4>
                                            <p class="text-sm text-gray-500">{{ $placement->flagProduct->flagType->name }} ({{ $placement->flagProduct->flagSize->name }})</p>
                                            <p class="text-sm text-gray-500">Removal: {{ $placement->removal_date->format('F j, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-semibold text-gray-900">{{ $placement->placement_date->format('M j') }}</div>
                                        <div class="text-sm text-gray-500">{{ $placement->placement_date->diffForHumans() }}</div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ ucfirst($placement->status) }}
                                        </span>
                                    </div>
                                </div>
                                
                                @if($placement->notes)
                                <div class="mt-3 p-3 bg-gray-50 rounded-md">
                                    <p class="text-sm text-gray-700">{{ $placement->notes }}</p>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            <a href="{{ route('customer.placements') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                View all placements →
                            </a>
                        </div>
                        @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No upcoming placements</h3>
                            <p class="mt-1 text-sm text-gray-500">Your next flag placements will appear here.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Account Summary & Quick Actions -->
            <div class="space-y-6">
                <!-- Account Stats -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Account Summary</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Total Subscriptions</span>
                                <span class="text-sm font-medium text-gray-900">{{ $stats['total_subscriptions'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Flags Placed This Year</span>
                                <span class="text-sm font-medium text-gray-900">{{ $stats['flags_placed_this_year'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Account Status</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ ucfirst($stats['subscription_status']) }}
                                </span>
                            </div>
                            @if($stats['next_placement'])
                            <div class="border-t border-gray-200 pt-4">
                                <span class="text-sm text-gray-500">Next Placement</span>
                                <div class="mt-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $stats['next_placement']->holiday->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $stats['next_placement']->placement_date->format('F j, Y') }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('customer.account') }}" 
                               class="block w-full bg-gray-50 border border-gray-200 rounded-md p-3 text-left hover:bg-gray-100 transition-colors">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900">Update Account Info</span>
                                </div>
                            </a>
                            
                            <a href="{{ route('customer.notifications') }}" 
                               class="block w-full bg-gray-50 border border-gray-200 rounded-md p-3 text-left hover:bg-gray-100 transition-colors">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900">Notification Settings</span>
                                </div>
                            </a>
                            
                            @if($activeSubscription && $activeSubscription->type === 'annual')
                            <button onclick="showCancelModal()" 
                                    class="block w-full bg-gray-50 border border-gray-200 rounded-md p-3 text-left hover:bg-gray-100 transition-colors">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span class="text-sm font-medium text-red-700">Cancel Subscription</span>
                                </div>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        @if($pastPlacements->count() > 0)
        <div class="mt-6">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Flag History</h3>
                    <div class="space-y-3">
                        @foreach($pastPlacements as $placement)
                        <div class="flex items-center justify-between py-3 border-b border-gray-200 last:border-b-0">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $placement->holiday->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $placement->flagProduct->flagType->name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-900">{{ $placement->placement_date->format('M j, Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $placement->status_display }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('customer.placements') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                            View full history →
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @else
        <!-- No Active Subscription -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Active Subscription</h3>
                <p class="mt-1 text-sm text-gray-500">Get started with a flag subscription to honor our nation on patriotic holidays.</p>
                <div class="mt-6">
                    <a href="{{ route('home') }}#flag-selection" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                        </svg>
                        Start Subscription
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@if($activeSubscription && $activeSubscription->type === 'annual')
<!-- Cancel Subscription Modal -->
<div id="cancelModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('customer.cancel-subscription') }}">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Cancel Subscription</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to cancel your subscription? Flags will still be placed for already scheduled holidays.
                                </p>
                            </div>
                            <div class="mt-4">
                                <label for="reason" class="block text-sm font-medium text-gray-700">Reason (optional)</label>
                                <textarea name="reason" id="reason" rows="3" 
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                                          placeholder="Let us know why you're canceling..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel Subscription
                    </button>
                    <button type="button" onclick="hideCancelModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Keep Subscription
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
function showCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
}

function hideCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
}
</script>
@endsection