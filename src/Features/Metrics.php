<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Responses\MetricsResponse;
use MillerPHP\LaravelBrowserless\Exceptions\MetricsException;
use GuzzleHttp\Psr7\Request;

class Metrics
{
    /**
     * Create a new Metrics instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {}

    /**
     * Get session metrics.
     *
     * @throws MetricsException
     */
    public function get(): MetricsResponse
    {
        try {
            $request = new Request(
                'GET',
                $this->client->url() . '/metrics?token=' . $this->client->token(),
                [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ]
            );

            return new MetricsResponse($this->client->send($request));
        } catch (\Throwable $e) {
            throw MetricsException::fromResponse($e);
        }
    }

    /**
     * Get total session metrics.
     *
     * @throws MetricsException
     */
    public function total(): MetricsResponse
    {
        try {
            $request = new Request(
                'GET',
                $this->client->url() . '/metrics/total?token=' . $this->client->token(),
                [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ]
            );

            return new MetricsResponse($this->client->send($request));
        } catch (\Throwable $e) {
            throw MetricsException::fromResponse($e);
        }
    }
} 