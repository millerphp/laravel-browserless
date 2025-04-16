<?php

use MillerPHP\LaravelBrowserless\Features\ExecuteFunction;
use Psr\Http\Message\RequestInterface;

beforeEach(function () {
    $this->client = test()->mockClient();
    $this->function = new ExecuteFunction($this->client);
});

it('can execute JavaScript code', function () {
    $code = 'export default async function ({ page }) { return await page.title(); }';

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($code) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $request->getMethod() === 'POST' &&
                   $request->getUri()->getPath() === '/function' &&
                   $body['code'] === $code;
        })
        ->andReturn(test()->mockResponse(['data' => ['title' => 'Test Page']]));

    $result = $this->function
        ->code($code)
        ->send();

    expect($result->response())
        ->toBeValidResponse()
        ->and($result->data())
        ->toHaveKey('title', 'Test Page');
});

it('can set context values', function () {
    $context = ['url' => 'https://example.com', 'selector' => '.title'];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($context) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['context'] === $context;
        })
        ->andReturn(test()->mockResponse(['data' => []]));

    $this->function
        ->code('export default async function() {}')
        ->context($context)
        ->send();
});

it('can set execution context options', function () {
    $options = [
        'isolateFromGlobalScope' => true,
        'contextId' => 'test-context',
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($options) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['executionContext'] === $options;
        })
        ->andReturn(test()->mockResponse(['data' => []]));

    $this->function
        ->code('export default async function() {}')
        ->executionContext($options)
        ->send();
});

it('can add external modules', function () {
    $modules = [
        ['url' => 'https://esm.sh/lodash'],
        ['url' => 'https://esm.sh/moment'],
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($modules) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['addModules'] === $modules;
        })
        ->andReturn(test()->mockResponse(['data' => []]));

    $this->function
        ->code('export default async function() {}')
        ->addModules($modules)
        ->send();
});

it('can set evaluation timeout', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['evaluationTimeout'] === 5000;
        })
        ->andReturn(test()->mockResponse(['data' => []]));

    $this->function
        ->code('export default async function() {}')
        ->evaluationTimeout(5000)
        ->send();
});

it('can handle function execution errors', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andThrow(new \Exception('Execution failed'));

    expect(fn () => $this->function->code('export default async function() {}')->send())
        ->toThrow(\Exception::class, 'Execution failed');
});

it('validates required code before sending', function () {
    expect(fn () => $this->function->send())
        ->toThrow(InvalidArgumentException::class, 'Code must be provided');
});

it('can handle complex return values', function () {
    $returnValue = [
        'string' => 'test',
        'number' => 42,
        'boolean' => true,
        'array' => [1, 2, 3],
        'object' => ['key' => 'value'],
        'null' => null,
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse(['data' => $returnValue]));

    $result = $this->function
        ->code('export default async function() {}')
        ->send();

    expect($result->data())
        ->toBe($returnValue);
});

it('can execute code with page interactions', function () {
    $code = <<<'JS'
    export default async function ({ page }) {
        await page.goto('https://example.com');
        await page.type('#search', 'test');
        await page.click('#submit');
        return await page.evaluate(() => document.title);
    }
    JS;

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($code) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['code'] === $code;
        })
        ->andReturn(test()->mockResponse(['data' => 'Search Results']));

    $result = $this->function
        ->code($code)
        ->send();

    expect($result->data())
        ->toBe('Search Results');
});

it('can combine multiple options', function () {
    $code = 'export default async function() {}';
    $context = ['url' => 'https://example.com'];
    $modules = [['url' => 'https://esm.sh/lodash']];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($code, $context, $modules) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['code'] === $code &&
                   $body['context'] === $context &&
                   $body['addModules'] === $modules &&
                   $body['evaluationTimeout'] === 5000;
        })
        ->andReturn(test()->mockResponse(['data' => []]));

    $this->function
        ->code($code)
        ->context($context)
        ->addModules($modules)
        ->evaluationTimeout(5000)
        ->send();
});

it('can handle async function results', function () {
    $code = <<<'JS'
    export default async function () {
        return new Promise(resolve => setTimeout(() => resolve('delayed'), 100));
    }
    JS;

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse(['data' => 'delayed']));

    $result = $this->function
        ->code($code)
        ->send();

    expect($result->data())
        ->toBe('delayed');
});
