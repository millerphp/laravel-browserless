<?php

declare(strict_types=1);

// config for MillerPHP/Browserless
return [
    /*
    |--------------------------------------------------------------------------
    | Browserless API Token
    |--------------------------------------------------------------------------
    |
    | Your Browserless API token for authentication
    | You can get this from the Browserless dashboard.
    | For the local instance, you can use any token.
    |
    */
    'token' => env('BROWSERLESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Browserless URL
    |--------------------------------------------------------------------------
    |
    | The URL for the Browserless service
    | By default, this is configured for the cloud service.
    | You can change this to your own browserless instance.
    |
    */
    'url' => env('BROWSERLESS_URL', 'https://production-sfo.browserless.io'),
];
