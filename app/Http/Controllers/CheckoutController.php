<?php
// app/Http/Controllers/CheckoutController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FlagProduct;
use App\Models\Holiday;
use App\Models\ServiceArea;
use App\Models\PotentialCustomer;
use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Services\StripeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Show checkout page.
     */
    public function index(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*' => 'exists:flag_products,id',
            'subscription_type' => 'required|in:onetime,annual',
        ]);

        $products = FlagProduct::with(['flagType', 'flagSize'])
            ->whereIn('id', $request->products)
            ->active()
            ->get();

        if ($products->isEmpty()) {
            return redirect()->route('home')->with('error', 'No valid products selected.');
        }

        $subscriptionType = $request->subscription_type;
        $holidays = Holiday::active()->ordered()->get();

        // Calculate totals
        $total = 0;
        $items = [];

        foreach ($products as $product) {
            $price = $subscriptionType === 'annual' 
                ? $product->annual_subscription_price 
                : $product->one_time_price;
            
            $total += $price;
            
            $items[] = [
                'product' => $product,
                'price' => $price,
            ];
        }

        // Calculate savings for annual subscription
        $savings = 0;
        if ($subscriptionType === 'annual') {
            $holidayCount = $holidays->count();
            $onetimeTotal = $products->sum('one_time_price') * $holidayCount;
            $savings = max(0, $onetimeTotal - $total);
        }

        return view('checkout.index', compact(
            'products', 
            'subscriptionType', 
            'holidays', 
            'items', 
            'total', 
            'savings'
        ));
    }

    /**
     * Process checkout form submission.
     */
    public function process(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*' => 'exists:flag_products,id',
            'subscription_type' => 'required|in:onetime,annual',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'special_instructions' => 'nullable|string|max:500',
            'create_account' => 'boolean',
            'password' => 'required_if:create_account,1|nullable|string|min:8|confirmed',
            'terms_accepted' => 'required|accepted',
        ]);

        // Get products and calculate pricing
        $products = FlagProduct::whereIn('id', $request->products)->active()->get();
        $subscriptionType = $request->subscription_type;

        $total = 0;
        foreach ($products as $product) {
            $price = $subscriptionType === 'annual' 
                ? $product->annual_subscription_price 
                : $product->one_time_price;
            $total += $price;
        }

        // Check if address is in service area
        $coordinates = $this->geocodeAddress($request);
        $inServiceArea = ServiceArea::isAddressServed(
            $coordinates['lat'] ?? null, 
            $coordinates['lng'] ?? null, 
            $request->zip_code
        );

        if (!$inServiceArea) {
            // Save as potential customer
            $potentialCustomer = PotentialCustomer::createFromCheckoutAttempt([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'latitude' => $coordinates['lat'] ?? null,
                'longitude' => $coordinates['lng'] ?? null,
            ], $request->products);

            return view('checkout.outside-service-area', compact('potentialCustomer'));
        }

        // Create or get user
        $user = $this->createOrGetUser($request, $coordinates);

        // Create Stripe customer if needed
        if (!$user->stripe_customer_id) {
            $stripeCustomer = $this->stripe->customers->create([
                'email' => $user->email,
                'name' => $user->full_name,
                'phone' => $user->phone,
                'address' => [
                    'line1' => $user->address,
                    'city' => $user->city,
                    'state' => $user->state,
                    'postal_code' => $user->zip_code,
                    'country' => 'US',
                ],
            ]);
            
            $user->stripe_customer_id = $stripeCustomer->id;
            $user->save();
        }

        // Create subscription record
        $subscription = $this->createSubscription($user, $request, $products, $total);

        // Create Stripe checkout session
        $checkoutSession = $this->createStripeCheckoutSession($user, $subscription, $products, $subscriptionType);

        // Store subscription ID in session for completion
        session(['pending_subscription_id' => $subscription->id]);

        return redirect($checkoutSession->url);
    }

    /**
     * Handle successful payment.
     */
    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        $subscriptionId = session('pending_subscription_id');

        if (!$sessionId || !$subscriptionId) {
            return redirect()->route('home')->with('error', 'Invalid checkout session.');
        }

        try {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId);
            $subscription = Subscription::find($subscriptionId);

            if (!$subscription || $session->payment_status !== 'paid') {
                return redirect()->route('home')->with('error', 'Payment was not completed.');
            }

            // Update subscription with Stripe data
            $subscription->stripe_subscription_id = $session->subscription ?? $session->payment_intent;
            $subscription->status = 'active';
            $subscription->save();

            // Generate flag placements
            $subscription->generateFlagPlacements();

            // Log in user if not already logged in
            if (!Auth::check()) {
                Auth::login($subscription->user);
            }

            // Clear session
            session()->forget('pending_subscription_id');

            return view('checkout.success', compact('subscription'));

        } catch (\Exception $e) {
            \Log::error('Checkout success error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'There was an error processing your payment.');
        }
    }

    /**
     * Handle cancelled payment.
     */
    public function cancel(Request $request)
    {
        $subscriptionId = session('pending_subscription_id');

        if ($subscriptionId) {
            $subscription = Subscription::find($subscriptionId);
            if ($subscription && $subscription->status === 'pending') {
                $subscription->status = 'canceled';
                $subscription->save();
            }
        }

        session()->forget('pending_subscription_id');

        return view('checkout.cancel');
    }

    /**
     * Create or get existing user.
     */
    private function createOrGetUser(Request $request, array $coordinates)
    {
        $user = null;

        // Check if user is already logged in
        if (Auth::check()) {
            $user = Auth::user();
            // Update address information
            $user->update([
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'latitude' => $coordinates['lat'] ?? null,
                'longitude' => $coordinates['lng'] ?? null,
                'in_service_area' => true,
            ]);
        } else {
            // Check if user exists with this email
            $user = User::where('email', $request->email)->first();

            if ($user) {
                // Update existing user
                $user->update([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip_code' => $request->zip_code,
                    'latitude' => $coordinates['lat'] ?? null,
                    'longitude' => $coordinates['lng'] ?? null,
                    'in_service_area' => true,
                ]);
            } else {
                // Create new user
                $userData = [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip_code' => $request->zip_code,
                    'latitude' => $coordinates['lat'] ?? null,
                    'longitude' => $coordinates['lng'] ?? null,
                    'in_service_area' => true,
                    'role' => 'customer',
                ];

                if ($request->create_account && $request->password) {
                    $userData['password'] = Hash::make($request->password);
                } else {
                    // Generate random password for checkout-only users
                    $userData['password'] = Hash::make(str_random(16));
                }

                $user = User::create($userData);
            }
        }

        return $user;
    }

    /**
     * Create subscription record.
     */
    private function createSubscription($user, Request $request, $products, $total)
    {
        $startDate = Carbon::now();
        $endDate = $request->subscription_type === 'annual' 
            ? $startDate->copy()->addYear() 
            : $startDate->copy()->addMonths(2); // Allow time for all holidays in one-time purchases

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'type' => $request->subscription_type,
            'status' => 'pending',
            'total_amount' => $total,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'selected_holidays' => Holiday::active()->pluck('id')->toArray(), // All holidays
            'special_instructions' => $request->special_instructions,
        ]);

        // Create subscription items
        foreach ($products as $product) {
            $price = $request->subscription_type === 'annual' 
                ? $product->annual_subscription_price 
                : $product->one_time_price;

            SubscriptionItem::create([
                'subscription_id' => $subscription->id,
                'flag_product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => $price,
                'total_price' => $price,
            ]);
        }

        return $subscription;
    }

    /**
     * Create Stripe checkout session.
     */
    private function createStripeCheckoutSession($user, $subscription, $products, $subscriptionType)
    {
        $lineItems = [];

        foreach ($products as $product) {
            $price = $subscriptionType === 'annual' 
                ? $product->annual_subscription_price 
                : $product->one_time_price;

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->display_name,
                        'description' => $subscriptionType === 'annual' 
                            ? 'Annual flag subscription service' 
                            : 'One-time flag placement service',
                    ],
                    'unit_amount' => $price * 100, // Stripe uses cents
                ],
                'quantity' => 1,
            ];
        }

        return $this->stripe->checkout->sessions->create([
            'customer' => $user->stripe_customer_id,
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel'),
            'billing_address_collection' => 'required',
            'shipping_address_collection' => [
                'allowed_countries' => ['US'],
            ],
            'metadata' => [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
            ],
        ]);
    }

    /**
     * Geocode address using Google Maps API (placeholder).
     */
    private function geocodeAddress(Request $request)
    {
        // In a real application, you'd use Google Maps Geocoding API
        // For now, return dummy coordinates based on zip code
        $zipCoordinates = [
            '77801' => ['lat' => 30.6744, 'lng' => -96.3698],
            '77802' => ['lat' => 30.6280, 'lng' => -96.3344],
            // Add more as needed
        ];

        return $zipCoordinates[$request->zip_code] ?? ['lat' => null, 'lng' => null];
    }
}