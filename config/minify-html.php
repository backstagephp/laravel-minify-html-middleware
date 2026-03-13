<?php

use Backstage\MinifyHtml\Transformers\RemoveComments;
use Backstage\MinifyHtml\Transformers\RemoveWhitespace;
use Backstage\MinifyHtml\Transformers\TrimScripts;

return [
    'transformers' => [
        RemoveComments::class,
        RemoveWhitespace::class,
        TrimScripts::class,
    ],
];
