@extends('layouts.app')

@section('title', 'Professional Flag Display Service - Flags Across Our Community')

@section('content')
<div class="bg-white" x-data="flagSelector()">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-50 to-indigo-100 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                        Honor Our Nation with
                        <span class="text-blue-600">Professional Flag Display</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-8">
                        We place and maintain flags at your home for all major patriotic holidays.
                        Choose from US flags and military branch flags with convenient annual subscriptions.
                    </p>

                    <!-- CHECKOUT BUTTONS WITH FIX -->
                    <div class="space-y-4">
                        <!-- Option 1: Dynamic product selection with fallback -->
                        @if(isset($flagProducts) && $flagProducts->count() > 0)
                            <div class="space-y-3">
                                <a href="{{ route('checkout.index', ['products' => [$flagProducts->first()->id], 'subscription_type' => 'annual']) }}"
                                   class="w-full bg-red-600 hover:bg-red-700 text-white py-4 px-8 rounded-lg text-xl font-semibold transition-colors text-center block">
                                    Start Annual Flag Service - ${{ number_format($flagProducts->first()->annual_subscription_price ?? 299.99, 2) }}/year
                                    <span class="block text-sm opacity-90">Save $60!</span>
                                </a>
                                <a href="{{ route('checkout.index', ['products' => [$flagProducts->first()->id], 'subscription_type' => 'monthly']) }}"
                                   class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-semibold text-center block">
                                    Monthly Flag Service - ${{ number_format($flagProducts->first()->monthly_price ?? 29.99, 2) }}/month
                                </a>
                            </div>
                        @else
                            <!-- Fallback buttons with JavaScript enhancement -->
                            <div class="space-y-3">
                                <button id="annual-checkout-btn"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white py-4 px-8 rounded-lg text-xl font-semibold transition-colors">
                                    Start Annual Flag Service - $299.99/year
                                    <span class="block text-sm opacity-90">Save $60!</span>
                                </button>
                                <button id="monthly-checkout-btn"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-semibold transition-colors">
                                    Monthly Flag Service - $29.99/month
                                </button>
                            </div>
                        @endif

                        <p class="text-sm text-gray-600 text-center">Professional installation and maintenance included</p>
                    </div>
                </div>

                <div class="text-center lg:text-right">
                    <div class="relative inline-block">
                        <div class="w-96 h-64 bg-gradient-to-r from-red-500 via-white to-blue-500 rounded-lg shadow-lg flex items-center justify-center">
                            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="absolute -top-4 -right-4 bg-yellow-400 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">
                            Professional Grade
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Flag Selection Section -->
    <section id="flag-selection" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Choose Your Flags</h2>
                <p class="text-lg text-gray-600">Select from our collection of high-quality flags</p>
            </div>

            <!-- Flag Categories -->
            <div class="flex justify-center mb-8">
                <div class="bg-gray-100 p-1 rounded-lg">
                    <button @click="activeCategory = 'us'"
                            :class="{'bg-white shadow-sm': activeCategory === 'us'}"
                            class="px-6 py-2 rounded-md font-medium text-sm transition-all">
                        US Flags
                    </button>
                    <button @click="activeCategory = 'military'"
                            :class="{'bg-white shadow-sm': activeCategory === 'military'}"
                            class="px-6 py-2 rounded-md font-medium text-sm transition-all">
                        Military Flags
                    </button>
                </div>
            </div>

            <!-- US Flags -->
            <div x-show="activeCategory === 'us'" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @if(isset($usFlags))
                        @foreach($usFlags as $flag)
                        <div class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-500 transition-colors cursor-pointer"
                             @click="toggleFlag({{ $flag->id }}, '{{ $flag->name }}', {{ $flag->one_time_price ?? 49.99 }}, {{ $flag->annual_price ?? 299.99 }})">
                            <div class="text-center">
                                <div class="w-20 h-14 bg-gradient-to-r from-red-500 via-white to-blue-500 rounded mx-auto mb-4 flex items-center justify-center">
                                    <span class="text-xs font-bold text-blue-800">{{ strtoupper(substr($flag->name, 0, 3)) }}</span>
                                </div>
                                <h3 class="font-semibold text-lg mb-2">{{ $flag->name }}</h3>
                                <p class="text-gray-600 text-sm mb-4">{{ $flag->description ?? 'Professional grade flag' }}</p>
                                <div class="space-y-2">
                                    <div class="text-sm text-gray-500">
                                        <span class="line-through">${{ number_format($flag->one_time_price ?? 49.99, 2) }} one-time</span>
                                    </div>
                                    <div class="text-lg font-semibold text-green-600">
                                        ${{ number_format($flag->annual_price ?? 299.99, 2) }}/year
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-500 transition-colors cursor-pointer"
                             @click="toggleFlag(1, 'American Flag', 49.99, 299.99)">
                            <div class="text-center">
                                <div class="w-20 h-14 bg-gradient-to-r from-red-500 via-white to-blue-500 rounded mx-auto mb-4 flex items-center justify-center">
                                    <span class="text-xs font-bold text-blue-800">USA</span>
                                </div>
                                <h3 class="font-semibold text-lg mb-2">American Flag</h3>
                                <p class="text-gray-600 text-sm mb-4">Professional grade US flag</p>
                                <div class="space-y-2">
                                    <div class="text-sm text-gray-500">
                                        <span class="line-through">$49.99 one-time</span>
                                    </div>
                                    <div class="text-lg font-semibold text-green-600">
                                        $299.99/year
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Military Flags -->
            <div x-show="activeCategory === 'military'" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @if(isset($militaryFlags))
                        @foreach($militaryFlags as $flag)
                        <div class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-500 transition-colors cursor-pointer"
                             @click="toggleFlag({{ $flag->id }}, '{{ $flag->name }}', {{ $flag->one_time_price ?? 49.99 }}, {{ $flag->annual_price ?? 299.99 }})">
                            <div class="text-center">
                                <div class="w-20 h-14 bg-gray-800 rounded mx-auto mb-4 flex items-center justify-center">
                                    <span class="text-xs font-bold text-white">{{ strtoupper(substr($flag->name, 0, 3)) }}</span>
                                </div>
                                <h3 class="font-semibold text-lg mb-2">{{ $flag->name }}</h3>
                                <p class="text-gray-600 text-sm mb-4">{{ $flag->description ?? 'Military service flag' }}</p>
                                <div class="space-y-2">
                                    <div class="text-sm text-gray-500">
                                        <span class="line-through">${{ number_format($flag->one_time_price ?? 49.99, 2) }} one-time</span>
                                    </div>
                                    <div class="text-lg font-semibold text-green-600">
                                        ${{ number_format($flag->annual_price ?? 299.99, 2) }}/year
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <!-- Default military flags -->
                        <div class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-500 transition-colors cursor-pointer"
                             @click="toggleFlag(2, 'Army Flag', 49.99, 299.99)">
                            <div class="text-center">
                                <div class="w-20 h-14 bg-gray-800 rounded mx-auto mb-4 flex items-center justify-center">
                                    <span class="text-xs font-bold text-white">ARM</span>
                                </div>
                                <h3 class="font-semibold text-lg mb-2">Army Flag</h3>
                                <p class="text-gray-600 text-sm mb-4">US Army service flag</p>
                                <div class="space-y-2">
                                    <div class="text-sm text-gray-500">
                                        <span class="line-through">$49.99 one-time</span>
                                    </div>
                                    <div class="text-lg font-semibold text-green-600">
                                        $299.99/year
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-500 transition-colors cursor-pointer"
                             @click="toggleFlag(3, 'Navy Flag', 49.99, 299.99)">
                            <div class="text-center">
                                <div class="w-20 h-14 bg-gray-800 rounded mx-auto mb-4 flex items-center justify-center">
                                    <span class="text-xs font-bold text-white">NAV</span>
                                </div>
                                <h3 class="font-semibold text-lg mb-2">Navy Flag</h3>
                                <p class="text-gray-600 text-sm mb-4">US Navy service flag</p>
                                <div class="space-y-2">
                                    <div class="text-sm text-gray-500">
                                        <span class="line-through">$49.99 one-time</span>
                                    </div>
                                    <div class="text-lg font-semibold text-green-600">
                                        $299.99/year
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Selected Flags Summary -->
            <div x-show="selectedFlags.length > 0" x-cloak class="mt-12 bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Selected Flags</h3>
                <div class="space-y-3">
                    <template x-for="flag in selectedFlags" :key="flag.id">
                        <div class="flex items-center justify-between bg-white rounded-lg p-3">
                            <div class="flex items-center">
                                <div class="w-8 h-6 bg-gradient-to-r from-red-500 via-white to-blue-500 rounded mr-3"></div>
                                <span x-text="flag.name" class="font-medium"></span>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span x-text="'$' + flag.yearlyPrice.toFixed(2)" class="font-semibold text-green-600"></span>
                                <button @click="removeFlag(flag.id)" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-medium">Annual Total:</span>
                        <span x-text="'$' + totalCost.toFixed(2)" class="font-semibold text-2xl text-green-600"></span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-sm text-gray-600">You save:</span>
                        <span x-text="'$' + totalSavings.toFixed(2)" class="text-sm font-medium text-green-600"></span>
                    </div>

                    <!-- CHECKOUT BUTTON WITH FIX FOR SELECTED FLAGS -->
                    <button @click="proceedToCheckout()"
                            class="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-lg font-semibold transition-colors">
                        Proceed to Checkout
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div x-show="selectedFlags.length === 0" x-cloak class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Select Your Flags</h3>
                <p class="text-gray-600">Choose one or more flags to get started with your subscription</p>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-lg text-gray-600">Simple, professional flag service for your home</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-white">1</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Choose Your Flags</h3>
                    <p class="text-gray-600">Select from US flags and military branch flags. All flags are professional grade and weather-resistant.</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-white">2</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">We Install & Maintain</h3>
                    <p class="text-gray-600">Our team installs your flag display and maintains it throughout the year for all major holidays.</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-white">3</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Enjoy the Display</h3>
                    <p class="text-gray-600">Sit back and enjoy your professionally displayed flags. We handle everything from placement to removal.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Areas -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Service Areas</h2>
                <p class="text-lg text-gray-600">Currently serving these communities with plans to expand</p>
            </div>

            <div class="bg-gray-100 rounded-lg p-8 text-center">
                <div class="max-w-md mx-auto">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Check if we serve your area</h3>
                    <div class="space-y-3">
                        <input type="text" id="address" placeholder="Street Address"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <input type="text" id="city" placeholder="City"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" id="state" placeholder="State"
                                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <input type="text" id="zip" placeholder="ZIP Code"
                                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button onclick="checkServiceArea()"
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            Check Service Area
                        </button>
                    </div>
                    <div id="service-area-result" class="mt-4"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">What Our Customers Say</h2>
                <p class="text-lg text-gray-600">Join hundreds of satisfied customers</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"As a veteran, I appreciate having my service flag displayed properly. The quality is excellent and the service is reliable."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                            <span class="text-green-600 font-semibold">MJ</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Michael Johnson</p>
                            <p class="text-sm text-gray-500">US Army Veteran</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"Love that I don't have to worry about anything. The flags always go up on time and look perfect. Great value for the convenience."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                            <span class="text-blue-600 font-semibold">SD</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Sarah Davis</p>
                            <p class="text-sm text-gray-500">Homeowner</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"The service is fantastic! Always professional and on time. My neighbors have been asking for referrals."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                            <span class="text-purple-600 font-semibold">RT</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Robert Thompson</p>
                            <p class="text-sm text-gray-500">Community Leader</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-blue-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Ready to Honor Our Nation?
            </h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Join our community of proud Americans who display their patriotism with professional flag service.
            </p>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="#flag-selection" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors">
                    Get Started Today
                </a>
                <a href="{{ route('contact') }}" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                    Contact Us
                </a>
            </div>
        </div>
    </section>
