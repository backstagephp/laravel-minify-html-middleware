# Changelog

All notable changes to `laravel-minify-html-middleware` will be documented in this file.

## v5.2.3 - 2026-08-29

### Fixed

**Skip responses that cannot be minified** (#6)

The middleware decided whether to minify from the **request** alone, then unconditionally read and rewrote the body. A file download triggered by ordinary browser navigation still arrives with `Accept: text/html`, so it passed every check — but `StreamedResponse::getContent()` returns `false` and `StreamedResponse::setContent()` throws a `LogicException`.

Any app registering this middleware globally **and** using `response()->streamDownload(...)` or `response()->download(...)` returned a 500 on every download. Non-HTML bodies served to a browser (XML sitemaps, RSS feeds, CSV exports) were also whitespace-collapsed.

A new `shouldMinifyResponse()` check now runs after the response resolves and skips:

- `StreamedResponse` and `BinaryFileResponse`
- responses declaring a non-HTML `Content-Type`
- responses whose body is not a readable string

Also makes the `Accept` header check null-safe — `$request->header('Accept')` returns `null` when the header is absent, which is a deprecation on PHP 8.1+.

### Upgrading

No action needed. A response with no `Content-Type` header yet is still treated as HTML, since Symfony defaults it to `text/html` in `prepare()` — so the common `response()->view(...)` path is unchanged. All pre-existing tests pass untouched.
