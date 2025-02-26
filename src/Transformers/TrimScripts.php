<?php

namespace Backstage\MinifyHtml\Transformers;

class TrimScripts
{
    public function transform(string $html): string
    {
        if (preg_match_all('~<script[^>]*>(.*)</script>~Uuis', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $replace = trim(preg_replace('~^\p{Z}+|\p{Z}+$|^\s+~m', '', $match[1]));

                $html = str_replace($match[1], $replace, $html);
            }
        }

        return $html;
    }
}
