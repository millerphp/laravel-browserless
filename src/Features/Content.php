<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use GuzzleHttp\Psr7\Request;
use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Exceptions\ContentException;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasAuthentication;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasCookieManagement;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasNavigationOptions;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasOptions;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasQueryParameters;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasResourceInjection;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasViewport;
use MillerPHP\LaravelBrowserless\Responses\ContentResponse;

class Content
{
    use HasAuthentication;
    use HasCookieManagement;
    use HasNavigationOptions;
    use HasOptions;
    use HasQueryParameters;
    use HasResourceInjection;
    use HasViewport;

    /**
     * The options for the content capture.
     *
     * @var array<string,mixed>
     */
    protected array $options = [
        'gotoOptions' => [], // For page.goto options
        'viewport' => [], // For page viewport options
    ];

    /**
     * Create a new Content instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {}

    /**
     * Set the HTML content to process.
     */
    public function html(string $html): self
    {
        $this->options['html'] = $html;

        return $this;
    }

    /**
     * Set the URL to process.
     */
    public function url(string $url): self
    {
        $this->options['url'] = $url;

        return $this;
    }

    /**
     * Set whether to wait for networkidle0 event.
     */
    public function waitForNetworkIdle(bool $wait = true): self
    {
        return $this->waitUntil($wait ? 'networkidle0' : 'load');
    }

    /**
     * Set resource types to reject during navigation.
     *
     * @param  array<string>  $types  Resource types to reject (e.g., ['image', 'stylesheet', 'script'])
     */
    public function rejectResourceTypes(array $types): self
    {
        $this->options['rejectResourceTypes'] = $types;

        return $this;
    }

    /**
     * Reject requests matching specific patterns.
     *
     * @param  array<string>  $patterns
     */
    public function rejectRequestPatterns(array $patterns): self
    {
        $this->options['rejectRequestPattern'] = $patterns;

        return $this;
    }

    /**
     * Set whether to attempt to proceed when async events fail.
     */
    public function bestAttempt(bool $enabled = true): self
    {
        $this->options['bestAttempt'] = $enabled;

        return $this;
    }

    /**
     * Wait for a specific event before capturing content.
     */
    public function waitForEvent(string $event, ?int $timeout = null): self
    {
        $this->options['waitForEvent'] = [
            'event' => $event,
            'timeout' => $timeout,
        ];

        return $this;
    }

    /**
     * Wait for a function to execute in the browser context.
     */
    public function waitForFunction(string $function, ?int $timeout = null): self
    {
        $this->options['waitForFunction'] = [
            'fn' => $function,
            'timeout' => $timeout,
        ];

        return $this;
    }

    /**
     * Wait for a selector to appear in the page.
     */
    public function waitForSelector(
        string $selector,
        ?int $timeout = null,
        bool $hidden = false,
        bool $visible = false
    ): self {
        $this->options['waitForSelector'] = array_filter([
            'selector' => $selector,
            'timeout' => $timeout,
            'hidden' => $hidden,
            'visible' => $visible,
        ]);

        return $this;
    }

    /**
     * Set multiple options at once.
     *
     * @param  array<string,mixed>  $options
     */
    public function withOptions(array $options): self
    {
        $this->options = array_merge_recursive($this->options, $options);

        return $this;
    }

    /**
     * Set content handling options.
     *
     * @param array{
     *   stripComments?: bool,
     *   minify?: bool,
     *   removeScripts?: bool,
     *   removeStyles?: bool,
     *   removeImages?: bool
     * } $options
     */
    public function contentOptions(array $options): self
    {
        $this->options['contentOptions'] = $options;

        return $this;
    }

    /**
     * Extract specific elements from the page.
     *
     * @param array{
     *   selector: string,
     *   attribute?: string,
     *   multiple?: bool
     * }[] $elements
     */
    public function extractElements(array $elements): self
    {
        $this->options['extractElements'] = $elements;

        return $this;
    }

    /**
     * Evaluate JavaScript on elements.
     *
     * @param array{
     *   selector: string,
     *   script: string,
     *   args?: array<mixed>
     * }[] $evaluations
     */
    public function evaluateSelector(array $evaluations): self
    {
        $this->options['evaluateSelector'] = $evaluations;

        return $this;
    }

