<?php
// app/Http/Controllers/WebhookController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\StripeService;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WebhookController extends Controller
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Handle Stripe webhooks.
     */
    public function handleStripe(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = $this->stripeService->verifyWebhookSignature($payload, $signature);
        } catch (\Exception $e) {
            Log::error('Webhook signature verification failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return response('Webhook signature verification failed', 400);
        }

        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->handleCheckoutSessionCompleted($event->data->object);
                    break;

                case 'customer.subscription.created':
                    $this->handleSubscriptionCreated($event->data->object);
                    break;

                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event->data->object);
                    break;

                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;

                case 'invoice.payment_succeeded':
                    $this->handleInvoicePaymentSucceeded($event->data->object);
                    break;

                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($event->data->object);
                    break;

                case 'customer.subscription.trial_will_end':
                    $this->handleTrialWillEnd($event->data->object);
                    break;

                default:
                    Log::info('Unhandled webhook event type', ['type' => $event->type]);
            }

            return response('Webhook handled successfully', 200);

        } catch (\Exception $e) {
            Log::error('Error handling webhook', [
                'type' => $event->type,
                'id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response('Error handling webhook', 500);
        }
    }

    /**
     * Handle successful checkout session completion.
     */
    protected function handleCheckoutSessionCompleted($session)
    {
        $subscriptionId = $session->metadata->subscription_id ?? null;
        
        if (!$subscriptionId) {
            Log::warning('Checkout session completed without subscription_id', [
                'session_id' => $session->id,
                'metadata' => $session->metadata,
            ]);
            return;
        }

        $subscription = Subscription::find($subscriptionId);
        if (!$subscription) {
            Log::error('Subscription not found for completed checkout', [
                'subscription_id' => $subscriptionId,
                'session_id' => $session->id,
            ]);
            return;
        }

        // Update subscription with Stripe data
        $updateData = [
            'status' => 'active',
        ];

        if ($session->mode === 'subscription') {
            $updateData['stripe_subscription_id'] = $session->subscription;
        } else {
            $updateData['stripe_payment_intent_id'] = $session->payment_intent;
        }

        $subscription->update($updateData);

        // Generate flag placements for the subscription
        $subscription->generateFlagPlacements();

        // Send welcome notification
        Notification::createWelcomeNotification($subscription->user_id);

        Log::info('Subscription activated from checkout', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'session_id' => $session->id,
        ]);
    }

    /**
     * Handle subscription creation (for recurring subscriptions).
     */
    protected function handleSubscriptionCreated($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        
        if (!$subscription) {
            Log::warning('Subscription created in Stripe but not found locally', [
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);
            return;
        }

        // Ensure subscription is active
        if ($subscription->status !== 'active') {
            $subscription->update(['status' => 'active']);
            $subscription->generateFlagPlacements();
            
            Log::info('Subscription activated via webhook', [
                'subscription_id' => $subscription->id,
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);
        }
    }

    /**
     * Handle subscription updates.
     */
    protected function handleSubscriptionUpdated($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        
        if (!$subscription) {
            Log::warning('Updated subscription not found locally', [
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);
            return;
        }

        // Update subscription status based on Stripe status
        $newStatus = $this->mapStripeStatusToLocal($stripeSubscription->status);
        
        if ($subscription->status !== $newStatus) {
            $oldStatus = $subscription->status;
            $subscription->update(['status' => $newStatus]);

            // Handle status change
            if ($newStatus === 'canceled') {
                $this->handleSubscriptionCancellation($subscription);
            } elseif ($newStatus === 'active' && $oldStatus !== 'active') {
                $subscription->generateFlagPlacements();
            }

            Log::info('Subscription status updated', [
                'subscription_id' => $subscription->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'stripe_status' => $stripeSubscription->status,
            ]);
        }

        // Check if subscription is set to cancel at period end
        if ($stripeSubscription->cancel_at_period_end && !$subscription->canceled_at) {
            $subscription->update([
                'canceled_at' => Carbon::createFromTimestamp($stripeSubscription->cancel_at),
            ]);

            // Send cancellation confirmation
            Notification::create([
                'user_id' => $subscription->user_id,
                'type' => 'email',
                'subject' => 'Subscription cancellation confirmed',
                'message' => "Your flag subscription has been cancelled and will end on {$subscription->end_date->format('F j, Y')}. Flags will still be placed for any scheduled holidays before that date.",
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'notification_type' => 'cancellation_confirmed',
                ],
            ]);
        }
    }

    /**
     * Handle subscription deletion.
     */
    protected function handleSubscriptionDeleted($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        
        if (!$subscription) {
            Log::warning('Deleted subscription not found locally', [
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);
            return;
        }

        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => Carbon::now(),
        ]);

        $this->handleSubscriptionCancellation($subscription);

        Log::info('Subscription deleted in Stripe', [
            'subscription_id' => $subscription->id,
            'stripe_subscription_id' => $stripeSubscription->id,
        ]);
    }

    /**
     * Handle successful invoice payment.
     */
    protected function handleInvoicePaymentSucceeded($invoice)
    {
        if (!$invoice->subscription) {
            return; // One-time payment, not a subscription
        }

        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();
        
        if (!$subscription) {
            Log::warning('Invoice payment succeeded for unknown subscription', [
                'invoice_id' => $invoice->id,
                'subscription_id' => $invoice->subscription,
            ]);
            return;
        }

        // Check if this is a renewal payment
        $isRenewal = $invoice->billing_reason === 'subscription_cycle';
        
        if ($isRenewal) {
            // Update subscription end date
            $newEndDate = Carbon::createFromTimestamp($invoice->lines->data[0]->period->end);
            $subscription->update(['end_date' => $newEndDate]);

            // Generate new flag placements for the next period
            $subscription->generateFlagPlacements();

            // Send renewal confirmation
            Notification::create([
                'user_id' => $subscription->user_id,
                'type' => 'email',
                'subject' => 'Subscription renewed successfully',
                'message' => "Your flag subscription has been renewed for another year. Your flags will continue to be placed for all patriotic holidays through {$newEndDate->format('F j, Y')}.",
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'notification_type' => 'renewal_success',
                    'invoice_id' => $invoice->id,
                ],
            ]);

            Log::info('Subscription renewed', [
                'subscription_id' => $subscription->id,
                'new_end_date' => $newEndDate->toDateString(),
                'invoice_id' => $invoice->id,
            ]);
        }

        // Ensure subscription is active
        if ($subscription->status !== 'active') {
            $subscription->update(['status' => 'active']);
        }
    }

    /**
     * Handle failed invoice payment.
     */
    protected function handleInvoicePaymentFailed($invoice)
    {
        if (!$invoice->subscription) {
            return; // One-time payment failure
        }

        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();
        
        if (!$subscription) {
            Log::warning('Invoice payment failed for unknown subscription', [
                'invoice_id' => $invoice->id,
                'subscription_id' => $invoice->subscription,
            ]);
            return;
        }

        // Send payment failure notification
        Notification::create([
            'user_id' => $subscription->user_id,
            'type' => 'email',
            'subject' => 'Payment failed for your flag subscription',
            'message' => "We couldn't process your payment for your flag subscription. Please update your payment method to avoid service interruption.",
            'metadata' => [
                'subscription_id' => $subscription->id,
                'notification_type' => 'payment_failed',
                'invoice_id' => $invoice->id,
            ],
        ]);

        Log::warning('Subscription payment failed', [
            'subscription_id' => $subscription->id,
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount_due,
        ]);
    }

    /**
     * Handle trial ending soon.
     */
    protected function handleTrialWillEnd($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        
        if (!$subscription) {
            return;
        }

        // Send trial ending notification
        $trialEndDate = Carbon::createFromTimestamp($stripeSubscription->trial_end);
        
        Notification::create([
            'user_id' => $subscription->user_id,
            'type' => 'email',
            'subject' => 'Your flag subscription trial ends soon',
            'message' => "Your flag subscription trial will end on {$trialEndDate->format('F j, Y')}. Make sure your payment method is up to date to continue your service.",
            'metadata' => [
                'subscription_id' => $subscription->id,
                'notification_type' => 'trial_ending',
                'trial_end_date' => $trialEndDate->toDateString(),
            ],
        ]);
    }

    /**
     * Handle subscription cancellation logic.
     */
    protected function handleSubscriptionCancellation(Subscription $subscription)
    {
        // Cancel future scheduled placements
        $subscription->flagPlacements()
            ->where('status', 'scheduled')
            ->where('placement_date', '>', Carbon::now())
            ->update(['status' => 'skipped']);

        // Send cancellation notification if not already sent
        if (!$subscription->canceled_at) {
            Notification::create([
                'user_id' => $subscription->user_id,
                'type' => 'email',
                'subject' => 'Your flag subscription has been cancelled',
                'message' => 'We\'re sorry to see you go! Your flag subscription has been cancelled. Flags scheduled for placement before the cancellation date will still be placed.',
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'notification_type' => 'subscription_cancelled',
                ],
            ]);
        }
    }

    /**
     * Map Stripe subscription status to local status.
     */
    protected function mapStripeStatusToLocal(string $stripeStatus): string
    {
        return match($stripeStatus) {
            'active', 'trialing' => 'active',
            'canceled' => 'canceled',
            'incomplete', 'incomplete_expired' => 'pending',
            'past_due', 'unpaid' => 'expired',
            default => 'pending',
        };
    }
}