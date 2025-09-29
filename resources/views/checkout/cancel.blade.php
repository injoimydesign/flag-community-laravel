@extends('layouts.app')

@section('title', 'Payment Cancelled - Flag Service')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Cancel Header -->
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Payment Cancelled</h1>
            <p class="mt-2 text-lg text-gray-600">Your payment was not processed</p>
        </div>

        <div class="space-y-6">
            <!-- Cancellation Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">What Happened?</h2>
                <div class="space-y-3 text-sm text-gray-600">
                    <p>Your payment was cancelled and no charges were made to your payment method. This can happen for several reasons:</p>
                    <ul class="list-disc list-inside space-y-1 ml-4">
                        <li>You chose to cancel the payment during checkout</li>
                        <li>You closed the payment window before completing the transaction</li>
                        <li>There was a technical issue with the payment processor</li>
                        <li>Your payment method was declined</li>
                    </ul>
                    <p class="font-medium text-gray-900">Don't worry - your information is saved and you can try again anytime!</p>
                </div>
            </div>

            <!-- Saved Information -->
            @if(session()->has('checkout_data') || session()->has('pending_subscription_id'))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-medium text-blue-900 mb-4">Your Information is Saved</h3>
                <p class="text-sm text-blue-700 mb-4">We've saved your information so you can complete your order quickly:</p>

                @if(session('checkout_data'))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @if(session('checkout_data.first_name'))
                    <div>
                        <span class="font-medium text-blue-900">Customer:</span>
                        <span class="text-blue-700">{{ session('checkout_data.first_name') }} {{ session('checkout_data.last_name') }}</span>
                    </div>
                    @endif

                    @if(session('checkout_data.email'))
                    <div>
                        <span class="font-medium text-blue-900">Email:</span>
                        <span class="text-blue-700">{{ session('checkout_data.email') }}</span>
                    </div>
                    @endif

                    @if(session('checkout_data.city') && session('checkout_data.state'))
                    <div>
                        <span class="font-medium text-blue-900">Address:</span>
                        <span class="text-blue-700">{{ session('checkout_data.city') }}, {{ session('checkout_data.state') }}</span>
                    </div>
                    @endif

                    @if(session('checkout_data.subscription_type'))
                    <div>
                        <span class="font-medium text-blue-900">Service:</span>
                        <span class="text-blue-700">{{ ucfirst(session('checkout_data.subscription_type')) }} Flag Service</span>
                    </div>
                    @endif
                </div>
                @else
                <div class="text-sm text-blue-700">
                    <p>✓ Service selection and customer information saved</p>
                    <p>✓ Address verified for service availability</p>
                </div>
                @endif
            </div>
            @endif

            <!-- Action Options -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">What Would You Like to Do?</h3>
                <div class="space-y-4">
                    <!-- Try Payment Again -->
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 transition duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Try Payment Again</h4>
                                    <p class="text-sm text-gray-500">Continue with your saved information</p>
                                </div>
                            </div>
                            <a href="{{ route('checkout.index', request()->query()) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                                Continue Checkout
                            </a>
                        </div>
                    </div>

                    <!-- Select Different Service -->
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Select Different Service</h4>
                                    <p class="text-sm text-gray-500">Change subscription type or flag options</p>
                                </div>
                            </div>
                            <a href="{{ route('home') }}#pricing" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                                View Options
                            </a>
                        </div>
                    </div>

                    <!-- Contact Support -->
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-green-300 transition duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Get Help</h4>
                                    <p class="text-sm text-gray-500">Speak with our support team</p>
                                </div>
                            </div>
                            <a href="#contact" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                                Contact Us
                            </a>
                        </div>
                    </div>

                    <!-- Save for Later -->
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Save for Later</h4>
                                    <p class="text-sm text-gray-500">We'll email you a reminder in 24 hours</p>
                                </div>
                            </div>
                            <button onclick="saveForLater()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                                Remind Me
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div id="contact" class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Need Assistance?</h3>
                <p class="text-sm text-gray-600 mb-6">Our customer support team is here to help with any questions or payment issues.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Phone Support -->
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-900">Phone Support</h4>
                        <p class="text-sm text-gray-600 mt-1">(555) 123-4567</p>
                        <p class="text-xs text-gray-500 mt-1">Mon-Fri 8AM-6PM CST</p>
                        <a href="tel:5551234567" class="inline-block mt-2 text-blue-600 hover:text-blue-500 text-sm font-medium">
                            Call Now
                        </a>
                    </div>

                    <!-- Email Support -->
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-900">Email Support</h4>
                        <p class="text-sm text-gray-600 mt-1">support@flagservice.com</p>
                        <p class="text-xs text-gray-500 mt-1">24/7 response within 2 hours</p>
                        <a href="mailto:support@flagservice.com?subject=Payment%20Issue%20-%20Order%20Cancelled" class="inline-block mt-2 text-green-600 hover:text-green-500 text-sm font-medium">
                            Send Email
                        </a>
                    </div>

                    <!-- Live Chat -->
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full mb-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-900">Live Chat</h4>
                        <p class="text-sm text-gray-600 mt-1">Real-time assistance</p>
                        <p class="text-xs text-gray-500 mt-1">Mon-Fri 8AM-6PM CST</p>
                        <button onclick="openChat()" class="inline-block mt-2 text-purple-600 hover:text-purple-500 text-sm font-medium">
                            Start Chat
                        </button>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="bg-gray-100 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Common Payment Questions</h3>
                <div class="space-y-4">
                    <details class="group">
                        <summary class="flex justify-between items-center cursor-pointer text-sm font-medium text-gray-900 p-2 hover:bg-white rounded">
                            Why was my payment declined?
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </summary>
                        <div class="mt-2 p-2 text-sm text-gray-600">
                            Payment declines can happen due to insufficient funds, expired cards, incorrect billing information, or bank security measures. Try using a different payment method or contact your bank.
                        </div>
                    </details>

                    <details class="group">
                        <summary class="flex justify-between items-center cursor-pointer text-sm font-medium text-gray-900 p-2 hover:bg-white rounded">
                            Is my information secure?
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </summary>
                        <div class="mt-2 p-2 text-sm text-gray-600">
                            Yes! We use industry-standard SSL encryption and Stripe's secure payment processing. Your payment information is never stored on our servers.
                        </div>
                    </details>

                    <details class="group">
                        <summary class="flex justify-between items-center cursor-pointer text-sm font-medium text-gray-900 p-2 hover:bg-white rounded">
                            Can I pay with a different method?
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </summary>
                        <div class="mt-2 p-2 text-sm text-gray-600">
                            We accept all major credit cards, debit cards, and digital wallets like Apple Pay and Google Pay. You can also use bank transfers for annual subscriptions.
                        </div>
                    </details>

                    <details class="group">
                        <summary class="flex justify-between items-center cursor-pointer text-sm font-medium text-gray-900 p-2 hover:bg-white rounded">
                            Will I be charged a cancellation fee?
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </summary>
                        <div class="mt-2 p-2 text-sm text-gray-600">
                            No charges were processed since your payment was cancelled. There are no cancellation fees, and you can try again anytime without penalty.
                        </div>
                    </details>
                </div>
            </div>

            <!-- Return to Home -->
            <div class="text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center text-red-600 hover:text-red-500 font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Return to Homepage
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Save for Later Modal -->
<div id="save-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Save for Later</h3>
            <p class="text-sm text-gray-600 mb-4">We'll send you a friendly reminder email in 24 hours with a link to complete your order.</p>
            <form id="save-form" class="space-y-4">
                @csrf
                <div>
                    <label for="reminder-email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" id="reminder-email" name="email" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                           value="{{ session('checkout_data.email', '') }}">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                        Set Reminder
                    </button>
                    <button type="button" onclick="closeSaveModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-md text-sm font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Message -->
