<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NextVerseSkill Academy')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:500,600,700,800|plus-jakarta-sans:400,500,600,700,800|caveat:500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    @livewireStyles
    @stack('head')
</head>
<body class="min-h-screen antialiased font-sans text-ink bg-mist">
    @include('components.nvs.header', ['variant' => $variant ?? 'marketing'])
    @yield('content')
    @include('components.nvs.auth-gate')
    @include('components.nvs.news-modal')
    @livewireScripts
    @stack('scripts')
</body>
</html>
