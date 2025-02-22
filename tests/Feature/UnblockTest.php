<?php

use MillerPHP\LaravelBrowserless\Features\Unblock;
use Psr\Http\Message\RequestInterface;

beforeEach(function () {
    $this->client = test()->mockClient();
    $this->unblock = new Unblock($this->client);
});

it('can unblock URL access', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $request->getMethod() === 'POST' &&
                   $request->getUri()->getPath() === '/unblock' &&
                   $body['url'] === 'https://example.com';
        })
        ->andReturn(test()->mockResponse(['content' => '<html></html>']));

    $result = $this->unblock
        ->url('https://example.com')
        ->send();

    expect($result->response())
        ->toBeValidResponse();
});

it('can request browser WebSocket endpoint', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['browserWSEndpoint'] === true;
        })
        ->andReturn(test()->mockResponse(['wsEndpoint' => 'ws://example.com']));

    $result = $this->unblock
        ->url('https://example.com')
        ->browserWSEndpoint()
        ->send();

    expect($result->wsEndpoint())
        ->toBe('ws://example.com');
});

it('can request HTML content', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['content'] === true;
        })
        ->andReturn(test()->mockResponse(['content' => '<html></html>']));

    $result = $this->unblock
        ->url('https://example.com')
        ->content()
        ->send();

    expect($result->content())
        ->toBe('<html></html>');
});

it('can request cookies', function () {
    $cookies = [
        ['name' => 'test', 'value' => 'value', 'domain' => 'example.com']
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['cookies'] === true;
        })
        ->andReturn(test()->mockResponse(['cookies' => $cookies]));

    $result = $this->unblock
        ->url('https://example.com')
        ->cookies()
        ->send();

    expect($result->cookies())
        ->toBe($cookies);
});

it('can request screenshot', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['screenshot'] === true;
        })
        ->andReturn(test()->mockResponse(['screenshot' => base64_encode('image data')]));

    $result = $this->unblock
        ->url('https://example.com')
        ->screenshot()
        ->send();

    expect($result->screenshot())
        ->toBe(base64_encode('image data'));
});

it('can set TTL', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['ttl'] === 30000;
        })
        ->andReturn(test()->mockResponse([]));

    $this->unblock
        ->url('https://example.com')
        ->ttl(30000)
        ->send();
});

it('can wait for event', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            $event = $body['waitForEvent'];
            return $event['event'] === 'networkidle0' && $event['timeout'] === 5000;
        })
        ->andReturn(test()->mockResponse([]));

    $this->unblock
        ->url('https://example.com')
        ->waitForEvent('networkidle0', 5000)
        ->send();
});

it('can wait for function', function () {
    $function = 'return document.readyState === "complete"';

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($function) {
            $body = json_decode($request->getBody()->getContents(), true);
            $waitFor = $body['waitForFunction'];
            return $waitFor['fn'] === $function && $waitFor['timeout'] === 5000;
        })
        ->andReturn(test()->mockResponse([]));

    $this->unblock
        ->url('https://example.com')
        ->waitForFunction($function, 5000)
        ->send();
});

it('can wait for selector', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            $waitFor = $body['waitForSelector'];
            return $waitFor['selector'] === '.content' &&
                   $waitFor['timeout'] === 5000 &&
                   $waitFor['visible'] === true;
        })
        ->andReturn(test()->mockResponse([]));

    $this->unblock
        ->url('https://example.com')
        ->waitForSelector('.content', ['timeout' => 5000, 'visible' => true])
        ->send();
});

it('validates required URL before sending', function () {
    expect(fn () => $this->unblock->send())
        ->toThrow(InvalidArgumentException::class, 'URL must be provided');
});

it('can handle unblock errors', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andThrow(new \Exception('Unblock failed'));

    expect(fn () => $this->unblock->url('https://example.com')->send())
        ->toThrow(\Exception::class, 'Unblock failed');
});

it('can combine multiple options', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['url'] === 'https://example.com' &&
                   $body['content'] === true &&
                   $body['cookies'] === true &&
                   $body['screenshot'] === true &&
                   $body['ttl'] === 30000;
        })
        ->andReturn(test()->mockResponse([
            'content' => '<html></html>',
            'cookies' => [],
            'screenshot' => base64_encode('image data')
        ]));

    $result = $this->unblock
        ->url('https://example.com')
        ->content()
        ->cookies()
        ->screenshot()
        ->ttl(30000)
        ->send();

    expect($result->content())
        ->toBe('<html></html>')
        ->and($result->cookies())
        ->toBeArray()
        ->and($result->screenshot())
        ->toBe(base64_encode('image data'));
});

it('can handle empty responses', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse([]));

    $result = $this->unblock
        ->url('https://example.com')
        ->send();

    expect($result->content())
        ->toBeNull()
        ->and($result->cookies())
        ->toBeNull()
        ->and($result->screenshot())
        ->toBeNull()
        ->and($result->wsEndpoint())
        ->toBeNull();
}); 