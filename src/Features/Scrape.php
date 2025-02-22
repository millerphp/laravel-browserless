<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Responses\ScrapeResponse;
use MillerPHP\LaravelBrowserless\Exceptions\ScrapeException;
use GuzzleHttp\Psr7\Request;

class Scrape
{
    /**
     * The options for the scrape operation.
     *
     * @var array<string,mixed>
     */
    protected array $options = [
        'elements' => [],
        'gotoOptions' => [], // For page.goto options
    ];

    /**
     * Create a new Scrape instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {}

    /**
     * Set the URL to scrape.
     */
    public function url(string $url): self
    {
        $this->options['url'] = $url;
        return $this;
    }

    /**
     * Set the HTML content to scrape.
     */
    public function html(string $html): self
    {
        if (isset($this->options['url'])) {
            throw ScrapeException::invalidOptions('Cannot set both URL and HTML content');
        }
        $this->options['html'] = $html;
        return $this;
    }

    /**
     * Add an element to scrape.
     *
     * @param array<string,mixed> $options
     */
    public function element(string $selector, array $options = []): self
    {
        $this->options['elements'][] = array_merge(
            ['selector' => $selector],
            $options
        );
        return $this;
    }

    /**
     * Wait for a specific timeout before scraping.
     */
    public function waitForTimeout(int $milliseconds): self
    {
        $this->options['waitForTimeout'] = $milliseconds;
        return $this;
    }

    /**
     * Wait for a selector to appear before scraping.
     *
     * @param array{hidden?: bool, timeout?: int, visible?: bool} $options
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
     * Wait for a function to execute before scraping.
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
     * Wait for an event before scraping.
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
     * @param array<string,mixed> $options
     */
    public function withOptions(array $options): self
    {
        $this->options = array_merge_recursive($this->options, $options);
        return $this;
    }

    /**
     * Send the scrape request.
     *
     * @throws ScrapeException
     */
    public function send(): ScrapeResponse
    {
        $this->validateOptions();

        try {
            $request = new Request(
                'POST',
                $this->client->url() . '/scrape?token=' . $this->client->token(),
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode($this->options, JSON_THROW_ON_ERROR)
            );

            return new ScrapeResponse($this->client->send($request));
        } catch (\JsonException $e) {
            throw ScrapeException::fromResponse($e);
        } catch (\Throwable $e) {
            throw ScrapeException::fromResponse($e);
        }
    }

    /**
     * Validate the scrape options.
     *
     * @throws ScrapeException
     */
    protected function validateOptions(): void
    {
        if (!isset($this->options['url']) && !isset($this->options['html'])) {
            throw ScrapeException::invalidOptions('Either URL or HTML content must be provided');
        }

        if (empty($this->options['elements'])) {
            throw ScrapeException::invalidOptions('At least one element selector must be provided');
        }
    }
} 