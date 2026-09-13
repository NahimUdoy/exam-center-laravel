<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (session('nvs_admin')) {
            return redirect()->route('admin.overview');
        }

        return view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $password = (string) $request->input('password', '');
        if (! hash_equals((string) config('cms.password'), $password)) {
            return back()->withErrors(['password' => 'That password doesn’t match.']);
        }

        $request->session()->put('nvs_admin', true);
        $request->session()->regenerate();

        return redirect()->route('admin.overview');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('nvs_admin');

        return redirect()->route('admin.login');
    }
}
