<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Responses\ExecuteFunctionResponse;
use MillerPHP\LaravelBrowserless\Exceptions\ExecuteFunctionException;
use GuzzleHttp\Psr7\Request;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasQueryParameters;

class ExecuteFunction
{
    use HasQueryParameters;

    /**
     * The options for the function execution.
     *
     * @var array<string,mixed>
     */
    protected array $options = [
        'gotoOptions' => [], // For page.goto options
    ];

    /**
     * Create a new Function instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {}

    /**
     * Set the JavaScript code to execute.
     */
    public function code(string $code): self
    {
        $this->options['code'] = $code;
        return $this;
    }

    /**
     * Set context values for the code execution.
     *
     * @param array<string,mixed> $context
     */
    public function context(array $context): self
    {
        $this->options['context'] = $context;
        return $this;
    }

    /**
     * Set whether to wait for networkidle0 event.
     */
    public function waitForNetworkIdle(bool $wait = true): self
    {
        $this->options['gotoOptions']['waitUntil'] = $wait ? 'networkidle0' : 'load';
        return $this;
    }

    /**
     * Set the navigation timeout in milliseconds.
     */
    public function timeout(int $milliseconds): self
    {
        $this->options['gotoOptions']['timeout'] = $milliseconds;
        return $this;
    }

    /**
     * Set authentication credentials.
     */
    public function authenticate(string $username, string $password): self
    {
        $this->options['authenticate'] = [
            'username' => $username,
            'password' => $password,
        ];
        return $this;
    }

    /**
     * Set cookies for the page.
     *
     * @param array<array{name: string, value: string, domain: string}> $cookies
     */
    public function cookies(array $cookies): self
    {
        $this->options['cookies'] = $cookies;
        return $this;
    }

    /**
     * Set whether to ignore HTTPS errors.
     */
    public function ignoreHTTPSErrors(bool $ignore = true): self
    {
        $this->options['ignoreHTTPSErrors'] = $ignore;
        return $this;
    }

    /**
     * Set multiple options at once.
     *
     * @param array<string,mixed> $options
     */
    public function withOptions(array $options): self
    {
        $this->options = array_merge_recursive($this->options, $options);
        return $this;
    }

    /**
     * Set function execution context.
     * 
     * @param array{
     *   isolateFromGlobalScope?: bool,
     *   returnByValue?: bool,
     *   userGesture?: bool
     * } $options
     */
    public function executionContext(array $options): self
    {
        $this->options['executionContext'] = $options;
        return $this;
    }

    /**
     * Add external modules to import.
     * 
     * @param array{url: string, type?: string}[] $modules
     */
    public function addModules(array $modules): self
    {
        if (!isset($this->options['modules'])) {
            $this->options['modules'] = [];
        }
        $this->options['modules'] = array_merge($this->options['modules'], $modules);
        return $this;
    }

    /**
     * Set evaluation timeout.
     */
    public function evaluationTimeout(int $milliseconds): self
    {
        $this->options['evaluationTimeout'] = $milliseconds;
        return $this;
    }

    /**
     * Add pre-defined browser actions.
     * 
     * @param array{
     *   type: 'click'|'type'|'select'|'hover',
     *   selector: string,
     *   value?: string,
     *   options?: array<string,mixed>
     * }[] $actions
     */
    public function browserActions(array $actions): self
    {
        $this->options['browserActions'] = $actions;
        return $this;
    }

    /**
     * Add code to run before main function.
     */
    public function evaluateBeforeCode(string $code): self
    {
        $this->options['evaluateBeforeCode'] = $code;
        return $this;
    }

    /**
     * Add code to run after main function.
     */
    public function evaluateAfterCode(string $code): self
    {
        $this->options['evaluateAfterCode'] = $code;
        return $this;
    }

    /**
     * Set debug options.
     * 
     * @param array{
     *   console?: bool,
     *   network?: bool,
     *   performance?: bool,
     *   saveTrace?: bool
     * } $options
     */
    public function debugOptions(array $options): self
    {
        $this->options['debugOptions'] = $options;
        return $this;
    }

    /**
     * Configure retry behavior.
     * 
     * @param array{
     *   attempts?: int,
     *   delay?: int,
     *   backoff?: float,
     *   conditions?: string[]
     * } $options
     */
    public function retryOnFailure(array $options): self
    {
        $this->options['retryOnFailure'] = $options;
        return $this;
    }

    /**
     * Set failure threshold before error.
     */
    public function failureThreshold(int $threshold): self
    {
        $this->options['failureThreshold'] = $threshold;
        return $this;
    }

