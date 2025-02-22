<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use MillerPHP\LaravelBrowserless\Exceptions\UnblockException;
use Psr\Http\Message\ResponseInterface;

class UnblockResponse
{
    /**
     * Create a new Unblock Response instance.
     */
    public function __construct(
        protected readonly ResponseInterface $response
    ) {}

    /**
     * Get the browser WebSocket endpoint.
     */
    public function browserWSEndpoint(): ?string
    {
        return $this->data()['browserWSEndpoint'] ?? null;
    }

    /**
     * Get the HTML content.
     */
    public function content(): ?string
    {
        return $this->data()['content'] ?? null;
    }

    /**
     * Get the cookies.
     *
     * @return array<array{name: string, value: string, domain: string}>
     */
    public function cookies(): array
    {
        return $this->data()['cookies'] ?? [];
    }

    /**
     * Get the screenshot data.
     */
    public function screenshot(): ?string
    {
        return $this->data()['screenshot'] ?? null;
    }

    /**
     * Get the TTL value.
     */
    public function ttl(): ?int
    {
        return $this->data()['ttl'] ?? null;
    }

    /**
     * Get the raw response content.
     */
    public function raw(): string
    {
        return (string) $this->response->getBody();
    }

    /**
     * Get the response data as an array.
     *
     * @return array<string,mixed>
     *
     * @throws UnblockException
     */
    protected function data(): array
    {
        try {
            return json_decode($this->raw(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw UnblockException::invalidResponse('Response is not valid JSON: '.$e->getMessage());
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
