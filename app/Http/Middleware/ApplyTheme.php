<?php

namespace App\Http\Middleware;

use App\Support\Theme;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ApplyTheme
{
    public function handle(Request $request, Closure $next): Response
    {
        $theme = Theme::resolve($request);
        View::share('theme', $theme);
        $request->attributes->set('theme', $theme);

        $response = $next($request);

        if (Theme::isValid($request->query('theme'))) {
            $response->headers->setCookie(Theme::makeCookie($theme));
        }

        return $response;
    }
}
