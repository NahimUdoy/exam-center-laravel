<!DOCTYPE html>
<html lang="en" data-theme="{{ $theme ?? 'classic' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — NextVerseSkill')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:600,700,800|outfit:400,500,600,700,800|sora:500,600,700,800|plus-jakarta-sans:400,500,600,700,800|caveat:500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="min-h-screen bg-[#eef3fb] font-sans text-ink antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_minmax(0,1fr)]" x-data="{ open: false }">
        <aside class="hidden bg-[#071428] text-white lg:sticky lg:top-0 lg:block lg:h-screen lg:overflow-y-auto">
            @include('layouts.partials.admin-nav')
        </aside>
        <div class="lg:hidden">
            <div class="flex items-center justify-between bg-[#071428] px-4 py-3 text-white">
                <span class="font-display font-bold">NV Admin</span>
                <button type="button" class="rounded-lg bg-white/10 px-3 py-1.5 text-sm" @click="open = true">Menu</button>
            </div>
            <div x-show="open" x-cloak class="fixed inset-0 z-50 bg-black/50" @click="open = false">
                <div class="h-full w-72 bg-[#071428] text-white" @click.stop>
                    @include('layouts.partials.admin-nav')
                </div>
            </div>
        </div>
        <main class="min-w-0 px-4 py-6 sm:px-8 lg:py-8">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>
</html>
