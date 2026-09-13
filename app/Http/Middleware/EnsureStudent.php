<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Livewire')) {
            return response()->json([
                'ok' => false,
                'auth' => true,
                'message' => 'Please log in or create an account to continue.',
            ], 401);
        }

        $request->session()->flash('auth_required', true);
        $request->session()->flash('auth_tab', 'login');

        return redirect()->guest(url()->previous() ?: route('dashboard'));
    }
}
