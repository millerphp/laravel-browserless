<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class BQLException extends \Exception
{
    /**
     * Create a new BQL exception from a response.
     */
    public static function fromResponse(\Throwable $e): self
    {
        return new self(
            'BQL query failed: ' . $e->getMessage(),
            (int) $e->getCode(),
            $e
        );
    }
} 