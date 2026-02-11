<?php

namespace Backstage\MinifyHtml\Transformers;

class RemoveWhitespace
{
    protected array $hiddenElements = [];

    protected array $ignoreElements = [
        'pre',
        'textarea',
        'script',
    ];

    public function hideElements(string $html): string
    {
        foreach ($this->ignoreElements as $element) {
            $pattern = '~<'.$element.'[^>]*>(.*)</'.$element.'>~Uuis';
            if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $this->hiddenElements['#'.md5($match[1]).'#'] = $match[1];
                }
            }
        }

        if (count($this->hiddenElements)) {
            $html = str_replace(array_values($this->hiddenElements), array_keys($this->hiddenElements), $html);
        }

        return $html;
    }

    public function restoreElements(string $html): string
    {
        if (count($this->hiddenElements)) {
            return str_replace(array_keys($this->hiddenElements), array_values($this->hiddenElements), $html);
        }

        return $html;
    }

    public function transform(string $html): string
    {
        $html = $this->hideElements($html);

        $replaces = [
            '~\s+~u' => ' ',
            '~> +<~' => '><',
            '~(<[a-z]+[^>]*>) +~i' => '$1',
            '~ +(</[a-z]+)~i' => '$1',
        ];

        $html = preg_replace(array_keys($replaces), array_values($replaces), $html);

        return $this->restoreElements($html);
    }
}
