<?php

namespace Backstage\MinifyHtml\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MinifyHtml
{
    public function handle($request, $next)
    {
        if (! $this->shouldMinifyHtml($request)) {
            return $next($request);
        }

        $response = $next($request);

        if (! $this->shouldMinifyResponse($response)) {
            return $response;
        }

        $content = $response->getContent();

        foreach (config('minify-html.transformers', []) as $x => $transformer) {
            $content = (new $transformer)->transform($content);
        }

        $original = $response->original ?? null;

        $response->setContent($content);

        if ($original !== null) {
            $response->original = $original;
        }

        return $response;
    }

    public function shouldMinifyHtml(Request $request)
    {
        if (! in_array($request->method(), ['GET', 'HEAD'])) {
            return false;
        }

        if ($request->isJson()) {
            return false;
        }

        if (! str_contains((string) $request->header('Accept'), 'html')) {
            return false;
        }

        if ($request->isPrecognitive() || $request->isXmlHttpRequest()) {
            return false;
        }

        if (stripos(substr($request->getContent(), 0, 100), '<!DOCTYPE') !== false) {
            return false;
        }

        return true;
    }

    /**
     * A browser asking for HTML still reaches file downloads and feeds. Streamed and
     * binary responses carry no readable body and reject setContent(), so minifying
     * one throws; anything declaring a non-HTML content type is left alone.
     *
     * @param  mixed  $response
     */
    public function shouldMinifyResponse($response): bool
    {
        if (! $response instanceof Response) {
            return false;
        }

        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');

        if ($contentType !== null && ! str_contains($contentType, 'html')) {
            return false;
        }

        return is_string($response->getContent());
    }
}
