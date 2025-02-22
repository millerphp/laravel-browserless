<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class ScrapeException extends BrowserlessException
{
    public static function fromResponse(\Throwable $previous): self
    {
        return new self(
            "Failed to scrape page: {$previous->getMessage()}",
            $previous->getCode(),
            $previous
        );
    }

    public static function invalidOptions(string $message): self
    {
        return new self("Invalid scrape options: {$message}");
    }

    public static function invalidResponse(string $message): self
    {
        return new self("Invalid scrape response: {$message}");
    }
} 