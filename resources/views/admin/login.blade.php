<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin login — NextVerseSkill</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:500,600,700,800|plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#071428] font-sans text-ink antialiased">
    <div class="grid min-h-screen place-items-center px-4">
        <form method="POST" action="{{ route('admin.login.store') }}" class="w-full max-w-md rounded-[1.8rem] bg-white p-8 shadow-2xl">
            @csrf
            <p class="font-display text-xl font-extrabold">NextVerse<span class="text-brand">Skill</span></p>
            <p class="mt-4 text-xs font-extrabold uppercase tracking-[0.18em] text-brand">Admin panel</p>
            <h1 class="font-display mt-2 text-3xl font-extrabold">Manage the whole site</h1>
            <p class="mt-2 text-sm text-muted">Update exams, images, colors, mocks, and certificates. The public website reads the same data.</p>
            <label class="mt-6 block text-sm font-bold">
                Password
                <input type="password" name="password" class="mt-2 h-12 w-full rounded-2xl border border-[rgba(15,40,80,0.08)] px-4" autofocus>
            </label>
            @error('password')
                <p class="mt-2 text-sm font-semibold text-rose">{{ $message }}</p>
            @enderror
            <button type="submit" class="mt-5 w-full rounded-full bg-ink py-3 text-sm font-bold text-white">Enter admin</button>
            <p class="mt-4 text-center text-xs text-muted">Local default password: <span class="font-bold text-ink">nextverse</span></p>
        </form>
    </div>
</body>
</html>
