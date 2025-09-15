@extends('layouts.app')

@section('title', 'Order Confirmed - Flags Across Our Community')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success Message -->
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Confirmed!</h1>
            <p class="text-lg text-gray-600">Thank you for choosing Flags Across Our Community</p>
        </div>

        <!-- Order Summary -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Summary</h2>
                
                <div class="space-y-4">
                    <!-- Subscription Type -->
                    <div class="flex justify-between items-center pb-4 border-b border-gray-200">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $subscription->type_display }}</h3>
                            <p class="text-sm text-gray-500">
                                Service period: {{ $subscription->start_date->format('F j, Y') }} - {{ $subscription->end_date->format('F j, Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-blue-600">{{ $subscription->formatted_total }}</div>
                            @if($subscription->type === 'annual')
                                <div class="text-sm text-green-600">
                                    @php
                                        $totalOnetime = $subscription->items->sum(function($item) {
                                            return $item->flagProduct->one_time_price;
                                        }) * 5; // 5 holidays
                                        $savings = $totalOnetime - $subscription->total_amount;
                                    @endphp
                                    You saved ${{ number_format($savings, 2) }}!
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Flag Products -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Your Flags</h4>
                        <div class="space-y-3">
                            @foreach($subscription->items as $item)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 {{ $item->flagProduct->flagType->category === 'us' ? 'bg-blue-100' : 'bg-green-100' }} rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 {{ $item->flagProduct->flagType->category === 'us' ? 'text-blue-600' : 'text-green-600' }}" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $item->flagProduct->flagType->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->flagProduct->flagSize->name }} ({{ $item->flagProduct->flagSize->dimensions }})</p>
                                    </div>
                                </div>
                                <div class="text-sm font-medium text-gray-900">{{ $item->formatted_unit_price }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- What's Next -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-blue-900 mb-4">What happens next?</h3>
            <div class="space-y-3">
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-sm font-semibold">1</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-900">We'll prepare your flags</p>
                        <p class="text-sm text-blue-700">Our team will prepare your flags and plan the placement routes.</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-sm font-semibold">2</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-900">You'll receive notifications</p>
                        <p class="text-sm text-blue-700">We'll email you before each flag placement and removal.</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="flex-shrink-0 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white text-sm font-semibold">3</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-900">Flags will be placed automatically</p>
                        <p class="text-sm text-blue-700">Your flags will be placed before each patriotic holiday and removed afterward.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Holidays -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Flag Schedule</h3>
                <p class="text-sm text-gray-600 mb-4">Your flags will be displayed for these patriotic holidays:</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php
                        $holidays = \App\Models\Holiday::active()->ordered()->get();
                    @endphp
                    @foreach($holidays as $holiday)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $holiday->name }}</p>
                                @if($holiday->slug === 'patriots-day')
                                <p class="text-xs text-gray-500">Next observance: 2026</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Account Access -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Manage Your Subscription</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Access your customer dashboard to view upcoming placements, update your address, and manage your account.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('customer.dashboard') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z" />
                        </svg>
                        View Dashboard
                    </a>
                    @if($subscription->type === 'annual' && $subscription->stripe_subscription_id)
                    <a href="{{ route('checkout.customer-portal') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Manage Billing
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="text-center">
            <p class="text-sm text-gray-500 mb-2">Questions about your order?</p>
            <p class="text-sm text-gray-900">
                Contact us at 
                <a href="mailto:support@flagsacrosscommunity.com" class="text-blue-600 hover:text-blue-500 font-medium">
                    support@flagsacrosscommunity.com
                </a>
                or call 
                <a href="tel:+1234567890" class="text-blue-600 hover:text-blue-500 font-medium">
                    (123) 456-7890
                </a>
            </p>
        </div>
    </div>
</div>

<!-- Confirmation Email Notification -->
<div class="fixed bottom-4 right-4 max-w-sm" x-data="{ show: true }" x-show="show" x-cloak>
    <div class="bg-green-500 text-white p-4 rounded-lg shadow-lg">
        <div class="flex items-center">
            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
            </svg>
            <div>
                <p class="font-medium">Confirmation email sent!</p>
                <p class="text-sm opacity-90">Check your inbox for details.</p>
            </div>
            <button @click="show = false" class="ml-4 text-green-100 hover:text-white">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
// Auto-hide notification after 5 seconds
setTimeout(() => {
    document.querySelector('[x-data]').__x.$data.show = false;
}, 5000);
</script>
@endsection