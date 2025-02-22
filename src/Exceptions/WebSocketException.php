<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Exceptions;

class WebSocketException extends BrowserlessException
{
    public static function connectionFailed(string $message): self
    {
        return new self("Failed to establish WebSocket connection: {$message}");
    }

    public static function notConnected(): self
    {
        return new self('WebSocket connection not established. Call connect() first.');
    }

    public static function sendFailed(string $message): self
    {
        return new self("Failed to send WebSocket message: {$message}");
    }
}