</div>

<!-- JavaScript for Enhanced Functionality -->
<script>
// Alpine.js Component for Flag Selection
function flagSelector() {
    return {
        activeCategory: 'us',
        selectedFlags: [],

        get totalCost() {
            return this.selectedFlags.reduce((sum, flag) => sum + flag.yearlyPrice, 0);
        },

        get totalSavings() {
            return this.selectedFlags.reduce((sum, flag) => sum + (flag.oneTimePrice - flag.yearlyPrice), 0);
        },

        toggleFlag(id, name, oneTimePrice, yearlyPrice) {
            const existingIndex = this.selectedFlags.findIndex(flag => flag.id === id);

            if (existingIndex > -1) {
                this.selectedFlags.splice(existingIndex, 1);
            } else {
                this.selectedFlags.push({
                    id: id,
                    name: name,
                    oneTimePrice: oneTimePrice,
                    yearlyPrice: yearlyPrice
                });
            }
        },

        removeFlag(id) {
            const index = this.selectedFlags.findIndex(flag => flag.id === id);
            if (index > -1) {
                this.selectedFlags.splice(index, 1);
            }
        },

        proceedToCheckout() {
            if (this.selectedFlags.length === 0) {
                alert('Please select at least one flag to continue.');
                return;
            }

            // Create product IDs array
            const productIds = this.selectedFlags.map(flag => flag.id);

            // Redirect to checkout with selected products
            const checkoutUrl = '{{ route("checkout.index") }}' +
                '?products[]=' + productIds.join('&products[]=') +
                '&subscription_type=annual';

            window.location.href = checkoutUrl;
        }
    }
}

