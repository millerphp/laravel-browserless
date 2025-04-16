<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use MillerPHP\LaravelBrowserless\Exceptions\ScrapeException;
use Psr\Http\Message\ResponseInterface;

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
     *
     * @throws ScrapeException
     */
    public function data(): array
    {
        try {
            return json_decode($this->content(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ScrapeException::invalidResponse('Response is not valid JSON: '.$e->getMessage());
        }
    }

    /**
     * Get the results for a specific element selector or all results if no selector is provided.
     *
     * @return array<mixed>
     *
     * @throws ScrapeException
     */
    public function results(?string $selector = null): array
    {
        $data = $this->data();

        // Debug the data we're working with
        \Log::debug('ScrapeResponse Data', [
            'raw_data' => $data,
            'selector' => $selector,
            'data_key_exists' => isset($data['data']),
            'results_key_exists' => isset($data['results']),
            'data_structure' => array_keys($data),
        ]);

        // Get the results array, handling both possible structures
        $results = $data['data'] ?? $data['results'] ?? [];

        if ($selector === null) {
            return $results;
        }

        foreach ($results as $result) {
            if ($result['selector'] === $selector) {
                return $result['results'];
            }
        }

        return [];
    }

    /**
     * Get all results from the scrape.
     *
     * @return array<string,mixed>
     */
    public function allResults(): array
    {
        return $this->results();
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
