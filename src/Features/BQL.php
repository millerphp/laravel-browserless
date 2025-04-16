<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use GuzzleHttp\Psr7\Request;
use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Exceptions\BQLException;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasOptions;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasQueryParameters;
use MillerPHP\LaravelBrowserless\Responses\BQLResponse;

class BQL
{
    use HasOptions;
    use HasQueryParameters;

    /**
     * Create a new BQL instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {
        $this->options = [
            'query' => '',
            'variables' => [],
            'operationName' => null,
        ];
    }

    /**
     * Set the GraphQL query.
     */
    public function query(string $query): self
    {
        // Ensure the query starts with 'mutation' or 'query'
        if (! preg_match('/^(mutation|query)\s+/i', $query)) {
            $query = 'mutation '.$query;
        }

        // Ensure proper newlines and indentation
        $query = str_replace(["\r\n", "\r"], "\n", $query);
        $query = preg_replace('/\n\s*\n/', "\n", $query); // Remove empty lines
        $query = trim($query);

        $this->options['query'] = $query;

        return $this;
    }

    /**
     * Set the variables for the query.
     *
     * @param  array<string,mixed>  $variables
     */
    public function variables(array $variables): self
    {
        $this->options['variables'] = $variables;

        return $this;
    }

    /**
     * Set the operation name for the query.
     */
    public function operationName(?string $name): self
    {
        $this->options['operationName'] = $name;

        // If the query doesn't have an operation name, add it
        if ($name && ! str_contains($this->options['query'], $name)) {
            $this->options['query'] = preg_replace(
                '/^(mutation|query)\s+/i',
                '$1 '.$name.' ',
                $this->options['query']
            );
        }

        return $this;
    }

    /**
     * Enable human-like behavior.
     */
    public function humanLike(bool $enabled = true): self
    {
        $this->addQueryParameter('humanlike', $enabled ? 'true' : 'false');

        return $this;
    }

    /**
     * Enable reconnection capability.
     */
    public function reconnect(bool $enabled = true): self
    {
        $this->addQueryParameter('reconnect', $enabled ? 'true' : 'false');

        return $this;
    }

    /**
     * Enable stealth mode.
     */
    public function stealth(bool $enabled = true): self
    {
        $this->addQueryParameter('stealth', $enabled ? 'true' : 'false');

        return $this;
    }

    /**
     * Set proxy configuration.
     */
    public function proxy(?string $proxy): self
    {
        if ($proxy) {
            $this->addQueryParameter('proxy', $proxy);
        }

        return $this;
    }

    /**
     * Set timeout in milliseconds.
     */
    public function timeout(int $milliseconds): self
    {
        $this->addQueryParameter('timeout', (string) $milliseconds);

        return $this;
    }

    /**
     * Enable block consent modals.
     */
    public function blockConsentModals(bool $enabled = true): self
    {
        $this->addQueryParameter('blockConsentModals', $enabled ? 'true' : 'false');

        return $this;
    }

    /**
     * Set wait until condition.
     */
    public function waitUntil(string $condition): self
    {
        $this->addQueryParameter('waitUntil', $condition);

        return $this;
    }

    /**
     * Send the BQL query.
     *
     * @throws BQLException
     */
    public function send(): BQLResponse
    {
        try {
            $request = new Request(
                'POST',
                $this->buildQueryString($this->client->url().'/chrome/bql?token='.$this->client->token()),
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode($this->options, JSON_THROW_ON_ERROR)
            );

            return new BQLResponse($this->client->send($request));
        } catch (\JsonException $e) {
            throw BQLException::fromResponse($e);
        } catch (\Throwable $e) {
            throw BQLException::fromResponse($e);
        }
    }
}
