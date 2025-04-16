<?php

use MillerPHP\LaravelBrowserless\Exceptions\BQLException;

it('can create exception from response', function () {
    $originalException = new \Exception('Original error', 500);
    $exception = BQLException::fromResponse($originalException);

    expect($exception)
        ->toBeInstanceOf(BQLException::class)
        ->and($exception->getMessage())
        ->toBe('BQL query failed: Original error')
        ->and($exception->getCode())
        ->toBe(500)
        ->and($exception->getPrevious())
        ->toBe($originalException);
});

it('preserves original exception code', function () {
    $codes = [400, 401, 403, 404, 500, 502, 503];

    foreach ($codes as $code) {
        $originalException = new \Exception('Error', $code);
        $exception = BQLException::fromResponse($originalException);

        expect($exception->getCode())->toBe($code);
    }
});

it('handles nested exceptions', function () {
    $deepestException = new \Exception('Deepest error');
    $middleException = new \Exception('Middle error', 0, $deepestException);
    $topException = new \Exception('Top error', 0, $middleException);

    $exception = BQLException::fromResponse($topException);

    expect($exception->getMessage())
        ->toBe('BQL query failed: Top error')
        ->and($exception->getPrevious())
        ->toBe($topException)
        ->and($exception->getPrevious()->getPrevious())
        ->toBe($middleException)
        ->and($exception->getPrevious()->getPrevious()->getPrevious())
        ->toBe($deepestException);
});

it('handles exceptions without codes', function () {
    $originalException = new \Exception('Error without code');
    $exception = BQLException::fromResponse($originalException);

    expect($exception->getCode())->toBe(0);
});

it('handles exceptions with non-integer codes', function () {
    $originalException = new \Exception('Error with string code', '500');
    $exception = BQLException::fromResponse($originalException);

    expect($exception->getCode())->toBe(500);
});

it('preserves exception trace', function () {
    $originalException = new \Exception('Error');
    $exception = BQLException::fromResponse($originalException);

    expect($exception->getTraceAsString())
        ->not->toBeEmpty();
});
