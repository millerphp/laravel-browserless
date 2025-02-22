<?php

use MillerPHP\LaravelBrowserless\Responses\BQLResponse;
use Psr\Http\Message\ResponseInterface;

beforeEach(function () {
    $this->mockResponse = test()->mockResponse([
        'data' => [
            'goto' => [
                'status' => 200,
                'content' => '<html></html>'
            ]
        ]
    ]);
    $this->response = new BQLResponse($this->mockResponse);
});

it('can get raw response', function () {
    expect($this->response->response())
        ->toBeInstanceOf(ResponseInterface::class);
});

it('can get response data', function () {
    expect($this->response->data())
        ->toHaveValidData()
        ->toHaveKey('data.goto.status', 200)
        ->toHaveKey('data.goto.content', '<html></html>');
});

it('can get specific data with dot notation', function () {
    expect($this->response->get('data.goto.status'))
        ->toBe(200);
});

it('returns default value when key not found', function () {
    expect($this->response->get('nonexistent', 'default'))
        ->toBe('default');
});

it('can check for errors when none exist', function () {
    expect($this->response->hasErrors())
        ->toBeFalse()
        ->and($this->response->errors())
        ->toBeEmpty();
});

it('can detect and retrieve errors', function () {
    $errorResponse = new BQLResponse(test()->mockResponse([
        'errors' => [
            ['message' => 'GraphQL error'],
            ['message' => 'Validation error']
        ]
    ]));

    expect($errorResponse->hasErrors())
        ->toBeTrue()
        ->and($errorResponse->errors())
        ->toHaveCount(2)
        ->and($errorResponse->errors()[0]['message'])
        ->toBe('GraphQL error');
});

it('handles malformed JSON response', function () {
    $malformedResponse = Mockery::mock(ResponseInterface::class);
    $malformedResponse->shouldReceive('getBody->__toString')
        ->andReturn('invalid json');

    $response = new BQLResponse($malformedResponse);

    expect($response->data())
        ->toBeNull();
});

it('handles empty response body', function () {
    $emptyResponse = Mockery::mock(ResponseInterface::class);
    $emptyResponse->shouldReceive('getBody->__toString')
        ->andReturn('');

    $response = new BQLResponse($emptyResponse);

    expect($response->data())
        ->toBeNull();
});

it('can handle nested data structures', function () {
    $complexResponse = new BQLResponse(test()->mockResponse([
        'data' => [
            'multi' => [
                'page1' => [
                    'goto' => ['status' => 200],
                    'evaluate' => ['value' => '{"items":[1,2,3]}']
                ],
                'page2' => [
                    'goto' => ['status' => 200],
                    'evaluate' => ['value' => '{"items":[4,5,6]}']
                ]
            ]
        ]
    ]));

    expect($complexResponse->get('data.multi.page1.goto.status'))
        ->toBe(200)
        ->and($complexResponse->get('data.multi.page2.evaluate.value'))
        ->toBe('{"items":[4,5,6]}');
});

it('safely handles null values in response', function () {
    $nullResponse = new BQLResponse(test()->mockResponse([
        'data' => [
            'result' => null,
            'meta' => [
                'nullValue' => null,
                'validValue' => 'test'
            ]
        ]
    ]));

    expect($nullResponse->get('data.result'))
        ->toBeNull()
        ->and($nullResponse->get('data.meta.nullValue'))
        ->toBeNull()
        ->and($nullResponse->get('data.meta.validValue'))
        ->toBe('test');
});

it('preserves boolean values in response', function () {
    $booleanResponse = new BQLResponse(test()->mockResponse([
        'data' => [
            'flags' => [
                'isValid' => true,
                'isError' => false
            ]
        ]
    ]));

    expect($booleanResponse->get('data.flags.isValid'))
        ->toBeTrue()
        ->and($booleanResponse->get('data.flags.isError'))
        ->toBeFalse();
}); 