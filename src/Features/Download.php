<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use GuzzleHttp\Psr7\Request;
use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Exceptions\DownloadException;
use MillerPHP\LaravelBrowserless\Responses\DownloadResponse;

class Download
{
    /**
     * The options for the download operation.
     *
     * @var array<string,mixed>
     */
    protected array $options = [
        'gotoOptions' => [], // For page.goto options
    ];

    /**
     * Create a new Download instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {}

    /**
     * Set the JavaScript code to execute.
     */
    public function code(string $code): self
    {
        $this->options['code'] = $code;

        return $this;
    }

    /**
     * Set context values for the code execution.
     *
     * @param  array<string,mixed>  $context
     */
    public function context(array $context): self
    {
        $this->options['context'] = $context;

        return $this;
    }

    /**
     * Set whether to wait for networkidle0 event.
     */
    public function waitForNetworkIdle(bool $wait = true): self
    {
        $this->options['gotoOptions']['waitUntil'] = $wait ? 'networkidle0' : 'load';

        return $this;
    }

    /**
     * Set the navigation timeout in milliseconds.
     */
    public function timeout(int $milliseconds): self
    {
        $this->options['gotoOptions']['timeout'] = $milliseconds;

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
     * Set cookies for the page.
     *
     * @param  array<array{name: string, value: string, domain: string}>  $cookies
     */
    public function cookies(array $cookies): self
    {
        $this->options['cookies'] = $cookies;

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
     * Send the download request.
     *
     * @throws DownloadException
     */
    public function send(): DownloadResponse
    {
        $this->validateOptions();

        try {
            $request = new Request(
                'POST',
                $this->client->url().'/download?token='.$this->client->token(),
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode($this->options, JSON_THROW_ON_ERROR)
            );

            return new DownloadResponse($this->client->send($request));
        } catch (\JsonException $e) {
            throw DownloadException::fromResponse($e);
        } catch (\Throwable $e) {
            throw DownloadException::fromResponse($e);
        }
    }

    /**
     * Validate the download options.
     *
     * @throws DownloadException
     */
    protected function validateOptions(): void
    {
        if (! isset($this->options['code'])) {
            throw DownloadException::invalidOptions('JavaScript code must be provided');
        }
    }
}
