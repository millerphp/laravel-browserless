<?php

use MillerPHP\LaravelBrowserless\Features\Screenshot;
use Psr\Http\Message\RequestInterface;

beforeEach(function () {
    $this->client = test()->mockClient();
    $this->screenshot = new Screenshot($this->client);
});

it('can take screenshot from URL', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $request->getMethod() === 'POST' &&
                   $request->getUri()->getPath() === '/screenshot' &&
                   $body['url'] === 'https://example.com';
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $result = $this->screenshot
        ->url('https://example.com')
        ->send();

    expect($result->response())
        ->toBeValidResponse();
});

it('can take screenshot from HTML content', function () {
    $html = '<html><body><h1>Test</h1></body></html>';

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($html) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['html'] === $html;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->screenshot
        ->html($html)
        ->send();
});

it('can set screenshot type', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['type'] === 'jpeg';
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->screenshot
        ->url('https://example.com')
        ->type('jpeg')
        ->send();
});

it('validates screenshot type', function () {
    expect(fn () => $this->screenshot->type('invalid'))
        ->toThrow(InvalidArgumentException::class, 'Type must be jpeg, png, or webp');
});

it('can set quality', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['quality'] === 80;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->screenshot
        ->url('https://example.com')
        ->type('jpeg')
        ->quality(80)
        ->send();
});

it('validates quality range', function () {
    expect(fn () => $this->screenshot->quality(101))
        ->toThrow(InvalidArgumentException::class, 'Quality must be between 0 and 100');

    expect(fn () => $this->screenshot->quality(-1))
        ->toThrow(InvalidArgumentException::class, 'Quality must be between 0 and 100');
});

it('can enable full page screenshot', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['fullPage'] === true;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->screenshot
        ->url('https://example.com')
        ->fullPage()
        ->send();
});

it('can set clip area', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['clip'] === [
                'x' => 0,
                'y' => 0,
                'width' => 1920,
                'height' => 1080,
            ];
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->screenshot
        ->url('https://example.com')
        ->clip(x: 0, y: 0, width: 1920, height: 1080)
        ->send();
});

it('can set viewport', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            $viewport = $body['viewport'];

            return $viewport['width'] === 1920 &&
                   $viewport['height'] === 1080 &&
                   $viewport['deviceScaleFactor'] === 2;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->screenshot
        ->url('https://example.com')
        ->viewport(1920, 1080, 2)
        ->send();
});

it('can emulate device', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['deviceName'] === 'iPhone X';
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->screenshot
        ->url('https://example.com')
        ->device('iPhone X')
        ->send();
});

it('can set encoding', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['encoding'] === 'base64';
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->screenshot
        ->url('https://example.com')
        ->encoding('base64')
        ->send();
});

it('validates encoding type', function () {
    expect(fn () => $this->screenshot->encoding('invalid'))
        ->toThrow(InvalidArgumentException::class, 'Encoding must be binary or base64');
});

it('can set optimization options', function () {
    $jpegOptions = ['progressive' => true];
    $pngOptions = ['compressionLevel' => 9];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($jpegOptions, $pngOptions) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['jpegOptimization'] === $jpegOptions &&
                   $body['pngOptimization'] === $pngOptions;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->screenshot
        ->url('https://example.com')
        ->jpegOptimization($jpegOptions)
        ->pngOptimization($pngOptions)
        ->send();
});

it('can capture specific element', function () {
    $options = [
        'selector' => '.header',
        'padding' => 10,
        'scrollIntoView' => true,
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($options) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['elementScreenshot'] === $options;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->screenshot
        ->url('https://example.com')
        ->elementScreenshot($options)
        ->send();
});

it('validates required options before sending', function () {
    expect(fn () => $this->screenshot->send())
        ->toThrow(InvalidArgumentException::class, 'Either URL or HTML content must be provided');
});

it('can combine multiple options', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['url'] === 'https://example.com' &&
                   $body['options']['type'] === 'jpeg' &&
                   $body['options']['quality'] === 80 &&
                   $body['options']['fullPage'] === true &&
                   $body['encoding'] === 'base64';
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->screenshot
        ->url('https://example.com')
        ->type('jpeg')
        ->quality(80)
        ->fullPage()
        ->encoding('base64')
        ->send();
});
