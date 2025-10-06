# Laravel Browserless SDK

A powerful Laravel package for interacting with the Browserless API, providing an elegant way to take screenshots, generate PDFs, analyze performance, and automate browser tasks.

## Installation

1. Install the package via Composer:

```bash
composer require millerphp/laravel-browserless
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --provider="MillerPHP\LaravelBrowserless\BrowserlessServiceProvider"
```

3. Configure your environment variables in `.env`:

```env
BROWSERLESS_API_KEY=your_api_key
BROWSERLESS_API_URL=https://chrome.browserless.io
```

## Quick Start

Take a screenshot:

```php
use MillerPHP\LaravelBrowserless\Facades\Browserless;

$screenshot = Browserless::screenshot()
    ->url('https://example.com')
    ->send();
```

Generate a PDF:

```php
$pdf = Browserless::pdf()
    ->url('https://example.com')
    ->send();
```

Analyze performance:

```php
$performance = Browserless::performance()
    ->url('https://example.com')
    ->send();

$score = $performance->categoryScore('performance');
```

## Documentation

- [Installation Guide](docs/01-installation.md) - Detailed installation and setup instructions
- [Usage Guide](docs/02-usage.md) - Basic usage examples and common features
- [API Reference](docs/03-api-reference.md) - Comprehensive API documentation
- [Configuration](docs/04-configuration.md) - Configuration options and environment variables
- [Advanced Usage](docs/05-advanced-usage.md) - Advanced features and customization
- [Testing](docs/06-testing.md) - Testing your Browserless integrations
- [Troubleshooting](docs/07-troubleshooting.md) - Common issues and solutions
- [Security](docs/08-security.md) - Security best practices
- [Performance](docs/09-performance.md) - Performance optimization guide

## Features

- **Screenshots**: Capture full-page or element-specific screenshots
- **PDF Generation**: Convert web pages to PDFs with custom options
- **Performance Analysis**: Analyze website performance using Lighthouse
- **Browser Automation**: Execute complex browser tasks using BQL
- **Custom Options**: Fine-tune every aspect of your requests
- **Error Handling**: Comprehensive error handling and logging
- **Testing Support**: Built-in testing utilities and mocking
- **Security**: Secure configuration and API key management
- **Performance**: Caching, batch processing, and resource optimization

## Requirements

- PHP 8.1 or higher
- Laravel 9.0 or higher
- Composer

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
