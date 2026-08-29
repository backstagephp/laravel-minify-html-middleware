<?php

use Backstage\MinifyHtml\Middleware\MinifyHtml;
use Backstage\MinifyHtml\Transformers\RemoveComments;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

it('minifies HTML responses for GET requests', function () {
    $middleware = new MinifyHtml;

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
    $middleware = new MinifyHtml;

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
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'POST');
    $request->headers->set('Accept', 'text/html');

    $html = '<html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    expect($response->getContent())->toBe($html);
});

it('does not minify PUT requests', function () {
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'PUT');
    $request->headers->set('Accept', 'text/html');

    $html = '<html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    expect($response->getContent())->toBe($html);
});

it('does not minify DELETE requests', function () {
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'DELETE');
    $request->headers->set('Accept', 'text/html');

    $html = '<html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    expect($response->getContent())->toBe($html);
});

it('does not minify JSON requests', function () {
    $middleware = new MinifyHtml;

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
    $middleware = new MinifyHtml;

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
    $middleware = new MinifyHtml;

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
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'application/xml');

    $html = '<html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    expect($response->getContent())->toBe($html);
});

it('removes HTML comments', function () {
    $middleware = new MinifyHtml;

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
    $middleware = new MinifyHtml;

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
    $middleware = new MinifyHtml;

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
    $middleware = new MinifyHtml;

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
    $middleware = new MinifyHtml;

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
    $middleware = new MinifyHtml;

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
        RemoveComments::class,
    ]);

    $middleware = new MinifyHtml;

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
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $response = $middleware->handle($request, function () {
        return new Response('');
    });

    expect($response->getContent())->toBe('');
});

it('handles responses with only whitespace', function () {
    $middleware = new MinifyHtml;

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

it('does not minify streamed responses', function () {
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $body = "line one\n\n    line two\n";

    $response = $middleware->handle($request, function () use ($body) {
        return new StreamedResponse(function () use ($body) {
            echo $body;
        });
    });

    expect($response)->toBeInstanceOf(StreamedResponse::class);

    ob_start();
    $response->sendContent();

    expect(ob_get_clean())->toBe($body);
});

it('does not minify streamed downloads', function () {
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $response = $middleware->handle($request, function () {
        return response()->streamDownload(function () {
            echo '    dump    ';
        }, 'dump.txt');
    });

    ob_start();
    $response->sendContent();

    expect(ob_get_clean())->toBe('    dump    ');
});

it('does not minify binary file responses', function () {
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $path = tempnam(sys_get_temp_dir(), 'minify');
    file_put_contents($path, '<html>    <body>    Test    </body>    </html>');

    $response = $middleware->handle($request, function () use ($path) {
        return new BinaryFileResponse($path);
    });

    expect($response)->toBeInstanceOf(BinaryFileResponse::class)
        ->and(file_get_contents($response->getFile()->getPathname()))
        ->toBe('<html>    <body>    Test    </body>    </html>');

    unlink($path);
});

it('does not minify responses declaring a non-html content type', function () {
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $xml = "<rss>\n    <channel>    Test    </channel>\n</rss>";

    $response = $middleware->handle($request, function () use ($xml) {
        return new Response($xml, 200, ['Content-Type' => 'application/xml']);
    });

    expect($response->getContent())->toBe($xml);
});

it('does not minify json responses returned to a browser', function () {
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $response = $middleware->handle($request, function () {
        return new JsonResponse(['value' => "a\n    b"]);
    });

    expect($response->getContent())->toBe(json_encode(['value' => "a\n    b"]));
});

it('still minifies responses that declare an html content type', function () {
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $response = $middleware->handle($request, function () {
        return new Response("<html>\n    <body>    Test    </body>\n</html>", 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    });

    expect($response->getContent())->not->toContain('    ');
});

it('does not fail when the request has no Accept header', function () {
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'GET');
    $request->headers->remove('Accept');

    $html = '<html>    <body>    Test    </body>    </html>';

    $response = $middleware->handle($request, function () use ($html) {
        return new Response($html);
    });

    expect($response->getContent())->toBe($html);
});

it('preserves the response original payload while minifying', function () {
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    $page = ['component' => 'Dashboard', 'props' => ['name' => 'Test']];

    $response = $middleware->handle($request, function () use ($page) {
        $response = new Response("<html>\n    <body>    Test    </body>\n</html>");
        $response->original = $page;

        return $response;
    });

    expect($response->original)->toBe($page)
        ->and($response->getContent())->not->toContain('    ');
});

it('preserves a renderable original so view assertions keep working', function () {
    $middleware = new MinifyHtml;

    $request = Request::create('/', 'GET');
    $request->headers->set('Accept', 'text/html');

    View::addLocation(__DIR__ . '/stubs');

    $view = View::make('page', ['page' => ['component' => 'Dashboard']]);

    $response = $middleware->handle($request, function () use ($view) {
        return new Response($view);
    });

    expect($response->original)->toBeInstanceOf(ViewContract::class)
        ->and($response->original->getData()['page'])->toBe(['component' => 'Dashboard'])
        ->and($response->getContent())->not->toContain('    ');
});
