@extends('layouts.nvs-page')

@section('title', 'Log in — NextVerseSkill')

@section('content')
<div class="min-h-[70vh] bg-aurora px-4 py-12">
    <div class="mx-auto w-full max-w-md rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-white p-6 shadow-sm sm:p-8">
        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand">Welcome back</p>
        <h1 class="font-display mt-2 text-2xl font-extrabold text-ink">Log in to continue</h1>
        <p class="mt-2 text-sm text-muted">Browse is free. An account saves practice and mock scores.</p>
        <x-auth-session-status class="mt-4" :status="session('status')" />
        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="intended" value="{{ old('intended', url()->previous()) }}">
            <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Email
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm outline-none ring-brand/30 focus:ring-2">
            </label>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
            <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Password
                <input id="password" type="password" name="password" required autocomplete="current-password" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm outline-none ring-brand/30 focus:ring-2">
            </label>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
            <label class="flex items-center gap-2 text-sm text-muted">
                <input type="checkbox" name="remember" class="rounded border-fog text-brand"> Remember me
            </label>
            <button type="submit" class="w-full rounded-full bg-ink py-3 text-sm font-bold text-white">Log in</button>
        </form>
        <p class="mt-5 text-center text-sm text-muted">
            New here?
            <a href="{{ route('register') }}" class="font-bold text-ink">Create an account</a>
        </p>
    </div>
</div>
@endsection