    /**
     * Block specific domains.
     *
     * @param  string[]  $domains
     */
    public function domainBlocklist(array $domains): self
    {
        $this->options['domainBlocklist'] = $domains;

        return $this;
    }

    /**
     * Transform response before returning.
     */
    public function transformResponse(string $transformScript): self
    {
        $this->options['transformResponse'] = $transformScript;

        return $this;
    }

    /**
     * Wait for specific DOM event.
     *
     * @param array{
     *   event: string,
     *   selector?: string,
     *   timeout?: int
     * } $options
     */
    public function waitForDOMEvent(array $options): self
    {
        $this->options['waitForDOMEvent'] = $options;

        return $this;
    }

    /**
     * Configure HTML attribute removal.
     *
     * @param array{
     *   data?: bool,
     *   aria?: bool,
     *   classIds?: bool,
     *   custom?: string[]
     * } $options
     */
    public function removeAttributes(array $options): self
    {
        $this->options['removeAttributes'] = $options;

        return $this;
    }

    /**
     * Configure HTML sanitization.
     *
     * @param array{
     *   allowedTags?: string[],
     *   allowedAttributes?: array<string,string[]>,
     *   allowedSchemes?: string[],
     *   stripComments?: bool
     * } $options
     */
    public function sanitizeHtml(array $options): self
    {
        $this->options['sanitizeHtml'] = $options;

        return $this;
    }

    /**
     * Configure response transformation.
     *
     * @param array{
     *   minify?: bool,
     *   beautify?: bool,
     *   indentSize?: int,
     *   wrapAttributes?: bool
     * } $options
     */
    public function transformationOptions(array $options): self
    {
        $this->options['transformationOptions'] = $options;

        return $this;
    }

    /**
     * Set response format.
     *
     * @param array{
     *   type: 'html'|'text'|'json',
     *   pretty?: bool,
     *   includeMetadata?: bool,
     *   encoding?: string
     * } $options
     */
    public function setResponseFormat(array $options): self
    {
        $this->options['responseFormat'] = $options;

        return $this;
    }

    /**
     * Configure DOM snapshot options.
     *
     * @param array{
     *   computedStyles?: string[],
     *   includeDOMRects?: bool,
     *   includeUserAgentShadowTree?: bool,
     *   skipNested?: bool
     * } $options
     */
    public function setDOMSnapshot(array $options): self
    {
        $this->options['domSnapshot'] = $options;

        return $this;
    }

    /**
     * Set resource loading priorities.
     *
     * @param  array<string,string>  $priorities  Map of resource types to priorities ('high'|'medium'|'low')
     */
    public function setResourceLoadingPriority(array $priorities): self
    {
        $this->options['resourcePriorities'] = $priorities;

        return $this;
    }

    /**
     * Set custom headers per request pattern.
     *
     * @param array{
     *   pattern: string,
     *   headers: array<string,string>
     * }[] $headers
     */
    public function setCustomHeaders(array $headers): self
    {
        $this->options['customHeaders'] = $headers;

        return $this;
    }

    /**
     * Configure resource interception.
     *
     * @param array{
     *   patterns: array{
     *     urlPattern: string,
     *     resourceType?: string[],
     *     action: 'block'|'allow'|'modify',
     *     response?: array{
     *       status?: int,
     *       headers?: array<string,string>,
     *       body?: string
     *     }
     *   }[],
     *   defaultAction?: 'block'|'allow'
     * } $options
     */
    public function setResourceInterception(array $options): self
    {
        $this->options['resourceInterception'] = $options;

        return $this;
    }

    /**
     * Set DOM manipulation options.
     *
     * @param array{
     *   removeElements?: string[],
     *   modifyElements?: array{
     *     selector: string,
     *     attributes?: array<string,string>,
     *     styles?: array<string,string>,
     *     textContent?: string
     *   }[],
     *   injectElements?: array{
     *     html: string,
     *     position: 'beforebegin'|'afterbegin'|'beforeend'|'afterend',
     *     selector: string
     *   }[]
     * } $options
     */
    public function setDOMManipulation(array $options): self
    {
        $this->options['domManipulation'] = $options;

        return $this;
    }

