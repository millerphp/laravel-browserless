<?php

namespace MillerPHP\LaravelBrowserless\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use MillerPHP\LaravelBrowserless\BrowserlessServiceProvider;
use Mockery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'MillerPHP\\Browserless\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            BrowserlessServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('browserless.token', 'test-token');
        config()->set('browserless.url', 'https://chrome.browserless.io');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }

    protected function mockClient(): ClientInterface
    {
        return Mockery::mock(ClientInterface::class);
    }

    protected function mockResponse(array $data = [], int $status = 200): ResponseInterface
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn($status);
        $response->shouldReceive('getBody->__toString')->andReturn(json_encode($data));
        $response->shouldReceive('getBody')->andReturn($response);
        $response->shouldReceive('getContents')->andReturn(json_encode($data));
        return $response;
    }
}
