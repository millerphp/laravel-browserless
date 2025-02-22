<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MillerPHP\Browserless\Browserless
 */
class Browserless extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MillerPHP\LaravelBrowserless\Browserless::class;
    }
}
