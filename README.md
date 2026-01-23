# Laravel HTML Minify Middleware

[![Latest Version on Packagist](https://img.shields.io/packagist/v/backstage/laravel-minify-html-middleware.svg?style=flat-square)](https://packagist.org/packages/backstage/laravel-minify-html-middleware)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/backstagephp/laravel-minify-html-middleware/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/backstagephp/laravel-minify-html-middleware/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/backstagephp/laravel-minify-html-middleware/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/backstagephp/laravel-minify-html-middleware/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/backstage/laravel-minify-html-middleware.svg?style=flat-square)](https://packagist.org/packages/backstage/laravel-minify-html-middleware)

A lightweight and efficient Laravel middleware package that automatically minifies HTML responses, reducing page size and improving load times. The package intelligently removes unnecessary whitespace, comments, and optimizes your HTML output while preserving functionality.

## Features

- **Automatic HTML Minification**: Minifies HTML responses on-the-fly with zero configuration required
- **Smart Detection**: Only processes HTML responses, automatically skipping JSON, AJAX requests, and other non-HTML content
- **Safe Minification**: Preserves content in sensitive elements like `<pre>`, `<textarea>`, and `<script>` tags
- **Framework-Aware**: Compatible with Livewire, Knockout.js, and other JavaScript frameworks
- **Configurable Transformers**: Choose which transformations to apply or create custom ones
- **Performance Optimized**: Minimal overhead with efficient regex-based transformations
- **Laravel 10, 11, and 12 Support**: Works seamlessly with modern Laravel versions

## What Gets Minified?

The package includes three built-in transformers:

1. **Remove Comments**: Strips HTML comments while preserving framework-specific comments (Livewire, Knockout.js)
2. **Remove Whitespace**: Eliminates unnecessary whitespace between tags and multiple spaces
3. **Trim Scripts**: Removes extra whitespace from within `<script>` tags

**Before Minification:**
```html
<!DOCTYPE html>
<html>
    <head>
        <title>My Page</title>
        <!-- This is a comment -->
    </head>
    <body>
        <div class="container">
            <h1>   Welcome   </h1>
            <script>
                console.log('Hello World');
            </script>
        </div>
    </body>
</html>
```

**After Minification:**
```html
<!DOCTYPE html><html><head><title>My Page</title></head><body><div class="container"><h1>Welcome</h1><script>console.log('Hello World');</script></div></body></html>
```

## Requirements

- PHP 8.2 or higher
- Laravel 10.x, 11.x, or 12.x

## Installation

Install the package via Composer:

```bash
composer require backstage/laravel-minify-html-middleware
```

The package will automatically register its service provider.

### Publish Configuration

Publish the configuration file to customize the transformers:

```bash
php artisan vendor:publish --tag="laravel-minify-html-middleware-config"
```

This will create a `config/minify-html.php` file with the following default configuration:

```php
<?php

return [
    'transformers' => [
        Backstage\MinifyHtml\Transformers\RemoveComments::class,
        Backstage\MinifyHtml\Transformers\RemoveWhitespace::class,
        Backstage\MinifyHtml\Transformers\TrimScripts::class,
    ],
];
```

## Usage

### Global Middleware

To minify all HTML responses in your application, add the middleware to the global middleware stack in `bootstrap/app.php` (Laravel 11+):

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Backstage\MinifyHtml\Middleware\MinifyHtml;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(MinifyHtml::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

For Laravel 10, add to `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \Backstage\MinifyHtml\Middleware\MinifyHtml::class,
];
```

### Route Middleware

To apply minification to specific routes or route groups, register the middleware with an alias:

**Laravel 11+** (`bootstrap/app.php`):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'minify' => \Backstage\MinifyHtml\Middleware\MinifyHtml::class,
    ]);
})
```

**Laravel 10** (`app/Http/Kernel.php`):

```php
protected $middlewareAliases = [
    // ...
    'minify' => \Backstage\MinifyHtml\Middleware\MinifyHtml::class,
];
```

Then apply it to specific routes:

```php
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('minify');

// Or to a group
Route::middleware(['minify'])->group(function () {
    Route::get('/about', [AboutController::class, 'index']);
    Route::get('/contact', [ContactController::class, 'index']);
});
```

### Conditional Minification

The middleware automatically determines if a response should be minified based on several conditions:

- ✅ Request method is `GET` or `HEAD`
- ✅ Response contains HTML (checks `Accept` header)
- ❌ Request is JSON
- ❌ Request is AJAX (XMLHttpRequest)
- ❌ Request is a precognitive request
- ❌ Response has no DOCTYPE declaration in the first 100 characters

This ensures that only actual HTML page responses are minified, avoiding issues with API responses or partial HTML fragments.

## Configuration

### Customizing Transformers

You can customize which transformers are applied by modifying the `config/minify-html.php` file:

```php
<?php

