<?php

return [
    'transformers' => [
        Backstage\MinifyHtml\Transformers\RemoveComments::class,
        Backstage\MinifyHtml\Transformers\RemoveWhitespace::class,
        Backstage\MinifyHtml\Transformers\TrimScripts::class,
    ],
];
