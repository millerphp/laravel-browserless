<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use MillerPHP\LaravelBrowserless\Exceptions\ExecuteFunctionException;
use Psr\Http\Message\ResponseInterface;

class ExecuteFunctionResponse
{
    /**
     * Create a new Function Response instance.
     */
    public function __construct(
        protected readonly ResponseInterface $response
    ) {}

    /**
     * Get the raw response content.
     */
    public function content(): string
    {
        return (string) $this->response->getBody();
    }

    /**
     * Get the response data as an array.
     *
     * @return array<mixed>
     *
     * @throws ExecuteFunctionException
     */
    public function data(): array
    {
        try {
            return json_decode($this->content(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ExecuteFunctionException::invalidResponse('Response is not valid JSON: '.$e->getMessage());
        }
    }

    /**
     * Get the HTTP response status code.
     */
    public function status(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Check if the response was successful.
     */
    public function successful(): bool
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /**
     * Get the underlying PSR-7 response.
     */
    public function getPsrResponse(): ResponseInterface
    {
        return $this->response;
    }
}
