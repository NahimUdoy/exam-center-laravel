<?php

namespace App\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class Theme
{
    public const COOKIE = 'nvs_theme';

    public const STUDIO = 'studio';

    public const CLASSIC = 'classic';

    public const DEFAULT = self::STUDIO;

    public static function options(): array
    {
        return [
            self::CLASSIC => ['label' => 'Classic', 'hint' => 'Navy academy'],
            self::STUDIO => ['label' => 'Studio', 'hint' => 'Warm cards'],
        ];
    }

    public static function isValid(?string $theme): bool
    {
        return in_array($theme, [self::STUDIO, self::CLASSIC], true);
    }

    public static function resolve(?Request $request = null): string
    {
        $request ??= request();
        $fromQuery = $request->query('theme');
        if (self::isValid($fromQuery)) {
            return $fromQuery;
        }

        $fromCookie = $request->cookie(self::COOKIE);

        return self::isValid($fromCookie) ? $fromCookie : self::DEFAULT;
    }

    public static function makeCookie(string $theme): Cookie
    {
        return cookie(self::COOKIE, $theme, 60 * 24 * 365, '/', null, false, false);
    }
}
