<?php $__env->startSection('title', 'Professional Flag Display Service - Flags Across Our Community'); ?>

<?php $__env->startSection('content'); ?>
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
                    <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <a href="#flag-selection" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors text-center">
                            Get Started
                        </a>
                        <a href="<?php echo e(route('how-it-works')); ?>" class="border border-gray-300 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-50 transition-colors text-center">
                            How It Works
                        </a>
                    </div>
                </div>

                <div class="relative">
                    <div class="bg-white rounded-2xl shadow-xl p-8 transform rotate-2">
                        <div class="bg-gray-100 rounded-lg p-6 mb-6">
                            <div class="flex items-center justify-center h-32 bg-gradient-to-br from-red-100 to-blue-100 rounded-lg">
                                <svg class="w-16 h-16 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">US Flag - 3'x5'</h3>
                        <p class="text-gray-600 mb-4">Professional grade flag with annual service</p>
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-blue-600">$89/year</span>
                            <span class="text-sm text-gray-500">vs $125 one-time</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Holiday Schedule -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Flag Display Schedule</h2>
                <p class="text-lg text-gray-600">We place your flags for all major patriotic holidays</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Presidents' Day -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Presidents' Day</h3>
                    <p class="text-gray-600 text-sm">Federal holiday honoring all U.S. presidents, observed on the third Monday in February</p>
                </div>

                <!-- Memorial Day -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Memorial Day</h3>
                    <p class="text-gray-600 text-sm">Federal holiday honoring military personnel who died in service, observed on the last Monday in May</p>
                </div>

                <!-- Flag Day -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Flag Day</h3>
                    <p class="text-gray-600 text-sm">Commemorates the adoption of the United States flag, observed on June 14</p>
                </div>

                <!-- Independence Day -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Independence Day</h3>
                    <p class="text-gray-600 text-sm">Commemorates the Declaration of Independence, observed on July 4</p>
                </div>

                <!-- Veterans Day -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Veterans Day</h3>
                    <p class="text-gray-600 text-sm">Federal holiday honoring military veterans, observed on November 11</p>
                </div>

                <!-- Patriots Day Special -->
                <div class="bg-gradient-to-br from-red-50 to-blue-50 rounded-xl border-2 border-blue-200 p-6">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Patriots Day</h3>
                    <p class="text-gray-600 text-sm">Special observance every 5 years to honor September 11th</p>
                    <span class="inline-block mt-2 px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Next: 2026</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Flag Selection -->
    <section id="flag-selection" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Choose Your Flags</h2>
                <p class="text-lg text-gray-600">Select from US flags and military branch flags</p>
            </div>

            <!-- Flag Categories -->
            <div class="flex justify-center mb-8">
                <div class="bg-gray-100 p-1 rounded-lg">
                    <button @click="activeCategory = 'us'"
                            :class="activeCategory === 'us' ? 'bg-white text-blue-600 shadow' : 'text-gray-600'"
                            class="px-6 py-2 rounded-md font-medium transition-all">
                        US Flags
                    </button>
                    <button @click="activeCategory = 'military'"
                            :class="activeCategory === 'military' ? 'bg-white text-blue-600 shadow' : 'text-gray-600'"
                            class="px-6 py-2 rounded-md font-medium transition-all">
                        Military Flags
                    </button>
                </div>
            </div>

            <!-- US Flags -->
            <div x-show="activeCategory === 'us'" x-cloak class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
                <div class="bg-white border-2 border-gray-200 rounded-xl p-6 hover:border-blue-300 transition-colors cursor-pointer"
                     @click="toggleFlag('us-flag', 'United States Flag', 25, 89)">
                    <div class="aspect-w-3 aspect-h-2 mb-4">
                        <div class="bg-gradient-to-br from-red-100 to-blue-100 rounded-lg flex items-center justify-center h-32">
                            <svg class="w-12 h-12 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">United States Flag</h3>
                    <p class="text-gray-600 text-sm mb-4">The official flag of the United States of America</p>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-lg font-bold text-blue-600">$89/year</span>
                            <span class="text-sm text-green-600 block">Save $36</span>
                        </div>
                        <div class="w-6 h-6 border-2 rounded border-gray-300"
                             :class="selectedFlags.some(f => f.id === 'us-flag') ? 'bg-blue-600 border-blue-600' : ''">
                            <svg x-show="selectedFlags.some(f => f.id === 'us-flag')" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Military Flags -->
            <div x-show="activeCategory === 'military'" x-cloak class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
                <!-- Army Flag -->
                <div class="bg-white border-2 border-gray-200 rounded-xl p-6 hover:border-blue-300 transition-colors cursor-pointer"
                     @click="toggleFlag('army-flag', 'Army Flag', 30, 109)">
                    <div class="aspect-w-3 aspect-h-2 mb-4">
                        <div class="bg-gradient-to-br from-green-100 to-yellow-100 rounded-lg flex items-center justify-center h-32">
                            <svg class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Army Flag</h3>
                    <p class="text-gray-600 text-sm mb-4">United States Army flag</p>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-lg font-bold text-blue-600">$109/year</span>
                            <span class="text-sm text-green-600 block">Save $41</span>
                        </div>
                        <div class="w-6 h-6 border-2 rounded border-gray-300"
                             :class="selectedFlags.some(f => f.id === 'army-flag') ? 'bg-blue-600 border-blue-600' : ''">
                            <svg x-show="selectedFlags.some(f => f.id === 'army-flag')" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Navy Flag -->
                <div class="bg-white border-2 border-gray-200 rounded-xl p-6 hover:border-blue-300 transition-colors cursor-pointer"
                     @click="toggleFlag('navy-flag', 'Navy Flag', 30, 109)">
                    <div class="aspect-w-3 aspect-h-2 mb-4">
                        <div class="bg-gradient-to-br from-blue-100 to-cyan-100 rounded-lg flex items-center justify-center h-32">
                            <svg class="w-12 h-12 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Navy Flag</h3>
                    <p class="text-gray-600 text-sm mb-4">United States Navy flag</p>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-lg font-bold text-blue-600">$109/year</span>
                            <span class="text-sm text-green-600 block">Save $41</span>
                        </div>
                        <div class="w-6 h-6 border-2 rounded border-gray-300"
                             :class="selectedFlags.some(f => f.id === 'navy-flag') ? 'bg-blue-600 border-blue-600' : ''">
                            <svg x-show="selectedFlags.some(f => f.id === 'navy-flag')" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Air Force Flag -->
                <div class="bg-white border-2 border-gray-200 rounded-xl p-6 hover:border-blue-300 transition-colors cursor-pointer"
                     @click="toggleFlag('airforce-flag', 'Air Force Flag', 30, 109)">
                    <div class="aspect-w-3 aspect-h-2 mb-4">
                        <div class="bg-gradient-to-br from-sky-100 to-blue-100 rounded-lg flex items-center justify-center h-32">
                            <svg class="w-12 h-12 text-sky-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Air Force Flag</h3>
                    <p class="text-gray-600 text-sm mb-4">United States Air Force flag</p>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-lg font-bold text-blue-600">$109/year</span>
                            <span class="text-sm text-green-600 block">Save $41</span>
                        </div>
                        <div class="w-6 h-6 border-2 rounded border-gray-300"
                             :class="selectedFlags.some(f => f.id === 'airforce-flag') ? 'bg-blue-600 border-blue-600' : ''">
                            <svg x-show="selectedFlags.some(f => f.id === 'airforce-flag')" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Marines Flag -->
                <div class="bg-white border-2 border-gray-200 rounded-xl p-6 hover:border-blue-300 transition-colors cursor-pointer"
                     @click="toggleFlag('marines-flag', 'Marine Corps Flag', 30, 109)">
                    <div class="aspect-w-3 aspect-h-2 mb-4">
                        <div class="bg-gradient-to-br from-red-100 to-yellow-100 rounded-lg flex items-center justify-center h-32">
                            <svg class="w-12 h-12 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Marine Corps Flag</h3>
                    <p class="text-gray-600 text-sm mb-4">United States Marine Corps flag</p>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-lg font-bold text-blue-600">$109/year</span>
                            <span class="text-sm text-green-600 block">Save $41</span>
                        </div>
                        <div class="w-6 h-6 border-2 rounded border-gray-300"
                             :class="selectedFlags.some(f => f.id === 'marines-flag') ? 'bg-blue-600 border-blue-600' : ''">
                            <svg x-show="selectedFlags.some(f => f.id === 'marines-flag')" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Coast Guard Flag -->
                <div class="bg-white border-2 border-gray-200 rounded-xl p-6 hover:border-blue-300 transition-colors cursor-pointer"
                     @click="toggleFlag('coastguard-flag', 'Coast Guard Flag', 30, 109)">
                    <div class="aspect-w-3 aspect-h-2 mb-4">
                        <div class="bg-gradient-to-br from-orange-100 to-blue-100 rounded-lg flex items-center justify-center h-32">
                            <svg class="w-12 h-12 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Coast Guard Flag</h3>
                    <p class="text-gray-600 text-sm mb-4">United States Coast Guard flag</p>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-lg font-bold text-blue-600">$109/year</span>
                            <span class="text-sm text-green-600 block">Save $41</span>
                        </div>
                        <div class="w-6 h-6 border-2 rounded border-gray-300"
                             :class="selectedFlags.some(f => f.id === 'coastguard-flag') ? 'bg-blue-600 border-blue-600' : ''">
                            <svg x-show="selectedFlags.some(f => f.id === 'coastguard-flag')" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Space Force Flag -->
                <div class="bg-white border-2 border-gray-200 rounded-xl p-6 hover:border-blue-300 transition-colors cursor-pointer"
                     @click="toggleFlag('spaceforce-flag', 'Space Force Flag', 30, 109)">
                    <div class="aspect-w-3 aspect-h-2 mb-4">
                        <div class="bg-gradient-to-br from-purple-100 to-blue-100 rounded-lg flex items-center justify-center h-32">
                            <svg class="w-12 h-12 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Space Force Flag</h3>
                    <p class="text-gray-600 text-sm mb-4">United States Space Force flag</p>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-lg font-bold text-blue-600">$109/year</span>
                            <span class="text-sm text-green-600 block">Save $41</span>
                        </div>
                        <div class="w-6 h-6 border-2 rounded border-gray-300"
                             :class="selectedFlags.some(f => f.id === 'spaceforce-flag') ? 'bg-blue-600 border-blue-600' : ''">
                            <svg x-show="selectedFlags.some(f => f.id === 'spaceforce-flag')" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
                      <!-- Selected Flags Summary -->
            <div x-show="selectedFlags.length > 0" x-cloak class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Selected Flags</h3>
                <div class="space-y-3">
                    <template x-for="flag in selectedFlags" :key="flag.id">
                        <div class="flex items-center justify-between bg-white rounded-lg p-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/>
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-900" x-text="flag.name"></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="font-semibold text-blue-600" x-text="'$' + flag.yearlyPrice + '/year'"></span>
                                <button @click="removeFlag(flag.id)" class="text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="border-t border-blue-200 mt-4 pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total Annual Cost:</span>
                        <span class="text-2xl font-bold text-blue-600" x-text="'$' + totalCost + '/year'"></span>
                    </div>
                    <p class="text-sm text-green-600 mt-1" x-text="'You save $' + totalSavings + ' vs one-time purchases'"></p>
                </div>
                <div class="mt-6">
                    <a href="#" @click.prevent="proceedToCheckout()" 
                       class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors text-center block">
                        Proceed to Checkout
                    </a>
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
                    <p class="text-gray-600">Our team installs your flag display and maintains it throughout the year. No work required on your part.</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-white">3</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Display on Holidays</h3>
                    <p class="text-gray-600">Your flags are displayed on all major patriotic holidays and properly maintained year-round.</p>
                </div>
            </div>

            <div class="text-center mt-12">
                <a href="<?php echo e(route('how-it-works')); ?>" class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-700">
                    Learn More About Our Service
                    <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">What Our Customers Say</h2>
                <p class="text-lg text-gray-600">Join hundreds of satisfied customers across our community</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-gray-50 rounded-xl p-6">
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
                    <p class="text-gray-600 mb-4">"Perfect service! Our neighborhood looks amazing on patriotic holidays. The team is professional and the flags are always perfectly maintained."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                            <span class="text-blue-600 font-semibold">MJ</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Mary Johnson</p>
                            <p class="text-sm text-gray-500">Homeowner</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-gray-50 rounded-xl p-6">
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
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                            <span class="text-blue-600 font-semibold">RT</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Robert Thompson</p>
                            <p class="text-sm text-gray-500">Navy Veteran</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-gray-50 rounded-xl p-6">
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
                <a href="<?php echo e(route('contact')); ?>" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                    Contact Us
                </a>
            </div>
        </div>
    </section>
</div>
<!-- Alpine.js Component -->
<script>
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
            
            // Store selected flags in localStorage or pass to next page
            localStorage.setItem('selectedFlags', JSON.stringify(this.selectedFlags));
            
            // Redirect to checkout or show modal
            window.location.href = '/checkout';
        }
    }
}
</script>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u768695191/domains/injoi-mydesign.com/public_html/flags/resources/views/home.blade.php ENDPATH**/ ?>