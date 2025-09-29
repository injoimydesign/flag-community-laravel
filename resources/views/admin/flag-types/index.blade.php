@extends('layouts.admin')

@section('title', 'Flag Types - Admin Dashboard')

@section('page-title', 'Flag Types')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <div>
            <p class="text-sm text-gray-500">Manage flag types and categories</p>
        </div>
        <a href="{{ route('admin.flag-types.create') }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New Flag Type
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6">
        <form method="GET" action="{{ route('admin.flag-types.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       placeholder="Search flag types..." 
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category" id="category" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="md:col-span-4 flex items-end space-x-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Search
                </button>
                <a href="{{ route('admin.flag-types.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Flag Types Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($flagTypes as $flagType)
            <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                <!-- Flag Image -->
                <div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
                    @if($flagType->image_path)
                        <img src="{{ asset('storage/' . $flagType->image_path) }}" 
                             alt="{{ $flagType->name }}" 
                             class="w-full h-full object-cover">
                    @elseif($flagType->image_url)
                        <img src="{{ $flagType->image_url }}" 
                             alt="{{ $flagType->name }}" 
                             class="w-full h-full object-cover">
                    @else
                        <svg class="h-20 w-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                    @endif
                </div>

                <!-- Flag Info -->
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $flagType->name }}</h3>
                            <p class="text-sm text-gray-500">{{ ucfirst($flagType->category) }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $flagType->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $flagType->active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    @if($flagType->description)
                        <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $flagType->description }}</p>
                    @endif

                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-4 py-3 border-t border-b border-gray-200">
                        <div>
                            <p class="text-xs text-gray-500">Products</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $flagType->flag_products_count ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Sort Order</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $flagType->sort_order ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between space-x-3">
                        <a href="{{ route('admin.flag-types.show', $flagType) }}" 
                           class="flex-1 text-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            View
                        </a>
                        <a href="{{ route('admin.flag-types.edit', $flagType) }}" 
                           class="flex-1 text-center px-3 py-2 border border-indigo-300 rounded-md text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('admin.flag-types.destroy', $flagType) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this flag type? This will also affect all related products.');"
                              class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-3 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 011-1h2a1 1 0 011 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12 bg-white rounded-lg shadow">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No flag types found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new flag type.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.flag-types.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Flag Type
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($flagTypes->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 rounded-lg shadow">
            {{ $flagTypes->links() }}
        </div>
    @endif
</div>
@endsection