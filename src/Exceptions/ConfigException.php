<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class ConfigException extends BrowserlessException
{
    public static function fromResponse(\Throwable $previous): self
    {
        return new self(
            "Failed to get configuration: {$previous->getMessage()}",
            $previous->getCode(),
            $previous
        );
    }

    public static function invalidResponse(string $message): self
    {
        return new self("Invalid configuration response: {$message}");
    }
} 