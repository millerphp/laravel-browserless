<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\WebSocket;

use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Exceptions\WebSocketException;
use WebSocket\Client;

class Connection
{
    protected ?Client $client = null;

    /**
     * Create a new WebSocket Connection instance.
     */
    public function __construct(
        protected readonly ClientContract $browserless,
        protected readonly string $endpoint = 'ws'
    ) {}

    /**
     * Connect to the WebSocket endpoint.
     *
     * @throws WebSocketException
     */
    public function connect(): self
    {
        try {
            $url = str_replace('http', 'ws', $this->browserless->url());
            $this->client = new Client(
                $url . '/' . $this->endpoint . '?token=' . $this->browserless->token()
            );
            return $this;
        } catch (\Throwable $e) {
            throw WebSocketException::connectionFailed($e->getMessage());
        }
    }

    /**
     * Send a message through the WebSocket connection.
     *
     * @throws WebSocketException
     */
    public function send(string $message): string
    {
        if (!$this->client) {
            throw WebSocketException::notConnected();
        }

        try {
            $this->client->send($message);
            return $this->client->receive();
        } catch (\Throwable $e) {
            throw WebSocketException::sendFailed($e->getMessage());
        }
    }

    /**
     * Close the WebSocket connection.
     */
    public function close(): void
    {
        if ($this->client) {
            $this->client->close();
            $this->client = null;
        }
    }
} 