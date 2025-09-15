<?php
// config/stripe.php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe API Keys
    |--------------------------------------------------------------------------
    |
    | The Stripe publishable and secret keys from your Stripe dashboard.
    | You can find these in your Stripe dashboard under API keys.
    |
    */

    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | The signing secret for your webhook endpoint. This is used to verify
    | that webhook events are coming from Stripe and not a third party.
    |
    */

    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for all Stripe transactions. This should match
    | the currency you've configured in your Stripe dashboard.
    |
    */

    'currency' => env('STRIPE_CURRENCY', 'usd'),

    /*
    |--------------------------------------------------------------------------
    | Billing Model
    |--------------------------------------------------------------------------
    |
    | Configuration for subscription billing behavior.
    |
    */

    'billing' => [
        'trial_days' => env('STRIPE_TRIAL_DAYS', 0),
        'grace_days' => env('STRIPE_GRACE_DAYS', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific Stripe features for your application.
    |
    */

    'features' => [
        'customer_portal' => env('STRIPE_CUSTOMER_PORTAL', true),
        'promotion_codes' => env('STRIPE_PROMOTION_CODES', true),
        'tax_rates' => env('STRIPE_TAX_RATES', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Events
    |--------------------------------------------------------------------------
    |
    | List of webhook events that your application should handle.
    | This is used for documentation and validation purposes.
    |
    */

    'webhook_events' => [
        'checkout.session.completed',
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'invoice.payment_succeeded',
        'invoice.payment_failed',
        'customer.subscription.trial_will_end',
        'payment_intent.succeeded',
        'payment_intent.payment_failed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Configuration
    |--------------------------------------------------------------------------
    |
    | Default product configuration for flag subscriptions.
    |
    */

    'products' => [
        'statement_descriptor' => env('STRIPE_STATEMENT_DESCRIPTOR', 'FLAGS COMMUNITY'),
        'metadata' => [
            'application' => 'flags-across-community',
            'version' => '1.0',
        ],
    ],
];