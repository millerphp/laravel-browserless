<?php

namespace MillerPHP\LaravelBrowserless;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class Logger
{
    /**
     * Check if logging is enabled
     */
    public static function isEnabled(): bool
    {
        return Config::get('browserless.logging.enabled', false);
    }

    /**
     * Log a request
     */
    public static function logRequest(string $endpoint, array $data): void
    {
        if (! self::isEnabled() || ! Config::get('browserless.logging.log_requests', true)) {
            return;
        }

        $data = self::maskSensitiveData($data);

        Log::channel(Config::get('browserless.logging.channels', ['stack']))
            ->log(
                Config::get('browserless.logging.level', 'info'),
                'Browserless Request',
                [
                    'endpoint' => $endpoint,
                    'data' => $data,
                ]
            );
    }

    /**
     * Log a response
     */
    public static function logResponse(string $endpoint, $response): void
    {
        if (! self::isEnabled() || ! Config::get('browserless.logging.log_responses', true)) {
            return;
        }

        Log::channel(Config::get('browserless.logging.channels', ['stack']))
            ->log(
                Config::get('browserless.logging.level', 'info'),
                'Browserless Response',
                [
                    'endpoint' => $endpoint,
                    'response' => $response,
                ]
            );
    }

    /**
     * Log an error
     */
    public static function logError(string $endpoint, \Throwable $error): void
    {
        if (! self::isEnabled() || ! Config::get('browserless.logging.log_errors', true)) {
            return;
        }

        Log::channel(Config::get('browserless.logging.channels', ['stack']))
            ->error(
                'Browserless Error',
                [
                    'endpoint' => $endpoint,
                    'error' => [
                        'message' => $error->getMessage(),
                        'code' => $error->getCode(),
                        'file' => $error->getFile(),
                        'line' => $error->getLine(),
                    ],
                ]
            );
    }

    /**
     * Log performance metrics
     */
    public static function logPerformance(string $endpoint, array $metrics): void
    {
        if (! self::isEnabled() || ! Config::get('browserless.logging.log_performance', true)) {
            return;
        }

        Log::channel(Config::get('browserless.logging.channels', ['stack']))
            ->log(
                Config::get('browserless.logging.level', 'info'),
                'Browserless Performance',
                [
                    'endpoint' => $endpoint,
                    'metrics' => $metrics,
                ]
            );
    }

    /**
     * Mask sensitive data in the request/response
     */
    private static function maskSensitiveData(array $data): array
    {
        if (! Config::get('browserless.logging.mask_sensitive_data', true)) {
            return $data;
        }

        $sensitiveKeys = ['api_key', 'token', 'password', 'secret'];

        foreach ($data as $key => $value) {
            if (in_array($key, $sensitiveKeys)) {
                $data[$key] = '***MASKED***';
            } elseif (is_array($value)) {
                $data[$key] = self::maskSensitiveData($value);
            }
        }

        return $data;
    }
}