return [
    'transformers' => [
        // Use only specific transformers
        Backstage\MinifyHtml\Transformers\RemoveComments::class,
        Backstage\MinifyHtml\Transformers\RemoveWhitespace::class,
        // Backstage\MinifyHtml\Transformers\TrimScripts::class, // Disabled

        // Add custom transformers
        App\HtmlTransformers\CustomTransformer::class,
    ],
];
```

### Disabling Specific Transformers

Remove or comment out any transformer you don't want to use:

```php
<?php

return [
    'transformers' => [
        // Only remove whitespace, keep comments
        Backstage\MinifyHtml\Transformers\RemoveWhitespace::class,
    ],
];
```

## Creating Custom Transformers

You can create your own HTML transformers by implementing a simple `transform` method:

```php
<?php

namespace App\HtmlTransformers;

class RemoveMetaTags
{
    public function transform(string $html): string
    {
        // Remove all meta tags
        return preg_replace('/<meta[^>]*>/i', '', $html);
    }
}
```

Then add it to your configuration:

```php
<?php

return [
    'transformers' => [
        Backstage\MinifyHtml\Transformers\RemoveComments::class,
        Backstage\MinifyHtml\Transformers\RemoveWhitespace::class,
        Backstage\MinifyHtml\Transformers\TrimScripts::class,
        App\HtmlTransformers\RemoveMetaTags::class,
    ],
];
```

### Example: Uppercase Title Transformer

```php
<?php

namespace App\HtmlTransformers;

class UppercaseTitle
{
    public function transform(string $html): string
    {
        return preg_replace_callback(
            '/<title>(.*?)<\/title>/i',
            function ($matches) {
                return '<title>' . strtoupper($matches[1]) . '</title>';
            },
            $html
        );
    }
}
```

## Built-in Transformers Explained

### RemoveComments

Removes HTML comments while preserving special comments needed by frameworks:

```php
// Removes: <!-- This will be removed -->
// Keeps: <!--Livewire--> and <!-- ko --> (Knockout.js)
```

### RemoveWhitespace

Intelligently removes whitespace while preserving content in:
- `<pre>` tags (preformatted text)
- `<textarea>` tags (form inputs)
- `<script>` tags (JavaScript code)

The transformer:
1. Temporarily hides protected elements
2. Removes multiple spaces, tabs, and newlines
3. Removes spaces between tags (`> <` becomes `><`)
4. Restores protected elements

### TrimScripts

Removes leading and trailing whitespace from within `<script>` tags without affecting functionality:

```html
<!-- Before -->
<script>
    console.log('Hello');
</script>

<!-- After -->
<script>console.log('Hello');</script>
```

## Performance Benefits

HTML minification can reduce your HTML file size by 10-30% on average, depending on your code formatting:

- **Faster Page Loads**: Smaller HTML files download quicker
- **Reduced Bandwidth**: Lower data transfer costs
- **Improved SEO**: Faster page loads contribute to better search rankings
- **Better Mobile Experience**: Crucial for users on slower connections

Example size reduction:
- **Before**: 45 KB
- **After**: 32 KB
- **Savings**: 28.9% reduction

## Framework Compatibility

### Livewire

The package preserves Livewire comments and attributes, ensuring full compatibility:

```html
<!-- Livewire components work perfectly -->
<div wire:model="name">
    <!-- Livewire comment preserved -->
</div>
```

### Inertia.js

Works seamlessly with Inertia.js responses as they are JSON and automatically skipped.

### Alpine.js

Alpine.js directives and attributes are preserved:

```html
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
</div>
```

## Troubleshooting

### HTML Not Being Minified

1. **Check your response type**: Ensure the response has an `Accept: text/html` header
2. **Verify middleware is registered**: Confirm the middleware is in your global or route middleware
3. **Check request method**: Only `GET` and `HEAD` requests are processed
4. **DOCTYPE declaration**: Make sure your HTML includes `<!DOCTYPE html>` early in the response

### Broken Layout or Functionality

1. **Whitespace-dependent CSS**: If your layout relies on whitespace between inline elements, add those elements to the `ignoreElements` array in a custom transformer
2. **Template literals in scripts**: Complex JavaScript might need special handling
3. **Pre-formatted content**: Ensure `<pre>` and `<textarea>` tags are being preserved

### Debugging

Temporarily disable transformers one by one to identify which transformation is causing issues:

```php
<?php

return [
    'transformers' => [
        // Backstage\MinifyHtml\Transformers\RemoveComments::class,
        Backstage\MinifyHtml\Transformers\RemoveWhitespace::class,
        // Backstage\MinifyHtml\Transformers\TrimScripts::class,
    ],
];
```

## Testing

Run the package tests:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

Fix code style:

```bash
composer format
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
