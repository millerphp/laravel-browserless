<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use Psr\Http\Message\ResponseInterface;
use MillerPHP\LaravelBrowserless\Exceptions\SessionsException;

class SessionsResponse
{
    /**
     * @var array<array<string,mixed>>|null
     */
    protected ?array $data = null;

    /**
     * Create a new Sessions Response instance.
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
     * Get all sessions data.
     *
     * @return array<array<string,mixed>>
     * @throws SessionsException
     */
    public function data(): array
    {
        if ($this->data === null) {
            try {
                $this->data = json_decode($this->content(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw SessionsException::invalidResponse('Response is not valid JSON: ' . $e->getMessage());
            }
        }

        return $this->data;
    }

    /**
     * Get all running sessions.
     *
     * @return array<array<string,mixed>>
     */
    public function running(): array
    {
        return array_filter($this->data(), fn($session) => $session['running'] ?? false);
    }

    /**
     * Get session by browser ID.
     *
     * @return array<string,mixed>|null
     */
    public function findById(string $browserId): ?array
    {
        foreach ($this->data() as $session) {
            if (($session['browserId'] ?? null) === $browserId) {
                return $session;
            }
        }

        return null;
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