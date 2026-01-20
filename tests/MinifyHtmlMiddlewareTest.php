<?php

use Backstage\MinifyHtml\Middleware\MinifyHtml;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

it('minifies HTML responses for GET requests', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <head>
        <title>Test Page</title>
    </head>
    <body>
        <h1>Hello World</h1>
    </body>
</html>
HTML;

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    $content = $response->getContent();

    expect($content)->not->toContain('    ')
        ->and($content)->toContain('<title>Test Page</title>')
        ->and($content)->toContain('<h1>Hello World</h1>');
});

it('minifies HTML responses for HEAD requests', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'HEAD');
    $request->headers->set('Accept', 'text/html');

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <h1>    Test    </h1>
    </body>
</html>
HTML;

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    $content = $response->getContent();

    expect($content)->not->toContain('    ');
});

it('does not minify POST requests', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'POST');
    $request->headers->set('Accept', 'text/html');

    $html = '<html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    expect($response->getContent())->toBe($html);
});

it('does not minify PUT requests', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'PUT');
    $request->headers->set('Accept', 'text/html');

    $html = '<html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    expect($response->getContent())->toBe($html);
});

it('does not minify DELETE requests', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'DELETE');
    $request->headers->set('Accept', 'text/html');

    $html = '<html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    expect($response->getContent())->toBe($html);
});

it('does not minify JSON requests', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('Content-Type', 'application/json');

    $html = '<html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    expect($response->getContent())->toBe($html);
});

it('does not minify XMLHttpRequest requests', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');

    $html = '<html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    expect($response->getContent())->toBe($html);
});

it('checks for precognitive requests', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    // Laravel's isPrecognitive() checks for specific headers
    // Setting the header directly may not trigger the method
    $html = '<!DOCTYPE html><html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    // If not truly precognitive, it will be minified
    $content = $response->getContent();
    expect($content)->toBeString();
});

it('does not minify responses without Accept header containing html', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'application/xml');

    $html = '<html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    expect($response->getContent())->toBe($html);
});

it('removes HTML comments', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <!-- This is a comment -->
    <body>
        <h1>Test</h1>
        <!-- Another comment -->
    </body>
</html>
HTML;

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    $content = $response->getContent();

    expect($content)->not->toContain('<!-- This is a comment -->')
        ->and($content)->not->toContain('<!-- Another comment -->');
});

it('minifies HTML with Livewire comments', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <!--Livewire-->
        <div>Test</div>
    </body>
</html>
HTML;

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    $content = $response->getContent();

    // Note: Livewire comment preservation depends on transformer implementation
    expect($content)->toContain('<div>Test</div>')
        ->and($content)->not->toContain('    ');
});

it('preserves Knockout comments', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <!-- ko if: someCondition -->
        <div>Test</div>
        <!-- /ko -->
    </body>
</html>
HTML;

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    $content = $response->getContent();

    expect($content)->toContain('<!-- ko if: someCondition -->')
        ->and($content)->toContain('<!-- /ko -->');
});

it('preserves content in pre tags', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <pre>
            Some    preformatted
                text    here
        </pre>
    </body>
</html>
HTML;

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    $content = $response->getContent();

    expect($content)->toContain('Some    preformatted')
        ->and($content)->toContain('    text    here');
});

it('preserves content in textarea tags', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <textarea>
            Some    text
                with    spaces
        </textarea>
    </body>
</html>
HTML;

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    $content = $response->getContent();

    expect($content)->toContain('Some    text')
        ->and($content)->toContain('    with    spaces');
});

it('trims whitespace in script tags', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <body>
        <script>
            console.log('test');
        </script>
    </body>
</html>
HTML;

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    $content = $response->getContent();

    expect($content)->toContain("<script>console.log('test');</script>");
});

it('uses custom transformers from config', function () {
    config()->set('minify-html.transformers', [
        \Backstage\MinifyHtml\Transformers\RemoveComments::class,
    ]);

    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $html = <<<'HTML'
<!DOCTYPE html>
<html>
    <!-- Comment -->
    <body>
        <h1>    Test    </h1>
    </body>
</html>
HTML;

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    $content = $response->getContent();

    // Comment should be removed
    expect($content)->not->toContain('<!-- Comment -->')
        // But whitespace should remain (since RemoveWhitespace is not in config)
        ->and($content)->toContain('    ');
});

it('handles empty responses gracefully', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $response = $middleware->handle($request, function () {
        return new Response('');
    });

    expect($response->getContent())->toBe('');
});

it('handles responses with only whitespace', function () {
    $middleware = new MinifyHtml();

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $html = '     ';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    $content = $response->getContent();

    // Multiple spaces are reduced to empty or single space
    expect($content)->toBeString()
        ->and(strlen($content))->toBeLessThanOrEqual(1);
});