<div id="success-message" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg hidden z-50">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Reminder set! We'll email you in 24 hours.
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Track cancellation event
    if (typeof gtag !== 'undefined') {
        gtag('event', 'checkout_cancelled', {
            event_category: 'ecommerce',
            event_label: 'payment_cancelled',
            value: 0
        });
    }

    // Save for later form submission
    document.getElementById('save-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const email = document.getElementById('reminder-email').value;
        const button = this.querySelector('button[type="submit"]');

        // Show loading state
        button.disabled = true;
        button.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>';

        // Send reminder request to backend
        fetch('{{ route("checkout.save-reminder") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                email: email,
                checkout_data: @json(session('checkout_data', [])),
                products: @json(request()->query('products', [])),
                subscription_type: '{{ request()->query("subscription_type", "monthly") }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeSaveModal();
                showSuccessMessage();

                // Track reminder set event
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'reminder_set', {
                        event_category: 'engagement',
                        event_label: 'checkout_reminder'
                    });
                }
            } else {
                alert('Sorry, we couldn\'t set your reminder. Please try again or contact support.');
            }
        })
        .catch(error => {
            console.error('Error setting reminder:', error);
            alert('Sorry, we couldn\'t set your reminder. Please try again or contact support.');
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = 'Set Reminder';
        });
    });
});

function saveForLater() {
    document.getElementById('save-modal').classList.remove('hidden');
}

function closeSaveModal() {
    document.getElementById('save-modal').classList.add('hidden');
}

function showSuccessMessage() {
    const message = document.getElementById('success-message');
    message.classList.remove('hidden');

    setTimeout(() => {
        message.classList.add('hidden');
    }, 5000);
}

function openChat() {
    // Integration point for live chat widget (Intercom, Zendesk Chat, etc.)
    if (window.Intercom) {
        window.Intercom('show');
    } else if (window.$crisp) {
        window.$crisp.push(['do', 'chat:open']);
    } else {
        // Fallback to contact form or phone
        window.location.href = 'tel:5551234567';
    }
}

// Auto-populate email from session if available
@if(session('checkout_data.email'))
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('reminder-email');
    if (emailInput && !emailInput.value) {
        emailInput.value = '{{ session("checkout_data.email") }}';
    }
});
@endif
</script>
@endsection
