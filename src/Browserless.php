<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr18ClientDiscovery;
use Illuminate\Support\Facades\Http;
use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Exceptions\BrowserlessException;
use MillerPHP\LaravelBrowserless\Exceptions\ClientSetupException;
use MillerPHP\LaravelBrowserless\Features\BQL;
use MillerPHP\LaravelBrowserless\Features\Config;
use MillerPHP\LaravelBrowserless\Features\Content;
use MillerPHP\LaravelBrowserless\Features\Download;
use MillerPHP\LaravelBrowserless\Features\ExecuteFunction;
use MillerPHP\LaravelBrowserless\Features\Metrics;
use MillerPHP\LaravelBrowserless\Features\PDF;
use MillerPHP\LaravelBrowserless\Features\Performance;
use MillerPHP\LaravelBrowserless\Features\Scrape;
use MillerPHP\LaravelBrowserless\Features\Screenshot;
use MillerPHP\LaravelBrowserless\Features\Sessions;
use MillerPHP\LaravelBrowserless\Features\Unblock;
use MillerPHP\LaravelBrowserless\WebSocket\PlaywrightConnection;
use MillerPHP\LaravelBrowserless\WebSocket\PuppeteerConnection;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Browserless implements ClientContract
{
    /**
     * The PSR-18 HTTP Client itself.
     */
    protected ?ClientInterface $http = null;

    /**
     * Global options for all requests.
     *
     * @var array<string,mixed>
     */
    protected array $globalOptions = [
        'timeout' => 30000,
        'ignoreHTTPSErrors' => false,
        'stealth' => false,
        'proxy' => null,
        'headers' => [],
        'args' => [],
        'defaultViewport' => [
            'width' => 1280,
            'height' => 720,
            'deviceScaleFactor' => 1,
            'hasTouch' => false,
            'isLandscape' => false,
            'isMobile' => false,
        ],
        'devtools' => false,
        'dumpio' => false,
        'headless' => true,
        'ignoreDefaultArgs' => false,
        'slowMo' => 0,
        'userDataDir' => null,
        'waitForInitialPage' => true,
    ];

    /**
     * Create a new Browserless Client instance.
     */
    public function __construct(
        protected readonly string $apiToken,
        protected readonly string $url,
        ?ClientInterface $client = null,
        array $options = []
    ) {
        if (empty($this->apiToken)) {
            throw new BrowserlessException('API token is required');
        }

        if (empty($this->url)) {
            throw new BrowserlessException('Browserless URL is required');
        }

        $this->globalOptions = array_merge($this->globalOptions, $options);

        if ($client) {
            $this->http = $client;
        } else {
            $this->setup();
        }
    }

    /**
     * Set global timeout in milliseconds.
     */
    public function setTimeout(int $timeout): self
    {
        $this->globalOptions['timeout'] = $timeout;

        return $this;
    }

    /**
     * Set whether to ignore HTTPS errors.
     */
    public function setIgnoreHTTPSErrors(bool $ignore): self
    {
        $this->globalOptions['ignoreHTTPSErrors'] = $ignore;

        return $this;
    }

    /**
     * Enable or disable stealth mode.
     */
    public function setStealth(bool $stealth): self
    {
        $this->globalOptions['stealth'] = $stealth;

        return $this;
    }

    /**
     * Set proxy configuration.
     */
    public function setProxy(?string $proxy): self
    {
        $this->globalOptions['proxy'] = $proxy;

        return $this;
    }

    /**
     * Set global headers.
     *
     * @param  array<string,string>  $headers
     */
    public function setHeaders(array $headers): self
    {
        $this->globalOptions['headers'] = $headers;

        return $this;
    }

    /**
     * Get the current global options.
     *
     * @return array<string,mixed>
     */
    public function getGlobalOptions(): array
    {
        return $this->globalOptions;
    }

    /**
     * Get the base URL with proper formatting.
     */
    protected function getBaseUrl(): string
    {
        $url = rtrim($this->url, '/');

        if (! preg_match('~^https?://~i', $url)) {
            throw new BrowserlessException('Invalid URL format. Must include http:// or https://');
        }

        return $url;
    }

    /**
     * @param  array<int,Plugin>  $plugins
     */
    public function setup(array $plugins = []): ClientContract
    {
        try {
            $this->http = new PluginClient(
                client: Psr18ClientDiscovery::find(),
                plugins: $plugins,
            );
        } catch (\Throwable $e) {
            throw new ClientSetupException(
                message: 'Failed to setup HTTP client: '.$e->getMessage(),
                previous: $e
            );
        }

        return $this;
    }

    /**
     * Return the URL for the API.
     */
    public function url(): string
    {
        return $this->getBaseUrl();
    }

    /**
     * Get the API token.
     */
    public function token(): string
    {
        return $this->apiToken;
    }

    /**
     * Set the HTTP Client for the SDK.
     */
    public function client(ClientInterface $client): ClientContract
    {
        $this->http = $client;

        return $this;
    }

    /**
     * Send an API Request.
     *
     * @throws ClientSetupException|ClientExceptionInterface|BrowserlessException
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        if (! $this->http instanceof ClientInterface) {
            throw new ClientSetupException(
                message: 'You have not setup the client correctly, you need to set the HTTP Client using `setup` or `client`.',
            );
        }

        try {
            $response = $this->http->sendRequest($request);

            if ($response->getStatusCode() >= 400) {
                throw new BrowserlessException(
                    sprintf(
                        'Browserless API error (HTTP %d): %s',
                        $response->getStatusCode(),
                        $response->getBody()->getContents()
                    )
                );
            }

            return $response;
        } catch (ClientExceptionInterface $e) {
            throw new BrowserlessException(
                'Failed to send request: '.$e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Create a new PDF instance.
     */
    public function pdf(): PDF
    {
        return new PDF($this);
    }

    /**
     * Create a new Screenshot instance.
     */
    public function screenshot(): Screenshot
    {
        return new Screenshot($this);
    }

    /**
     * Create a new Content instance.
     */
    public function content(): Content
    {
        return new Content($this);
    }

    /**
     * Create a new Download instance.
     */
    public function download(): Download
    {
        return new Download($this);
    }

    /**
     * Create a new Function instance.
     */
    public function executeFunction(): ExecuteFunction
    {
        return new ExecuteFunction($this);
    }

    /**
     * Create a new Unblock instance.
     */
    public function unblock(): Unblock
    {
        return new Unblock($this);
    }

    /**
     * Create a new Scrape instance.
     */
    public function scrape(): Scrape
    {
        return new Scrape($this);
    }

    /**
     * Create a new Performance instance for analyzing page performance.
     * This method allows for more granular control over the performance analysis.
     *
     * @return \MillerPHP\LaravelBrowserless\Performance
     */
    public function performance(): Performance
    {
        return new Performance($this);
    }

    /**
     * Create a new Sessions instance.
     */
    public function sessions(): Sessions
    {
        return new Sessions($this);
    }

    /**
     * Create a new Config instance.
     */
    public function config(): Config
    {
        return new Config($this);
    }

    /**
     * Create a new Metrics instance.
     */
    public function metrics(): Metrics
    {
        return new Metrics($this);
    }

    /**
     * Create a new Puppeteer WebSocket connection.
     */
    public function puppeteer(): PuppeteerConnection
    {
        return new PuppeteerConnection($this);
    }

    /**
     * Create a new Playwright WebSocket connection.
     */
    public function playwright(): PlaywrightConnection
    {
        return new PlaywrightConnection($this);
    }

    /**
     * Set browser launch arguments.
     *
     * @param  array<string>  $args
     */
    public function setLaunchArgs(array $args): self
    {
        $this->globalOptions['args'] = $args;

        return $this;
    }

    /**
     * Set default viewport settings.
     */
    public function setDefaultViewport(
        int $width,
        int $height,
        float $deviceScaleFactor = 1.0,
        bool $hasTouch = false,
        bool $isLandscape = false,
        bool $isMobile = false
    ): self {
        $this->globalOptions['defaultViewport'] = [
            'width' => $width,
            'height' => $height,
            'deviceScaleFactor' => $deviceScaleFactor,
            'hasTouch' => $hasTouch,
            'isLandscape' => $isLandscape,
            'isMobile' => $isMobile,
        ];

        return $this;
    }

    /**
     * Enable or disable DevTools.
     */
    public function setDevTools(bool $enabled): self
    {
        $this->globalOptions['devtools'] = $enabled;

        return $this;
    }

    /**
     * Enable or disable stdio debugging.
     */
    public function setDumpio(bool $enabled): self
    {
        $this->globalOptions['dumpio'] = $enabled;

        return $this;
    }

    /**
     * Set headless mode (true, false, or 'shell').
     *
     * @param  bool|'shell'  $mode
     */
    public function setHeadless(bool|string $mode): self
    {
        if (! is_bool($mode) && $mode !== 'shell') {
            throw new BrowserlessException('Headless mode must be true, false, or "shell"');
        }
        $this->globalOptions['headless'] = $mode;

        return $this;
    }

    /**
     * Set whether to ignore default arguments.
     *
     * @param  bool|array<string>  $value
     */
    public function setIgnoreDefaultArgs(bool|array $value): self
    {
        $this->globalOptions['ignoreDefaultArgs'] = $value;

        return $this;
    }

    /**
     * Set slow motion delay in milliseconds.
     */
    public function setSlowMo(int $milliseconds): self
    {
        $this->globalOptions['slowMo'] = $milliseconds;

        return $this;
    }

    /**
     * Set user data directory.
     */
    public function setUserDataDir(string $dir): self
    {
        $this->globalOptions['userDataDir'] = $dir;

        return $this;
    }

    /**
     * Set whether to wait for the initial page to load.
     */
    public function setWaitForInitialPage(bool $wait): self
    {
        $this->globalOptions['waitForInitialPage'] = $wait;

        return $this;
    }

    /**
     * Create a new BQL instance.
     */
    public function bql(): BQL
    {
        return new BQL($this);
    }

    /**
     * Send a request to the Browserless API
     */
    protected function sendRequest(string $endpoint, array $data = []): array
    {
        try {
            Logger::logRequest($endpoint, $data);

            $response = Http::withHeaders([
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'application/json',
            ])
                ->withToken($this->apiToken)
                ->post($this->url.$endpoint, $data);

            Logger::logResponse($endpoint, $response->json());

            if ($response->failed()) {
                throw new \Exception($response->json()['message'] ?? 'Unknown error occurred');
            }

            return $response->json();
        } catch (\Throwable $e) {
            Logger::logError($endpoint, $e);
            throw $e;
        }
    }

    /**
     * Get performance metrics for a URL.
     * This is a convenience method that provides a simpler way to get performance metrics.
     *
     * @param  string  $url  The URL to analyze
     * @return \MillerPHP\LaravelBrowserless\Responses\PerformanceResponse
     *
     * @throws \Exception If the performance analysis fails
     */
    public function getPerformanceMetrics(string $url): PerformanceResponse
    {
        try {
            Logger::info('Starting performance analysis', ['url' => $url]);

            $response = $this->sendRequest('/performance', [
                'url' => $url,
            ]);

            Logger::info('Performance analysis completed', [
                'url' => $url,
                'response' => $response,
            ]);

            return new PerformanceResponse($response);
        } catch (\Exception $e) {
            Logger::error('Performance analysis failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