    /**
     * Set granular network conditions.
     *
     * @param array{
     *   latency?: array{
     *     min: int,
     *     max: int,
     *     jitter?: int
     *   },
     *   throughput?: array{
     *     download: int,
     *     upload: int,
     *     unit: 'kb'|'mb'|'gb'
     *   },
     *   packetLoss?: float,
     *   connectionType?: 'none'|'cellular2g'|'cellular3g'|'cellular4g'|'bluetooth'|'ethernet'|'wifi'|'wimax'|'other'
     * } $options
     */
    public function setNetworkConditions(array $options): self
    {
        $this->options['networkConditions'] = $options;

        return $this;
    }

    /**
     * Configure script execution behavior.
     *
     * @param array{
     *   allowedSources?: string[],
     *   blockInlineScripts?: bool,
     *   allowWorkers?: bool,
     *   isolateScripts?: bool,
     *   scriptTimeout?: int,
     *   moduleSupport?: bool
     * } $options
     */
    public function setScriptExecution(array $options): self
    {
        $this->options['scriptExecution'] = $options;

        return $this;
    }

    /**
     * Set advanced request handling options.
     *
     * @param array{
     *   caching?: array{
     *     enabled: bool,
     *     maxAge?: int,
     *     allowStale?: bool
     *   },
     *   compression?: array{
     *     enabled: bool,
     *     level?: int,
     *     algorithms?: string[]
     *   },
     *   redirects?: array{
     *     maxRedirects?: int,
     *     followCrossOrigin?: bool,
     *     rewriteRules?: array{from: string, to: string}[]
     *   }
     * } $options
     */
    public function setRequestHandling(array $options): self
    {
        $this->options['requestHandling'] = $options;

        return $this;
    }

    /**
     * Configure response validation.
     *
     * @param array{
     *   statusCodes?: array{min: int, max: int},
     *   contentTypes?: string[],
     *   minSize?: int,
     *   maxSize?: int,
     *   requiredSelectors?: string[],
     *   forbiddenSelectors?: string[],
     *   textMatches?: array{selector: string, text: string, matchType?: 'exact'|'contains'|'regex'}[]
     * } $options
     */
    public function setResponseValidation(array $options): self
    {
        $this->options['responseValidation'] = $options;

        return $this;
    }

    /**
     * Set additional security options.
     *
     * @param array{
     *   sandbox?: array{
     *     enabled: bool,
     *     allowForms?: bool,
     *     allowModals?: bool,
     *     allowOrientationLock?: bool,
     *     allowPointerLock?: bool,
     *     allowPopups?: bool,
     *     allowPresentation?: bool,
     *     allowSameOrigin?: bool,
     *     allowScripts?: bool,
     *     allowTopNavigation?: bool
     *   },
     *   csp?: array{
     *     enabled: bool,
     *     directives?: array<string,string[]>,
     *     reportOnly?: bool
     *   },
     *   referrerPolicy?: string,
     *   crossOriginIsolated?: bool,
     *   crossOriginEmbedderPolicy?: string
     * } $options
     */
    public function setSecurityOptions(array $options): self
    {
        $this->options['securityOptions'] = $options;

        return $this;
    }

    /**
     * Configure error handling behavior.
     *
     * @param array{
     *   ignoreErrors?: string[],
     *   retryOnError?: bool,
     *   errorScreenshot?: bool,
     *   errorCallback?: string,
     *   fallbackContent?: string,
     *   timeoutAction?: 'error'|'continue'|'retry'
     * } $options
     */
    public function setErrorHandling(array $options): self
    {
        $this->options['errorHandling'] = $options;

        return $this;
    }

    /**
     * Set performance optimization options.
     *
     * @param array{
     *   preconnect?: string[],
     *   prefetch?: string[],
     *   preload?: array{url: string, as: string}[],
     *   lazyLoading?: array{
     *     enabled: bool,
     *     threshold?: int,
     *     types?: string[]
     *   },
     *   resourceHints?: array{
     *     dnsPrefetch?: string[],
     *     prerender?: string[]
     *   }
     * } $options
     */
    public function setPerformanceOptions(array $options): self
    {
        $this->options['performanceOptions'] = $options;

        return $this;
    }

