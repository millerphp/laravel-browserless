<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use Psr\Http\Message\ResponseInterface;
use MillerPHP\LaravelBrowserless\Exceptions\ContentException;

class ContentResponse
{
    /**
     * Create a new Content Response instance.
     */
    public function __construct(
        protected readonly ResponseInterface $response
    ) {}

    /**
     * Get the raw HTML content.
     */
    public function content(): string
    {
        return (string) $this->response->getBody();
    }

    /**
     * Save the content to a file.
     *
     * @throws ContentException
     */
    public function save(string $path): bool
    {
        try {
            $result = file_put_contents($path, $this->content());
            
            if ($result === false) {
                throw new \RuntimeException("Failed to save content to {$path}");
            }

            return true;
        } catch (\Throwable $e) {
            throw ContentException::fromResponse($e);
        }
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