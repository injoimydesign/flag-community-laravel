@extends('layouts.admin')

@section('title', 'Flag Type Details - Admin Dashboard')

@section('page-title', 'Flag Type Details')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.flag-types.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Flag Types
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Flag Type Details -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">{{ $flagType->name }}</h3>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $flagType->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $flagType->active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <!-- Flag Image -->
                @if($flagType->image_path || $flagType->image_url)
                <div class="px-6 py-4">
                    <img src="{{ $flagType->image_path ? asset('storage/' . $flagType->image_path) : $flagType->image_url }}"
                         alt="{{ $flagType->name }}"
                         class="w-full h-64 object-cover rounded-lg">
                </div>
                @endif

                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Category</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($flagType->category) }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Slug</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $flagType->slug }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagType->sort_order ?? 'Not set' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Featured</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagType->featured ? 'Yes' : 'No' }}</dd>
                        </div>

                        @if($flagType->description)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagType->description }}</dd>
                        </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagType->created_at->format('M j, Y') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $flagType->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <a href="{{ route('admin.flag-types.edit', $flagType) }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Flag Type
                    </a>

                    <form method="POST" action="{{ route('admin.flag-types.destroy', $flagType) }}"
                          onsubmit="return confirm('Are you sure you want to delete this flag type? This will also affect all related products.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 011-1h2a1 1 0 011 1v3M4 7h16" />
                            </svg>
                            Delete Flag Type
                        </button>
                    </form>
                </div>
            </div>

            <!-- Associated Products -->
            @if($flagType->flagProducts && $flagType->flagProducts->count() > 0)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Associated Products</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">One-Time Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Annual Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inventory</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($flagType->flagProducts as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $product->flagSize->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ number_format($product->one_time_price / 100, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ number_format($product->annual_subscription_price / 100, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $product->current_inventory ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $product->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.flag-products.show', $product) }}"
                                       class="text-indigo-600 hover:text-indigo-900">View</a>
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
                        <dt class="text-sm font-medium text-gray-500">Total Products</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_products'] ?? 0 }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Active Products</dt>
                        <dd class="mt-1 text-2xl font-semibold text-green-600">{{ $stats['active_products'] ?? 0 }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Inventory</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_inventory'] ?? 0 }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Low Inventory Products</dt>
                        <dd class="mt-1 text-2xl font-semibold text-red-600">{{ $stats['low_inventory_products'] ?? 0 }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.flag-products.create', ['flag_type_id' => $flagType->id]) }}"
                       class="block w-full text-center px-4 py-2 border border-indigo-300 rounded-md text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Add Product to This Type
                    </a>
                    <a href="{{ route('admin.flag-types.index') }}"
                       class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        View All Flag Types
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
