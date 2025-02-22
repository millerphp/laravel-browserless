<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class PerformanceException extends BrowserlessException
{
    public static function fromResponse(\Throwable $previous): self
    {
        return new self(
            "Failed to analyze performance: {$previous->getMessage()}",
            $previous->getCode(),
            $previous
        );
    }

    public static function invalidOptions(string $message): self
    {
        return new self("Invalid performance options: {$message}");
    }

    public static function invalidResponse(string $message): self
    {
        return new self("Invalid performance response: {$message}");
    }
}
