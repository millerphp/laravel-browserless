<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use MillerPHP\LaravelBrowserless\Exceptions\DownloadException;
use Psr\Http\Message\ResponseInterface;

class DownloadResponse
{
    /**
     * Create a new Download Response instance.
     */
    public function __construct(
        protected readonly ResponseInterface $response
    ) {}

    /**
     * Get the raw file content.
     */
    public function content(): string
    {
        return (string) $this->response->getBody();
    }

    /**
     * Save the file to disk.
     *
     * @throws DownloadException
     */
    public function save(string $path): bool
    {
        try {
            $result = file_put_contents($path, $this->content());

            if ($result === false) {
                throw new \RuntimeException("Failed to save file to {$path}");
            }

            return true;
        } catch (\Throwable $e) {
            throw DownloadException::fromResponse($e);
        }
    }

    /**
     * Save the file and return its path.
     *
     * @throws DownloadException
     */
    public function saveAs(string $path): string
    {
        $this->save($path);

        return $path;
    }

    /**
     * Get the file size in bytes.
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
     * Stream the file to the browser for download.
     */
    public function download(?string $filename = null): never
    {
        if (headers_sent()) {
            throw new \RuntimeException('Headers have already been sent');
        }

        $filename = $filename ?? 'download-'.date('Y-m-d-His');
        $contentType = $this->response->getHeaderLine('Content-Type') ?: 'application/octet-stream';

        header('Content-Type: '.$contentType);
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Length: '.$this->size());
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $this->content();
        exit;
    }
}
