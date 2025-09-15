<?php
// app/Services/StripeService.php

namespace App\Services;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use App\Models\User;
use App\Models\Subscription;
use App\Models\FlagProduct;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
    }

    /**
     * Create or retrieve Stripe customer for user.
     */
    public function createOrGetCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            try {
                // Verify customer exists
                $this->stripe->customers->retrieve($user->stripe_customer_id);
                return $user->stripe_customer_id;
            } catch (ApiErrorException $e) {
                // Customer doesn't exist, create new one
                Log::warning("Stripe customer {$user->stripe_customer_id} not found, creating new one");
            }
        }

        try {
            $customer = $this->stripe->customers->create([
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
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            $user->update(['stripe_customer_id' => $customer->id]);
            return $customer->id;

        } catch (ApiErrorException $e) {
            Log::error('Failed to create Stripe customer: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getError(),
            ]);
            throw $e;
        }
    }

    /**
     * Create Stripe products and prices for flag products.
     */
    public function createOrUpdateProduct(FlagProduct $flagProduct): array
    {
        try {
            // Create or update product
            $productData = [
                'name' => $flagProduct->display_name,
                'description' => "Professional flag display service - {$flagProduct->flagType->name} ({$flagProduct->flagSize->dimensions})",
                'metadata' => [
                    'flag_product_id' => $flagProduct->id,
                    'flag_type' => $flagProduct->flagType->name,
                    'flag_size' => $flagProduct->flagSize->name,
                ],
            ];

            $stripeProduct = null;
            if ($flagProduct->stripe_product_id) {
                try {
                    $stripeProduct = $this->stripe->products->update(
                        $flagProduct->stripe_product_id,
                        $productData
                    );
                } catch (ApiErrorException $e) {
                    Log::warning("Stripe product {$flagProduct->stripe_product_id} not found, creating new one");
                    $stripeProduct = null;
                }
            }

            if (!$stripeProduct) {
                $stripeProduct = $this->stripe->products->create($productData);
                $flagProduct->update(['stripe_product_id' => $stripeProduct->id]);
            }

            // Create or update prices
            $prices = [];

            // One-time price
            if (!$flagProduct->stripe_price_id_onetime) {
                $onetimePrice = $this->stripe->prices->create([
                    'product' => $stripeProduct->id,
                    'unit_amount' => $flagProduct->one_time_price * 100, // Convert to cents
                    'currency' => 'usd',
                    'nickname' => 'One-time purchase',
                    'metadata' => [
                        'flag_product_id' => $flagProduct->id,
                        'type' => 'onetime',
                    ],
                ]);
                $flagProduct->update(['stripe_price_id_onetime' => $onetimePrice->id]);
                $prices['onetime'] = $onetimePrice->id;
            } else {
                $prices['onetime'] = $flagProduct->stripe_price_id_onetime;
            }

            // Annual subscription price
            if (!$flagProduct->stripe_price_id_annual) {
                $annualPrice = $this->stripe->prices->create([
                    'product' => $stripeProduct->id,
                    'unit_amount' => $flagProduct->annual_subscription_price * 100, // Convert to cents
                    'currency' => 'usd',
                    'recurring' => [
                        'interval' => 'year',
                        'interval_count' => 1,
                    ],
                    'nickname' => 'Annual subscription',
                    'metadata' => [
                        'flag_product_id' => $flagProduct->id,
                        'type' => 'annual',
                    ],
                ]);
                $flagProduct->update(['stripe_price_id_annual' => $annualPrice->id]);
                $prices['annual'] = $annualPrice->id;
            } else {
                $prices['annual'] = $flagProduct->stripe_price_id_annual;
            }

            return $prices;

        } catch (ApiErrorException $e) {
            Log::error('Failed to create/update Stripe product: ' . $e->getMessage(), [
                'flag_product_id' => $flagProduct->id,
                'error' => $e->getError(),
            ]);
            throw $e;
        }
    }

    /**
     * Create Stripe checkout session.
     */
    public function createCheckoutSession(
        User $user, 
        Subscription $subscription, 
        array $flagProducts, 
        string $subscriptionType
    ): string {
        $customerId = $this->createOrGetCustomer($user);
        $lineItems = [];

        foreach ($flagProducts as $product) {
            // Ensure Stripe products/prices exist
            $this->createOrUpdateProduct($product);

            $priceId = $subscriptionType === 'annual' 
                ? $product->stripe_price_id_annual 
                : $product->stripe_price_id_onetime;

            $lineItems[] = [
                'price' => $priceId,
                'quantity' => 1,
            ];
        }

        try {
            $sessionData = [
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.cancel'),
                'billing_address_collection' => 'required',
                'shipping_address_collection' => [
                    'allowed_countries' => ['US'],
                ],
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id,
                    'subscription_type' => $subscriptionType,
                ],
                'allow_promotion_codes' => true,
            ];

            // Set mode based on subscription type
            if ($subscriptionType === 'annual') {
                $sessionData['mode'] = 'subscription';
                $sessionData['subscription_data'] = [
                    'metadata' => [
                        'subscription_id' => $subscription->id,
                        'user_id' => $user->id,
                    ],
                ];
            } else {
                $sessionData['mode'] = 'payment';
            }

            $session = $this->stripe->checkout->sessions->create($sessionData);
            return $session->url;

        } catch (ApiErrorException $e) {
            Log::error('Failed to create checkout session: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'error' => $e->getError(),
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve checkout session.
     */
    public function getCheckoutSession(string $sessionId): object
    {
        try {
            return $this->stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['payment_intent', 'subscription']
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve checkout session: ' . $e->getMessage(), [
                'session_id' => $sessionId,
                'error' => $e->getError(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel Stripe subscription.
     */
    public function cancelSubscription(string $stripeSubscriptionId, bool $atPeriodEnd = true): object
    {
        try {
            if ($atPeriodEnd) {
                return $this->stripe->subscriptions->update($stripeSubscriptionId, [
                    'cancel_at_period_end' => true,
                ]);
            } else {
                return $this->stripe->subscriptions->cancel($stripeSubscriptionId);
            }
        } catch (ApiErrorException $e) {
            Log::error('Failed to cancel Stripe subscription: ' . $e->getMessage(), [
                'subscription_id' => $stripeSubscriptionId,
                'error' => $e->getError(),
            ]);
            throw $e;
        }
    }

    /**
     * Reactivate cancelled subscription.
     */
    public function reactivateSubscription(string $stripeSubscriptionId): object
    {
        try {
            return $this->stripe->subscriptions->update($stripeSubscriptionId, [
                'cancel_at_period_end' => false,
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Failed to reactivate Stripe subscription: ' . $e->getMessage(), [
                'subscription_id' => $stripeSubscriptionId,
                'error' => $e->getError(),
            ]);
            throw $e;
        }
    }

    /**
     * Update subscription payment method.
     */
    public function updateSubscriptionPaymentMethod(
        string $stripeSubscriptionId, 
        string $paymentMethodId
    ): object {
        try {
            // Attach payment method to customer first
            $subscription = $this->stripe->subscriptions->retrieve($stripeSubscriptionId);
            
            $this->stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $subscription->customer,
            ]);

            // Update subscription
            return $this->stripe->subscriptions->update($stripeSubscriptionId, [
                'default_payment_method' => $paymentMethodId,
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Failed to update subscription payment method: ' . $e->getMessage(), [
                'subscription_id' => $stripeSubscriptionId,
                'payment_method_id' => $paymentMethodId,
                'error' => $e->getError(),
            ]);
            throw $e;
        }
    }

    /**
     * Create customer portal session.
     */
    public function createPortalSession(User $user, string $returnUrl): string
    {
        $customerId = $this->createOrGetCustomer($user);

        try {
            $session = $this->stripe->billingPortal->sessions->create([
                'customer' => $customerId,
                'return_url' => $returnUrl,
            ]);

            return $session->url;
        } catch (ApiErrorException $e) {
            Log::error('Failed to create portal session: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'customer_id' => $customerId,
                'error' => $e->getError(),
            ]);
            throw $e;
        }
    }

    /**
     * Get upcoming invoice for subscription.
     */
    public function getUpcomingInvoice(string $stripeSubscriptionId): ?object
    {
        try {
            return $this->stripe->invoices->upcoming([
                'subscription' => $stripeSubscriptionId,
            ]);
        } catch (ApiErrorException $e) {
            if ($e->getError()->code === 'invoice_upcoming_none') {
                return null;
            }
            
            Log::error('Failed to get upcoming invoice: ' . $e->getMessage(), [
                'subscription_id' => $stripeSubscriptionId,
                'error' => $e->getError(),
            ]);
            throw $e;
        }
    }

    /**
     * Apply coupon to subscription.
     */
    public function applyCoupon(string $stripeSubscriptionId, string $couponId): object
    {
        try {
            return $this->stripe->subscriptions->update($stripeSubscriptionId, [
                'coupon' => $couponId,
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Failed to apply coupon: ' . $e->getMessage(), [
                'subscription_id' => $stripeSubscriptionId,
                'coupon_id' => $couponId,
                'error' => $e->getError(),
            ]);
            throw $e;
        }
    }

    /**
     * Create refund.
     */
    public function createRefund(
        string $paymentIntentId, 
        ?int $amount = null, 
        ?string $reason = null
    ): object {
        try {
            $refundData = [
                'payment_intent' => $paymentIntentId,
            ];

            if ($amount) {
                $refundData['amount'] = $amount;
            }

            if ($reason) {
                $refundData['reason'] = $reason;
            }

            return $this->stripe->refunds->create($refundData);
        } catch (ApiErrorException $e) {
            Log::error('Failed to create refund: ' . $e->getMessage(), [
                'payment_intent_id' => $paymentIntentId,
                'amount' => $amount,
                'reason' => $reason,
                'error' => $e->getError(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature): object
    {
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');
        
        try {
            return \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            throw $e;
        }
    }
}