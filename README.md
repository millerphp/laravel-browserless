# Laravel Browserless SDK

A powerful Laravel SDK for interacting with Browserless.io's API services. This package provides a clean, fluent interface for working with Browserless.io's Chrome-as-a-service platform, enabling PDF generation, screenshots, content capture, and more.

## Table of Contents

- [Features](#features)
- [BrowserQL (BQL)](#browserql-bql)
- [Getting Started](#getting-started)
  - [Cloud Service](#cloud-service)
  - [Local Docker](#local-docker)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [PDF Generation](#pdf-generation)
  - [Screenshots](#screenshots)
  - [Content Capture](#content-capture)
  - [File Downloads](#file-downloads)
  - [Function Execution](#function-execution)
  - [Unblock Bot Detection](#unblock-bot-detection)
  - [Content Scraping](#content-scraping)
  - [Performance Analysis](#performance-analysis)
  - [Session Management](#session-management)
  - [Worker Configuration](#worker-configuration)

## Features

- 🔥 **PDF Generation**: Create high-quality PDFs from URLs or HTML content
- 📸 **Screenshots**: Capture full-page or element-specific screenshots
- 🌐 **Content Capture**: Extract rendered HTML content from JavaScript-heavy pages
- ⬇️ **File Downloads**: Programmatically download files from web pages
- 🤖 **Function Execution**: Run custom JavaScript code in a browser context
- 🛡️ **Bot Detection Bypass**: Sophisticated bot detection avoidance
- 🔍 **Content Scraping**: Extract structured data from web pages
- 📊 **Performance Analysis**: Generate Lighthouse-powered performance reports
- 👥 **Session Management**: Control browser sessions (Enterprise/Self-hosted)
- ⚙️ **Worker Configuration**: Access and manage worker settings

## BrowserQL (BQL)

BrowserQL is a powerful query language for browser automation, combining the flexibility of GraphQL with browser operations. It allows you to write complex browser automation workflows in a declarative way.

```php
use MillerPHP\LaravelBrowserless\Facades\Browserless;

// Basic BQL query
$result = Browserless::bql()
    ->query('
        mutation scraping_example {
            articles: {
                cnn: goto(
                    url: "https://edition.cnn.com",
                    waitUntil: load
                ) {
                    status
                }
                
                cnnArticles: evaluate(
                    content: "JSON.stringify(Array.from(document.querySelectorAll(\'[data-component-name=\"card\"]\'))
                        .map(e => {
                            const h = e.querySelector(\'[data-editable=\"headline\"]\');
                            if (!h) return null;
                            const t = h.textContent.trim();
                            if (!t) return null;
                            const l = e.querySelector(\'a.container__link\');
                            return {
                                headline: t,
                                link: l ? l.href : null
                            };
                        })
                        .filter(x => x !== null));"
                ) {
                    value
                }
            }
        }
    ')
    ->humanLike()
    ->send();

// Access the results
$articles = json_decode($result->get('data.articles.cnnArticles.value'), true);
```

### BQL Options

| Method | Description | Example |
|--------|-------------|---------|
| `query(string $query)` | Set the BQL query | `->query('mutation { ... }')` |
| `variables(array $variables)` | Set query variables | `->variables(['url' => 'example.com'])` |
| `operationName(?string $name)` | Set operation name | `->operationName('scraping_example')` |
| `humanLike(bool $enabled = true)` | Enable human-like behavior | `->humanLike()` |
| `reconnect(bool $enabled = true)` | Enable reconnection | `->reconnect()` |
| `stealth(bool $enabled = true)` | Enable stealth mode | `->stealth()` |
| `proxy(?string $proxy)` | Set proxy configuration | `->proxy('http://proxy:8080')` |
| `timeout(int $milliseconds)` | Set timeout | `->timeout(30000)` |

### BQL Response Methods

| Method | Description |
|--------|-------------|
| `data()` | Get all response data |
| `get(string $key, mixed $default = null)` | Get specific data by key |
| `hasErrors()` | Check for errors |
| `errors()` | Get error messages |
| `response()` | Get raw response |

### Example: Multi-page Scraping

```php
$result = Browserless::bql()
    ->query('
        mutation multi_page_example {
            news: {
                cnn: goto(
                    url: "https://edition.cnn.com",
                    waitUntil: load
                ) {
                    status
                    articles: evaluate(
                        content: "JSON.stringify(Array.from(document.querySelectorAll(\'article\')).map(e => ({
                            title: e.querySelector(\'h3\')?.textContent?.trim(),
                            link: e.querySelector(\'a\')?.href
                        })).filter(x => x.title && x.link));"
                    ) {
                        value
                    }
                }
                
                bbc: goto(
                    url: "https://www.bbc.com/news",
                    waitUntil: load
                ) {
                    status
                    articles: evaluate(
                        content: "JSON.stringify(Array.from(document.querySelectorAll(\'a.gs-c-promo-heading\')).map(e => ({
                            title: e.textContent.trim(),
                            link: e.href
                        })));"
                    ) {
                        value
                    }
                }
            }
        }
    ')
    ->humanLike()
    ->stealth()
    ->send();

$cnnArticles = json_decode($result->get('data.news.cnn.articles.value'), true);
$bbcArticles = json_decode($result->get('data.news.bbc.articles.value'), true);
```

### Error Handling

```php
try {
    $result = Browserless::bql()
        ->query('mutation { ... }')
        ->send();
        
    if ($result->hasErrors()) {
        $errors = $result->errors();
        // Handle GraphQL errors
    }
} catch (BQLException $e) {
    // Handle execution errors
}
```

For more information about BrowserQL, visit the [official documentation](https://docs.browserless.io/browserql/start).

## Getting Started

### Cloud Service

1. Visit [Browserless.io](https://browserless.io) and create an account
2. Navigate to your dashboard
3. Copy your API token from the dashboard settings
4. Add your token to your Laravel environment:

```env
BROWSERLESS_TOKEN=your-api-token
BROWSERLESS_URL=https://production-sfo.browserless.io
```

### Local Docker

You can run Browserless locally or on your own infrastructure using Docker:

1. Pull the Docker image:
```bash
docker pull browserless/chrome
```

2. Run the container:
```bash
docker run -p 3000:3000 browserless/chrome
```

3. Configure your `.env` for local usage:
```env
BROWSERLESS_TOKEN=local
BROWSERLESS_URL=http://localhost:3000
```

#### Docker Configuration Options

Customize your Docker container with environment variables:

```bash
docker run -p 3000:3000 \
  -e "MAX_CONCURRENT_SESSIONS=10" \
  -e "CONNECTION_TIMEOUT=30000" \
  -e "MAX_QUEUE_LENGTH=20" \
  -e "TOKEN=your-secret-token" \
  browserless/chrome
```

Common environment variables:
- `MAX_CONCURRENT_SESSIONS`: Maximum concurrent sessions (default: 10)
- `CONNECTION_TIMEOUT`: Connection timeout in milliseconds (default: 30000)
- `MAX_QUEUE_LENGTH`: Maximum queued sessions (default: 10)
- `TOKEN`: Authentication token for securing your instance
- `CHROME_REFRESH_TIME`: Chrome process refresh interval
- `DEFAULT_BLOCK_ADS`: Block ads by default
- `DEFAULT_STEALTH`: Enable stealth mode by default
- `ENABLE_DEBUGGER`: Enable Chrome devtools protocol
- `PREBOOT_CHROME`: Start Chrome on container launch
- `WORKSPACE_DIR`: Custom workspace directory path

## Installation

Install via Composer:

```bash
composer require millerphp/laravel-browserless
```

## Configuration

1. Publish the configuration file:

```bash
php artisan vendor:publish --tag="browserless-config"
```

2. Configure your environment variables:

```env
BROWSERLESS_TOKEN=your-api-token
BROWSERLESS_URL=https://production-sfo.browserless.io

# Optional configurations
BROWSERLESS_TIMEOUT=30000
BROWSERLESS_IGNORE_HTTPS_ERRORS=false
BROWSERLESS_STEALTH=false
BROWSERLESS_BLOCK_ADS=true
```

## Usage

### PDF Generation

Generate PDFs from URLs or HTML content with extensive customization options:

```php
use MillerPHP\LaravelBrowserless\Facades\Browserless;

// Basic PDF generation
$pdf = Browserless::pdf()
    ->url('https://example.com')
    ->format('A4')
    ->landscape()
    ->margin(1, 1, 1, 1)
    ->send();

// Save to file
$pdf->save('document.pdf');

// Advanced PDF generation
$pdf = Browserless::pdf()
    ->url('https://example.com')
    ->format('A4')
    ->printBackground()
    ->displayHeaderFooter()
    ->headerTemplate('<div style="text-align: center;">My Company</div>')
    ->footerTemplate('<div style="text-align: center;">Page <span class="pageNumber"></span></div>')
    ->waitForNetworkIdle()
    ->authenticate('username', 'password')
    ->send();
```

[View all PDF options](#pdf-options)

### Screenshots

Capture screenshots with customizable settings:

```php
// Basic screenshot
$screenshot = Browserless::screenshot()
    ->url('https://example.com')
    ->type('png')
    ->fullPage()
    ->send();

// Advanced screenshot
$screenshot = Browserless::screenshot()
    ->url('https://example.com')
    ->type('jpeg')
    ->quality(80)
    ->clip(0, 0, 1920, 1080)
    ->waitForNetworkIdle()
    ->device('iPhone X')
    ->send();

// Save or download
$screenshot->save('screenshot.png');
$screenshot->download('my-screenshot.png');
```

[View all Screenshot options](#screenshot-options)

### Content Capture

Extract rendered HTML content:

```php
$content = Browserless::content()
    ->url('https://example.com')
    ->waitForNetworkIdle()
    ->rejectResourceTypes(['image', 'stylesheet'])
    ->waitForSelector('.content')
    ->send();

// Get the HTML content
$html = $content->content();
```

[View all Content options](#content-options)

### File Downloads

Download files programmatically:

```php
$download = Browserless::download()
    ->code('
        export default async function ({ page }) {
            await page.goto("https://example.com/file.pdf");
            // Download logic here
        }
    ')
    ->send();

$download->save('local-file.pdf');
```

[View all Download options](#download-options)

### Function Execution

Execute custom JavaScript in a browser context:

```php
$result = Browserless::executeFunction()
    ->code('
        export default async function ({ page }) {
            await page.goto("https://example.com");
            return {
                title: await page.title(),
                url: page.url(),
            };
        }
    ')
    ->send();

$data = $result->data();
```

[View all Function options](#function-options)

### Unblock Bot Detection

Bypass sophisticated bot detection:

```php
$result = Browserless::unblock()
    ->url('https://example.com')
    ->content(true)
    ->cookies(true)
    ->screenshot(true)
    ->send();

$html = $result->content();
$cookies = $result->cookies();
```

[View all Unblock options](#unblock-options)

### Content Scraping

Extract structured data from web pages:

```php
$result = Browserless::scrape()
    ->url('https://example.com')
    ->element('h1')
    ->element('.product', [
        'text' => true,
        'html' => true,
        'attributes' => ['id', 'class'],
    ])
    ->send();

$products = $result->results('.product');
```

[View all Scraping options](#scrape-options)

### Performance Analysis

Generate performance reports:

```php
$performance = Browserless::performance()
    ->url('https://example.com')
    ->categories(['performance', 'accessibility'])
    ->send();

$score = $performance->categoryScore('performance');
```

[View all Performance options](#performance-options)

### Session Management

Manage browser sessions (Enterprise/Self-hosted):

```php
$sessions = Browserless::sessions()->get();
$running = $sessions->running();
$session = $sessions->findById('browser-123');
```

[View all Session options](#session-options)

### Worker Configuration

Access worker settings:

```php
$config = Browserless::config()->get();
$maxConcurrent = $config->get('maxConcurrent');
$timeout = $config->get('timeout');
```

[View all Configuration options](#config-options)

## Testing

Run the test suite:

```bash
composer test
```

## Security

If you discover any security-related issues, please email security@example.com instead of using the issue tracker.

## Credits

- [Christopher Miller](https://github.com/millerchristopher)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Detailed Options Reference

### PDF Options {#pdf-options}

| Method | Description | Example |
|--------|-------------|---------|
| `url(string $url)` | Set the URL to generate PDF from | `->url('https://example.com')` |
| `html(string $html)` | Set HTML content to generate PDF from | `->html('<h1>Hello</h1>')` |
| `format(string $format)` | Set paper format (A4, Letter, etc.) | `->format('A4')` |
| `landscape(bool $landscape = true)` | Set landscape orientation | `->landscape()` |
| `margin(float $top, float $right, float $bottom, float $left)` | Set page margins in inches | `->margin(1, 1, 1, 1)` |
| `printBackground(bool $print = true)` | Include background graphics | `->printBackground()` |
| `displayHeaderFooter(bool $display = true)` | Show header and footer | `->displayHeaderFooter()` |
| `headerTemplate(string $html)` | Set header HTML template | `->headerTemplate('<div>Header</div>')` |
| `footerTemplate(string $html)` | Set footer HTML template | `->footerTemplate('<div>Footer</div>')` |
| `scale(float $scale)` | Set scale (0.1 to 2) | `->scale(0.8)` |
| `waitForNetworkIdle(bool $wait = true)` | Wait for network idle | `->waitForNetworkIdle()` |
| `timeout(int $milliseconds)` | Set navigation timeout | `->timeout(30000)` |
| `authenticate(string $username, string $password)` | Set HTTP authentication | `->authenticate('user', 'pass')` |
| `bestAttempt(bool $enabled = true)` | Continue even if awaited events timeout | `->bestAttempt()` |
| `addScriptTag(array $script)` | Inject JavaScript | `->addScriptTag(['url' => 'https://code.jquery.com/jquery-3.6.0.min.js'])` |
| `addStyleTag(array $style)` | Inject CSS | `->addStyleTag(['content' => 'body { background: #f0f0f0; }'])` |
| `rejectResourceTypes(array $types)` | Block specific resource types | `->rejectResourceTypes(['image', 'stylesheet'])` |
| `pageRanges(string $ranges)` | Set PDF page ranges | `->pageRanges('1-5, 8, 11-13')` |
| `taggedPDF(bool $enabled)` | Enable tagged PDF | `->taggedPDF()` |
| `metadata(array $metadata)` | Set PDF metadata | `->metadata(['title' => 'Doc'])` |
| `compressionLevel(int $level)` | Set compression (0-9) | `->compressionLevel(9)` |
| `pdfA(bool $enabled)` | Enable PDF/A compliance | `->pdfA()` |

### Screenshot Options {#screenshot-options}

| Method | Description | Example |
|--------|-------------|---------|
| `url(string $url)` | Set the URL to screenshot | `->url('https://example.com')` |
| `html(string $html)` | Set HTML content to screenshot | `->html('<h1>Hello</h1>')` |
| `type(string $type)` | Set image type (jpeg, png, webp) | `->type('png')` |
| `quality(int $quality)` | Set image quality (0-100) | `->quality(80)` |
| `fullPage(bool $full = true)` | Capture full page height | `->fullPage()` |
| `omitBackground(bool $omit = true)` | Make background transparent | `->omitBackground()` |
| `clip(int $x, int $y, int $width, int $height)` | Set capture area | `->clip(0, 0, 1920, 1080)` |
| `viewport(int $width, int $height)` | Set viewport dimensions | `->viewport(1920, 1080)` |
| `device(string $name)` | Set device to emulate | `->device('iPhone X')` |
| `waitForNetworkIdle(bool $wait = true)` | Wait for network idle | `->waitForNetworkIdle()` |
| `encoding(string $encoding)` | Set encoding (binary, base64) | `->encoding('base64')` |
| `jpegOptimization(array $options)` | JPEG optimization options | `->jpegOptimization(['progressive' => true])` |
| `pngOptimization(array $options)` | PNG optimization options | `->pngOptimization(['compressionLevel' => 9])` |

### Content Options {#content-options}

| Method | Description | Example |
|--------|-------------|---------|
| `url(string $url)` | Set the URL to capture | `->url('https://example.com')` |
| `html(string $html)` | Set HTML content to render | `->html('<h1>Hello</h1>')` |
| `waitForNetworkIdle(bool $wait = true)` | Wait for network idle | `->waitForNetworkIdle()` |
| `rejectResourceTypes(array $types)` | Reject specific resource types | `->rejectResourceTypes(['image'])` |
| `rejectRequestPatterns(array $patterns)` | Reject specific request patterns | `->rejectRequestPatterns(['/\.css$/'])` |
| `bestAttempt(bool $enabled = true)` | Continue on async errors | `->bestAttempt()` |
| `waitForEvent(string $event, ?int $timeout)` | Wait for page event | `->waitForEvent('load', 5000)` |
| `waitForFunction(string $function, ?int $timeout)` | Wait for function to execute | `->waitForFunction('() => true')` |
| `waitForSelector(string $selector, ?int $timeout)` | Wait for element to appear | `->waitForSelector('.ready')` |
| `contentOptions(array $options)` | Content handling options | `->contentOptions(['minify' => true])` |
| `extractElements(array $elements)` | Extract specific elements | `->extractElements([['selector' => '.item']])` |

### Download Options {#download-options}

| Method | Description | Example |
|--------|-------------|---------|
| `code(string $code)` | Set JavaScript code to execute | `->code('export default...')` |
| `context(array $context)` | Set context values for code | `->context(['url' => 'example.com'])` |
| `waitForNetworkIdle(bool $wait = true)` | Wait for network idle | `->waitForNetworkIdle()` |
| `timeout(int $milliseconds)` | Set navigation timeout | `->timeout(30000)` |
| `authenticate(string $username, string $password)` | Set HTTP authentication | `->authenticate('user', 'pass')` |
| `ignoreHTTPSErrors(bool $ignore = true)` | Ignore HTTPS errors | `->ignoreHTTPSErrors()` |

### Function Options {#function-options}

| Method | Description | Example |
|--------|-------------|---------|
| `code(string $code)` | Set JavaScript code to execute | `->code('export default...')` |
| `context(array $context)` | Set context values for code | `->context(['len' => 10])` |
| `waitForNetworkIdle(bool $wait = true)` | Wait for network idle | `->waitForNetworkIdle()` |
| `timeout(int $milliseconds)` | Set navigation timeout | `->timeout(30000)` |
| `executionContext(array $options)` | Context options | `->executionContext(['isolateFromGlobalScope' => true])` |
| `addModules(array $modules)` | Add external modules | `->addModules([['url' => 'https://esm.sh/lodash']])` |
| `evaluationTimeout(int $milliseconds)` | Set evaluation timeout | `->evaluationTimeout(5000)` |

### Unblock Options {#unblock-options}

| Method | Description | Example |
|--------|-------------|---------|
| `url(string $url)` | Set the URL to unblock | `->url('https://example.com')` |
| `browserWSEndpoint(bool $enabled)` | Request WebSocket endpoint | `->browserWSEndpoint()` |
| `content(bool $enabled)` | Request HTML content | `->content()` |
| `cookies(bool $enabled)` | Request cookies | `->cookies()` |
| `screenshot(bool $enabled)` | Request screenshot | `->screenshot()` |
| `ttl(int $milliseconds)` | Set browser instance TTL | `->ttl(30000)` |
| `waitForEvent(string $event, ?int $timeout)` | Wait for event | `->waitForEvent('networkidle0')` |
| `waitForFunction(string $function, ?int $timeout)` | Wait for function | `->waitForFunction('() => true')` |
| `waitForSelector(string $selector, array $options)` | Wait for selector | `->waitForSelector('.content')` |

### Scrape Options {#scrape-options}

| Method | Description | Example |
|--------|-------------|---------|
| `url(string $url)` | Set the URL to scrape | `->url('https://example.com')` |
| `html(string $html)` | Set HTML content to scrape | `->html('<div>content</div>')` |
| `element(string $selector, array $options)` | Add element to scrape | `->element('h1', ['text' => true])` |
| `waitForTimeout(int $milliseconds)` | Wait for timeout | `->waitForTimeout(5000)` |
| `waitForSelector(string $selector, array $options)` | Wait for selector | `->waitForSelector('.content')` |
| `waitForFunction(string $function, ?int $timeout)` | Wait for function | `->waitForFunction('() => true')` |
| `waitForEvent(string $event, ?int $timeout)` | Wait for event | `->waitForEvent('load')` |

Element options for scraping:
```php
->element('.selector', [
    'text' => true,      // Get text content
    'html' => true,      // Get HTML content
    'attributes' => [    // Array of attributes to extract
        'id',
        'class',
        'href'
    ]
])
```

### Performance Options {#performance-options}

| Method | Description | Example |
|--------|-------------|---------|
| `url(string $url)` | Set the URL to analyze | `->url('https://example.com')` |
| `categories(array $categories)` | Set categories to analyze | `->categories(['performance'])` |
| `audits(array $audits)` | Set specific audits to run | `->audits(['first-contentful-paint'])` |

Available categories:
- `performance`: Core web vitals and performance metrics
- `accessibility`: Accessibility best practices
- `best-practices`: Web development best practices
- `seo`: Search engine optimization
- `pwa`: Progressive Web App capabilities

Common audit IDs:
- Performance: `first-contentful-paint`, `largest-contentful-paint`, `total-blocking-time`
- Accessibility: `color-contrast`, `document-title`, `html-lang`
- Best Practices: `https`, `doctype`, `no-vulnerable-libraries`
- SEO: `meta-description`, `robots-txt`, `canonical`

### Session Options {#session-options}

| Method | Description | Example |
|--------|-------------|---------|
| `get()` | Get all sessions | `->get()` |
| `data()` | Get raw sessions data | `->data()` |
| `running()` | Get only running sessions | `->running()` |
| `findById(string $browserId)` | Find session by ID | `->findById('browser-123')` |

Session information includes:
- `browserId`: Unique browser identifier
- `running`: Whether the session is active
- `startTime`: When the session started
- `url`: Current page URL
- `wsEndpoint`: WebSocket endpoint
- `ttl`: Time-to-live in milliseconds
- `port`: Debug port (if available)
- `trackingId`: Session tracking ID

### Configuration Options {#config-options}

| Method | Description | Example |
|--------|-------------|---------|
| `get()` | Get worker configuration | `->get()` |
| `data()` | Get raw configuration data | `->data()` |

Configuration values include:
- System settings: `maxConcurrent`, `timeout`, `queued`, `rejectAlertURL`, `retries`
- Chrome settings: `windowSize`, `headless`, `ignoreHTTPSErrors`
- Worker settings: `host`, `port`, `token`, `concurrent`