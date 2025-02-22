<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use Psr\Http\Message\ResponseInterface;
use MillerPHP\LaravelBrowserless\Exceptions\ScreenshotException;

class ScreenshotResponse
{
    /**
     * Create a new Screenshot Response instance.
     */
    public function __construct(
        protected readonly ResponseInterface $response
    ) {}

    /**
     * Get the raw image content.
     */
    public function content(): string
    {
        return (string) $this->response->getBody();
    }

    /**
     * Save the screenshot to a file.
     *
     * @throws ScreenshotException
     */
    public function save(string $path): bool
    {
        try {
            $result = file_put_contents($path, $this->content());
            
            if ($result === false) {
                throw new \RuntimeException("Failed to save screenshot to {$path}");
            }

            return true;
        } catch (\Throwable $e) {
            throw ScreenshotException::fromResponse($e);
        }
    }

    /**
     * Save the screenshot to a file and return its path.
     *
     * @throws ScreenshotException
     */
    public function saveAs(string $path): string
    {
        $this->save($path);
        return $path;
    }

    /**
     * Get the image file size in bytes.
     */
    public function size(): int
    {
        return (int) $this->response->getHeaderLine('Content-Length') ?: strlen($this->content());
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

    /**
     * Stream the image to the browser for download.
     */
    public function download(?string $filename = null): never
    {
        if (headers_sent()) {
            throw new \RuntimeException('Headers have already been sent');
        }

        $filename = $filename ?? 'screenshot-' . date('Y-m-d-His') . '.png';
        $contentType = $this->getContentType();

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $this->size());
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $this->content();
        exit;
    }

    /**
     * Stream the image to the browser for display.
     */
    public function display(?string $filename = null): never
    {
        if (headers_sent()) {
            throw new \RuntimeException('Headers have already been sent');
        }

        $filename = $filename ?? 'screenshot-' . date('Y-m-d-His') . '.png';
        $contentType = $this->getContentType();

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . $this->size());
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $this->content();
        exit;
    }

    /**
     * Get the content type based on the response headers.
     */
    protected function getContentType(): string
    {
        $contentType = $this->response->getHeaderLine('Content-Type');
        
        if (empty($contentType)) {
            // Default to PNG if no content type is set
            return 'image/png';
        }

        return $contentType;
    }
} 