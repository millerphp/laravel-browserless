<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class PDFGenerationException extends BrowserlessException
{
    public static function fromResponse(\Throwable $previous): self
    {
        return new self(
            "Failed to generate PDF: {$previous->getMessage()}",
            $previous->getCode(),
            $previous
        );
    }

    public static function invalidOptions(string $message): self
    {
        return new self("Invalid PDF options: {$message}");
    }
}
