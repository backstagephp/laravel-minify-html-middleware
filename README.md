# Laravel HTML Minify Middleware

[![Latest Version on Packagist](https://img.shields.io/packagist/v/backstage/laravel-minify-html-middleware.svg?style=flat-square)](https://packagist.org/packages/backstage/laravel-minify-html-middleware)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/backstagephp/laravel-minify-html-middleware/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/backstagephp/laravel-minify-html-middleware/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/backstagephp/laravel-minify-html-middleware/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/backstagephp/laravel-minify-html-middleware/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/backstage/laravel-minify-html-middleware.svg?style=flat-square)](https://packagist.org/packages/backstage/laravel-minify-html-middleware)

This package minifies HTML responses safely within Laravel using middleware.

## Installation

You can install the package via composer:

```bash
composer require backstage/laravel-minify-html-middleware
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-minify-html-middleware-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-minify-html-middleware-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-minify-html-middleware-views"
```

## Usage

...

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mark van Eijk](https://github.com/markvaneijk)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
