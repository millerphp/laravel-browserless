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
     * The options for the BQL query.
     *
     * @var array<string,mixed>
     */
    protected array $options = [
        'query' => '',
        'variables' => [],
        'operationName' => null,
    ];

    /**
     * Create a new BQL instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {}

    /**
     * Set the GraphQL query.
     */
    public function query(string $query): self
    {
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
     * Set the operation name.
     */
    public function operationName(?string $name): self
    {
        $this->options['operationName'] = $name;

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
