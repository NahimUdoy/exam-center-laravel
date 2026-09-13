@extends('layouts.nvs-page')

@section('title', 'Create account — NextVerseSkill')

@section('content')
<div class="min-h-[70vh] bg-aurora px-4 py-12">
    <div class="mx-auto w-full max-w-md rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-white p-6 shadow-sm sm:p-8">
        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand">Join NextVerseSkill</p>
        <h1 class="font-display mt-2 text-2xl font-extrabold text-ink">Create your account</h1>
        <p class="mt-2 text-sm text-muted">Takes a minute. Then your practice and tests are saved.</p>
        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="intended" value="{{ old('intended', url()->previous()) }}">
            <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Name
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm outline-none ring-brand/30 focus:ring-2">
            </label>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
            <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Email
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm outline-none ring-brand/30 focus:ring-2">
            </label>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
            <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Password
                <input id="password" type="password" name="password" required autocomplete="new-password" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm outline-none ring-brand/30 focus:ring-2">
            </label>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
            <label class="block text-xs font-extrabold uppercase tracking-wide text-muted">Confirm password
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm outline-none ring-brand/30 focus:ring-2">
            </label>
            <button type="submit" class="w-full rounded-full bg-ink py-3 text-sm font-bold text-white">Create account</button>
        </form>
        <p class="mt-5 text-center text-sm text-muted">
            Already have an account?
            <a href="{{ route('login') }}" class="font-bold text-ink">Log in</a>
        </p>
    </div>
</div>
@endsection
