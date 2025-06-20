<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use MillerPHP\LaravelBrowserless\Exceptions\MetricsException;
use Psr\Http\Message\ResponseInterface;

class MetricsResponse
{
    /**
     * @var array<array<string,mixed>>|null
     */
    protected ?array $data = null;

    /**
     * Create a new Metrics Response instance.
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
     * Get the metrics data as an array.
     *
     * @return array<array<string,mixed>>
     *
     * @throws MetricsException
     */
    public function data(): array
    {
        if ($this->data === null) {
            try {
                $this->data = json_decode($this->content(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw MetricsException::invalidResponse('Response is not valid JSON: '.$e->getMessage());
            }
        }

        return $this->data;
    }

    /**
     * Get the latest metrics.
     *
     * @return array<string,mixed>|null
     */
    public function latest(): ?array
    {
        $data = $this->data();

        return ! empty($data) ? $data[0] : null;
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
