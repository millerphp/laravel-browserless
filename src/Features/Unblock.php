<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use GuzzleHttp\Psr7\Request;
use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Exceptions\UnblockException;
use MillerPHP\LaravelBrowserless\Responses\UnblockResponse;

class Unblock
{
    /**
     * The options for the unblock operation.
     *
     * @var array<string,mixed>
     */
    protected array $options = [
        'gotoOptions' => [], // For page.goto options
        'browserWSEndpoint' => false,
        'cookies' => false,
        'content' => false,
        'screenshot' => false,
    ];

    /**
     * Create a new Unblock instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {}

    /**
     * Set the URL to unblock.
     */
    public function url(string $url): self
    {
        $this->options['url'] = $url;

        return $this;
    }

    /**
     * Request a browser WebSocket endpoint.
     */
    public function browserWSEndpoint(bool $enabled = true): self
    {
        $this->options['browserWSEndpoint'] = $enabled;

        return $this;
    }

    /**
     * Request cookies in the response.
     */
    public function cookies(bool $enabled = true): self
    {
        $this->options['cookies'] = $enabled;

        return $this;
    }

    /**
     * Request HTML content in the response.
     */
    public function content(bool $enabled = true): self
    {
        $this->options['content'] = $enabled;

        return $this;
    }

    /**
     * Request a screenshot in the response.
     */
    public function screenshot(bool $enabled = true): self
    {
        $this->options['screenshot'] = $enabled;

        return $this;
    }

    /**
     * Set the TTL for the browser instance.
     */
    public function ttl(int $milliseconds): self
    {
        $this->options['ttl'] = $milliseconds;

        return $this;
    }

    /**
     * Wait for a specific event before continuing.
     */
    public function waitForEvent(string $event, ?int $timeout = null): self
    {
        $this->options['waitForEvent'] = [
            'event' => $event,
            'timeout' => $timeout,
        ];

        return $this;
    }

    /**
     * Wait for a function to execute before continuing.
     */
    public function waitForFunction(string $function, ?int $timeout = null): self
    {
        $this->options['waitForFunction'] = [
            'fn' => $function,
            'timeout' => $timeout,
        ];

        return $this;
    }

    /**
     * Wait for a selector to appear before continuing.
     *
     * @param  array{hidden?: bool, timeout?: int, visible?: bool}  $options
     */
    public function waitForSelector(string $selector, array $options = []): self
    {
        $this->options['waitForSelector'] = array_merge(
            ['selector' => $selector],
            $options
        );

        return $this;
    }

    /**
     * Set authentication credentials.
     */
    public function authenticate(string $username, string $password): self
    {
        $this->options['authenticate'] = [
            'username' => $username,
            'password' => $password,
        ];

        return $this;
    }

    /**
     * Set whether to ignore HTTPS errors.
     */
    public function ignoreHTTPSErrors(bool $ignore = true): self
    {
        $this->options['ignoreHTTPSErrors'] = $ignore;

        return $this;
    }

    /**
     * Set multiple options at once.
     *
     * @param  array<string,mixed>  $options
     */
    public function withOptions(array $options): self
    {
        $this->options = array_merge_recursive($this->options, $options);

        return $this;
    }

    /**
     * Send the unblock request.
     *
     * @throws UnblockException
     */
    public function send(): UnblockResponse
    {
        $this->validateOptions();

        try {
            $request = new Request(
                'POST',
                $this->client->url().'/unblock?token='.$this->client->token(),
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode($this->options, JSON_THROW_ON_ERROR)
            );

            return new UnblockResponse($this->client->send($request));
        } catch (\JsonException $e) {
            throw UnblockException::fromResponse($e);
        } catch (\Throwable $e) {
            throw UnblockException::fromResponse($e);
        }
    }

    /**
     * Validate the unblock options.
     *
     * @throws UnblockException
     */
    protected function validateOptions(): void
    {
        if (! isset($this->options['url'])) {
            throw UnblockException::invalidOptions('URL must be provided');
        }
    }
}
