<?php

use MillerPHP\LaravelBrowserless\Features\Download;
use Psr\Http\Message\RequestInterface;

beforeEach(function () {
    $this->client = test()->mockClient();
    $this->download = new Download($this->client);
});

it('can execute download code', function () {
    $code = 'export default async function ({ page }) { await page.goto("https://example.com/file.pdf"); }';

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($code) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $request->getMethod() === 'POST' &&
                   $request->getUri()->getPath() === '/download' &&
                   $body['code'] === $code;
        })
        ->andReturn(test()->mockResponse(['data' => base64_encode('file content')]));

    $result = $this->download
        ->code($code)
        ->send();

    expect($result->response())
        ->toBeValidResponse();
});

it('can set context values', function () {
    $context = ['url' => 'https://example.com', 'selector' => '.download-link'];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($context) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['context'] === $context;
        })
        ->andReturn(test()->mockResponse(['data' => '']));

    $this->download
        ->code('export default async function() {}')
        ->context($context)
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
        ->andReturn(test()->mockResponse(['data' => '']));

    $this->download
        ->code('export default async function() {}')
        ->waitForNetworkIdle()
        ->send();
});

it('can set timeout', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['timeout'] === 30000;
        })
        ->andReturn(test()->mockResponse(['data' => '']));

    $this->download
        ->code('export default async function() {}')
        ->timeout(30000)
        ->send();
});

it('can set authentication', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);
            $auth = $body['authenticate'];

            return $auth['username'] === 'user' && $auth['password'] === 'pass';
        })
        ->andReturn(test()->mockResponse(['data' => '']));

    $this->download
        ->code('export default async function() {}')
        ->authenticate('user', 'pass')
        ->send();
});

it('can ignore HTTPS errors', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['ignoreHTTPSErrors'] === true;
        })
        ->andReturn(test()->mockResponse(['data' => '']));

    $this->download
        ->code('export default async function() {}')
        ->ignoreHTTPSErrors()
        ->send();
});

it('can handle download errors', function () {
    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andThrow(new \Exception('Download failed'));

    expect(fn () => $this->download->code('export default async function() {}')->send())
        ->toThrow(\Exception::class, 'Download failed');
});

it('validates required code before sending', function () {
    expect(fn () => $this->download->send())
        ->toThrow(InvalidArgumentException::class, 'Code must be provided');
});

it('can combine multiple options', function () {
    $code = 'export default async function() {}';
    $context = ['url' => 'https://example.com'];

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->withArgs(function (RequestInterface $request) use ($code, $context) {
            $body = json_decode($request->getBody()->getContents(), true);

            return $body['code'] === $code &&
                   $body['context'] === $context &&
                   $body['timeout'] === 30000 &&
                   $body['ignoreHTTPSErrors'] === true;
        })
        ->andReturn(test()->mockResponse(['data' => '']));

    $this->download
        ->code($code)
        ->context($context)
        ->timeout(30000)
        ->ignoreHTTPSErrors()
        ->send();
});

it('can handle binary response data', function () {
    $binaryData = random_bytes(1024);
    $base64Data = base64_encode($binaryData);

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse(['data' => $base64Data]));

    $result = $this->download
        ->code('export default async function() {}')
        ->send();

    expect($result->data())
        ->toBe($binaryData);
});

it('can save downloaded file', function () {
    $tempFile = tempnam(sys_get_temp_dir(), 'test');
    $fileContent = 'test content';

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse(['data' => base64_encode($fileContent)]));

    $result = $this->download
        ->code('export default async function() {}')
        ->send();

    $result->save($tempFile);

    expect(file_get_contents($tempFile))
        ->toBe($fileContent);

    unlink($tempFile);
});

it('can handle large file downloads', function () {
    $largeData = str_repeat('x', 1024 * 1024); // 1MB
    $base64Data = base64_encode($largeData);

    $this->client->shouldReceive('url')->andReturn('https://chrome.browserless.io');
    $this->client->shouldReceive('token')->andReturn('test-token');
    $this->client->shouldReceive('send')
        ->andReturn(test()->mockResponse(['data' => $base64Data]));

    $result = $this->download
        ->code('export default async function() {}')
        ->send();

    expect(strlen($result->data()))
        ->toBe(strlen($largeData));
});
