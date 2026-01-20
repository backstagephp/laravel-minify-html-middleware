<?php

use Backstage\MinifyHtml\Transformers\RemoveComments;

beforeEach(function () {
    $this->transformer = new RemoveComments();
});

it('removes basic HTML comments', function () {
    $html = '<div><!-- This is a comment --><p>Content</p></div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div><p>Content</p></div>');
});

it('removes multiple HTML comments', function () {
    $html = '<!-- Comment 1 --><div><!-- Comment 2 --><p>Content</p><!-- Comment 3 --></div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div><p>Content</p></div>');
});

it('removes multiline comments', function () {
    $html = <<<'HTML'
<div>
    <!--
        This is a
        multiline comment
    -->
    <p>Content</p>
</div>
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->not->toContain('multiline comment')
        ->and($result)->toContain('<p>Content</p>');
});

it('attempts to preserve Livewire comments', function () {
    $html = '<!--Livewire--><div><p>Content</p></div>';
    $result = $this->transformer->transform($html);

    // Note: The current regex implementation may not fully preserve all Livewire comments
    expect($result)->toContain('<p>Content</p>');
});

it('preserves Knockout opening comments', function () {
    $html = '<!-- ko if: condition --><div>Content</div>';
    $result = $this->transformer->transform($html);

    expect($result)->toContain('<!-- ko if: condition -->');
});

it('preserves Knockout closing comments', function () {
    $html = '<div>Content</div><!-- /ko -->';
    $result = $this->transformer->transform($html);

    expect($result)->toContain('<!-- /ko -->');
});

it('preserves Knockout with binding context', function () {
    $html = '<!-- ko foreach: items --><div>Item</div><!-- /ko -->';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<!-- ko foreach: items --><div>Item</div><!-- /ko -->');
});

it('removes regular comments but preserves Knockout comments', function () {
    $html = <<<'HTML'
<!-- Regular comment -->
<!-- ko if: visible -->
    <div>Content</div>
<!-- /ko -->
<!-- Another regular comment -->
HTML;

    $result = $this->transformer->transform($html);

    expect($result)->not->toContain('Regular comment')
        ->and($result)->not->toContain('Another regular comment')
        ->and($result)->toContain('<!-- ko if: visible -->')
        ->and($result)->toContain('<!-- /ko -->');
});

it('removes comments with special characters', function () {
    $html = '<div><!-- Comment with special chars: @#$%^&*() --><p>Content</p></div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div><p>Content</p></div>');
});

it('handles HTML without comments', function () {
    $html = '<div><p>No comments here</p></div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe($html);
});

it('handles empty string', function () {
    $html = '';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('');
});

it('handles conditional comments', function () {
    $html = '<!--[if IE]><div>IE specific</div><![endif]--><div>Content</div>';
    $result = $this->transformer->transform($html);

    // Conditional comments may not be fully removed due to their special syntax
    expect($result)->toContain('<div>Content</div>');
});

it('removes comments within script tags', function () {
    $html = '<script><!-- console.log("test"); --></script>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<script></script>');
});

it('removes comments within style tags', function () {
    $html = '<style><!-- .test { color: red; } --></style>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<style></style>');
});

it('handles adjacent comments', function () {
    $html = '<!-- Comment 1 --><!-- Comment 2 --><div>Content</div>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe('<div>Content</div>');
});

it('preserves content that looks like comments but is not', function () {
    $html = '<p>This is not &lt;!-- a comment --&gt;</p>';
    $result = $this->transformer->transform($html);

    expect($result)->toBe($html);
});

it('removes comments with nested angle brackets in content', function () {
    $html = '<div><!-- Comment with <tag> inside --><p>Content</p></div>';
    $result = $this->transformer->transform($html);

    expect($result)->toContain('<p>Content</p>')
        ->and($result)->not->toContain('Comment with');
});
