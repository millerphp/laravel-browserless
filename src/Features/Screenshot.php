<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use GuzzleHttp\Psr7\Request;
use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Exceptions\ScreenshotException;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasCookieManagement;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasNavigationOptions;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasOptions;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasQueryParameters;
use MillerPHP\LaravelBrowserless\Responses\ScreenshotResponse;

class Screenshot
{
    use HasCookieManagement;
    use HasNavigationOptions;
    use HasOptions;
    use HasQueryParameters;

    /**
     * The options for the screenshot generation.
     *
     * @var array<string,mixed>
     */
    protected array $options = [];

    /**
     * Create a new Screenshot instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {
        $this->options = [
            'options' => [], // For page.screenshot options
            'gotoOptions' => [], // For page.goto options
            'viewport' => [
                'width' => 800,
                'height' => 600,
                'deviceScaleFactor' => 1.0,
                'isMobile' => false,
                'hasTouch' => false,
                'isLandscape' => false,
            ],
        ];
    }

    /**
     * Set the HTML content to screenshot.
     */
    public function html(string $html): self
    {
        $this->options['html'] = $html;

        return $this;
    }

    /**
     * Set the URL to screenshot.
     */
    public function url(string $url): self
    {
        $this->options['url'] = $url;

        return $this;
    }

    /**
     * Set whether to capture the full page.
     */
    public function fullPage(bool $fullPage = true): self
    {
        $this->options['options']['fullPage'] = $fullPage;

        return $this;
    }

    /**
     * Set the screenshot type (jpeg, png, or webp).
     */
    public function type(string $type): self
    {
        if (! in_array($type, ['jpeg', 'png', 'webp'])) {
            throw ScreenshotException::invalidOptions('Type must be jpeg, png, or webp');
        }
        $this->options['options']['type'] = $type;

        return $this;
    }

    /**
     * Set the quality of the image (0-100).
     */
    public function quality(int $quality): self
    {
        if ($quality < 0 || $quality > 100) {
            throw ScreenshotException::invalidOptions('Quality must be between 0 and 100');
        }
        $this->options['options']['quality'] = $quality;

        return $this;
    }

    /**
     * Set whether to omit the background.
     */
    public function omitBackground(bool $omit = true): self
    {
        $this->options['options']['omitBackground'] = $omit;

        return $this;
    }

    /**
     * Set the clip area of the page.
     *
     * @param  float  $x  The x-coordinate of the top-left corner of the clip area
     * @param  float  $y  The y-coordinate of the top-left corner of the clip area
     * @param  float  $width  The width of the clip area
     * @param  float  $height  The height of the clip area
     */
    public function clip(
        float $x = 0,
        float $y = 0,
        float $width = 800,
        float $height = 600
    ): self {
        $this->options['options']['clip'] = [
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
        ];

        return $this;
    }

    /**
     * Add a script tag to the page.
     *
     * @param  array{url?: string, content?: string}  $script
     */
    public function addScript(array $script): self
    {
        if (! isset($this->options['addScriptTag'])) {
            $this->options['addScriptTag'] = [];
        }
        $this->options['addScriptTag'][] = $script;

        return $this;
    }

