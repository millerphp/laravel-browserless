<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\WebSocket;

use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Exceptions\WebSocketException;
use WebSocket\Client;

abstract class Connection
{
    protected readonly string $endpoint;

    protected ?Client $wsClient = null;

    public function __construct(
        protected readonly ClientContract $client,
        string $endpoint
    ) {
        $this->endpoint = $endpoint;
    }

    /**
     * Connect to the WebSocket server.
     */
    public function connect(): void
    {
        try {
            $url = str_replace('http', 'ws', $this->client->url());
            $this->wsClient = new Client(
                $url.'/'.$this->endpoint.'?token='.$this->client->token()
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to connect to WebSocket server: '.$e->getMessage());
        }
    }

    /**
     * Send a message through the WebSocket connection.
     *
     * @throws WebSocketException
     */
    public function send(string $message): string
    {
        if (! $this->wsClient) {
            throw WebSocketException::notConnected();
        }

        try {
            $this->wsClient->send($message);

            return $this->wsClient->receive();
        } catch (\Throwable $e) {
            throw WebSocketException::sendFailed($e->getMessage());
        }
    }

    /**
     * Close the WebSocket connection.
     */
    public function close(): void
    {
        if ($this->wsClient) {
            $this->wsClient->close();
            $this->wsClient = null;
        }
    }
}
