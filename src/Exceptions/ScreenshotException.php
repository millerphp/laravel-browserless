<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class ScreenshotException extends BrowserlessException
{
    public static function fromResponse(\Throwable $previous): self
    {
        return new self(
            "Failed to generate screenshot: {$previous->getMessage()}",
            $previous->getCode(),
            $previous
        );
    }

    public static function invalidOptions(string $message): self
    {
        return new self("Invalid screenshot options: {$message}");
    }
} 