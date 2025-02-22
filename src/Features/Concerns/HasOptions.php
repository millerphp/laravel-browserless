<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features\Concerns;

trait HasOptions
{
    /**
     * The options.
     *
     * @var array<string,mixed>
     */
    protected array $options = [];

    /**
     * Set an option.
     *
     * @param mixed $value
     */
    protected function setOption(string $key, mixed $value): void
    {
        $this->options[$key] = $value;
    }

    /**
     * Get an option.
     *
     * @param mixed $default
     * @return mixed
     */
    protected function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Get all options.
     *
     * @return array<string,mixed>
     */
    protected function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Merge options.
     *
     * @param array<string,mixed> $options
     */
    protected function mergeOptions(array $options): void
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Enable best attempt mode - continue even if awaited events fail/timeout.
     */
    public function bestAttempt(bool $enabled = true): self
    {
        $this->options['bestAttempt'] = $enabled;
        return $this;
    }

    /**
     * Add script tags to inject.
     * 
     * @param array{url?: string, path?: string, content?: string, type?: string, id?: string} $script
     */
    public function addScriptTag(array $script): self
    {
        if (!isset($this->options['addScriptTag'])) {
            $this->options['addScriptTag'] = [];
        }
        $this->options['addScriptTag'][] = $script;
        return $this;
    }

    /**
     * Add style tags to inject.
     * 
     * @param array{url?: string, path?: string, content?: string} $style
     */
    public function addStyleTag(array $style): self
    {
        if (!isset($this->options['addStyleTag'])) {
            $this->options['addStyleTag'] = [];
        }
        $this->options['addStyleTag'][] = $style;
        return $this;
    }

    /**
     * Set HTTP authentication.
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
     * Add patterns to reject requests.
     *
     * @param string[] $patterns
     */
    public function rejectRequestPattern(array $patterns): self
    {
        $this->options['rejectRequestPattern'] = $patterns;
        return $this;
    }

    /**
     * Add resource types to reject.
     *
     * @param array<string> $types Valid types: "document", "stylesheet", "image", "media", 
     *                            "font", "script", "texttrack", "xhr", "fetch", "websocket"
     */
    public function rejectResourceTypes(array $types): self
    {
        $this->options['rejectResourceTypes'] = $types;
        return $this;
    }

    /**
     * Add request interceptors.
     * 
     * @param array{pattern: string, response: array{status: int, headers?: array<string,string>, 
     *                                               contentType?: string, body?: string}} $interceptor
     */
    public function addRequestInterceptor(array $interceptor): self
    {
        if (!isset($this->options['requestInterceptors'])) {
            $this->options['requestInterceptors'] = [];
        }
        $this->options['requestInterceptors'][] = $interceptor;
        return $this;
    }

    /**
     * Set viewport dimensions and properties.
     * 
     * @param array{
     *   width: int,
     *   height: int,
     *   deviceScaleFactor?: float,
     *   isMobile?: bool,
     *   isLandscape?: bool,
     *   hasTouch?: bool
     * } $viewport
     */
    public function viewport(array $viewport): self
    {
        $this->options['viewport'] = $viewport;
        return $this;
    }

    /**
     * Set extra HTTP headers.
     * 
     * @param array<string,string> $headers
     */
    public function setExtraHTTPHeaders(array $headers): self
    {
        $this->options['setExtraHTTPHeaders'] = $headers;
        return $this;
    }

    /**
     * Enable or disable JavaScript.
     */
    public function setJavaScriptEnabled(bool $enabled = true): self
    {
        $this->options['setJavaScriptEnabled'] = $enabled;
        return $this;
    }

    /**
     * Set the user agent.
     */
    public function userAgent(string $userAgent): self
    {
        $this->options['userAgent'] = $userAgent;
        return $this;
    }

    /**
     * Set media emulation type (screen, print, etc).
     */
    public function emulateMediaType(string $type): self
    {
        $this->options['emulateMediaType'] = $type;
        return $this;
    }

    /**
     * Configure page.goto options.
     * 
     * @param array{
     *   timeout?: int,
     *   waitUntil?: string|string[],
     *   referer?: string
     * } $options
     */
    public function gotoOptions(array $options): self
    {
        $this->options['gotoOptions'] = $options;
        return $this;
    }

    /**
     * Wait for a specific timeout.
     */
    public function waitForTimeout(int $timeout): self
    {
        $this->options['waitForTimeout'] = $timeout;
        return $this;
    }

    /**
     * Wait for a specific selector.
     * 
     * @param array{
     *   selector: string,
     *   timeout?: int,
     *   visible?: bool,
     *   hidden?: bool
     * } $options
     */
    public function waitForSelector(array $options): self
    {
        $this->options['waitForSelector'] = $options;
        return $this;
    }