// Service Area Check Function
function checkServiceArea() {
    const address = document.getElementById('address').value;
    const city = document.getElementById('city').value;
    const state = document.getElementById('state').value;
    const zip = document.getElementById('zip').value;

    if (!address || !city || !state || !zip) {
        showServiceAreaResult('Please fill in all fields.', 'error');
        return;
    }

    // Show loading state
    showServiceAreaResult('Checking service area...', 'loading');

    // Make AJAX request to check service area
    fetch('{{ route("check-service-area") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            address: address,
            city: city,
            state: state,
            zip_code: zip
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.served) {
            showServiceAreaResult(data.message, 'success');
        } else {
            showServiceAreaResult(data.message, 'warning');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showServiceAreaResult('Sorry, there was an error checking your service area. Please try again.', 'error');
    });
}

function showServiceAreaResult(message, type) {
    const resultDiv = document.getElementById('service-area-result');
    let bgColor = 'bg-gray-100';
    let textColor = 'text-gray-700';

    switch (type) {
        case 'success':
            bgColor = 'bg-green-100';
            textColor = 'text-green-700';
            break;
        case 'warning':
            bgColor = 'bg-yellow-100';
            textColor = 'text-yellow-700';
            break;
        case 'error':
            bgColor = 'bg-red-100';
            textColor = 'text-red-700';
            break;
        case 'loading':
            bgColor = 'bg-blue-100';
            textColor = 'text-blue-700';
            break;
    }

    resultDiv.innerHTML = `
        <div class="${bgColor} ${textColor} p-3 rounded-lg text-sm">
            ${message}
        </div>
    `;
}

