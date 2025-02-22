<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Responses\PerformanceResponse;
use MillerPHP\LaravelBrowserless\Exceptions\PerformanceException;
use GuzzleHttp\Psr7\Request;

class Performance
{
    /**
     * The options for the performance analysis.
     *
     * @var array<string,mixed>
     */
    protected array $options = [];

    /**
     * Create a new Performance instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {}

    /**
     * Set the URL to analyze.
     */
    public function url(string $url): self
    {
        $this->options['url'] = $url;
        return $this;
    }

    /**
     * Set specific categories to analyze.
     *
     * @param array<string> $categories
     */
    public function categories(array $categories): self
    {
        $this->options['config'] = [
            'extends' => 'lighthouse:default',
            'settings' => [
                'onlyCategories' => $categories,
            ],
        ];
        return $this;
    }

    /**
     * Set specific audits to run.
     *
     * @param array<string> $audits
     */
    public function audits(array $audits): self
    {
        $this->options['config'] = [
            'extends' => 'lighthouse:default',
            'settings' => [
                'onlyAudits' => $audits,
            ],
        ];
        return $this;
    }

    /**
     * Send the performance analysis request.
     *
     * @throws PerformanceException
     */
    public function send(): PerformanceResponse
    {
        $this->validateOptions();

        try {
            $request = new Request(
                'POST',
                $this->client->url() . '/performance?token=' . $this->client->token(),
                [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-cache',
                ],
                json_encode($this->options, JSON_THROW_ON_ERROR)
            );

            return new PerformanceResponse($this->client->send($request));
        } catch (\JsonException $e) {
            throw PerformanceException::fromResponse($e);
        } catch (\Throwable $e) {
            throw PerformanceException::fromResponse($e);
        }
    }

    /**
     * Validate the performance options.
     *
     * @throws PerformanceException
     */
    protected function validateOptions(): void
    {
        if (!isset($this->options['url'])) {
            throw PerformanceException::invalidOptions('URL must be provided');
        }
    }
} 