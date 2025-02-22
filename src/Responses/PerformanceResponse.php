<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use Psr\Http\Message\ResponseInterface;
use MillerPHP\LaravelBrowserless\Exceptions\PerformanceException;

class PerformanceResponse
{
    /**
     * @var array<string,mixed>|null
     */
    protected ?array $data = null;

    /**
     * Create a new Performance Response instance.
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
     * Get the performance data as an array.
     *
     * @return array<string,mixed>
     * @throws PerformanceException
     */
    public function data(): array
    {
        if ($this->data === null) {
            try {
                $this->data = json_decode($this->content(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw PerformanceException::invalidResponse('Response is not valid JSON: ' . $e->getMessage());
            }
        }

        return $this->data;
    }

    /**
     * Get a specific category score.
     */
    public function categoryScore(string $category): ?float
    {
        $data = $this->data();
        return $data['categories'][$category]['score'] ?? null;
    }

    /**
     * Get all category scores.
     *
     * @return array<string,float>
     */
    public function categoryScores(): array
    {
        $scores = [];
        $data = $this->data();
        
        foreach ($data['categories'] ?? [] as $category => $info) {
            $scores[$category] = $info['score'];
        }

        return $scores;
    }

    /**
     * Get a specific audit result.
     *
     * @return array<string,mixed>|null
     */
    public function audit(string $auditId): ?array
    {
        $data = $this->data();
        return $data['audits'][$auditId] ?? null;
    }

    /**
     * Get all audit results.
     *
     * @return array<string,mixed>
     */
    public function audits(): array
    {
        return $this->data()['audits'] ?? [];
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