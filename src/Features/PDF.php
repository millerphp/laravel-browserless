<?php

declare(strict_types=1);

namespace MillerPHP\LaravelBrowserless\Features;

use GuzzleHttp\Psr7\Request;
use MillerPHP\LaravelBrowserless\Contracts\ClientContract;
use MillerPHP\LaravelBrowserless\Exceptions\PDFGenerationException;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasAuthentication;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasCookieManagement;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasNavigationOptions;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasOptions;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasQueryParameters;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasResourceInjection;
use MillerPHP\LaravelBrowserless\Features\Concerns\HasViewport;
use MillerPHP\LaravelBrowserless\Responses\PDFResponse;

class PDF
{
    use HasAuthentication;
    use HasCookieManagement;
    use HasNavigationOptions;
    use HasOptions;
    use HasQueryParameters;
    use HasResourceInjection;
    use HasViewport;

    /**
     * The options for the PDF generation.
     *
     * @var array<string,mixed>
     */
    protected array $options = [];

    /**
     * Create a new PDF instance.
     */
    public function __construct(
        protected readonly ClientContract $client
    ) {
        $this->options = [
            'options' => [], // For page.pdf options
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
     * Set the HTML content to generate PDF from.
     */
    public function html(string $html): self
    {
        $this->options['html'] = $html;

        return $this;
    }

    /**
     * Set the URL to generate PDF from.
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
     * Set viewport dimensions.
     *
     * @param  array{width: int, height: int}|int  $width  The width in pixels or an array of dimensions
     * @param  int|null  $height  The height in pixels (required if $width is not an array)
     *
     * @throws \InvalidArgumentException If dimensions are invalid
     */
    public function viewport(array|int $width, ?int $height = null): self
    {
        if (is_array($width)) {
            $this->options['viewport'] = [
                'width' => $width['width'],
                'height' => $width['height'],
                'deviceScaleFactor' => 1.0,
                'isMobile' => false,
                'hasTouch' => false,
                'isLandscape' => false,
            ];

            return $this;
        }

        if ($height === null) {
            throw new \InvalidArgumentException('Height is required when width is an integer');
        }

        $this->options['viewport'] = [
            'width' => $width,
            'height' => $height,
            'deviceScaleFactor' => 1.0,
            'isMobile' => false,
            'hasTouch' => false,
            'isLandscape' => false,
        ];

        return $this;
    }

    /**
     * Set whether to create tagged (accessible) PDF.
     */
    public function tagged(bool $tagged = true): self
    {
        $this->options['options']['tagged'] = $tagged;

        return $this;
    }

    /**
     * Set PDF encryption options.
     *
     * @param  array<string,bool>  $permissions
     */
    public function encryption(?string $userPassword = null, ?string $ownerPassword = null, array $permissions = []): self
    {
        if ($userPassword !== null || $ownerPassword !== null) {
            $this->options['options']['encryption'] = array_filter([
                'userPassword' => $userPassword,
                'ownerPassword' => $ownerPassword,
                'permissions' => $permissions,
            ]);
        }

        return $this;
    }

    // Page Size and Layout Options

    /**
     * Set the paper format (e.g., 'Letter', 'A4', 'A3', 'A5', 'Legal', 'Tabloid').
     */
    public function format(string $format): self
    {
        $this->options['options']['format'] = $format;

        return $this;
    }

    /**
     * Set the paper width (accepts units like px, in, cm, mm).
     */
    public function width(string $width): self
    {
        $this->options['options']['width'] = $width;

        return $this;
    }

    /**
     * Set the paper height (accepts units like px, in, cm, mm).
     */
    public function height(string $height): self
    {
        $this->options['options']['height'] = $height;

        return $this;
    }

    /**
     * Set page margins in inches.
     */
    public function margin(float $top = 0, float $right = 0, float $bottom = 0, float $left = 0): self
    {
        $this->options['options']['margin'] = [
            'top' => $top,
            'right' => $right,
            'bottom' => $bottom,
            'left' => $left,
        ];

        return $this;
    }

    /**
     * Set whether to print in landscape orientation.
     */
    public function landscape(bool $landscape = true): self
    {
        $this->options['options']['landscape'] = $landscape;

        return $this;
    }

    // Content Display Options

    /**
     * Set whether to print background graphics.
     */
    public function printBackground(bool $printBackground = true): self
    {
        $this->options['options']['printBackground'] = $printBackground;

        return $this;
    }

    /**
     * Set whether to display header and footer.
     */
    public function displayHeaderFooter(bool $display = true): self
    {
        $this->options['options']['displayHeaderFooter'] = $display;

        return $this;
    }

    /**
     * Set the header template (HTML).
     */
    public function headerTemplate(string $html): self
    {
        $this->options['options']['headerTemplate'] = $html;

        return $this;
    }

    /**
     * Set the footer template (HTML).
     */
    public function footerTemplate(string $html): self
    {
        $this->options['options']['footerTemplate'] = $html;

        return $this;
    }

    /**
     * Set whether to prefer CSS page size over viewport size.
     */
    public function preferCSSPageSize(bool $prefer = true): self
    {
        $this->options['options']['preferCSSPageSize'] = $prefer;

        return $this;
    }

    // Page Range Options

    /**
     * Set page ranges to print, e.g., '1-5, 8, 11-13'.
     */
    public function pageRanges(string $ranges): self
    {
        $this->options['options']['pageRanges'] = $ranges;

        return $this;
    }

    // Scale Options

    /**
     * Set the scale of the webpage rendering (between 0.1 and 2).
     */
    public function scale(float $scale): self
    {
        if ($scale < 0.1 || $scale > 2) {
            throw new \InvalidArgumentException('Scale must be between 0.1 and 2');
        }
        $this->options['options']['scale'] = $scale;

        return $this;
    }

    // Authentication Options

    /**
     * Set HTTP authentication credentials.
     *
     * @param  string|array{username: string, password: string}  $username  The username or an array of credentials
     * @param  string|null  $password  The password (required if $username is not an array)
     *
     * @throws \InvalidArgumentException If credentials are invalid
     */
    public function authenticate(string|array $username, ?string $password = null): self
    {
        if (is_string($username)) {
            if ($password === null) {
                throw new \InvalidArgumentException('Password is required when username is a string');
            }
            $this->options['authentication'] = [
                'username' => $username,
                'password' => $password,
            ];

            return $this;
        }

        $this->options['authentication'] = $username;

        return $this;
    }

    // Additional Options

    /**
     * Set whether to emit the PDF that would have been downloaded if the page contained an inline download.
     */
    public function emulateMedia(bool $emulate = true): self
    {
        $this->options['options']['emulateMedia'] = $emulate;

        return $this;
    }

    /**
     * Set whether to print using the CSS screen media type.
     */
    public function printMediaType(bool $print = true): self
    {
        $this->options['options']['printMediaType'] = $print;

        return $this;
    }

    /**
     * Set margin units (px, in, cm, mm).
     */
    public function marginUnits(string $units): self
    {
        $this->options['options']['marginUnits'] = $units;

        return $this;
    }

    /**
     * Set PDF metadata.
     *
     * @param array{
     *   title?: string,
     *   author?: string,
     *   subject?: string,
     *   keywords?: string,
     *   creator?: string,
     *   producer?: string
     * } $metadata
     */
    public function metadata(array $metadata): self
    {
        $this->options['options']['metadata'] = $metadata;

        return $this;
    }

    /**
     * Set PDF compression level (0-9).
     */
    public function compressionLevel(int $level): self
    {
        if ($level < 0 || $level > 9) {
            throw PDFGenerationException::invalidOptions('Compression level must be between 0 and 9');
        }
        $this->options['options']['compressionLevel'] = $level;

        return $this;
    }

    /**
     * Enable PDF/A compliance.
     */
    public function pdfA(bool $enabled = true): self
    {
        $this->options['options']['pdfA'] = $enabled;

        return $this;
    }

    /**
     * Set advanced page range options.
     *
     * @param array{
     *   ranges: string[],
     *   separator?: string,
     *   reverse?: bool,
     *   collate?: bool
     * } $options
     */
    public function setPageRanges(array $options): self
    {
        $this->options['options']['pageRanges'] = $options;

        return $this;
    }

    /**
     * Set advanced header template options.
     *
     * @param array{
     *   html: string,
     *   height?: string,
     *   variables?: array<string,string>,
     *   assets?: array{url: string, type: 'script'|'style'}[],
     *   waitForSelector?: string
     * } $options
     */
    public function setHeaderTemplate(array $options): self
    {
        $this->options['options']['headerTemplate'] = $options;

        return $this;
    }

    /**
     * Set advanced footer template options.
     *
     * @param array{
     *   html: string,
     *   height?: string,
     *   variables?: array<string,string>,
     *   assets?: array{url: string, type: 'script'|'style'}[],
     *   waitForSelector?: string
     * } $options
     */
    public function setFooterTemplate(array $options): self
    {
        $this->options['options']['footerTemplate'] = $options;

        return $this;
    }

    /**
     * Add watermark to PDF.
     *
     * @param array{
     *   text: string,
     *   font?: string,
     *   size?: int,
     *   color?: string,
     *   opacity?: float,
     *   angle?: int,
     *   position?: 'center'|'diagonal'|'tile',
     *   layer?: 'foreground'|'background'
     * } $options
     */
    public function setWatermark(array $options): self
    {
        $this->options['options']['watermark'] = $options;

        return $this;
    }

    /**
     * Set PDF outline/bookmarks options.
     *
     * @param array{
     *   enabled?: bool,
     *   depth?: int,
     *   headingTags?: string[],
     *   customSelectors?: array{selector: string, level: int}[]
     * } $options
     */
    public function setOutlineOptions(array $options): self
    {
        $this->options['options']['outline'] = $options;

        return $this;
    }

    /**
     * Set PDF accessibility options.
     *
     * @param array{
     *   tagged?: bool,
     *   structureOnly?: bool,
     *   language?: string,
     *   title?: string,
     *   author?: string,
     *   subject?: string,
     *   keywords?: string[],
     *   preserveAcroForm?: bool
     * } $options
     */
    public function setAccessibilityOptions(array $options): self
    {
        $this->options['options']['accessibility'] = $options;

        return $this;
    }

    /**
     * Configure digital signature options.
     *
     * @param array{
     *   certificate: string,
     *   privateKey: string,
     *   password?: string,
     *   location?: string,
     *   reason?: string,
     *   contactInfo?: string,
     *   signatureAppearance?: array{
     *     background?: string,
     *     text?: string,
     *     fontSize?: int,
     *     position?: array{x: int, y: int, width: int, height: int}
     *   }
     * } $options
     */
    public function setSignature(array $options): self
    {
        $this->options['options']['signature'] = $options;

        return $this;
    }

    /**
     * Add file attachments to PDF.
     *
     * @param array{
     *   path: string,
     *   name?: string,
     *   description?: string,
     *   creationDate?: string,
     *   modificationDate?: string,
     *   mimeType?: string,
     *   relationship?: 'Source'|'Data'|'Alternative'|'Supplement'|'Unspecified'
     * }[] $attachments
     */
    public function setAttachments(array $attachments): self
    {
        $this->options['options']['attachments'] = $attachments;

        return $this;
    }

    /**
     * Set custom page labels.
     *
     * @param array{
     *   startPage: int,
     *   style?: 'decimal'|'roman'|'letters'|'none',
     *   prefix?: string,
     *   firstNumber?: int
     * }[] $labels
     */
    public function setPageLabels(array $labels): self
    {
        $this->options['options']['pageLabels'] = $labels;

        return $this;
    }

    /**
     * Configure PDF/X compliance.
     *
     * @param array{
     *   version: 'PDF/X-1a'|'PDF/X-3'|'PDF/X-4',
     *   outputIntent?: array{
     *     outputCondition: string,
     *     outputConditionIdentifier: string,
     *     registryName?: string,
     *     info?: string
     *   },
     *   colorConversion?: array{
     *     enabled: bool,
     *     profile?: string
     *   }
     * } $options
     */
    public function setPdfX(array $options): self
    {
        $this->options['options']['pdfX'] = $options;

        return $this;
    }

    /**
     * Set advanced font options.
     *
     * @param array{
     *   embedding?: array{
     *     subset?: bool,
     *     force?: bool,
     *     formats?: string[]
     *   },
     *   fallback?: array{
     *     enabled: bool,
     *     fonts?: string[]
     *   },
     *   aliases?: array<string,string>,
     *   directory?: string
     * } $options
     */
    public function setFontOptions(array $options): self
    {
        $this->options['options']['fontOptions'] = $options;

        return $this;
    }

    /**
     * Send the PDF generation request.
     *
     * @throws PDFGenerationException
     */
    public function send(): PDFResponse
    {
        $this->validateOptions();

        try {
            $request = new Request(
                'POST',
                $this->buildQueryString($this->client->url().'/pdf?token='.$this->client->token()),
                [
                    'Content-Type' => 'application/json',
                ],
                json_encode($this->options, JSON_THROW_ON_ERROR)
            );

            return new PDFResponse($this->client->send($request));
        } catch (\JsonException $e) {
            throw PDFGenerationException::fromResponse($e);
        } catch (\Throwable $e) {
            throw PDFGenerationException::fromResponse($e);
        }
    }

    /**
     * Validate the PDF options.
     *
     * @throws PDFGenerationException
     */
    protected function validateOptions(): void
    {
        if (! isset($this->options['url']) && ! isset($this->options['html'])) {
            throw PDFGenerationException::invalidOptions('Either URL or HTML content must be provided');
        }

        if (isset($this->options['options']['scale'])) {
            $scale = $this->options['options']['scale'];
            if ($scale < 0.1 || $scale > 2) {
                throw PDFGenerationException::invalidOptions('Scale must be between 0.1 and 2');
            }
        }

        // Add any other validation rules here
    }
}