    /**
     * Add environment setup script.
     * 
     * @param array{
     *   content: string,
     *   runBeforeLoad?: bool,
     *   isolateFromPage?: bool
     * } $script
     */
    public function setupScript(array $script): self
    {
        $this->options['setupScript'] = $script;
        return $this;
    }

    /**
     * Add environment cleanup script.
     * 
     * @param array{
     *   content: string,
     *   runOnError?: bool,
     *   isolateFromPage?: bool
     * } $script
     */
    public function teardownScript(array $script): self
    {
        $this->options['teardownScript'] = $script;
        return $this;
    }

    /**
     * Configure execution environment.
     * 
     * @param array{
     *   nodeVersion?: string,
     *   environment?: 'browser'|'node'|'worker',
     *   globals?: array<string,mixed>,
     *   modules?: array{
     *     type: 'commonjs'|'esm',
     *     paths?: array<string,string>,
     *     aliases?: array<string,string>
     *   },
     *   experimental?: array{
     *     topLevelAwait?: bool,
     *     importAssertions?: bool,
     *     importMeta?: bool
     *   }
     * } $options
     */
    public function setExecutionEnvironment(array $options): self
    {
        $this->options['executionEnvironment'] = $options;
        return $this;
    }

    /**
     * Set memory management options.
     * 
     * @param array{
     *   maxHeapSize?: int,
     *   gcInterval?: int,
     *   memoryLimit?: int,
     *   exposureControl?: array{
     *     maxExposureTime?: int,
     *     maxExposureSize?: int,
     *     protectedObjects?: string[]
     *   },
     *   cleanup?: array{
     *     automatic?: bool,
     *     interval?: int,
     *     targets?: string[]
     *   }
     * } $options
     */
    public function setMemoryManagement(array $options): self
    {
        $this->options['memoryManagement'] = $options;
        return $this;
    }

    /**
     * Configure code instrumentation and profiling.
     * 
     * @param array{
     *   profiling?: array{
     *     enabled: bool,
     *     sampling?: array{
     *       interval?: int,
     *       stackDepth?: int
     *     },
     *     timeline?: bool,
     *     heapSnapshots?: bool
     *   },
     *   coverage?: array{
     *     statements?: bool,
     *     branches?: bool,
     *     functions?: bool,
     *     lines?: bool
     *   },
     *   metrics?: array{
     *     timing?: bool,
     *     memory?: bool,
     *     cpu?: bool,
     *     events?: string[]
     *   }
     * } $options
     */
    public function setCodeInstrumentation(array $options): self
    {
        $this->options['codeInstrumentation'] = $options;
        return $this;
    }

    /**
     * Control async execution behavior.
     * 
     * @param array{
     *   concurrency?: array{
     *     maxParallel?: int,
     *     maxQueue?: int,
     *     priorityLevels?: int
     *   },
     *   timeouts?: array{
     *     execution?: int,
     *     idle?: int,
     *     total?: int
     *   },
     *   errorHandling?: array{
     *     retryCount?: int,
     *     retryDelay?: int,
     *     fallbackValue?: mixed,
     *     errorTypes?: string[]
     *   },
     *   scheduling?: array{
     *     priority?: 'high'|'normal'|'low',
     *     weight?: int,
     *     dependencies?: string[]
     *   }
     * } $options
     */
    public function setAsyncBehavior(array $options): self
    {
        $this->options['asyncBehavior'] = $options;
        return $this;
    }

    /**
     * Set execution context isolation options.
     * 
     * @param array{
     *   realm?: array{
     *     create?: bool,
     *     name?: string,
     *     shared?: string[]
     *   },
     *   contextIsolation?: array{
     *     level: 'none'|'partial'|'strict',
     *     allowList?: string[],
     *     denyList?: string[]
     *   },
     *   vmOptions?: array{
     *     timeout?: int,
     *     displayErrors?: bool,
     *     throwOnError?: bool,
     *     compiler?: string
     *   }
     * } $options
     */
    public function setContextIsolation(array $options): self
    {
        $this->options['contextIsolation'] = $options;
        return $this;
    }

    /**
     * Configure source map support.
     * 
     * @param array{
     *   enabled: bool,
     *   inline?: bool,
     *   sourceRoot?: string,
     *   sourcesContent?: bool,
     *   sourceMapUrl?: string,
     *   skipValidation?: bool
     * } $options
     */
    public function setSourceMapOptions(array $options): self
    {
        $this->options['sourceMapOptions'] = $options;
        return $this;
    }

