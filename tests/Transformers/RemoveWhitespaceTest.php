<?php

use Backstage\MinifyHtml\Transformers\RemoveWhitespace;

beforeEach(function () {
    $this->transformer = new RemoveWhitespace();
});

it('removes multiple spaces', function () {
    $html = '<div>    <p>Test</p>    </div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div><p>Test</p></div>');
});

it('removes spaces between tags', function () {
    $html = '<div> <p> Text </p> </div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div><p>Text</p></div>');
});

it('removes newlines and replaces with single space', function () {
    $html = <<<'HTML'
<div>
    <p>Test</p>
</div>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->not->toContain("\n")
        ->and($result)->toContain('<div><p>Test</p></div>');
});

it('removes tabs', function () {
    $html = "<div>\t\t<p>Test</p>\t</div>";
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div><p>Test</p></div>');
});

it('preserves single spaces in text content', function () {
    $html = '<p>Hello World</p>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<p>Hello World</p>');
});

it('preserves content in pre tags', function () {
    $html = '<div><pre>    Some    preformatted    text    </pre></div>';
    $result = $this->transformer->transform($html);

    expect($result)->toContain('    Some    preformatted    text    ');
});

it('preserves content in textarea tags', function () {
    $html = '<div><textarea>    Some    text    with    spaces    </textarea></div>';
    $result = $this->transformer->transform($html);

    expect($result)->toContain('    Some    text    with    spaces    ');
});

it('preserves content in script tags', function () {
    $html = '<script>    var x = 1;    var y = 2;    </script>';
    $result = $this->transformer->transform($html);

    expect($result)->toContain('    var x = 1;    var y = 2;    ');
});

it('handles multiple pre tags', function () {
    $html = '<div><pre>First    pre</pre> <pre>Second    pre</pre></div>';
    $result = $this->transformer->transform($html);

    expect($result)->toContain('First    pre')
        ->and($result)->toContain('Second    pre');
});

it('handles nested elements with whitespace', function () {
    $html = <<<'HTML'
<div>
    <section>
        <article>
            <p>Content</p>
        </article>
    </section>
</div>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div><section><article><p>Content</p></article></section></div>');
});

it('removes space after opening tag', function () {
    $html = '<div> Content</div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div>Content</div>');
});

it('removes space before closing tag', function () {
    $html = '<div>Content </div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div>Content</div>');
});

it('handles self-closing tags', function () {
    $html = '<div>    <img src="test.jpg" />    <p>Text</p>    </div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div><img src="test.jpg" /><p>Text</p></div>');
});

it('preserves attributes with spaces', function () {
    $html = '<div class="foo bar">Content</div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div class="foo bar">Content</div>');
});

it('handles empty tags', function () {
    $html = '<div>    </div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div></div>');
});

it('handles mixed content with preserved elements', function () {
    $html = <<<'HTML'
<div>
    <h1>    Title    </h1>
    <pre>    Preformatted    </pre>
    <p>    Paragraph    </p>
</div>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('<h1>Title</h1>')
        ->and($result)->toContain('<pre>    Preformatted    </pre>')
        ->and($result)->toContain('<p>Paragraph</p>');
});

it('handles pre tags with attributes', function () {
    $html = '<div><pre class="code">    var x = 1;    </pre></div>';
    $result = $this->transformer->transform($html);

    expect($result)->toContain('    var x = 1;    ');
});

it('handles textarea tags with attributes', function () {
    $html = '<div><textarea name="content" rows="5">    Text    </textarea></div>';
    $result = $this->transformer->transform($html);

    expect($result)->toContain('    Text    ');
});

it('handles script tags with attributes', function () {
    $html = '<script type="text/javascript">    console.log("test");    </script>';
    $result = $this->transformer->transform($html);

    expect($result)->toContain('    console.log("test");    ');
});

it('handles multiple script tags', function () {
    $html = <<<'HTML'
<div>
    <script>    var a = 1;    </script>
    <p>Text</p>
    <script>    var b = 2;    </script>
</div>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('    var a = 1;    ')
        ->and($result)->toContain('    var b = 2;    ')
        ->and($result)->toContain('<p>Text</p>');
});

it('handles empty string', function () {
    $html = '';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('');
});

it('removes whitespace around block elements', function () {
    $html = <<<'HTML'
<html>
    <head>
        <title>Test</title>
    </head>
    <body>
        <div>Content</div>
    </body>
</html>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->not->toContain("\n")
        ->and($result)->toContain('<html><head><title>Test</title></head><body><div>Content</div></body></html>');
});

it('handles inline elements with whitespace', function () {
    $html = '<p>This is <strong>    bold    </strong> text</p>';
    $result = $this->transformer->transform($html);

    // Spaces between words are preserved to maintain readability
    expect($result)->toContain('<strong>bold</strong>')
        ->and($result)->toContain('This is')
        ->and($result)->toContain('text');
});

it('removes multiple consecutive spaces', function () {
    $html = '<div>Text     with     many     spaces</div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div>Text with many spaces</div>');
});

it('handles complex nested pre tags', function () {
    $html = <<<'HTML'
<div>
    <article>
        <pre>
function test() {
    return true;
}
        </pre>
        <p>Description</p>
    </article>
</div>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('function test()')
        ->and($result)->toContain('    return true;')
        ->and($result)->toContain('<p>Description</p>');
});

it('handles textarea with newlines', function () {
    $html = <<<'HTML'
<div>
    <textarea>
Line 1
    Line 2
        Line 3
    </textarea>
</div>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain("Line 1\n    Line 2\n        Line 3");
});

it('handles uppercase tag names', function () {
    $html = '<DIV>    <PRE>    Preserved    </PRE>    <P>Minified</P>    </DIV>';
    $result = $this->transformer->transform($html);

    expect($result)->toContain('    Preserved    ')
        ->and($result)->toContain('<P>Minified</P>');
});

it('removes carriage returns', function () {
    $html = "<div>\r\n    <p>Test</p>\r\n</div>";
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div><p>Test</p></div>');
});
