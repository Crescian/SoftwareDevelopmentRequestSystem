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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ticketing System (centralized identity)
    |--------------------------------------------------------------------------
    |
    | The ticketing system's /api endpoints are the shared identity source
    | for this application. Login credentials are verified there, never
    | stored locally; "client" identifies this app to /api/login so it gets
    | its own independent token per user.
    |
    */

    'ticketing' => [
        'base_url' => env('TICKETING_API_URL'),
        'client' => env('TICKETING_API_CLIENT', 'software-development-request'),
        'timeout' => env('TICKETING_API_TIMEOUT', 10),
    ],

];
