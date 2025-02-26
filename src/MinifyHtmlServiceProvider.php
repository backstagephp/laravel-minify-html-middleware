<?php

namespace Backstage\MinifyHtml;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MinifyHtmlServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-minify-html-middleware')
            ->hasConfigFile('minify-html');
    }
}