    /**
     * Add a style tag to the page.
     *
     * @param  array{url?: string, content?: string}  $style
     */
    public function addStyle(array $style): self
    {
        if (! isset($this->options['addStyleTag'])) {
            $this->options['addStyleTag'] = [];
        }
        $this->options['addStyleTag'][] = $style;

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
     * Set viewport dimensions.
     */
    public function viewport(int $width, int $height, float $deviceScaleFactor = 1.0): self
    {
        $this->options['viewport'] = [
            'width' => $width,
            'height' => $height,
            'deviceScaleFactor' => $deviceScaleFactor,
        ];

        return $this;
    }

    /**
     * Set the device to emulate.
     */
    public function device(string $name): self
    {
        $this->options['options']['deviceName'] = $name;

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
     * Set screenshot encoding (binary or base64).
     */
    public function encoding(string $encoding): self
    {
        if (! in_array($encoding, ['binary', 'base64'])) {
            throw ScreenshotException::invalidOptions('Encoding must be binary or base64');
        }
        $this->options['encoding'] = $encoding;

        return $this;
    }

    /**
     * Set JPEG optimization options.
     *
     * @param array{
     *   progressive?: bool,
     *   optimizeScans?: bool,
     *   chromaSubsampling?: bool
     * } $options
     */
    public function jpegOptimization(array $options): self
    {
        $this->options['jpegOptimization'] = $options;

        return $this;
    }

    /**
     * Set PNG optimization options.
     *
     * @param array{
     *   compressionLevel?: int,
     *   palette?: bool,
     *   interlaced?: bool
     * } $options
     */
    public function pngOptimization(array $options): self
    {
        $this->options['pngOptimization'] = $options;

        return $this;
    }

    /**
     * Capture from specific frame/iframe.
     */
    public function frameSelector(string $selector): self
    {
        $this->options['frameSelector'] = $selector;

        return $this;
    }

    /**
     * Set advanced capture options.
     *
     * @param array{
     *   optimizationLevel?: int,
     *   captureBeyondViewport?: bool,
     *   fromSurface?: bool,
     *   disableAnimations?: bool
     * } $options
     */
    public function captureOptions(array $options): self
    {
        $this->options['captureOptions'] = $options;

        return $this;
    }

    /**
     * Capture specific element.
     *
     * @param array{
     *   selector: string,
     *   padding?: int,
     *   scrollIntoView?: bool
     * } $options
     */
    public function elementScreenshot(array $options): self
    {
        $this->options['elementScreenshot'] = $options;

        return $this;
    }

    /**
     * Mask sensitive elements.
     *
     * @param array{
     *   selector: string,
     *   color?: string,
     *   opacity?: float
     * }[] $selectors
     */
    public function maskSelectors(array $selectors): self
    {
        $this->options['maskSelectors'] = $selectors;

        return $this;
    }

    /**
     * Set WebP specific options.
     *
     * @param array{
     *   lossless?: bool,
     *   nearLossless?: bool,
     *   smartSubsample?: bool,
     *   mixed?: bool
     * } $options
     */
    public function webpOptions(array $options): self
    {
        $this->options['webpOptions'] = $options;

        return $this;
    }

    /**
     * Capture performance timeline.
     *
     * @param array{
     *   screenshots?: bool,
     *   trace?: bool,
     *   network?: bool,
     *   memory?: bool
     * } $options
     */
    public function captureTimeline(array $options): self
    {
        $this->options['captureTimeline'] = $options;

        return $this;
    }

    /**
     * Configure page scrolling before capture.
     *
     * @param array{
     *   direction?: 'vertical'|'horizontal',
     *   distance?: int,
     *   speed?: int,
     *   smooth?: bool
     * } $options
     */
    public function scrollPage(array $options): self
    {
        $this->options['scrollPage'] = $options;

        return $this;
    }

    /**
     * Wait for JavaScript expression to be true.
     */
    public function waitForExpression(string $expression, ?int $timeout = null): self
    {
        $this->options['waitForExpression'] = [
            'expression' => $expression,
            'timeout' => $timeout,
        ];

        return $this;
    }

    /**
     * Emulate vision deficiencies.
     *
     * @param  'achromatopsia'|'deuteranopia'|'protanopia'|'tritanopia'|'blurredVision'|null  $type
     */
    public function emulateVisionDeficiency(?string $type): self
    {
        $this->options['emulateVisionDeficiency'] = $type;

        return $this;
    }

    /**
     * Configure clip overflow behavior.
     *
     * @param array{
     *   strategy: 'crop'|'expand'|'scroll',
     *   padding?: int,
     *   fadeOut?: bool
     * } $options
     */
    public function setClipOverflow(array $options): self
    {
        $this->options['clipOverflow'] = $options;

        return $this;
    }

    /**
     * Set optimization preset.
     *
     * @param  'balanced'|'performance'|'quality'|'size'  $preset
     */
    public function setOptimizationPreset(string $preset): self
    {
        $this->options['optimizationPreset'] = $preset;

        return $this;
    }

    /**
     * Set custom background color.
     *
     * @param array{
     *   r: int,
     *   g: int,
     *   b: int,
     *   alpha?: float
     * } $color
     */
    public function setBackgroundColor(array $color): self
    {
        $this->options['backgroundColor'] = $color;

        return $this;
    }

    /**
     * Set quality vs size priority.
     *
     * @param array{
     *   mode: 'quality'|'size'|'balanced',
     *   threshold?: float,
     *   allowLossless?: bool
     * } $options
     */
    public function setQualityPriority(array $options): self
    {
        $this->options['qualityPriority'] = $options;

        return $this;
    }

    /**
     * Set screenshot animation options.
     *
     * @param array{
     *   duration?: int,
     *   fps?: int,
     *   frames?: int,
     *   format?: 'gif'|'webp'|'png',
     *   quality?: int,
     *   loop?: bool
     * } $options
     */
    public function setAnimationOptions(array $options): self
    {
        $this->options['animationOptions'] = $options;

        return $this;
    }

    /**
     * Set screenshot composition options.
     *
     * @param array{
     *   layers?: array{selector: string, index: int}[],
     *   blendMode?: string,
     *   opacity?: float,
     *   mask?: string
     * } $options
     */
    public function setCompositionOptions(array $options): self
    {
        $this->options['compositionOptions'] = $options;

        return $this;
    }

    /**
     * Configure screenshot retries.
     *
     * @param array{
     *   attempts?: int,
     *   delay?: int,
     *   validateFn?: string,
     *   timeout?: int
     * } $options
     */
    public function setRetryOptions(array $options): self
    {
        $this->options['retryOptions'] = $options;

        return $this;
    }

    /**
     * Set advanced image processing options.
     *
     * @param array{
     *   sharpen?: array{amount: float, radius: float, threshold: float},
     *   blur?: array{sigma: float, radius: float},
     *   brightness?: float,
     *   contrast?: float,
     *   gamma?: float,
     *   grayscale?: bool,
     *   normalize?: bool
     * } $options
     */
    public function setImageProcessing(array $options): self
    {
        $this->options['imageProcessing'] = $options;

        return $this;
    }

    /**
     * Configure OCR settings.
     *
     * @param array{
     *   enabled: bool,
     *   language?: string,
     *   confidence?: float,
     *   preprocessing?: array{
     *     deskew?: bool,
     *     denoise?: bool,
     *     scale?: float
     *   }
     * } $options
     */
    public function setOpticalCharacterRecognition(array $options): self
    {
        $this->options['ocr'] = $options;

        return $this;
    }

    /**
     * Set visual diff comparison options.
     *
     * @param array{
     *   baseImage: string,
     *   threshold?: float,
     *   highlightColor?: string,
     *   ignoreRegions?: array{x: int, y: int, width: int, height: int}[],
     *   outputDiffMask?: bool
     * } $options
     */
    public function setDiffOptions(array $options): self
    {
        $this->options['diffOptions'] = $options;

        return $this;
    }

    /**
     * Set granular image optimization settings.
     *
     * @param array{
     *   colorQuantization?: array{
     *     enabled: bool,
     *     colors?: int,
     *     dither?: bool
     *   },
     *   metadata?: array{
     *     strip?: bool,
     *     preserve?: string[]
     *   },
     *   resampling?: array{
     *     algorithm: 'lanczos3'|'bilinear'|'bicubic',
     *     unsharpMask?: bool
     *   }
     * } $options
     */
    public function setImageOptimization(array $options): self
    {
        $this->options['imageOptimization'] = $options;

        return $this;
    }

    /**
     * Configure advanced viewport emulation.
     *
     * @param array{
     *   orientation?: array{
     *     angle: int,
     *     type: 'portraitPrimary'|'portraitSecondary'|'landscapePrimary'|'landscapeSecondary'
     *   },
     *   devicePixelRatio?: float,
     *   colorDepth?: int,
     *   colorSpace?: string,
     *   forcedColors?: 'active'|'none',
     *   reducedMotion?: 'reduce'|'no-preference'
     * } $options
     */
    public function setViewportEmulation(array $options): self
    {
        $this->options['viewportEmulation'] = $options;

        return $this;
    }

    /**
     * Set advanced image filtering options.
     *
     * @param array{
     *   filters?: array{
     *     name: string,
     *     options?: array<string,mixed>
     *   }[],
     *   convolution?: array{
     *     kernel: float[][],
     *     divisor?: float,
     *     offset?: float
     *   },
     *   colorBalance?: array{
     *     shadows?: array{r: float, g: float, b: float},
     *     midtones?: array{r: float, g: float, b: float},
     *     highlights?: array{r: float, g: float, b: float}
     *   }
     * } $options
     */
    public function setImageFilters(array $options): self
    {
        $this->options['imageFilters'] = $options;

        return $this;
    }

    /**
     * Configure color management.
     *
     * @param array{
     *   profile?: string,
     *   intent?: 'perceptual'|'saturation'|'relative'|'absolute',
     *   blackPointCompensation?: bool,
     *   calibration?: array{
     *     gamma?: float,
     *     brightness?: float,
     *     contrast?: float,
     *     exposure?: float
     *   }
     * } $options
     */
    public function setColorManagement(array $options): self
    {
        $this->options['colorManagement'] = $options;

        return $this;
    }

    /**
     * Set advanced output configuration.
     *
     * @param array{
     *   format?: array{
     *     progressive?: bool,
     *     baseline?: bool,
     *     optimizeScans?: bool
     *   },
     *   metadata?: array{
     *     title?: string,
     *     author?: string,
     *     description?: string,
     *     copyright?: string,
     *     keywords?: string[]
     *   },
     *   compression?: array{
     *     method?: 'auto'|'rle'|'lzw'|'deflate',
     *     level?: int,
     *     strategy?: string
     *   }
     * } $options
     */
    public function setOutputOptions(array $options): self
    {
        $this->options['outputOptions'] = $options;

        return $this;
    }

    /**
     * Set an overlay to be rendered on top of the page.
     */
    public function overlay(string $html): self
    {
        $this->options['overlay'] = $html;

        return $this;
    }

    /**
     * Send the screenshot generation request.
     *
     * @throws ScreenshotException
     */
    public function send(): ScreenshotResponse
    {
        $this->validateOptions();

        try {
            $request = new Request(
                'POST',
                $this->buildQueryString($this->client->url().'/screenshot?token='.$this->client->token()),
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode($this->options, JSON_THROW_ON_ERROR)
            );

            return new ScreenshotResponse($this->client->send($request));
        } catch (\JsonException $e) {
            throw ScreenshotException::fromResponse($e);
        } catch (\Throwable $e) {
            throw ScreenshotException::fromResponse($e);
        }
    }

    /**
     * Validate the screenshot options.
     *
     * @throws ScreenshotException
     */
    protected function validateOptions(): void
    {
        if (! isset($this->options['url']) && ! isset($this->options['html'])) {
            throw ScreenshotException::invalidOptions('Either URL or HTML content must be provided');
        }

        if (isset($this->options['options']['quality'])) {
            $quality = $this->options['options']['quality'];
            if ($quality < 0 || $quality > 100) {
                throw ScreenshotException::invalidOptions('Quality must be between 0 and 100');
            }
        }
    }
}
