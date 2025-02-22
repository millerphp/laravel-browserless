<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Responses\SessionsResponse;
use MillerPHP\LaravelBrowserless\Exceptions\SessionsException;
use GuzzleHttp\Psr7\Request;

class Sessions
{
    /**
     * Create a new Sessions instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {}

    /**
     * Get information about running sessions.
     *
     * @throws SessionsException
     */
    public function get(): SessionsResponse
    {
        try {
            $request = new Request(
                'GET',
                $this->client->url() . '/sessions?token=' . $this->client->token(),
                [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ]
            );

            return new SessionsResponse($this->client->send($request));
        } catch (\Throwable $e) {
            throw SessionsException::fromResponse($e);
        }
    }
} 