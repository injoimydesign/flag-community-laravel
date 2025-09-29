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

                    <!-- Hidden product and subscription type inputs -->
                    @foreach($products as $product)
                        <input type="hidden" name="products[]" value="{{ $product->id }}">
                    @endforeach
                    <input type="hidden" name="subscription_type" value="{{ $subscriptionType }}">

                    <!-- Product Review Section -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Service Selection</h2>

                        <div class="space-y-4">
                            @foreach($items as $item)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg bg-red-50">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-16 h-12 bg-red-600 rounded flex items-center justify-center">
                                            <span class="text-white text-xs font-bold">{{ strtoupper(substr($item['product']->flagType->name, 0, 4)) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">{{ $item['product']->display_name }}</h3>
                                        <p class="text-sm text-gray-500">
                                            {{ $subscriptionType === 'annual' ? 'Annual subscription service' : 'Holiday flag placement service' }}
                                        </p>
                                        @if($item['product']->flagSize)
                                            <p class="text-xs text-gray-400">Size: {{ $item['product']->flagSize->display_name }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">
                                        ${{ number_format($item['price'], 2) }}{{ $subscriptionType === 'annual' ? '/year' : '' }}
                                    </div>
                                    @if($subscriptionType === 'annual' && $savings > 0)
                                        <div class="text-xs text-green-600">Save ${{ number_format($savings, 2) }}/year</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach

                            @if($subscriptionType === 'annual')
                            <!-- Holiday Schedule Preview -->
                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-900 mb-2">Included Holidays ({{ $holidays->count() }} per year)</h4>
                                <div class="grid grid-cols-2 gap-2 text-xs text-blue-700">
                                    @foreach($holidays->take(6) as $holiday)
                                        <div class="flex items-center space-x-1">
                                            <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                                            <span>{{ $holiday->name }}</span>
                                        </div>
                                    @endforeach
                                    @if($holidays->count() > 6)
                                        <div class="flex items-center space-x-1">
                                            <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                                            <span>+{{ $holidays->count() - 6 }} more holidays</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h2>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                    <input type="text" name="first_name" id="first_name" required
                                           value="{{ old('first_name') }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm @error('first_name') border-red-300 @enderror">
                                    @error('first_name')
                                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" required
                                           value="{{ old('last_name') }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm @error('last_name') border-red-300 @enderror">
                                    @error('last_name')
                                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="email" id="email" required
                                       value="{{ old('email') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm @error('email') border-red-300 @enderror">
                                @error('email')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="tel" name="phone" id="phone"
                                       value="{{ old('phone') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm @error('phone') border-red-300 @enderror">
                                @error('phone')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Service Address</h2>

                        <div class="space-y-4">
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Street Address</label>
                                <input type="text" name="address" id="address" required
                                       value="{{ old('address') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm @error('address') border-red-300 @enderror"
                                       placeholder="123 Main Street">
                                @error('address')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                                <div class="text-green-600 text-xs mt-1 hidden" id="address_success">✓ Address verified - Service available!</div>
                                <div class="text-orange-600 text-xs mt-1 hidden" id="address_warning">⚠ Checking service availability...</div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                    <input type="text" name="city" id="city" required
                                           value="{{ old('city') }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm @error('city') border-red-300 @enderror">
                                    @error('city')
                                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                                    <select name="state" id="state" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm @error('state') border-red-300 @enderror">
                                        <option value="">Select State</option>
                                        <option value="TX" {{ old('state') === 'TX' ? 'selected' : '' }}>Texas</option>
                                        <option value="OK" {{ old('state') === 'OK' ? 'selected' : '' }}>Oklahoma</option>
                                        <option value="AR" {{ old('state') === 'AR' ? 'selected' : '' }}>Arkansas</option>
                                        <option value="LA" {{ old('state') === 'LA' ? 'selected' : '' }}>Louisiana</option>
                                        <option value="NM" {{ old('state') === 'NM' ? 'selected' : '' }}>New Mexico</option>
                                    </select>
                                    @error('state')
                                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label for="zip_code" class="block text-sm font-medium text-gray-700">ZIP Code</label>
                                    <input type="text" name="zip_code" id="zip_code" required
                                           value="{{ old('zip_code') }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm @error('zip_code') border-red-300 @enderror">
                                    @error('zip_code')
                                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="special_instructions" class="block text-sm font-medium text-gray-700">Special Instructions (Optional)</label>
                                <textarea name="special_instructions" id="special_instructions" rows="3"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm @error('special_instructions') border-red-300 @enderror"
                                          placeholder="Flag pole location, access instructions, etc.">{{ old('special_instructions') }}</textarea>
                                @error('special_instructions')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Account Options -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Account Options</h2>

                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input id="create_account" name="create_account" type="checkbox" value="1"
                                       {{ old('create_account') ? 'checked' : '' }}
                                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <label for="create_account" class="ml-2 block text-sm text-gray-900">
                                    Create an account to manage your subscription online
                                </label>
                            </div>

                            <div id="password_fields" class="space-y-3 {{ old('create_account') ? '' : 'hidden' }}">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                    <input type="password" name="password" id="password"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm @error('password') border-red-300 @enderror">
                                    @error('password')
                                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <input id="terms_accepted" name="terms_accepted" type="checkbox" value="1" required
                                       {{ old('terms_accepted') ? 'checked' : '' }}
                                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1">
                                <label for="terms_accepted" class="ml-2 block text-sm text-gray-900">
                                    I agree to the <a href="#" class="text-red-600 hover:text-red-500">Terms of Service</a> and <a href="#" class="text-red-600 hover:text-red-500">Privacy Policy</a>
                                </label>
                            </div>
                            @error('terms_accepted')
                                <div class="text-red-600 text-xs">{{ $message }}</div>
                            @enderror

                            <div class="flex items-start">
                                <input id="marketing_consent" name="marketing_consent" type="checkbox" value="1"
                                       {{ old('marketing_consent') ? 'checked' : '' }}
                                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1">
                                <label for="marketing_consent" class="ml-2 block text-sm text-gray-900">
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
                        @foreach($items as $item)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">{{ $item['product']->display_name }}</span>
                            <span class="text-sm font-medium text-gray-900">
                                ${{ number_format($item['price'], 2) }}{{ $subscriptionType === 'annual' ? '/year' : '' }}
                            </span>
                        </div>
                        @endforeach

                        @if($subscriptionType === 'annual' && $savings > 0)
                        <div class="flex justify-between text-green-600">
                            <span class="text-sm">Annual Savings</span>
                            <span class="text-sm font-medium">-${{ number_format($savings, 2) }}</span>
                        </div>
                        @endif

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
                                <span class="text-base font-medium text-gray-900">
                                    ${{ number_format($total, 2) }}{{ $subscriptionType === 'annual' ? '/year' : '' }}
                                </span>
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
                    <button type="submit" form="checkout-form" id="continue-button" disabled
                            class="mt-6 w-full bg-gray-400 text-white py-3 px-4 rounded-md text-sm font-medium cursor-not-allowed">
                        Complete all fields to continue
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
    const zipInput = document.getElementById('zip_code');
    const serviceAreaStatus = document.getElementById('service-area-status');
    const serviceAreaText = document.getElementById('service-area-text');
    const checkingSpinner = document.getElementById('checking-spinner');

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

            // Make AJAX call to check service area
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
                checkingSpinner.classList.add('hidden');

                if (data.available) {
                    serviceAreaValid = true;
                    serviceAreaText.textContent = '✓ Great! Service is available at this address.';
                    serviceAreaStatus.className = 'mt-6 p-4 bg-green-50 border border-green-200 rounded-lg';
                    document.getElementById('address_success').classList.remove('hidden');
                    document.getElementById('address_warning').classList.add('hidden');
                } else {
                    //serviceAreaValid = false;
                    
                    serviceAreaStatus.className = 'mt-6 p-4 bg-red-50 border border-red-200 rounded-lg';
                    document.getElementById('address_success').classList.add('hidden');
                    document.getElementById('address_warning').classList.add('hidden');
                }

                updateContinueButton();
            })
            .catch(error => {
                console.error('Service area check failed:', error);
                checkingSpinner.classList.add('hidden');
                serviceAreaValid = false;
                serviceAreaText.textContent = 'Unable to verify service availability. Please try again.';
                serviceAreaStatus.className = 'mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg';
                updateContinueButton();
            });
        } else {
            serviceAreaValid = false;
            serviceAreaText.textContent = 'Enter complete address to check service availability';
            serviceAreaStatus.className = 'mt-6 p-4 bg-gray-50 rounded-lg';
            updateContinueButton();
        }
    }

    // Debounced address checking
    /*
    [addressInput, cityInput, stateInput, zipInput].forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(addressCheckTimeout);
            addressCheckTimeout = setTimeout(checkServiceArea, 500);
        });
    });*/

    // Form validation
    function validateForm() {
        const requiredFields = form.querySelectorAll('input[required], select[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
            }
        });

        // Check password confirmation if account creation is selected
        if (createAccountCheckbox.checked) {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            if (password !== confirmation || password.length < 8) {
                isValid = false;
            }
        }

        //return isValid && serviceAreaValid;
        return isValid;
    }

    function updateContinueButton() {
        if (validateForm()) {
            continueButton.disabled = false;
            continueButton.className = 'mt-6 w-full bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-md text-sm font-medium transition duration-200';
            continueButton.textContent = 'Continue to Stripe Checkout';
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
        if (!validateForm()) {
            e.preventDefault();
            alert('Please complete all required fields and ensure your address is in our service area.');
            return;
        }

        // Show loading state
        continueButton.disabled = true;
        continueButton.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>';

        // Store checkout data in session for potential retry
        const checkoutData = {
            first_name: document.getElementById('first_name').value,
            last_name: document.getElementById('last_name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            address: document.getElementById('address').value,
            city: document.getElementById('city').value,
            state: document.getElementById('state').value,
            zip_code: document.getElementById('zip_code').value,
            subscription_type: '{{ $subscriptionType }}',
            products: @json($products->pluck('id')),
            create_account: document.getElementById('create_account').checked
        };

        sessionStorage.setItem('checkoutData', JSON.stringify(checkoutData));

        // Let the form submit naturally to the process route
        // The controller will handle redirecting to Stripe checkout
    });

    // Initial validation
    updateContinueButton();

    // Check service area on page load if address fields are populated
    if (addressInput.value && cityInput.value && stateInput.value && zipInput.value) {
        setTimeout(checkServiceArea, 500);
    }
});
</script>
@endsection
