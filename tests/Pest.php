<?php

use MillerPHP\LaravelBrowserless\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

expect()->extend('toBeValidResponse', function () {
    return $this->toBeInstanceOf(\Psr\Http\Message\ResponseInterface::class);
});

expect()->extend('toHaveValidData', function () {
    return $this->toBeArray()
        ->not->toBeEmpty();
});
