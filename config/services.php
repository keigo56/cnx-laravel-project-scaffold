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
    
    'azure' => [
        'app_id' => env('OAUTH_APP_ID'),
        'tenant_id' => env('OAUTH_TENANT_ID'),
        'secret' => env('OAUTH_APP_SECRET'),
        'redirect_uri' => env('OAUTH_REDIRECT_URI'),
        'authorize_endpoint' => env('OAUTH_AUTHORIZE_ENDPOINT'),
        'token_endpoint' => env('OAUTH_TOKEN_ENDPOINT'),
        'scopes' => env('OAUTH_SCOPES'),
        'frontend_uri' => env('FRONTEND_URL')
    ],

];