    /**
     * Wait for a specific function to evaluate to true.
     * 
     * @param array{
     *   fn: string,
     *   polling?: string|int,
     *   timeout?: int
     * } $options
     */
    public function waitForFunction(array $options): self
    {
        $this->options['waitForFunction'] = $options;
        return $this;
    }

    /**
     * Wait for a specific event.
     * 
     * @param array{
     *   event: string,
     *   timeout?: int
     * } $options
     */
    public function waitForEvent(array $options): self
    {
        $this->options['waitForEvent'] = $options;
        return $this;
    }

    /**
     * Set cookies for the page.
     * 
     * @param array{
     *   name: string,
     *   value: string,
     *   domain?: string,
     *   path?: string,
     *   expires?: int,
     *   httpOnly?: bool,
     *   secure?: bool,
     *   sameSite?: "Strict"|"Lax"|"None"
     * }[] $cookies
     */
    public function cookies(array $cookies): self
    {
        $this->options['cookies'] = $cookies;
        return $this;
    }

    /**
     * Emulate a specific device.
     * 
     * @param string $name Device name from Puppeteer's devices list (e.g., 'iPhone X')
     */
    public function emulateDevice(string $name): self
    {
        $this->options['device'] = $name;
        return $this;
    }

    /**
     * Block ads and trackers.
     */
    public function blockAds(bool $enabled = true): self
    {
        $this->options['blockAds'] = $enabled;
        return $this;
    }

    /**
     * Set timezone for the browser context.
     */
    public function setTimezone(string $timezone): self
    {
        $this->options['setTimezone'] = $timezone;
        return $this;
    }

    /**
     * Set geolocation.
     * 
     * @param array{
     *   latitude: float,
     *   longitude: float,
     *   accuracy?: float
     * } $location
     */
    public function setGeolocation(array $location): self
    {
        $this->options['setGeolocation'] = $location;
        return $this;
    }

    /**
     * Set browser language.
     * 
     * @param string|string[] $languages Browser language codes (e.g., 'en-US')
     */
    public function setLanguage(string|array $languages): self
    {
        $this->options['setLanguage'] = $languages;
        return $this;
    }

    /**
     * Set offline mode.
     */
    public function setOfflineMode(bool $enabled = true): self
    {
        $this->options['setOfflineMode'] = $enabled;
        return $this;
    }

    /**
     * Set permissions for the browser context.
     * 
     * @param array<string,string> $permissions Map of permission names to states ('granted'|'denied')
     */
    public function setPermissions(array $permissions): self
    {
        $this->options['setPermissions'] = $permissions;
        return $this;
    }

    /**
     * Set network conditions for throttling.
     * 
     * @param array{
     *   latency?: int,
     *   downloadThroughput?: int,
     *   uploadThroughput?: int,
     *   offline?: bool
     * } $conditions
     */
    public function setNetworkConditions(array $conditions): self
    {
        $this->options['networkConditions'] = $conditions;
        return $this;
    }

    /**
     * Set color scheme emulation.
     * 
     * @param 'light'|'dark'|'no-preference' $scheme
     */
    public function emulateColorScheme(string $scheme): self
    {
        $this->options['colorScheme'] = $scheme;
        return $this;
    }

    /**
     * Set reduced motion preference.
     * 
     * @param 'reduce'|'no-preference' $preference
     */
    public function reducedMotion(string $preference): self
    {
        $this->options['reducedMotion'] = $preference;
        return $this;
    }

    /**
     * Set forced colors.
     * 
     * @param 'active'|'none' $colors
     */
    public function forcedColors(string $colors): self
    {
        $this->options['forcedColors'] = $colors;
        return $this;
    }

    /**
     * Set page ranges for PDF generation.
     * Example: '1-5, 8, 11-13'
     */
    public function pageRanges(string $ranges): self
    {
        $this->options['options']['pageRanges'] = $ranges;
        return $this;
    }

    /**
     * Enable tagged PDF generation (for accessibility).
     */
    public function taggedPDF(bool $enabled = true): self
    {
        $this->options['options']['tagged'] = $enabled;
        return $this;
    }

    /**
     * Set font preferences.
     * 
     * @param array{
     *   family?: string,
     *   size?: int,
     *   weight?: int|string,
     *   lineHeight?: float
     * } $preferences
     */
    public function setFontPreferences(array $preferences): self
    {
        $this->options['fontPreferences'] = $preferences;
        return $this;
    }

    /**
     * Set contrast preference.
     * 
     * @param 'more'|'less'|'no-preference' $preference
     */
    public function prefersContrast(string $preference): self
    {
        $this->options['prefersContrast'] = $preference;
        return $this;
    }

