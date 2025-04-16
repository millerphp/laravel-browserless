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
        try {
            $this->data = json_decode((string) $this->response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->data = null;
        }
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
        if ($this->data === null) {
            try {
                $this->data = json_decode((string) $this->response->getBody(), true, 512, JSON_THROW_ON_ERROR);

                \Log::debug('BQL Response Data', [
                    'status_code' => $this->response->getStatusCode(),
                    'data_structure' => array_keys($this->data),
                    'has_errors' => $this->hasErrors(),
                    'has_data' => $this->hasData(),
                    'full_response' => $this->data,
                    'raw_body' => (string) $this->response->getBody(),
                ]);
            } catch (\JsonException $e) {
                \Log::error('Failed to decode BQL response', [
                    'error' => $e->getMessage(),
                    'response_body' => (string) $this->response->getBody(),
                    'status_code' => $this->response->getStatusCode(),
                ]);

                $this->data = null;
            }
        }

        return $this->data;
    }

    /**
     * Get a specific value from the response data using dot notation.
     *
     * @param  string  $key  The key to get from the response data
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The value from the response data or the default value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->data === null) {
            \Log::warning('Attempted to get value from null BQL response data', [
                'key' => $key,
                'default' => $default,
            ]);

            return $default;
        }

        $keys = explode('.', $key);
        $value = $this->data;

        foreach ($keys as $k) {
            if (! is_array($value) || ! array_key_exists($k, $value)) {
                \Log::debug('Key not found in BQL response data', [
                    'key' => $k,
                    'path' => $key,
                    'available_keys' => is_array($value) ? array_keys($value) : 'not an array',
                    'full_response' => $this->data,
                    'current_value' => $value,
                    'response_status' => $this->status(),
                    'has_errors' => $this->hasErrors(),
                    'errors' => $this->errors(),
                ]);

                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Check if the response has errors.
     */
    public function hasErrors(): bool
    {
        $hasErrors = isset($this->data['errors']) && ! empty($this->data['errors']);

        if ($hasErrors) {
            \Log::warning('BQL response contains errors', [
                'errors' => $this->data['errors'],
            ]);
        }

        return $hasErrors;
    }

    /**
     * Get the errors from the response.
     *
     * @return array<int,array<string,mixed>>
     */
    public function errors(): array
    {
        $errors = $this->data['errors'] ?? [];

        if (! empty($errors)) {
            \Log::warning('Retrieved BQL response errors', [
                'error_count' => count($errors),
                'errors' => $errors,
            ]);
        }

        return $errors;
    }

    /**
     * Get the first error message from the response.
     */
    public function firstError(): ?string
    {
        $error = $this->errors()[0]['message'] ?? null;

        if ($error !== null) {
            \Log::warning('First BQL error message', [
                'error' => $error,
            ]);
        }

        return $error;
    }

    /**
     * Check if the response has data.
     */
    public function hasData(): bool
    {
        $hasData = isset($this->data['data']) && ! empty($this->data['data']);

        if (! $hasData) {
            \Log::warning('BQL response has no data', [
                'data_structure' => array_keys($this->data ?? []),
            ]);
        }

        return $hasData;
    }

    /**
     * Get the response data under the 'data' key.
     *
     * @return array<string,mixed>|null
     */
    public function getData(): ?array
    {
        $data = $this->data['data'] ?? null;

        if ($data === null) {
            \Log::warning('No data found in BQL response', [
                'data_structure' => array_keys($this->data ?? []),
            ]);
        }

        return $data;
    }

    /**
     * Check if the response is successful.
     */
    public function successful(): bool
    {
        $successful = ! $this->hasErrors() && $this->hasData();

        if (! $successful) {
            \Log::warning('BQL response was not successful', [
                'has_errors' => $this->hasErrors(),
                'has_data' => $this->hasData(),
                'status_code' => $this->status(),
            ]);
        }

        return $successful;
    }

    /**
     * Get the HTTP status code.
     */
    public function status(): int
    {
        return $this->response->getStatusCode();
    }
}
