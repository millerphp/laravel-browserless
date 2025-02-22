<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class DownloadException extends BrowserlessException
{
    public static function fromResponse(\Throwable $previous): self
    {
        return new self(
            "Failed to download file: {$previous->getMessage()}",
            $previous->getCode(),
            $previous
        );
    }

    public static function invalidOptions(string $message): self
    {
        return new self("Invalid download options: {$message}");
    }
} 