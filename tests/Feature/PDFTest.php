<?php

use MillerPHP\LaravelBrowserless\Features\PDF;
use Psr\Http\Message\RequestInterface;

beforeEach(function () {
    $this->client = test()->mockClient();
    $this->pdf = new PDF($this->client);
});

it('can generate PDF from URL', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $request->getMethod() === 'POST' &&
                   $request->getUri()->getPath() === '/pdf' &&
                   $body['url'] === 'https://example.com';
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $result = $this->pdf
        ->url('https://example.com')
        ->send();

    expect($result->response())
        ->toBeValidResponse();
});

it('can generate PDF from HTML content', function () {
    $html = '<html><body><h1>Test</h1></body></html>';

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($html) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['html'] === $html;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->html($html)
        ->send();
});

it('can set paper format', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['format'] === 'A4';
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->format('A4')
        ->send();
});

it('can set landscape mode', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['landscape'] === true;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->landscape()
        ->send();
});

it('can set margins', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            $margins = $body['options']['margin'];

            return $margins['top'] === 1 &&
                   $margins['right'] === 2 &&
                   $margins['bottom'] === 1 &&
                   $margins['left'] === 2;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->margin(1, 2, 1, 2)
        ->send();
});

it('can enable print background', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['printBackground'] === true;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->printBackground()
        ->send();
});

it('can set header and footer templates', function () {
    $header = '<div>Header</div>';
    $footer = '<div>Footer</div>';

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($header, $footer) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['displayHeaderFooter'] === true &&
                   $body['options']['headerTemplate'] === $header &&
                   $body['options']['footerTemplate'] === $footer;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->displayHeaderFooter()
        ->headerTemplate($header)
        ->footerTemplate($footer)
        ->send();
});

it('can set page ranges', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['pageRanges'] === '1-5, 8, 11-13';
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->pageRanges('1-5, 8, 11-13')
        ->send();
});

it('can set scale', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['scale'] === 0.8;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->scale(0.8)
        ->send();
});

it('validates scale range', function () {
    expect(fn () => $this->pdf->scale(2.1))
        ->toThrow(InvalidArgumentException::class, 'Scale must be between 0.1 and 2');

    expect(fn () => $this->pdf->scale(0.05))
        ->toThrow(InvalidArgumentException::class, 'Scale must be between 0.1 and 2');
});

it('can set PDF metadata', function () {
    $metadata = [
        'title' => 'Test Document',
        'author' => 'Test Author',
        'subject' => 'Test Subject',
    ];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($metadata) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['metadata'] === $metadata;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->metadata($metadata)
        ->send();
});

it('can enable tagged PDF', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['tagged'] === true;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->tagged()
        ->send();
});

it('can set compression level', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['options']['compressionLevel'] === 9;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->compressionLevel(9)
        ->send();
});

it('validates compression level range', function () {
    expect(fn () => $this->pdf->compressionLevel(10))
        ->toThrow(InvalidArgumentException::class, 'Compression level must be between 0 and 9');

    expect(fn () => $this->pdf->compressionLevel(-1))
        ->toThrow(InvalidArgumentException::class, 'Compression level must be between 0 and 9');
});

it('can set encryption options', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            $encryption = $body['options']['encryption'];

            return $encryption['userPassword'] === 'user123' &&
                   $encryption['ownerPassword'] === 'owner123' &&
                   $encryption['permissions'] === ['printing'];
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->encryption('user123', 'owner123', ['printing'])
        ->send();
});

it('can combine multiple options', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['url'] === 'https://example.com' &&
                   $body['options']['format'] === 'A4' &&
                   $body['options']['landscape'] === true &&
                   $body['options']['printBackground'] === true &&
                   $body['options']['scale'] === 1.0;
        })
        ->andReturn(test()->mockResponse(['success' => true]));

    $this->pdf
        ->url('https://example.com')
        ->format('A4')
        ->landscape()
        ->printBackground()
        ->scale(1.0)
        ->send();
});

it('validates required options before sending', function () {
    expect(fn () => $this->pdf->send())
        ->toThrow(InvalidArgumentException::class, 'Either URL or HTML content must be provided');
});
