<?php

use MillerPHP\LaravelBrowserless\Features\Performance;
use Psr\Http\Message\RequestInterface;

beforeEach(function () {
    $this->client = test()->mockClient();
    $this->performance = new Performance($this->client);
});

it('can analyze URL performance', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $request->getMethod() === 'POST' &&
                   $request->getUri()->getPath() === '/performance' &&
                   $body['url'] === 'https://example.com';
        })
        ->andReturn(test()->mockResponse([
            'categories' => [
                'performance' => ['score' => 0.95],
            ],
        ]));

    $result = $this->performance
        ->url('https://example.com')
        ->send();

    expect($result->response())
        ->toBeValidResponse()
        ->and($result->categoryScore('performance'))
        ->toBe(0.95);
});

it('can set specific categories to analyze', function () {
    $categories = ['performance', 'accessibility', 'best-practices', 'seo'];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($categories) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['categories'] === $categories;
        })
        ->andReturn(test()->mockResponse(['categories' => []]));

    $this->performance
        ->url('https://example.com')
        ->categories($categories)
        ->send();
});

it('can set specific audits to run', function () {
    $audits = ['first-contentful-paint', 'largest-contentful-paint', 'total-blocking-time'];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($audits) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['audits'] === $audits;
        })
        ->andReturn(test()->mockResponse(['audits' => []]));

    $this->performance
        ->url('https://example.com')
        ->audits($audits)
        ->send();
});

it('can handle detailed performance results', function () {
    $response = [
        'categories' => [
            'performance' => [
                'score' => 0.95,
                'title' => 'Performance',
            ],
            'accessibility' => [
                'score' => 0.88,
                'title' => 'Accessibility',
            ],
        ],
        'audits' => [
            'first-contentful-paint' => [
                'score' => 0.9,
                'displayValue' => '1.2 s',
                'numericValue' => 1234,
            ],
            'largest-contentful-paint' => [
                'score' => 0.85,
                'displayValue' => '2.1 s',
                'numericValue' => 2123,
            ],
        ],
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse($response));

    $result = $this->performance
        ->url('https://example.com')
        ->send();

    expect($result->categoryScore('performance'))
        ->toBe(0.95)
        ->and($result->categoryScore('accessibility'))
        ->toBe(0.88)
        ->and($result->auditResult('first-contentful-paint'))
        ->toHaveKey('score', 0.9)
        ->and($result->auditResult('largest-contentful-paint'))
        ->toHaveKey('displayValue', '2.1 s');
});

it('can set throttling options', function () {
    $throttling = [
        'throughput' => [
            'download' => 1000000,
            'upload' => 500000,
        ],
        'latency' => 100,
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($throttling) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['throttling'] === $throttling;
        })
        ->andReturn(test()->mockResponse([]));

    $this->performance
        ->url('https://example.com')
        ->throttling($throttling)
        ->send();
});

it('can set device emulation', function () {
    $device = [
        'name' => 'iPhone X',
        'userAgent' => 'iPhone User Agent',
        'viewport' => [
            'width' => 375,
            'height' => 812,
            'deviceScaleFactor' => 3,
            'isMobile' => true,
        ],
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($device) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['device'] === $device;
        })
        ->andReturn(test()->mockResponse([]));

    $this->performance
        ->url('https://example.com')
        ->device($device)
        ->send();
});

it('validates required URL before sending', function () {
    expect(fn () => $this->performance->send())
        ->toThrow(InvalidArgumentException::class, 'URL must be provided');
});

it('can handle performance analysis errors', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andThrow(new \Exception('Performance analysis failed'));

    expect(fn () => $this->performance->url('https://example.com')->send())
        ->toThrow(\Exception::class, 'Performance analysis failed');
});

it('can combine multiple options', function () {
    $categories = ['performance', 'accessibility'];
    $audits = ['first-contentful-paint'];
    $throttling = ['latency' => 100];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($categories, $audits, $throttling) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['url'] === 'https://example.com' &&
                   $body['categories'] === $categories &&
                   $body['audits'] === $audits &&
                   $body['throttling'] === $throttling;
        })
        ->andReturn(test()->mockResponse([]));

    $this->performance
        ->url('https://example.com')
        ->categories($categories)
        ->audits($audits)
        ->throttling($throttling)
        ->send();
});

it('can handle missing category scores', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse(['categories' => []]));

    $result = $this->performance
        ->url('https://example.com')
        ->send();

    expect($result->categoryScore('performance'))
        ->toBeNull();
});

it('can handle missing audit results', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse(['audits' => []]));

    $result = $this->performance
        ->url('https://example.com')
        ->send();

    expect($result->auditResult('first-contentful-paint'))
        ->toBeNull();
});

it('can get all category scores', function () {
    $categories = [
        'performance' => ['score' => 0.95],
        'accessibility' => ['score' => 0.88],
        'best-practices' => ['score' => 1.0],
        'seo' => ['score' => 0.92],
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse(['categories' => $categories]));

    $result = $this->performance
        ->url('https://example.com')
        ->send();

    expect($result->categories())
        ->toBe($categories)
        ->and($result->categoryScore('performance'))
        ->toBe(0.95)
        ->and($result->categoryScore('seo'))
        ->toBe(0.92);
});