    /**
     * Set proxy configuration.
     * 
     * @param array{
     *   server: string,
     *   username?: string,
     *   password?: string,
     *   bypass?: string[]
     * } $config
     */
    public function setProxy(array $config): self
    {
        $this->options['proxy'] = $config;
        return $this;
    }

    /**
     * Scale PDF to width.
     */
    public function scaleToWidth(int $width): self
    {
        $this->options['options']['scaleToWidth'] = $width;
        return $this;
    }

    /**
     * Scale PDF to height.
     */
    public function scaleToHeight(int $height): self
    {
        $this->options['options']['scaleToHeight'] = $height;
        return $this;
    }

    /**
     * Add delay before taking screenshot.
     */
    public function delay(int $milliseconds): self
    {
        $this->options['delay'] = $milliseconds;
        return $this;
    }

    /**
     * Set default timeout for all navigations.
     */
    public function setDefaultNavigationTimeout(int $timeout): self
    {
        $this->options['setDefaultNavigationTimeout'] = $timeout;
        return $this;
    }

    /**
     * Set default timeout for all operations.
     */
    public function setDefaultTimeout(int $timeout): self
    {
        $this->options['setDefaultTimeout'] = $timeout;
        return $this;
    }

    /**
     * Add initialization script to run in every page context.
     * 
     * @param array{
     *   path?: string,
     *   content?: string
     * } $script
     */
    public function addInitScript(array $script): self
    {
        if (!isset($this->options['addInitScript'])) {
            $this->options['addInitScript'] = [];
        }
        $this->options['addInitScript'][] = $script;
        return $this;
    }

    /**
     * Enable/disable Content Security Policy bypass.
     */
    public function setBypassCSP(bool $enabled = true): self
    {
        $this->options['setBypassCSP'] = $enabled;
        return $this;
    }

    /**
     * Enable/disable request interception.
     */
    public function setRequestInterception(bool $enabled = true): self
    {
        $this->options['setRequestInterception'] = $enabled;
        return $this;
    }

    /**
     * Set extra CDP parameters.
     * 
     * @param array<string,mixed> $params
     */
    public function setExtraParams(array $params): self
    {
        $this->options['extraParams'] = $params;
        return $this;
    }

    /**
     * Configure screencast behavior.
     * 
     * @param array{
     *   format?: string,
     *   quality?: int,
     *   maxWidth?: int,
     *   maxHeight?: int,
     *   everyNthFrame?: int
     * } $options
     */
    public function setScreencast(array $options): self
    {
        $this->options['screencast'] = $options;
        return $this;
    }

    /**
     * Configure video recording.
     * 
     * @param array{
     *   dir?: string,
     *   size?: array{width: int, height: int},
     *   fps?: int,
     *   format?: string,
     *   codec?: string
     * } $options
     */
    public function setVideoRecording(array $options): self
    {
        $this->options['video'] = $options;
        return $this;
    }

    /**
     * Set preferred color scheme.
     * 
     * @param 'light'|'dark'|'no-preference' $scheme
     */
    public function setPreferredColorScheme(string $scheme): self
    {
        $this->options['preferredColorScheme'] = $scheme;
        return $this;
    }

    /**
     * Set browser locale.
     */
    public function setLocale(string $locale): self
    {
        $this->options['locale'] = $locale;
        return $this;
    }

    /**
     * Set download path for files.
     */
    public function setDownloadPath(string $path): self
    {
        $this->options['downloadPath'] = $path;
        return $this;
    }

    /**
     * Configure HAR recording.
     * 
     * @param array{
     *   path: string,
     *   omitContent?: bool,
     *   mode?: 'minimal'|'full'
     * } $options
     */
    public function setRecordHar(array $options): self
    {
        $this->options['recordHar'] = $options;
        return $this;
    }

    /**
     * Configure session video recording.
     * 
     * @param array{
     *   dir: string,
     *   size?: array{width: int, height: int},
     *   saveAs?: string
     * } $options
     */
    public function setRecordVideo(array $options): self
    {
        $this->options['recordVideo'] = $options;
        return $this;
    }

    /**
     * Add Chrome launch arguments.
     * 
     * @param string[] $args
     */
    public function addArguments(array $args): self
    {
        if (!isset($this->options['addArguments'])) {
            $this->options['addArguments'] = [];
        }
        $this->options['addArguments'] = array_merge($this->options['addArguments'], $args);
        return $this;
    }

    /**
     * Set browser environment variables.
     * 
     * @param array<string,string> $vars
     */
    public function setEnvironmentVariables(array $vars): self
    {
        $this->options['env'] = $vars;
        return $this;
    }
} 