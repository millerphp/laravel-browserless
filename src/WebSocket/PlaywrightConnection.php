<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\WebSocket;

class PlaywrightConnection extends Connection
{
    protected string $endpoint = 'playwright';
} 