    /**
     * Configure advanced debugging options.
     * 
     * @param array{
     *   breakpoints?: array{
     *     location: string,
     *     condition?: string,
     *     hitCondition?: string,
     *     logMessage?: string
     *   }[],
     *   stepping?: array{
     *     skipFiles?: string[],
     *     skipFrames?: int,
     *     stepInTargets?: string[]
     *   },
     *   console?: array{
     *     captureStdout?: bool,
     *     captureStderr?: bool,
     *     redirectOutput?: string,
     *     filterByLevel?: string[]
     *   },
     *   sourceMaps?: array{
     *     enabled?: bool,
     *     sourceRoot?: string,
     *     urlRewrite?: array{from: string, to: string}[]
     *   }
     * } $options
     */
    public function setDebugOptions(array $options): self
    {
        $this->options['debugOptions'] = $options;
        return $this;
    }

    /**
     * Configure Web Worker management.
     * 
     * @param array{
     *   maxWorkers?: int,
     *   terminationTimeout?: int,
     *   shared?: array{
     *     enabled: bool,
     *     name?: string,
     *     scope?: string
     *   },
     *   dedicated?: array{
     *     scriptURL: string,
     *     options?: array{
     *       type?: 'classic'|'module',
     *       credentials?: 'omit'|'same-origin'|'include',
     *       name?: string
     *     }
     *   }[],
     *   serviceWorker?: array{
     *     register?: bool,
     *     scope?: string,
     *     updateViaCache?: 'none'|'imports'|'all'
     *   }
     * } $options
     */
    public function setWorkerOptions(array $options): self
    {
        $this->options['workerOptions'] = $options;
        return $this;
    }

    /**
     * Configure ES Module handling.
     * 
     * @param array{
     *   importMap?: array{
     *     imports?: array<string,string>,
     *     scopes?: array<string,array<string,string>>
     *   },
     *   assertions?: array{
     *     type?: string,
     *     supported?: string[]
     *   },
     *   preload?: array{
     *     modules?: string[],
     *     warnOnUnsupported?: bool
     *   },
     *   dynamicImport?: array{
     *     allowList?: string[],
     *     denyList?: string[],
     *     timeout?: int
     *   }
     * } $options
     */
    public function setModuleOptions(array $options): self
    {
        $this->options['moduleOptions'] = $options;
        return $this;
    }

    /**
     * Configure JavaScript runtime settings.
     * 
     * @param array{
     *   environment?: array{
     *     target?: 'es5'|'es2015'|'es2016'|'es2017'|'es2018'|'es2019'|'es2020'|'es2021',
     *     loose?: bool,
     *     include?: string[],
     *     exclude?: string[]
     *   },
     *   optimization?: array{
     *     level?: 0|1|2|3,
     *     inlineThreshold?: int,
     *     propertyRenaming?: bool,
     *     deadCodeElimination?: bool
     *   },
     *   polyfills?: array{
     *     automatic?: bool,
     *     list?: string[],
     *     useBuiltIns?: 'entry'|'usage'|false
     *   },
     *   experimental?: array{
     *     decorators?: bool,
     *     privateFields?: bool,
     *     classProperties?: bool,
     *     topLevelAwait?: bool
     *   }
     * } $options
     */
    public function setRuntimeOptions(array $options): self
    {
        $this->options['runtimeOptions'] = $options;
        return $this;
    }

    /**
     * Configure execution lifecycle hooks.
     * 
     * @param array{
     *   beforeExecution?: string,
     *   afterExecution?: string,
     *   onError?: string,
     *   onTimeout?: string,
     *   cleanup?: string,
     *   validate?: string
     * } $options
     */
    public function setLifecycleHooks(array $options): self
    {
        $this->options['lifecycleHooks'] = $options;
        return $this;
    }

    /**
     * Set execution environment variables.
     * 
     * @param array{
     *   env?: array<string,string>,
     *   args?: string[],
     *   cwd?: string,
     *   shell?: bool|string
     * } $options
     */
    public function setEnvironmentVariables(array $options): self
    {
        $this->options['environmentVariables'] = $options;
        return $this;
    }

    /**
     * Configure security policy settings.
     * 
     * @param array{
     *   csp?: array{
     *     directives?: array<string,string[]>,
     *     reportOnly?: bool,
     *     reportUri?: string
     *   },
     *   trustedTypes?: array{
     *     enabled?: bool,
     *     defaultPolicy?: string,
     *     policies?: array{name: string, script: string}[]
     *   },
     *   permissions?: array{
     *     features?: array<string,'granted'|'denied'|'prompt'>,
     *     origins?: array<string,string[]>
     *   },
     *   isolation?: array{
     *     framePolicy?: 'allow'|'deny'|'sameorigin',
     *     processIsolation?: bool,
     *     siteIsolation?: bool
     *   }
     * } $options
     */
    public function setSecurityPolicy(array $options): self
    {
        $this->options['securityPolicy'] = $options;
        return $this;
    }

