@extends('layouts.admin')

@section('title', 'Flag Placements - Admin Dashboard')

@section('page-title', 'Flag Placements')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Flag Placements</h1>
            <p class="text-gray-600 mt-1">Manage scheduled flag placements</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.placements.create') }}" class="btn btn-primary">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Placement
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <form method="GET" action="{{ route('admin.placements.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Customer name..." class="w-full border-gray-300 rounded-md">
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full border-gray-300 rounded-md">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="placed" {{ request('status') === 'placed' ? 'selected' : '' }}>Placed</option>
                    <option value="removed" {{ request('status') === 'removed' ? 'selected' : '' }}>Removed</option>
                    <option value="skipped" {{ request('status') === 'skipped' ? 'selected' : '' }}>Skipped</option>
                </select>
            </div>

            <!-- Holiday Filter -->
            <div>
                <label for="holiday_id" class="block text-sm font-medium text-gray-700 mb-1">Holiday</label>
                <select name="holiday_id" id="holiday_id" class="w-full border-gray-300 rounded-md">
                    <option value="">All Holidays</option>
                    @foreach($holidays ?? [] as $holiday)
                        <option value="{{ $holiday->id }}" {{ request('holiday_id') == $holiday->id ? 'selected' : '' }}>
                            {{ $holiday->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="btn btn-primary flex-1">Apply</button>
                <a href="{{ route('admin.placements.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Bulk Actions Bar (shown when items selected) -->
    <div id="bulk-actions-bar" class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6 hidden">
        <div class="flex items-center justify-between">
            <span id="selected-count" class="text-sm font-medium text-indigo-900">
                0 placements selected
            </span>
            <div class="flex space-x-3">
                <button onclick="clearSelection()" class="btn btn-secondary btn-sm">
                    Clear Selection
                </button>
                <button onclick="bulkDelete()" class="btn btn-danger btn-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete Selected
                </button>
            </div>
        </div>
    </div>

    <!-- Placements Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left">
                        <input type="checkbox" id="select-all" onchange="toggleSelectAll()" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Holiday</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Placement Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($placements as $placement)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="placement-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="{{ $placement->id }}" onchange="updateSelection()">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $placement->subscription->user->name ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $placement->subscription->user->email ?? '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">

                            @if(isset($placement->holiday_ids))
                                <!-- Option 1: JSON field -->
                                @foreach(Holiday::whereIn('id', $placement->holiday_ids)->get() as $holiday)
                                    <span class="badge">{{ $holiday->name }}</span>
                                @endforeach
                            @else
                                <!-- Option 2: Pivot table -->
                                @foreach($placement->holidays as $holiday)
                                    <span class="badge text-xs font-semibold rounded-full
                                 bg-blue-100 text-blue-500">{{ $holiday->name }}</span>
                                @endforeach
                            @endif

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $placement->placement_date ? $placement->placement_date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($placement->status === 'scheduled') bg-blue-100 text-blue-800
                                @elseif($placement->status === 'placed') bg-green-100 text-green-800
                                @elseif($placement->status === 'removed') bg-gray-100 text-gray-800
                                @elseif($placement->status === 'skipped') bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($placement->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('admin.placements.show', $placement) }}" class="text-indigo-600 hover:text-indigo-900">
                                    View
                                </a>

                                @if($placement->status === 'scheduled')
                                    <form action="{{ route('admin.placements.place', $placement) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900">
                                            Mark Placed
                                        </button>
                                    </form>
                                @endif

                                <button onclick="deletePlacement({{ $placement->id }}, '{{ $placement->subscription->user->name ?? 'this placement' }}')" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="mt-2">No placements found</p>
                            <a href="{{ route('admin.placements.create') }}" class="mt-3 inline-flex items-center text-indigo-600 hover:text-indigo-900">
                                Create your first placement
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($placements->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $placements->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Hidden form for single delete -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Hidden form for bulk delete -->
<form id="bulk-delete-form" action="{{ route('admin.placements.bulk-delete') }}" method="POST" style="display: none;">
    @csrf
    <div id="bulk-placement-ids-container"></div>
</form>

@push('scripts')
<script>
// Track selected placements
let selectedPlacements = new Set();

// Update selection tracking
function updateSelection() {
    selectedPlacements.clear();
    document.querySelectorAll('.placement-checkbox:checked').forEach(cb => {
        selectedPlacements.add(cb.value);
    });

    // Update UI
    const count = selectedPlacements.size;
    const bulkBar = document.getElementById('bulk-actions-bar');
    const countSpan = document.getElementById('selected-count');

    if (count > 0) {
        bulkBar.classList.remove('hidden');
        countSpan.textContent = `${count} placement${count !== 1 ? 's' : ''} selected`;
    } else {
        bulkBar.classList.add('hidden');
    }

    // Update select-all checkbox
    const allCheckboxes = document.querySelectorAll('.placement-checkbox');
    const selectAllCb = document.getElementById('select-all');
    selectAllCb.checked = allCheckboxes.length > 0 && count === allCheckboxes.length;
}

// Toggle select all
function toggleSelectAll() {
    const selectAll = document.getElementById('select-all').checked;
    document.querySelectorAll('.placement-checkbox').forEach(cb => {
        cb.checked = selectAll;
    });
    updateSelection();
}

// Clear selection
function clearSelection() {
    document.querySelectorAll('.placement-checkbox').forEach(cb => {
        cb.checked = false;
    });
    document.getElementById('select-all').checked = false;
    updateSelection();
}

// Delete single placement
function deletePlacement(placementId, customerName) {
    if (!confirm(`Are you sure you want to delete the placement for ${customerName}? This action cannot be undone.`)) {
        return;
    }

    const form = document.getElementById('delete-form');
    form.action = `/admin/placements/${placementId}`;
    form.submit();
}

// Bulk delete - FIXED
function bulkDelete() {
    if (selectedPlacements.size === 0) {
        alert('Please select placements to delete');
        return;
    }

    if (!confirm(`Are you sure you want to delete ${selectedPlacements.size} placement(s)? This action cannot be undone.`)) {
        return;
    }

    // Clear existing inputs
    const container = document.getElementById('bulk-placement-ids-container');
    container.innerHTML = '';

    // Add each placement ID as a separate input field (array format)
    selectedPlacements.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'placement_ids[]';  // ← Array notation
        input.value = id;
        container.appendChild(input);
    });

    document.getElementById('bulk-delete-form').submit();
}
</script>
@endpush
@endsection
