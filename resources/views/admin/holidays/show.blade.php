@extends('layouts.admin')

@section('title', 'Holiday Details - Admin Dashboard')

@section('page-title', 'Holiday Details')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.holidays.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Holidays
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Holiday Details -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">{{ $holiday->name }}</h3>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $holiday->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $holiday->active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(isset($holiday->date))
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($holiday->date)->format('F j, Y') }}</dd>
                        </div>
                        @endif

                        @if(isset($holiday->recurring))
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Recurring</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $holiday->recurring ? 'Yes (Annually)' : 'No' }}</dd>
                        </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Placement Days Before</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $holiday->placement_days_before ?? 1 }} days</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Removal Days After</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $holiday->removal_days_after ?? 1 }} days</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $holiday->sort_order ?? 0 }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $holiday->created_at->format('M j, Y') }}</dd>
                        </div>

                        @if($holiday->description)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $holiday->description }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('admin.holidays.edit', $holiday) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Holiday
                        </a>

                        @if(method_exists($holiday, 'placements'))
                        <form method="POST" action="{{ route('admin.holidays.generate-placements', $holiday) }}"
                              onsubmit="return confirm('Generate placements for all active subscriptions?');">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-indigo-300 rounded-md shadow-sm text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Generate Placements
                            </button>
                        </form>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('admin.holidays.destroy', $holiday) }}"
                          onsubmit="return confirm('Are you sure you want to delete this holiday? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 011-1h2a1 1 0 011 1v3M4 7h16" />
                            </svg>
                            Delete Holiday
                        </button>
                    </form>
                </div>
            </div>

            <!-- Associated Placements -->
            @if(isset($holiday->placements) && $holiday->placements->count() > 0)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Flag Placements</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placed At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($holiday->placements as $placement)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $placement->subscription->user->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ isset($placement->scheduled_date) ? \Carbon\Carbon::parse($placement->scheduled_date)->format('M j, Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'scheduled' => 'bg-blue-100 text-blue-800',
                                            'placed' => 'bg-green-100 text-green-800',
                                            'removed' => 'bg-gray-100 text-gray-800',
                                            'skipped' => 'bg-yellow-100 text-yellow-800',
                                        ];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$placement->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($placement->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $placement->placed_at ? \Carbon\Carbon::parse($placement->placed_at)->format('M j, Y g:i A') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Stats Sidebar -->
        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Placements</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_placements'] ?? 0 }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Scheduled</dt>
                        <dd class="mt-1 text-2xl font-semibold text-blue-600">{{ $stats['scheduled_placements'] ?? 0 }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Completed</dt>
                        <dd class="mt-1 text-2xl font-semibold text-green-600">{{ $stats['completed_placements'] ?? 0 }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Skipped</dt>
                        <dd class="mt-1 text-2xl font-semibold text-yellow-600">{{ $stats['skipped_placements'] ?? 0 }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.placements.index', ['holiday_id' => $holiday->id]) }}" 
                       class="block w-full text-center px-4 py-2 border border-indigo-300 rounded-md text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        View All Placements
                    </a>
                    <a href="{{ route('admin.holidays.index') }}" 
                       class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        View All Holidays
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection