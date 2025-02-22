<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class UnblockException extends BrowserlessException
{
    public static function fromResponse(\Throwable $previous): self
    {
        return new self(
            "Failed to unblock page: {$previous->getMessage()}",
            $previous->getCode(),
            $previous
        );
    }

    public static function invalidOptions(string $message): self
    {
        return new self("Invalid unblock options: {$message}");
    }

    public static function invalidResponse(string $message): self
    {
        return new self("Invalid unblock response: {$message}");
    }
} 