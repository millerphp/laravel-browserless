<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Responses;

use Psr\Http\Message\ResponseInterface;

class BQLResponse
{
    /**
     * The decoded response data.
     *
     * @var array<string,mixed>|null
     */
    protected ?array $data = null;

    /**
     * Create a new BQL response instance.
     */
    public function __construct(
        protected readonly ResponseInterface $response
    ) {
        $this->data = json_decode((string) $this->response->getBody(), true);
    }

    /**
     * Get the raw response instance.
     */
    public function response(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Get the response data.
     *
     * @return array<string,mixed>|null
     */
    public function data(): ?array
    {
        return $this->data;
    }

    /**
     * Get a specific value from the response data.
     *
     * @param  string  $key  The key to get from the response data
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The value from the response data or the default value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if the response has errors.
     */
    public function hasErrors(): bool
    {
        return isset($this->data['errors']) && ! empty($this->data['errors']);
    }

    /**
     * Get the errors from the response.
     *
     * @return array<int,array<string,mixed>>
     */
    public function errors(): array
    {
        return $this->data['errors'] ?? [];
    }
}
