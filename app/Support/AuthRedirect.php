<?php

namespace App\Support;

class AuthRedirect
{
    public static function remember(?string $url = null): void
    {
        $url = $url ?: url()->previous();
        $host = parse_url($url, PHP_URL_HOST);

        if ($host && $host !== request()->getHost()) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        if (preg_match('#^/(login|register|forgot-password|reset-password)#', $path)) {
            return;
        }

        session()->put('url.intended', $url);
    }
}
