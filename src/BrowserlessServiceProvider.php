<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless;

use Illuminate\Support\ServiceProvider;
use MillerPHP\LaravelBrowserless\Contracts\ClientContract;

class BrowserlessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/browserless.php',
            'browserless'
        );

        $this->app->singleton(ClientContract::class, function ($app) {
            return new Browserless(
                apiToken: config('browserless.token'),
                url: config('browserless.url'),
            );
        });

        // Alias the contract to the Browserless class for easier type-hinting
        $this->app->alias(ClientContract::class, Browserless::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/browserless.php' => config_path('browserless.php'),
            ], 'browserless-config');
        }
    }
}
