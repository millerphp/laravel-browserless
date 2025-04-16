<?php

use MillerPHP\LaravelBrowserless\Exceptions\BQLException;
use MillerPHP\LaravelBrowserless\Features\BQL;
use MillerPHP\LaravelBrowserless\Responses\BQLResponse;
use Psr\Http\Message\RequestInterface;

beforeEach(function () {
    $this->client = test()->mockClient();
    $this->bql = new BQL($this->client);
});

it('can create a basic BQL query', function () {
    $query = 'mutation { goto(url: "https://example.com") { status } }';
    $response = ['data' => ['goto' => ['status' => 200]]];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($query) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $request->getMethod() === 'POST' &&
                   $request->getUri()->getPath() === '/chrome/bql' &&
                   $body['query'] === $query;
        })
        ->andReturn(test()->mockResponse($response));

    $result = $this->bql
        ->query($query)
        ->send();

    expect($result)
        ->toBeInstanceOf(BQLResponse::class)
        ->and($result->data())
        ->toBe($response);
});

it('can set query variables', function () {
    $query = 'mutation ($url: String!) { goto(url: $url) { status } }';
    $variables = ['url' => 'https://example.com'];
    $response = ['data' => ['goto' => ['status' => 200]]];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($query, $variables) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['query'] === $query && $body['variables'] === $variables;
        })
        ->andReturn(test()->mockResponse($response));

    $result = $this->bql
        ->query($query)
        ->variables($variables)
        ->send();

    expect($result->data())
        ->toHaveValidData()
        ->toHaveKey('data.goto.status', 200);
});

it('can enable human-like behavior', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            return str_contains($request->getUri()->getQuery(), 'humanlike=true');
        })
        ->andReturn(test()->mockResponse(['data' => []]));

    $this->bql
        ->query('mutation { }')
        ->humanLike()
        ->send();
});

it('can enable stealth mode', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            return str_contains($request->getUri()->getQuery(), 'stealth=true');
        })
        ->andReturn(test()->mockResponse(['data' => []]));

    $this->bql
        ->query('mutation { }')
        ->stealth()
        ->send();
});

it('can set proxy configuration', function () {
    $proxy = 'http://proxy:8080';

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($proxy) {
            return str_contains($request->getUri()->getQuery(), "proxy=$proxy");
        })
        ->andReturn(test()->mockResponse(['data' => []]));

    $this->bql
        ->query('mutation { }')
        ->proxy($proxy)
        ->send();
});

it('can set timeout', function () {
    $timeout = 30000;

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($timeout) {
            return str_contains($request->getUri()->getQuery(), "timeout=$timeout");
        })
        ->andReturn(test()->mockResponse(['data' => []]));

    $this->bql
        ->query('mutation { }')
        ->timeout($timeout)
        ->send();
});

it('handles GraphQL errors correctly', function () {
    $response = [
        'errors' => [
            ['message' => 'Invalid query'],
        ],
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse($response));

    $result = $this->bql
        ->query('invalid { query')
        ->send();

    expect($result->hasErrors())->toBeTrue()
        ->and($result->errors())
        ->toHaveCount(1)
        ->and($result->errors()[0]['message'])
        ->toBe('Invalid query');
});

it('throws exception on request failure', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andThrow(new \Exception('Request failed'));

    expect(fn () => $this->bql->query('mutation { }')->send())
        ->toThrow(BQLException::class, 'BQL query failed: Request failed');
});

it('can handle complex multi-page scraping queries', function () {
    $query = <<<'GRAPHQL'
    mutation multi_page {
        news: {
            cnn: goto(url: "https://edition.cnn.com") {
                status
                articles: evaluate(
                    content: "JSON.stringify(Array.from(document.querySelectorAll('article')))"
                ) {
                    value
                }
            }
            bbc: goto(url: "https://bbc.com/news") {
                status
                articles: evaluate(
                    content: "JSON.stringify(Array.from(document.querySelectorAll('article')))"
                ) {
                    value
                }
            }
        }
    }
    GRAPHQL;

    $response = [
        'data' => [
            'news' => [
                'cnn' => [
                    'status' => 200,
                    'articles' => ['value' => '[]'],
                ],
                'bbc' => [
                    'status' => 200,
                    'articles' => ['value' => '[]'],
                ],
            ],
        ],
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($query) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['query'] === $query;
        })
        ->andReturn(test()->mockResponse($response));

    $result = $this->bql
        ->query($query)
        ->humanLike()
        ->stealth()
        ->send();

    expect($result->data())
        ->toHaveValidData()
        ->toHaveKey('data.news.cnn.status', 200)
        ->toHaveKey('data.news.bbc.status', 200);
});
