<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class ExecuteFunctionException extends BrowserlessException
{
    public static function fromResponse(\Throwable $previous): self
    {
        return new self(
            "Failed to execute function: {$previous->getMessage()}",
            $previous->getCode(),
            $previous
        );
    }

    public static function invalidOptions(string $message): self
    {
        return new self("Invalid function options: {$message}");
    }

    public static function invalidResponse(string $message): self
    {
        return new self("Invalid function response: {$message}");
    }
} 