    /**
     * Configure network traffic interception.
     * 
     * @param array{
     *   requests?: array{
     *     patterns?: array{
     *       urlPattern: string,
     *       resourceType?: string[],
     *       action: 'block'|'allow'|'modify'
     *     }[],
     *     modifications?: array{
     *       headers?: array<string,string>,
     *       queryParams?: array<string,string>,
     *       postData?: string
     *     }
     *   },
     *   responses?: array{
     *     patterns?: array{
     *       urlPattern: string,
     *       statusCode?: int,
     *       action: 'mock'|'modify'|'delay'
     *     }[],
     *     mocks?: array{
     *       body?: string,
     *       headers?: array<string,string>,
     *       status?: int
     *     }
     *   },
     *   websockets?: array{
     *     enabled?: bool,
     *     interceptMessages?: bool,
     *     mockResponses?: array{pattern: string, response: string}[]
     *   }
     * } $options
     */
    public function setNetworkInterception(array $options): self
    {
        $this->options['networkInterception'] = $options;
        return $this;
    }

    /**
     * Configure resource management settings.
     * 
     * @param array{
     *   cpu?: array{
     *     limit?: int,
     *     throttling?: array{rate: float, period: int},
     *     priority?: 'high'|'normal'|'low'
     *   },
     *   memory?: array{
     *     limit?: int,
     *     threshold?: float,
     *     action?: 'warn'|'terminate'|'gc'
     *   },
     *   storage?: array{
     *     quota?: int,
     *     persistent?: bool,
     *     clearOnExit?: bool
     *   },
     *   network?: array{
     *     maxConcurrentConnections?: int,
     *     bandwidthLimit?: int,
     *     timeoutPolicy?: array{
     *       dns?: int,
     *       connect?: int,
     *       send?: int,
     *       receive?: int
     *     }
     *   }
     * } $options
     */
    public function setResourceManagement(array $options): self
    {
        $this->options['resourceManagement'] = $options;
        return $this;
    }

    /**
     * Configure browser extension handling.
     * 
     * @param array{
     *   extensions?: array{
     *     paths?: string[],
     *     ids?: string[],
     *     permissions?: string[]
     *   },
     *   preferences?: array{
     *     enabled?: bool,
     *     incognito?: bool,
     *     background?: bool
     *   },
     *   management?: array{
     *     autoUpdate?: bool,
     *     installationMode?: 'normal'|'development'|'force',
     *     loadOrder?: string[]
     *   },
     *   devtools?: array{
     *     enabled?: bool,
     *     panelConfigs?: array{
     *       id: string,
     *       title: string,
     *       icon?: string
     *     }[]
     *   }
     * } $options
     */
    public function setBrowserExtensions(array $options): self
    {
        $this->options['browserExtensions'] = $options;
        return $this;
    }

    /**
     * Configure browser automation settings.
     * 
     * @param array{
     *   input?: array{
     *     keyboard?: array{
     *       layout?: string,
     *       delay?: int,
     *       autoRepeat?: bool
     *     },
     *     mouse?: array{
     *       sensitivity?: float,
     *       doubleClickDelay?: int,
     *       dragThreshold?: int
     *     },
     *     touch?: array{
     *       enabled?: bool,
     *       maxPoints?: int,
     *       pressure?: float
     *     }
     *   },
     *   navigation?: array{
     *     maxDepth?: int,
     *     allowBackForward?: bool,
     *     handleNewWindows?: bool,
     *     scrollBehavior?: 'auto'|'smooth'
     *   },
     *   automation?: array{
     *     waitForSelectors?: bool,
     *     assertVisibility?: bool,
     *     recordVideo?: bool,
     *     recordHar?: bool
     *   }
     * } $options
     */
    public function setAutomationOptions(array $options): self
    {
        $this->options['automationOptions'] = $options;
        return $this;
    }

    /**
     * Send the function execution request.
     *
     * @throws ExecuteFunctionException
     */
    public function send(): ExecuteFunctionResponse
    {
        $this->validateOptions();

        try {
            $request = new Request(
                'POST',
                $this->buildQueryString($this->client->url() . '/function?token=' . $this->client->token()),
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode($this->options, JSON_THROW_ON_ERROR)
            );

            return new ExecuteFunctionResponse($this->client->send($request));
        } catch (\JsonException $e) {
            throw ExecuteFunctionException::fromResponse($e);
        } catch (\Throwable $e) {
            throw ExecuteFunctionException::fromResponse($e);
        }
    }

    /**
     * Validate the function options.
     *
     * @throws ExecuteFunctionException
     */
    protected function validateOptions(): void
    {
        if (!isset($this->options['code'])) {
            throw ExecuteFunctionException::invalidOptions('JavaScript code must be provided');
        }
    }
} 