<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features\Concerns;

trait HasQueryParameters
{
    /**
     * The query parameters.
     *
     * @var array<string,string>
     */
    protected array $queryParameters = [];

    /**
     * Enable proxy mode.
     */
    public function proxy(bool $enabled = true): self
    {
        $this->queryParameters['proxy'] = $enabled;
        return $this;
    }

    /**
     * Enable stealth mode.
     */
    public function stealth(bool $enabled = true): self
    {
        $this->queryParameters['stealth'] = $enabled;
        return $this;
    }

    /**
     * Enable keepalive mode.
     */
    public function keepalive(bool $enabled = true): self
    {
        $this->queryParameters['keepalive'] = $enabled;
        return $this;
    }

    /**
     * Add a query parameter.
     */
    protected function addQueryParameter(string $key, string $value): void
    {
        $this->queryParameters[$key] = $value;
    }

    /**
     * Build the query string.
     */
    protected function buildQueryString(string $baseUrl): string
    {
        if (empty($this->queryParameters)) {
            return $baseUrl;
        }

        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        return $baseUrl . $separator . http_build_query($this->queryParameters);
    }
} 