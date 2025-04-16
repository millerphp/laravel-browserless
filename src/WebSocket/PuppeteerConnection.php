<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\WebSocket;

use MillerPHP\LaravelBrowserless\Contracts\ClientContract;

class PuppeteerConnection extends Connection
{
    public function __construct(ClientContract $client)
    {
        parent::__construct($client, 'puppeteer');
    }
}
