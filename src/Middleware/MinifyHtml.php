<?php

namespace Backstage\MinifyHtml\Middleware;

use Illuminate\Http\Request;

class MinifyHtml
{
    public function handle($request, $next)
    {
        if (! $this->shouldMinifyHtml($request)) {
            return $next($request);
        }

        $response = $next($request);

        $content = $response->getContent();

        foreach (config('minify-html.transformers', []) as $x => $transformer) {
            $content = (new $transformer)->transform($content);
        }

        return $response->setContent($content);
    }

    public function shouldMinifyHtml(Request $request)
    {
        if (! in_array($request->method(), ['GET', 'HEAD'])) {
            return false;
        }

        if ($request->isJson()) {
            return false;
        }

        if (! str_contains($request->header('Accept'), 'html')) {
            return false;
        }

        if ($request->isPrecognitive() || $request->isXmlHttpRequest()) {
            return false;
        }

        if(stripos(substr($request->getContent(), 0, 100), '<!DOCTYPE') !== false) {
            return false;
        }

        return true;
    }
}
