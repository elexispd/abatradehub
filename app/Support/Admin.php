<?php

namespace App\Support;

class Admin
{
    /**
     * Get the configured administrative panel route prefix (e.g. 'hub-ops').
     */
    public static function path(string $subpath = ''): string
    {
        $base = (string) config('abatradehub.admin_path', 'hub-ops');
        $sub = ltrim($subpath, '/');

        return $sub ? "{$base}/{$sub}" : $base;
    }

    /**
     * Get the full URL to the administrative panel or a subpath.
     */
    public static function url(string $subpath = ''): string
    {
        return url(self::path($subpath));
    }
}

