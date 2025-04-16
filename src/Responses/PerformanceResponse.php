<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use MillerPHP\LaravelBrowserless\Exceptions\PerformanceException;
use Psr\Http\Message\ResponseInterface;

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
     *
     * @throws PerformanceException
     */
    public function data(): array
    {
        if ($this->data === null) {
            try {
                $this->data = json_decode($this->content(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw PerformanceException::invalidResponse('Response is not valid JSON: '.$e->getMessage());
            }
        }

        return $this->data;
    }

    /**
     * Get a specific category score.
     */
    public function categoryScore(string $category): ?float
    {
        try {
            $data = $this->data();

            // Debug the data we're working with
            \Log::debug('PerformanceResponse Category Score', [
                'category' => $category,
                'raw_data' => $data,
                'categories_exists' => isset($data['categories']),
                'category_exists' => isset($data['categories'][$category]),
                'category_data' => $data['categories'][$category] ?? null,
                'data_structure' => array_keys($data),
            ]);

            // Handle both direct category access and data.categories structure
            if (isset($data['categories'][$category]['score'])) {
                return (float) $data['categories'][$category]['score'];
            }

            if (isset($data['data']['categories'][$category]['score'])) {
                return (float) $data['data']['categories'][$category]['score'];
            }

            // Check for legacy format
            if (isset($data['lighthouseResult']['categories'][$category]['score'])) {
                return (float) $data['lighthouseResult']['categories'][$category]['score'];
            }

            \Log::warning('Category score not found', [
                'category' => $category,
                'available_categories' => array_keys($data['categories'] ?? $data['data']['categories'] ?? []),
            ]);

            return null;
        } catch (\Throwable $e) {
            \Log::error('Error getting category score', [
                'category' => $category,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Get all category scores.
     *
     * @return array<string,float>
     */
    public function categoryScores(): array
    {
        try {
            $scores = [];
            $data = $this->data();

            // Debug the data we're working with
            \Log::debug('PerformanceResponse Category Scores', [
                'raw_data' => $data,
                'categories_exists' => isset($data['categories']),
                'data_structure' => array_keys($data),
            ]);

            // Handle both direct category access and data.categories structure
            $categories = $data['categories'] ?? $data['data']['categories'] ?? $data['lighthouseResult']['categories'] ?? [];

            foreach ($categories as $category => $info) {
                if (isset($info['score'])) {
                    $scores[$category] = (float) $info['score'];
                }
            }

            if (empty($scores)) {
                \Log::warning('No category scores found', [
                    'available_categories' => array_keys($categories),
                ]);
            }

            return $scores;
        } catch (\Throwable $e) {
            \Log::error('Error getting category scores', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Get a specific audit result.
     *
     * @return array<string,mixed>|null
     */
    public function audit(string $auditId): ?array
    {
        try {
            $data = $this->data();

            // Handle both direct audit access and data.audits structure
            $audit = $data['audits'][$auditId] ??
                    $data['data']['audits'][$auditId] ??
                    $data['lighthouseResult']['audits'][$auditId] ?? null;

            if ($audit === null) {
                \Log::warning('Audit not found', [
                    'audit_id' => $auditId,
                    'available_audits' => array_keys($data['audits'] ?? $data['data']['audits'] ?? []),
                ]);
            }

            return $audit;
        } catch (\Throwable $e) {
            \Log::error('Error getting audit', [
                'audit_id' => $auditId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Get all audit results.
     *
     * @return array<string,mixed>
     */
    public function audits(): array
    {
        $data = $this->data();

        // Handle both direct audit access and data.audits structure
        return $data['audits'] ?? $data['data']['audits'] ?? [];
    }

    /**
     * Get the performance metrics.
     *
     * @return array<string,mixed>
     */
    public function metrics(): array
    {
        $data = $this->data();
        $metrics = [];

        // Get core web vitals and other important metrics
        $importantMetrics = [
            'first-contentful-paint',
            'largest-contentful-paint',
            'total-blocking-time',
            'cumulative-layout-shift',
            'speed-index',
            'interactive',
        ];

        foreach ($importantMetrics as $metric) {
            $audit = $this->audit($metric);
            if ($audit) {
                $metrics[$metric] = [
                    'score' => $audit['score'] ?? null,
                    'value' => $audit['numericValue'] ?? null,
                    'unit' => $audit['numericUnit'] ?? null,
                    'displayValue' => $audit['displayValue'] ?? null,
                ];
            }
        }

        return $metrics;
    }

    /**
     * Get the performance score.
     */
    public function performanceScore(): ?float
    {
        return $this->categoryScore('performance');
    }

    /**
     * Get the accessibility score.
     */
    public function accessibilityScore(): ?float
    {
        return $this->categoryScore('accessibility');
    }

    /**
     * Get the best practices score.
     */
    public function bestPracticesScore(): ?float
    {
        return $this->categoryScore('best-practices');
    }

    /**
     * Get the SEO score.
     */
    public function seoScore(): ?float
    {
        return $this->categoryScore('seo');
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
