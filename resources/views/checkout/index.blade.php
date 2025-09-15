@extends('layouts.app')

@section('title', 'Checkout - Flag Service')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Progress Indicator -->
        <div class="mb-8">
            <nav aria-label="Progress">
                <ol role="list" class="flex items-center justify-center">
                    <li class="relative pr-8 sm:pr-20">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-red-200"></div>
                        </div>
                        <a href="#" class="relative w-8 h-8 flex items-center justify-center bg-red-600 rounded-full hover:bg-red-900">
                            <span class="text-white text-sm font-medium">1</span>
                        </a>
                        <span class="absolute top-10 left-1/2 transform -translate-x-1/2 text-sm font-medium text-gray-900">Review</span>
                    </li>
                    <li class="relative pr-8 sm:pr-20">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-gray-200"></div>
                        </div>
                        <div class="relative w-8 h-8 flex items-center justify-center bg-gray-300 rounded-full">
                            <span class="text-white text-sm font-medium">2</span>
                        </div>
                        <span class="absolute top-10 left-1/2 transform -translate-x-1/2 text-sm font-medium text-gray-500">Information</span>
                    </li>
                    <li class="relative">
                        <div class="relative w-8 h-8 flex items-center justify-center bg-gray-300 rounded-full">
                            <span class="text-white text-sm font-medium">3</span>
                        </div>
                        <span class="absolute top-10 left-1/2 transform -translate-x-1/2 text-sm font-medium text-gray-500">Payment</span>
                    </li>
                </ol>
            </nav>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column - Forms -->
            <div class="space-y-8">
                <form id="checkout-form" method="POST" action="{{ route('checkout.process') }}">
                    @csrf
                    
                    <!-- Product Review Section -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Service Selection</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg bg-red-50">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-16 h-12 bg-red-600 rounded flex items-center justify-center">
                                            <span class="text-white text-xs font-bold">FLAG</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Professional Flag Service</h3>
                                        <p class="text-sm text-gray-500">Weekly flag maintenance & replacement</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">$29.99/month</div>
                                    <div class="text-xs text-gray-500">Save $180/year</div>
                                </div>
                            </div>

                            <!-- Subscription Type -->
                            <div class="space-y-3">
                                <label class="text-sm font-medium text-gray-700">Billing Frequency</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center justify-between p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <div>
                                            <div class="font-medium text-sm">Monthly</div>
                                            <div class="text-xs text-gray-500">$29.99/month</div>
                                        </div>
                                        <input type="radio" name="billing_frequency" value="monthly" class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500" checked>
                                    </label>
                                    <label class="flex items-center justify-between p-3 border-2 border-green-300 bg-green-50 rounded-lg cursor-pointer">
                                        <div>
                                            <div class="font-medium text-sm">Annual</div>
                                            <div class="text-xs text-green-600">$299.99/year (Save $60!)</div>
                                        </div>
                                        <input type="radio" name="billing_frequency" value="annual" class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h2>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                    <input type="text" name="first_name" id="first_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                    <div class="text-red-600 text-xs mt-1 hidden" id="first_name_error"></div>
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                    <div class="text-red-600 text-xs mt-1 hidden" id="last_name_error"></div>
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="email" id="email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                <div class="text-red-600 text-xs mt-1 hidden" id="email_error"></div>
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="tel" name="phone" id="phone" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                <div class="text-red-600 text-xs mt-1 hidden" id="phone_error"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Service Address</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Street Address</label>
                                <input type="text" name="address" id="address" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="123 Main Street">
                                <div class="text-red-600 text-xs mt-1 hidden" id="address_error"></div>
                                <div class="text-green-600 text-xs mt-1 hidden" id="address_success">✓ Address verified - Service available!</div>
                                <div class="text-orange-600 text-xs mt-1 hidden" id="address_warning">⚠ Checking service availability...</div>
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
                                    </select>
                                </div>
                                <div>
                                    <label for="zip" class="block text-sm font-medium text-gray-700">ZIP Code</label>
                                    <input type="text" name="zip" id="zip" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                </div>
                            </div>

                            <div>
                                <label for="special_instructions" class="block text-sm font-medium text-gray-700">Special Instructions (Optional)</label>
                                <textarea name="special_instructions" id="special_instructions" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Flag pole location, access instructions, etc."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Account Options -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Account Options</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input id="create_account" name="create_account" type="checkbox" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <label for="create_account" class="ml-2 block text-sm text-gray-900">
                                    Create an account to manage your subscription online
                                </label>
                            </div>
                            
                            <div id="password_fields" class="space-y-3 hidden">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                    <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <input id="terms" name="terms" type="checkbox" required class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1">
                                <label for="terms" class="ml-2 block text-sm text-gray-900">
                                    I agree to the <a href="#" class="text-red-600 hover:text-red-500">Terms of Service</a> and <a href="#" class="text-red-600 hover:text-red-500">Privacy Policy</a>
                                </label>
                            </div>
                            
                            <div class="flex items-start">
                                <input id="marketing" name="marketing" type="checkbox" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1">
                                <label for="marketing" class="ml-2 block text-sm text-gray-900">
                                    I'd like to receive updates and special offers via email
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="lg:sticky lg:top-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Professional Flag Service</span>
                            <span class="text-sm font-medium text-gray-900" id="service-price">$29.99/month</span>
                        </div>
                        
                        <div class="flex justify-between text-green-600" id="savings-line">
                            <span class="text-sm">Annual Savings</span>
                            <span class="text-sm font-medium">-$60.00</span>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Setup Fee</span>
                                <span class="text-sm font-medium text-gray-900 line-through">$25.00</span>
                            </div>
                            <div class="flex justify-between text-green-600">
                                <span class="text-sm">Setup Fee (Waived)</span>
                                <span class="text-sm font-medium">FREE</span>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between">
                                <span class="text-base font-medium text-gray-900">Total</span>
                                <span class="text-base font-medium text-gray-900" id="total-price">$29.99/month</span>
                            </div>
                        </div>
                    </div>

                    <!-- Service Area Status -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg" id="service-area-status">
                        <div class="flex items-center">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-red-600 hidden" id="checking-spinner"></div>
                            <span class="text-sm text-gray-600" id="service-area-text">Enter address to check service availability</span>
                        </div>
                    </div>

                    <!-- Continue Button -->
                    <button type="submit" form="checkout-form" id="continue-button" disabled class="mt-6 w-full bg-gray-400 text-white py-3 px-4 rounded-md text-sm font-medium cursor-not-allowed">
                        Continue to Payment
                    </button>
                    
                    <div class="mt-4 flex items-center justify-center space-x-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Secure checkout powered by Stripe</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    const continueButton = document.getElementById('continue-button');
    const createAccountCheckbox = document.getElementById('create_account');
    const passwordFields = document.getElementById('password_fields');
    const addressInput = document.getElementById('address');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    const zipInput = document.getElementById('zip');
    const serviceAreaStatus = document.getElementById('service-area-status');
    const serviceAreaText = document.getElementById('service-area-text');
    const checkingSpinner = document.getElementById('checking-spinner');
    const billingFrequencyInputs = document.querySelectorAll('input[name="billing_frequency"]');
    const servicePriceEl = document.getElementById('service-price');
    const totalPriceEl = document.getElementById('total-price');
    const savingsLine = document.getElementById('savings-line');

    let serviceAreaValid = false;
    let addressCheckTimeout;

    // Toggle password fields
    createAccountCheckbox.addEventListener('change', function() {
        if (this.checked) {
            passwordFields.classList.remove('hidden');
            document.getElementById('password').required = true;
            document.getElementById('password_confirmation').required = true;
        } else {
            passwordFields.classList.add('hidden');
            document.getElementById('password').required = false;
            document.getElementById('password_confirmation').required = false;
        }
    });

    // Update pricing based on billing frequency
    billingFrequencyInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'annual') {
                servicePriceEl.textContent = '$299.99/year';
                totalPriceEl.textContent = '$299.99/year';
                savingsLine.classList.remove('hidden');
            } else {
                servicePriceEl.textContent = '$29.99/month';
                totalPriceEl.textContent = '$29.99/month';
                savingsLine.classList.add('hidden');
            }
        });
    });

    // Address validation with debouncing
    function checkServiceArea() {
        const address = addressInput.value.trim();
        const city = cityInput.value.trim();
        const state = stateInput.value;
        const zip = zipInput.value.trim();

        if (address && city && state && zip) {
            checkingSpinner.classList.remove('hidden');
            serviceAreaText.textContent = 'Checking service availability...';
            serviceAreaStatus.className = 'mt-6 p-4 bg-orange-50 border border-orange-200 rounded-lg';

            // Simulate API call
            setTimeout(() => {
                checkingSpinner.classList.add('hidden');
                
                // Mock service area validation
                const servicableZips = ['75001', '75002', '75003', '76001', '76002', '77001'];
                
                if (servicableZips.includes(zip)) {
                    serviceAreaValid = true;
                    serviceAreaText.textContent = '✓ Great! Service is available at this address.';
                    serviceAreaStatus.className = 'mt-6 p-4 bg-green-50 border border-green-200 rounded-lg';
                    document.getElementById('address_success').classList.remove('hidden');
                    document.getElementById('address_warning').classList.add('hidden');
                } else {
                    serviceAreaValid = false;
                    serviceAreaText.innerHTML = '⚠ Service not yet available in this area. <a href="/checkout/outside-service-area" class="text-red-600 hover:text-red-500 underline">Join our waitlist</a>';
                    serviceAreaStatus.className = 'mt-6 p-4 bg-red-50 border border-red-200 rounded-lg';
                    document.getElementById('address_success').classList.add('hidden');
                    document.getElementById('address_warning').classList.add('hidden');
                }
                
                updateContinueButton();
            }, 1500);
        } else {
            serviceAreaValid = false;
            serviceAreaText.textContent = 'Enter complete address to check service availability';
            serviceAreaStatus.className = 'mt-6 p-4 bg-gray-50 rounded-lg';
            updateContinueButton();
        }
    }

    // Debounced address checking
    [addressInput, cityInput, stateInput, zipInput].forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(addressCheckTimeout);
            addressCheckTimeout = setTimeout(checkServiceArea, 500);
        });
    });

    // Form validation
    function validateForm() {
        const requiredFields = form.querySelectorAll('input[required], select[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
            }
        });

        // Check terms agreement
        if (!document.getElementById('terms').checked) {
            isValid = false;
        }

        // Check password confirmation if account creation is selected
        if (createAccountCheckbox.checked) {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            if (password !== confirmation || password.length < 6) {
                isValid = false;
            }
        }

        return isValid && serviceAreaValid;
    }

    function updateContinueButton() {
        if (validateForm()) {
            continueButton.disabled = false;
            continueButton.className = 'mt-6 w-full bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-md text-sm font-medium transition duration-200';
            continueButton.textContent = 'Continue to Payment';
        } else {
            continueButton.disabled = true;
            continueButton.className = 'mt-6 w-full bg-gray-400 text-white py-3 px-4 rounded-md text-sm font-medium cursor-not-allowed';
            continueButton.textContent = 'Complete all fields to continue';
        }
    }

    // Real-time form validation
    form.addEventListener('input', updateContinueButton);
    form.addEventListener('change', updateContinueButton);

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            alert('Please complete all required fields and ensure your address is in our service area.');
            return;
        }

        // Show loading state
        continueButton.disabled = true;
        continueButton.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>';

        // Simulate form processing
        setTimeout(() => {
            // In real implementation, this would submit to your backend
            window.location.href = '/checkout/payment';
        }, 1000);
    });

    // Initial validation
    updateContinueButton();
});
</script>
@endsection