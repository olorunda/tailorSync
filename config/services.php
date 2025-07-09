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

    'payment' => [
        'subscription' => [
            'gateway' => env('SUBSCRIPTION_PAYMENT_GATEWAY','paystack'),
            'paystack' => [
                'public_key' => env('SUBSCRIPTION_PAYSTACK_PUBLIC_KEY', env('PAYSTACK_PUBLIC_KEY')),
                'secret_key' => env('SUBSCRIPTION_PAYSTACK_SECRET_KEY', env('PAYSTACK_SECRET_KEY')),
            ],
            'flutterwave' => [
                'public_key' => env('SUBSCRIPTION_FLUTTERWAVE_PUBLIC_KEY', env('FLUTTERWAVE_PUBLIC_KEY')),
                'secret_key' => env('SUBSCRIPTION_FLUTTERWAVE_SECRET_KEY', env('FLUTTERWAVE_SECRET_KEY')),
            ],
            'stripe' => [
                'public_key' => env('SUBSCRIPTION_STRIPE_PUBLIC_KEY', env('STRIPE_PUBLIC_KEY')),
                'secret_key' => env('SUBSCRIPTION_STRIPE_SECRET_KEY', env('STRIPE_SECRET_KEY')),
            ],
        ],
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY','AIzaSyBVpemolFRYQqCfKDiA-xciHJG-JIP78XY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

    'imagerouter' => [
        'api_key' => env('IMAGEROUTER_API_KEY', 'f03aacb31e77283a4e26915311145f548c6c7e9c9da931259e17ae5432aa3146'),
        'model' => env('IMAGEROUTER_MODEL', 'google/gemini-2.0-flash-exp:free'),
    ],

    'image_generator' => [
        'provider' => env('IMAGE_GENERATOR_PROVIDER', 'imagerouter'), // Options: 'gemini', 'imagerouter'
    ],

    'zeptomail' => [
        'api_key' => env('ZEPTOMAIL_API_KEY'),
        'endpoint' => env('ZEPTOMAIL_ENDPOINT', 'https://api.zeptomail.com/v1.1/email'),
    ],

];
