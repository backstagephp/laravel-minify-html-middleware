<?php

namespace Backstage\MinifyHtml\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Backstage\MinifyHtml\MinifyHtml
 */
class MinifyHtml extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Backstage\MinifyHtml\MinifyHtml::class;
    }
}
