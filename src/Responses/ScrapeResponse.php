<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use Psr\Http\Message\ResponseInterface;
use MillerPHP\LaravelBrowserless\Exceptions\ScrapeException;

class ScrapeResponse
{
    /**
     * Create a new Scrape Response instance.
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
     * Get the scraped data as an array.
     *
     * @return array<string,mixed>
     * @throws ScrapeException
     */
    public function data(): array
    {
        try {
            return json_decode($this->content(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ScrapeException::invalidResponse('Response is not valid JSON: ' . $e->getMessage());
        }
    }

    /**
     * Get the results for a specific element selector.
     *
     * @return array<mixed>
     * @throws ScrapeException
     */
    public function results(string $selector): array
    {
        $data = $this->data();
        foreach ($data['results'] ?? [] as $result) {
            if ($result['selector'] === $selector) {
                return $result['results'];
            }
        }
        return [];
    }

    /**
     * Get all scraped results.
     *
     * @return array<array{selector: string, results: array<mixed>}>
     * @throws ScrapeException
     */
    public function allResults(): array
    {
        return $this->data()['results'] ?? [];
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