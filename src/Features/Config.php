<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Responses\ConfigResponse;
use MillerPHP\LaravelBrowserless\Exceptions\ConfigException;
use GuzzleHttp\Psr7\Request;

class Config
{
    /**
     * Create a new Config instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {}

    /**
     * Get worker configuration.
     *
     * @throws ConfigException
     */
    public function get(): ConfigResponse
    {
        try {
            $request = new Request(
                'GET',
                $this->client->url() . '/config?token=' . $this->client->token(),
                [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ]
            );

            return new ConfigResponse($this->client->send($request));
        } catch (\Throwable $e) {
            throw ConfigException::fromResponse($e);
        }
    }
} 