<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use Psr\Http\Message\ResponseInterface;
use MillerPHP\LaravelBrowserless\Exceptions\PDFGenerationException;

class PDFResponse
{
    /**
     * Create a new PDF Response instance.
     */
    public function __construct(
        protected readonly ResponseInterface $response
    ) {}

    /**
     * Get the raw PDF content.
     */
    public function content(): string
    {
        return (string) $this->response->getBody();
    }

    /**
     * Save the PDF to a file.
     *
     * @throws PDFGenerationException
     */
    public function save(string $path): bool
    {
        try {
            $result = file_put_contents($path, $this->content());
            
            if ($result === false) {
                throw new \RuntimeException("Failed to save PDF to {$path}");
            }

            return true;
        } catch (\Throwable $e) {
            throw PDFGenerationException::fromResponse($e);
        }
    }

    /**
     * Save the PDF to a file and return its path.
     *
     * @throws \RuntimeException
     */
    public function saveAs(string $path): string
    {
        $this->save($path);
        return $path;
    }

    /**
     * Get the PDF file size in bytes.
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
     * Stream the PDF to the browser for download.
     *
     * @param string|null $filename The filename to suggest in the download
     */
    public function download(?string $filename = null): never
    {
        if (headers_sent()) {
            throw new \RuntimeException('Headers have already been sent');
        }

        $filename = $filename ?? 'document-' . date('Y-m-d-His') . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $this->size());
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $this->content();
        exit;
    }

    /**
     * Stream the PDF to the browser for inline display.
     *
     * @param string|null $filename The filename to suggest if the user chooses to download
     */
    public function display(?string $filename = null): never
    {
        if (headers_sent()) {
            throw new \RuntimeException('Headers have already been sent');
        }

        $filename = $filename ?? 'document-' . date('Y-m-d-His') . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . $this->size());
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $this->content();
        exit;
    }
} 