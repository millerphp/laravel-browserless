<?php

declare(strict_types=1);

// config for MillerPHP/Browserless
return [
    /*
    |--------------------------------------------------------------------------
    | Browserless API Key
    |--------------------------------------------------------------------------
    |
    | Your Browserless API key. You can get one from https://www.browserless.io/
    |
    */
    'api_key' => env('BROWSERLESS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Browserless API URL
    |--------------------------------------------------------------------------
    |
    | The URL of the Browserless API. Defaults to the official API URL.
    |
    */
    'api_url' => env('BROWSERLESS_API_URL', 'https://chrome.browserless.io'),

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging for Browserless requests and responses.
    |
    */
    'logging' => [
        'enabled' => env('BROWSERLESS_LOGGING_ENABLED', false),
        'level' => env('BROWSERLESS_LOGGING_LEVEL', 'info'),
        'channels' => env('BROWSERLESS_LOGGING_CHANNELS', ['stack']),
        'log_requests' => env('BROWSERLESS_LOG_REQUESTS', true),
        'log_responses' => env('BROWSERLESS_LOG_RESPONSES', true),
        'log_errors' => env('BROWSERLESS_LOG_ERRORS', true),
        'log_performance' => env('BROWSERLESS_LOG_PERFORMANCE', true),
        'mask_sensitive_data' => env('BROWSERLESS_MASK_SENSITIVE_DATA', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    |
    | Default options for all requests. These can be overridden per request.
    |
    */
    'default_options' => [
        'timeout' => 30000,
        'waitUntil' => 'networkidle0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Viewport
    |--------------------------------------------------------------------------
    |
    | Default viewport settings for all requests. These can be overridden per request.
    |
    */
    'default_viewport' => [
        'width' => 1920,
        'height' => 1080,
        'deviceScaleFactor' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default PDF Options
    |--------------------------------------------------------------------------
    |
    | Default PDF generation options. These can be overridden per request.
    |
    */
    'default_pdf_options' => [
        'format' => 'A4',
        'landscape' => false,
        'printBackground' => true,
        'margin' => [
            'top' => '1cm',
            'right' => '1cm',
            'bottom' => '1cm',
            'left' => '1cm',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Screenshot Options
    |--------------------------------------------------------------------------
    |
    | Default screenshot options. These can be overridden per request.
    |
    */
    'default_screenshot_options' => [
        'fullPage' => false,
        'omitBackground' => false,
    ],
];
