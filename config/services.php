<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ... existing service configurations ...

    /*
    |--------------------------------------------------------------------------
    | Google Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Maps API and other Google services.
    |
    | To get a Google Maps API key:
    | 1. Go to https://console.cloud.google.com/
    | 2. Create a new project or select an existing one
    | 3. Enable the following APIs:
    |    - Maps JavaScript API
    |    - Directions API
    |    - Geocoding API
    | 4. Create an API key in Credentials
    | 5. Add the key to your .env file as GOOGLE_MAPS_API_KEY
    |
    */

    'google' => [
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),

        // Optional: Restrict API key usage by HTTP referer
        'maps_referer_restrictions' => [
            env('APP_URL'),
        ],
    ],

];
