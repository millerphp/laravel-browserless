<?php

use MillerPHP\LaravelBrowserless\Features\Content;
use Psr\Http\Message\RequestInterface;

beforeEach(function () {
    $this->client = test()->mockClient();
    $this->content = new Content($this->client);
});

it('can capture content from URL', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $request->getMethod() === 'POST' &&
                   $request->getUri()->getPath() === '/content' &&
                   $body['url'] === 'https://example.com';
        })
        ->andReturn(test()->mockResponse(['content' => '<html></html>']));

    $result = $this->content
        ->url('https://example.com')
        ->send();

    expect($result->response())
        ->toBeValidResponse();
});

it('can capture content from HTML', function () {
    $html = '<html><body><h1>Test</h1></body></html>';

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($html) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['html'] === $html;
        })
        ->andReturn(test()->mockResponse(['content' => $html]));

    $this->content
        ->html($html)
        ->send();
});

it('can wait for network idle', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['gotoOptions']['waitUntil'] === 'networkidle0';
        })
        ->andReturn(test()->mockResponse(['content' => '']));

    $this->content
        ->url('https://example.com')
        ->waitForNetworkIdle()
        ->send();
});

it('can reject resource types', function () {
    $types = ['image', 'stylesheet', 'script'];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($types) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['rejectResourceTypes'] === $types;
        })
        ->andReturn(test()->mockResponse(['content' => '']));

    $this->content
        ->url('https://example.com')
        ->rejectResourceTypes($types)
        ->send();
});

it('can reject request patterns', function () {
    $patterns = ['/\.css$/', '/\.js$/'];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($patterns) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['rejectRequestPattern'] === $patterns;
        })
        ->andReturn(test()->mockResponse(['content' => '']));

    $this->content
        ->url('https://example.com')
        ->rejectRequestPatterns($patterns)
        ->send();
});

it('can enable best attempt mode', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['bestAttempt'] === true;
        })
        ->andReturn(test()->mockResponse(['content' => '']));

    $this->content
        ->url('https://example.com')
        ->bestAttempt()
        ->send();
});

it('can wait for event', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            $event = $body['waitForEvent'];
            return $event['event'] === 'load' && $event['timeout'] === 5000;
        })
        ->andReturn(test()->mockResponse(['content' => '']));

    $this->content
        ->url('https://example.com')
        ->waitForEvent('load', 5000)
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
        ->andReturn(test()->mockResponse(['content' => '']));

    $this->content
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
            return $waitFor['selector'] === '.ready' &&
                   $waitFor['timeout'] === 5000 &&
                   $waitFor['visible'] === true;
        })
        ->andReturn(test()->mockResponse(['content' => '']));

    $this->content
        ->url('https://example.com')
        ->waitForSelector('.ready', 5000, false, true)
        ->send();
});

it('can extract elements', function () {
    $elements = [
        [
            'selector' => '.item',
            'attribute' => 'href',
            'multiple' => true
        ]
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($elements) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['extractElements'] === $elements;
        })
        ->andReturn(test()->mockResponse(['content' => '']));

    $this->content
        ->url('https://example.com')
        ->extractElements($elements)
        ->send();
});

it('can set content options', function () {
    $options = [
        'stripComments' => true,
        'minify' => true,
        'removeScripts' => true
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($options) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['contentOptions'] === $options;
        })
        ->andReturn(test()->mockResponse(['content' => '']));

    $this->content
        ->url('https://example.com')
        ->contentOptions($options)
        ->send();
});

it('validates required options before sending', function () {
    expect(fn () => $this->content->send())
        ->toThrow(InvalidArgumentException::class, 'Either URL or HTML content must be provided');
});

it('can combine multiple options', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            return $body['url'] === 'https://example.com' &&
                   $body['gotoOptions']['waitUntil'] === 'networkidle0' &&
                   $body['rejectResourceTypes'] === ['image'] &&
                   $body['bestAttempt'] === true;
        })
        ->andReturn(test()->mockResponse(['content' => '']));

    $this->content
        ->url('https://example.com')
        ->waitForNetworkIdle()
        ->rejectResourceTypes(['image'])
        ->bestAttempt()
        ->send();
}); 