    /**
     * Configure accessibility audit options.
     *
     * @param array{
     *   standards?: array{
     *     wcag2a?: bool,
     *     wcag2aa?: bool,
     *     wcag2aaa?: bool,
     *     section508?: bool
     *   },
     *   includedImpacts?: array{'minor'|'moderate'|'serious'|'critical'},
     *   rules?: array{
     *     include?: string[],
     *     exclude?: string[]
     *   },
     *   context?: array{
     *     element?: string,
     *     ancestor?: string,
     *     xpath?: string
     *   }
     * } $options
     */
    public function setAccessibilityAudit(array $options): self
    {
        $this->options['accessibilityAudit'] = $options;

        return $this;
    }

    /**
     * Configure performance metrics collection.
     *
     * @param array{
     *   timings?: array{
     *     fcp?: bool,
     *     lcp?: bool,
     *     cls?: bool,
     *     fid?: bool,
     *     ttfb?: bool
     *   },
     *   resources?: array{
     *     sizes?: bool,
     *     timing?: bool,
     *     priority?: bool,
     *     compression?: bool
     *   },
     *   javascript?: array{
     *     coverage?: bool,
     *     execution?: bool,
     *     parsing?: bool,
     *     compilation?: bool
     *   },
     *   memory?: array{
     *     heap?: bool,
     *     documents?: bool,
     *     nodes?: bool,
     *     listeners?: bool
     *   }
     * } $options
     */
    public function setPerformanceMetrics(array $options): self
    {
        $this->options['performanceMetrics'] = $options;

        return $this;
    }

    /**
     * Configure browser storage management.
     *
     * @param array{
     *   localStorage?: array{
     *     clear?: bool,
     *     items?: array{key: string, value: string}[]
     *   },
     *   sessionStorage?: array{
     *     clear?: bool,
     *     items?: array{key: string, value: string}[]
     *   },
     *   cookies?: array{
     *     clear?: bool,
     *     items?: array{
     *       name: string,
     *       value: string,
     *       domain?: string,
     *       path?: string,
     *       expires?: int,
     *       httpOnly?: bool,
     *       secure?: bool,
     *       sameSite?: 'Strict'|'Lax'|'None'
     *     }[]
     *   },
     *   indexedDB?: array{
     *     clear?: bool,
     *     databases?: string[]
     *   }
     * } $options
     */
    public function setStorageManagement(array $options): self
    {
        $this->options['storageManagement'] = $options;

        return $this;
    }

    /**
     * Configure geolocation emulation.
     *
     * @param array{
     *   latitude: float,
     *   longitude: float,
     *   accuracy?: float,
     *   altitude?: float,
     *   altitudeAccuracy?: float,
     *   heading?: float,
     *   speed?: float,
     *   proximityEnabled?: bool,
     *   timezoneId?: string
     * } $options
     */
    public function setGeolocation(array $options): self
    {
        $this->options['geolocation'] = $options;

        return $this;
    }

    /**
     * Configure browser permissions.
     *
     * @param array{
     *   permissions?: array{
     *     name: 'geolocation'|'notifications'|'camera'|'microphone'|'background-sync'|'sensors'|'clipboard',
     *     state: 'granted'|'denied'|'prompt'
     *   }[],
     *   origins?: array{
     *     origin: string,
     *     permissions: string[]
     *   }[]
     * } $options
     */
    public function setPermissions(array $options): self
    {
        $this->options['permissions'] = $options;

        return $this;
    }

    /**
     * Send the content capture request.
     *
     * @throws ContentException
     */
    public function send(): ContentResponse
    {
        $this->validateOptions();

        try {
            $request = new Request(
                'POST',
                $this->buildQueryString($this->client->url().'/content?token='.$this->client->token()),
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode($this->options, JSON_THROW_ON_ERROR)
            );

            return new ContentResponse($this->client->send($request));
        } catch (\JsonException $e) {
            throw ContentException::fromResponse($e);
        } catch (\Throwable $e) {
            throw ContentException::fromResponse($e);
        }
    }

    /**
     * Validate the content options.
     *
     * @throws ContentException
     */
    protected function validateOptions(): void
    {
        if (! isset($this->options['url']) && ! isset($this->options['html'])) {
            throw ContentException::invalidOptions('Either URL or HTML content must be provided');
        }
    }
}
