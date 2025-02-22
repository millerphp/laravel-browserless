<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use Psr\Http\Message\ResponseInterface;
use MillerPHP\LaravelBrowserless\Exceptions\ConfigException;

class ConfigResponse
{
    /**
     * @var array<string,mixed>|null
     */
    protected ?array $data = null;

    /**
     * Create a new Config Response instance.
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
     * Get the configuration data as an array.
     *
     * @return array<string,mixed>
     * @throws ConfigException
     */
    public function data(): array
    {
        if ($this->data === null) {
            try {
                $this->data = json_decode($this->content(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw ConfigException::invalidResponse('Response is not valid JSON: ' . $e->getMessage());
            }
        }

        return $this->data;
    }

    /**
     * Get a specific configuration value.
     *
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->data()[$key] ?? null;
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