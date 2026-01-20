<?php

use Backstage\MinifyHtml\Transformers\TrimScripts;

beforeEach(function () {
    $this->transformer = new TrimScripts;
});

it('trims leading whitespace from script content', function () {
    $html = '<script>    console.log("test");</script>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<script>console.log("test");</script>');
});

it('trims trailing whitespace from script content', function () {
    $html = '<script>console.log("test");    </script>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<script>console.log("test");</script>');
});

it('trims both leading and trailing whitespace', function () {
    $html = '<script>    console.log("test");    </script>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<script>console.log("test");</script>');
});

it('removes leading newlines', function () {
    $html = <<<'HTML'
<script>
console.log("test");
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('<script>console.log("test");')
        ->and($result)->not->toMatch('/^<script>\n/');
});

it('removes trailing newlines', function () {
    $html = <<<'HTML'
<script>
console.log("test");

</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('console.log("test");</script>');
});

it('handles script with multiple lines', function () {
    $html = <<<'HTML'
<script>
    var x = 1;
    var y = 2;
    console.log(x + y);
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('var x = 1;')
        ->and($result)->toContain('var y = 2;')
        ->and($result)->toContain('console.log(x + y);');
});

it('removes indentation from each line', function () {
    $html = <<<'HTML'
<script>
    function test() {
        return true;
    }
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('function test()')
        ->and($result)->toContain('return true;')
        ->and($result)->not->toContain('    function');
});

it('handles multiple script tags', function () {
    $html = <<<'HTML'
<div>
    <script>
        var a = 1;
    </script>
    <script>
        var b = 2;
    </script>
</div>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('<script>var a = 1;</script>')
        ->and($result)->toContain('<script>var b = 2;</script>');
});

it('handles script tags with type attribute', function () {
    $html = <<<'HTML'
<script type="text/javascript">
    console.log("test");
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('<script type="text/javascript">console.log("test");</script>');
});

it('handles script tags with src attribute', function () {
    $html = <<<'HTML'
<script src="app.js">
    // Fallback code
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('// Fallback code')
        ->and($result)->not->toContain('    // Fallback');
});

it('handles empty script tags', function () {
    $html = '<script>    </script>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<script></script>');
});

it('handles script with only newlines', function () {
    $html = "<script>\n\n\n</script>";
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<script></script>');
});

it('preserves internal spacing in code', function () {
    $html = '<script>    var x = 1 + 2;    </script>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<script>var x = 1 + 2;</script>')
        ->and($result)->toContain('1 + 2');
});

it('handles script with tabs', function () {
    $html = "<script>\t\tconsole.log('test');\t\t</script>";
    $result = $this->transformer->transform($html);

    expect($result)->toBe("<script>console.log('test');</script>");
});

it('handles mixed whitespace characters', function () {
    $html = "<script> \t \n  console.log('test'); \n \t </script>";
    $result = $this->transformer->transform($html);

    expect($result)->toBe("<script>console.log('test');</script>");
});

it('does not affect HTML outside script tags', function () {
    $html = <<<'HTML'
<div>
    <p>    Test    </p>
    <script>
        console.log("test");
    </script>
    <p>    Another    </p>
</div>
HTML;

    $result = $this->transformer->transform($html);

    // Whitespace outside script should remain
    expect($result)->toContain('    Test    ')
        ->and($result)->toContain('    Another    ')
        // But script content should be trimmed
        ->and($result)->toContain('<script>console.log("test");</script>');
});

it('handles script with string containing newlines', function () {
    $html = <<<'HTML'
<script>
    var str = "Line 1\nLine 2";
    console.log(str);
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('var str = "Line 1\nLine 2";')
        ->and($result)->toContain('console.log(str);');
});

it('handles script with template literals', function () {
    $html = <<<'HTML'
<script>
    const template = `
        <div>
            Test
        </div>
    `;
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('const template =');
});

it('handles script with comments', function () {
    $html = <<<'HTML'
<script>
    // This is a comment
    var x = 1;
    /* Multi-line
       comment */
    var y = 2;
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('// This is a comment')
        ->and($result)->toContain('var x = 1;')
        ->and($result)->toContain('/* Multi-line')
        ->and($result)->toContain('var y = 2;');
});

it('handles script with inline event handlers', function () {
    $html = '<button onclick="    console.log(\'clicked\');    ">Click</button><script>    var x = 1;    </script>';
    $result = $this->transformer->transform($html);

    // Only script tag content should be trimmed, not onclick attribute
    expect($result)->toContain("onclick=\"    console.log('clicked');    \"")
        ->and($result)->toContain('<script>var x = 1;</script>');
});

it('handles uppercase SCRIPT tags', function () {
    $html = '<SCRIPT>    console.log("test");    </SCRIPT>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<SCRIPT>console.log("test");</SCRIPT>');
});

it('handles script with JSON data', function () {
    $html = <<<'HTML'
<script type="application/json">
    {
        "key": "value",
        "number": 123
    }
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('{')
        ->and($result)->toContain('"key": "value"')
        ->and($result)->toContain('"number": 123')
        ->and($result)->toContain('}');
});

it('handles HTML with no script tags', function () {
    $html = '<div>    <p>No scripts here</p>    </div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe($html);
});

it('handles empty string', function () {
    $html = '';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('');
});

it('handles script with regex patterns', function () {
    $html = <<<'HTML'
<script>
    var pattern = /\s+/g;
    var result = str.replace(pattern, ' ');
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('var pattern = /\s+/g;')
        ->and($result)->toContain("var result = str.replace(pattern, ' ');");
});

it('handles nested script-like content', function () {
    $html = <<<'HTML'
<script>
    var html = '<script>alert("test");<\/script>';
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain('var html =');
});

it('handles script with module type', function () {
    $html = <<<'HTML'
<script type="module">
    import { test } from './module.js';
    test();
</script>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->toContain("import { test } from './module.js';")
        ->and($result)->toContain('test();');
});