// Enhanced checkout buttons for fallback scenario
document.addEventListener('DOMContentLoaded', function() {
    // Handle annual checkout button
    const annualBtn = document.getElementById('annual-checkout-btn');
    if (annualBtn) {
        annualBtn.addEventListener('click', function() {
            this.disabled = true;
            this.textContent = 'Loading...';

            // Try to fetch available products first
            fetch('{{ route("flag-products") }}')
                .then(response => response.json())
                .then(products => {
                    if (products && products.length > 0) {
                        const productId = products[0].id;
                        const checkoutUrl = '{{ route("checkout.index") }}' +
                            `?products[]=${productId}&subscription_type=annual`;
                        window.location.href = checkoutUrl;
                    } else {
                        // Fallback to hardcoded product ID 1
                        window.location.href = '{{ route("checkout.index") }}?products[]=1&subscription_type=annual';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback to hardcoded product ID 1
                    window.location.href = '{{ route("checkout.index") }}?products[]=1&subscription_type=annual';
                });
        });
    }

    // Handle monthly checkout button
    const monthlyBtn = document.getElementById('monthly-checkout-btn');
    if (monthlyBtn) {
        monthlyBtn.addEventListener('click', function() {
            this.disabled = true;
            this.textContent = 'Loading...';

            // Try to fetch available products first
            fetch('{{ route("flag-products") }}')
                .then(response => response.json())
                .then(products => {
                    if (products && products.length > 0) {
                        const productId = products[0].id;
                        const checkoutUrl = '{{ route("checkout.index") }}' +
                            `?products[]=${productId}&subscription_type=monthly`;
                        window.location.href = checkoutUrl;
                    } else {
                        // Fallback to hardcoded product ID 1
                        window.location.href = '{{ route("checkout.index") }}?products[]=1&subscription_type=monthly';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback to hardcoded product ID 1
                    window.location.href = '{{ route("checkout.index") }}?products[]=1&subscription_type=monthly';
                });
        });
    }
});
</script>

@endsection
