@extends('layouts.app')

@section('title', 'Service Area Expansion - Flag Service')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Coming to Your Area Soon!</h1>
            <p class="mt-2 text-lg text-gray-600">We're expanding our professional flag service</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column - Information -->
            <div class="space-y-6">
                <!-- Current Service Areas -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Current Service Areas</h2>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-3 h-3 bg-green-500 rounded-full mt-2"></div>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Dallas-Fort Worth, TX</h3>
                                <p class="text-sm text-gray-600">Including Plano, Irving, Arlington, and surrounding areas</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">75001-75099</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">76001-76099</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-3 h-3 bg-green-500 rounded-full mt-2"></div>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Houston, TX</h3>
                                <p class="text-sm text-gray-600">Select neighborhoods in greater Houston area</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">77001-77099</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expansion Timeline -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Expansion Timeline</h2>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 text-sm font-medium">Q2</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Spring 2025</h3>
                                <p class="text-sm text-gray-600">Austin, San Antonio, Oklahoma City</p>
                                <div class="mt-1 text-xs text-blue-600">Planning Phase</div>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                    <span class="text-orange-600 text-sm font-medium">Q3</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Summer 2025</h3>
                                <p class="text-sm text-gray-600">Little Rock, Shreveport, Tyler</p>
                                <div class="mt-1 text-xs text-orange-600">Team Recruitment</div>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 text-sm font-medium">Q4</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Fall 2025</h3>
                                <p class="text-sm text-gray-600">Additional Texas markets</p>
                                <div class="mt-1 text-xs text-green-600">Launch Ready</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Why Join the Waitlist -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-red-900 mb-4">Why Join Our Waitlist?</h3>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-red-700">Be the first to know when we launch in your area</span>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-red-700">Early bird pricing - save 50% for the first 3 months</span>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-red-700">Priority scheduling for installation and service</span>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-red-700">Free setup and installation (normally $25)</span>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm text-red-700">Help us prioritize which areas to expand to first</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Waitlist Form -->
            <div class="space-y-6">
                <!-- Waitlist Signup -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Join the Waitlist</h2>
                    <form id="waitlist-form" class="space-y-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" name="first_name" id="first_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" name="last_name" id="last_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" id="email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number (Optional)</label>
                            <input type="tel" name="phone" id="phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Your Address</label>
                            <input type="text" name="address" id="address" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="123 Main Street">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                <input type="text" name="city" id="city" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                                <select name="state" id="state" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                    <option value="">Select State</option>
                                    <option value="TX">Texas</option>
                                    <option value="OK">Oklahoma</option>
                                    <option value="AR">Arkansas</option>
                                    <option value="LA">Louisiana</option>
                                    <option value="NM">New Mexico</option>
                                    <option value="MS">Mississippi</option>
                                    <option value="TN">Tennessee</option>
                                </select>
                            </div>
                            <div>
                                <label for="zip" class="block text-sm font-medium text-gray-700">ZIP Code</label>
                                <input type="text" name="zip" id="zip" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="interest_level" class="block text-sm font-medium text-gray-700">How soon would you like service?</label>
                            <select name="interest_level" id="interest_level" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                <option value="">Select timeframe</option>
                                <option value="immediate">Immediately</option>
                                <option value="1-3months">Within 1-3 months</option>
                                <option value="3-6months">Within 3-6 months</option>
                                <option value="6-12months">Within 6-12 months</option>
                                <option value="1year+">More than a year</option>
                            </select>
                        </div>

                        <div>
                            <label for="additional_info" class="block text-sm font-medium text-gray-700">Additional Information (Optional)</label>
                            <textarea name="additional_info" id="additional_info" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Tell us about your flag needs, special requirements, etc."></textarea>
                        </div>

                        <div class="flex items-start">
                            <input id="marketing_consent" name="marketing_consent" type="checkbox" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1">
                            <label for="marketing_consent" class="ml-2 block text-sm text-gray-900">
                                I'd like to receive updates about service expansion and special offers
                            </label>
                        </div>

                        <button type="submit" id="submit-button" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-md text-sm font-medium transition duration-200">
                            Join the Waitlist
                        </button>
                    </form>
                </div>

                <!-- Current Waitlist Stats -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-blue-900 mb-4">Waitlist Progress</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm font-medium text-blue-900 mb-1">
                                <span>Total signups</span>
                                <span id="total-signups">1,247</span>
                            </div>
                            <div class="w-full bg-blue-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: 62%"></div>
                            </div>
                            <div class="text-xs text-blue-600 mt-1">Goal: 2,000 signups for expansion</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-blue-900">89</div>
                                <div class="text-xs text-blue-600">This week</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-blue-900">342</div>
                                <div class="text-xs text-blue-600">This month</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alternative Contact -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Have Questions?</h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <div>
                                <div class="font-medium text-gray-900">(555) 123-4567</div>
                                <div class="text-sm text-gray-500">Mon-Fri 8AM-6PM</div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <div class="font-medium text-gray-900">expansion@flagservice.com</div>
                                <div class="text-sm text-gray-500">Questions about expansion</div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <a href="{{ route('home') }}" class="w-full bg-gray-600 hover:bg-gray-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium transition duration-200 block">
                                Return to Homepage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to the Waitlist!</h3>
                <p class="text-sm text-gray-600 mb-6">You're now #<span id="waitlist-position">1,248</span> on our waitlist. We'll notify you as soon as service is available in your area.</p>
                <div class="space-y-3">
                    <p class="text-xs text-gray-500">✓ Confirmation email sent</p>
                    <p class="text-xs text-gray-500">✓ Early bird pricing reserved</p>
                    <p class="text-xs text-gray-500">✓ Priority scheduling guaranteed</p>
                </div>
                <button onclick="closeSuccessModal()" class="mt-6 w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                    Great! Got it
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('waitlist-form');
    const submitButton = document.getElementById('submit-button');
    const totalSignupsEl = document.getElementById('total-signups');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>';

        // Simulate form submission
        setTimeout(() => {
            // Update waitlist count
            const currentCount = parseInt(totalSignupsEl.textContent);
            const newCount = currentCount + 1;
            totalSignupsEl.textContent = newCount.toLocaleString();
            document.getElementById('waitlist-position').textContent = newCount.toLocaleString();

            // Show success modal
            document.getElementById('success-modal').classList.remove('hidden');

            // Reset form
            form.reset();
            submitButton.disabled = false;
            submitButton.textContent = 'Join the Waitlist';

            // Track signup event
            if (typeof gtag !== 'undefined') {
                gtag('event', 'waitlist_signup', {
                    event_category: 'engagement',
                    event_label: 'outside_service_area'
                });
            }
        }, 1500);
    });

    // Auto-update waitlist stats
    function updateStats() {
        const currentCount = parseInt(totalSignupsEl.textContent.replace(',', ''));
        const increment = Math.floor(Math.random() * 3) + 1;
        if (currentCount < 2000) {
            totalSignupsEl.textContent = (currentCount + increment).toLocaleString();
        }
    }

    // Update stats every 30 seconds
    setInterval(updateStats, 30000);
});

function closeSuccessModal() {
    document.getElementById('success-modal').classList.add('hidden');
}

// Auto-populate from previous checkout attempt if available
if (sessionStorage.getItem('checkoutData')) {
    try {
        const checkoutData = JSON.parse(sessionStorage.getItem('checkoutData'));
        document.getElementById('first_name').value = checkoutData.first_name || '';
        document.getElementById('last_name').value = checkoutData.last_name || '';
        document.getElementById('email').value = checkoutData.email || '';
        document.getElementById('phone').value = checkoutData.phone || '';
        document.getElementById('address').value = checkoutData.address || '';
        document.getElementById('city').value = checkoutData.city || '';
        document.getElementById('state').value = checkoutData.state || '';
        document.getElementById('zip').value = checkoutData.zip || '';
    } catch (e) {
        console.log('Could not parse saved checkout data');
    }
}
</script>
@endsection
