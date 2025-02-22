<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class ContentException extends BrowserlessException
{
    public static function fromResponse(\Throwable $previous): self
    {
        return new self(
            "Failed to capture content: {$previous->getMessage()}",
            $previous->getCode(),
            $previous
        );
    }

    public static function invalidOptions(string $message): self
    {
        return new self("Invalid content options: {$message}");
    }
}
