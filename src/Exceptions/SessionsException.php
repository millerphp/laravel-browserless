<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class SessionsException extends BrowserlessException
{
    public static function fromResponse(\Throwable $previous): self
    {
        return new self(
            "Failed to get sessions: {$previous->getMessage()}",
            $previous->getCode(),
            $previous
        );
    }

    public static function invalidResponse(string $message): self
    {
        return new self("Invalid sessions response: {$message}");
    }
} 