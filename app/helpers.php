<?php

declare(strict_types=1);

if (! function_exists('fp_asset_path')) {
    /**
     * Root-relative URL path for a public asset (e.g. /backend/assets/css/app.css).
     * The browser resolves it against the current page origin, so CSS/JS load over the same
     * scheme (HTTPS) as the HTML without a separate connection to a mismatched absolute URL.
     */
    function fp_asset_path(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        $full = asset($path);
        $rel = parse_url((string) $full, PHP_URL_PATH);

        return is_string($rel) && $rel !== '' ? $rel : '/'.ltrim($path, '/');
    }
}
