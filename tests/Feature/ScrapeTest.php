<?php

use MillerPHP\LaravelBrowserless\Features\Scrape;
use Psr\Http\Message\RequestInterface;

beforeEach(function () {
    $this->client = test()->mockClient();
    $this->scrape = new Scrape($this->client);
});

it('can scrape content from URL', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $request->getMethod() === 'POST' &&
                   $request->getUri()->getPath() === '/scrape' &&
                   $body['url'] === 'https://example.com';
        })
        ->andReturn(test()->mockResponse(['results' => [['text' => 'Test Content']]]));

    $result = $this->scrape
        ->url('https://example.com')
        ->send();

    expect($result->response())
        ->toBeValidResponse()
        ->and($result->results())
        ->toHaveCount(1)
        ->and($result->results()[0]['text'])
        ->toBe('Test Content');
});

it('can scrape content from HTML', function () {
    $html = '<html><body><h1>Test</h1></body></html>';

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($html) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['html'] === $html;
        })
        ->andReturn(test()->mockResponse(['results' => []]));

    $this->scrape
        ->html($html)
        ->send();
});

it('can add elements to scrape', function () {
    $elements = [
        ['selector' => 'h1'],
        ['selector' => '.product'],
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($elements) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['elements'] === $elements;
        })
        ->andReturn(test()->mockResponse(['results' => []]));

    $this->scrape
        ->url('https://example.com')
        ->element('h1')
        ->element('.product')
        ->send();
});

it('can wait for timeout', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['waitForTimeout'] === 5000;
        })
        ->andReturn(test()->mockResponse(['results' => []]));

    $this->scrape
        ->url('https://example.com')
        ->waitForTimeout(5000)
        ->send();
});

it('can wait for selector', function () {
    $options = ['timeout' => 5000, 'visible' => true];

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
        ->andReturn(test()->mockResponse(['results' => []]));

    $this->scrape
        ->url('https://example.com')
        ->waitForSelector('.content', $options)
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
        ->andReturn(test()->mockResponse(['results' => []]));

    $this->scrape
        ->url('https://example.com')
        ->waitForFunction($function, 5000)
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
        ->andReturn(test()->mockResponse(['results' => []]));

    $this->scrape
        ->url('https://example.com')
        ->waitForEvent('load', 5000)
        ->send();
});

it('validates required source before sending', function () {
    expect(fn () => $this->scrape->send())
        ->toThrow(InvalidArgumentException::class, 'Either URL or HTML content must be provided');
});

it('can handle scraping errors', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andThrow(new \Exception('Scraping failed'));

    expect(fn () => $this->scrape->url('https://example.com')->send())
        ->toThrow(\Exception::class, 'Scraping failed');
});

it('can handle complex scraping results', function () {
    $results = [
        [
            'selector' => 'h1',
            'results' => [
                [
                    'text' => 'Main Title',
                    'html' => '<h1>Main Title</h1>',
                    'attributes' => ['id' => 'title'],
                ],
            ],
        ],
        [
            'selector' => '.product',
            'results' => [
                [
                    'text' => 'Product Name',
                    'html' => '<div class="product">Product Name</div>',
                    'attributes' => [
                        'id' => 'product-1',
                        'class' => 'product featured',
                    ],
                ],
            ],
        ],
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse(['results' => $results]));

    $result = $this->scrape
        ->url('https://example.com')
        ->element('h1')
        ->element('.product')
        ->send();

    expect($result->results())
        ->toBe($results)
        ->and($result->results('h1'))
        ->toHaveCount(1)
        ->and($result->results('.product'))
        ->toHaveCount(1);
});

it('can handle pagination scraping', function () {
    $code = <<<'JS'
    export default async function ({ page }) {
        const results = [];
        let hasNextPage = true;
        
        while (hasNextPage) {
            results.push(...await page.$$eval('.item', items => 
                items.map(item => ({
                    title: item.textContent,
                    link: item.href
                }))
            ));
            
            hasNextPage = await page.$('.next-page') !== null;
            if (hasNextPage) {
                await page.click('.next-page');
                await page.waitForSelector('.item');
            }
        }
        
        return results;
    }
    JS;

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($code) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['evaluateFunction'] === $code;
        })
        ->andReturn(test()->mockResponse(['results' => []]));

    $this->scrape
        ->url('https://example.com')
        ->evaluateFunction($code)
        ->send();
});

it('can combine multiple options', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['url'] === 'https://example.com' &&
                   count($body['elements']) === 2 &&
                   $body['waitForTimeout'] === 5000 &&
                   $body['waitForSelector']['selector'] === '.content';
        })
        ->andReturn(test()->mockResponse(['results' => []]));

    $this->scrape
        ->url('https://example.com')
        ->element('h1')
        ->element('.product')
        ->waitForTimeout(5000)
        ->waitForSelector('.content')
        ->send();
});

it('can filter results by selector', function () {
    $results = [
        ['selector' => 'h1', 'text' => 'Title 1'],
        ['selector' => 'h1', 'text' => 'Title 2'],
        ['selector' => '.product', 'text' => 'Product'],
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse(['results' => $results]));

    $result = $this->scrape
        ->url('https://example.com')
        ->element('h1')
        ->element('.product')
        ->send();

    expect($result->results('h1'))
        ->toHaveCount(2)
        ->and($result->results('.product'))
        ->toHaveCount(1);
});
