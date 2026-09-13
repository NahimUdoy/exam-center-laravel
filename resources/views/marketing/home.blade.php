@extends('layouts.nvs-page')

@section('title', 'NextVerseSkill — Practice Today, Achieve Tomorrow')

@section('content')
@php
    $freeItems = $site['freeItems'] ?? ['All Practice Questions', 'All Mock Tests', 'Personalized Study Plans', 'Progress Tracking', 'E-Certificates', 'All Exam Sections'];
    $howSteps = $site['howSteps'] ?? [];
@endphp

<div class="bg-white text-ink">
    <div class="bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 py-2.5 text-center text-sm font-extrabold tracking-wide text-white shadow-md">
        100% FREE — Everything unlocked · No hidden cost · Practice · Mocks · Plans · Certificates
    </div>

    <section class="relative overflow-hidden bg-hero-premium">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-20 top-10 h-72 w-72 rounded-full bg-brand/10 blur-3xl animate-float"></div>
            <div class="absolute right-10 top-24 h-80 w-80 rounded-full bg-sky/20 blur-3xl animate-float" style="animation-delay: 1s"></div>
        </div>
        <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 pb-10 pt-10 sm:px-6 lg:grid-cols-2 lg:pb-6 lg:pt-14">
            <div class="relative max-w-xl">
                    <p class="hand-note absolute -top-2 left-0 rotate-[-6deg] text-xl sm:text-2xl">{{ $site['heroNote'] ?? 'Your IELTS Journey Starts Here!' }}</p>
                <h1 class="font-display mt-10 text-4xl font-extrabold leading-[1.08] tracking-tight text-ink sm:text-5xl lg:text-[3.4rem]">
                    Practice Today,<br>
                    <span class="bg-gradient-to-r from-brand to-indigo-600 bg-clip-text text-transparent">Achieve Tomorrow</span>
                </h1>
                <p class="mt-5 max-w-md text-base leading-relaxed text-muted sm:text-lg">
                    Your all-in-one free practice hub for IELTS, PTE, MBA & BBA. Real exam patterns. Clear plans. Keep the spirit — never stop.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-brand to-brand-deep px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand/30">Start Your Free Practice →</a>
                    <a href="#how" class="text-sm font-bold text-brand">Watch How It Works</a>
                </div>
            </div>
            <div class="relative animate-rise rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-gradient-to-br from-brand-soft/80 via-white to-sky/10 p-8 shadow-premium-lg">
                <p class="hand-note absolute -right-1 -top-4 rotate-[8deg] text-lg">Keep Practicing Never Stop! ♥</p>
                <div class="grid grid-cols-2 gap-3 pt-6">
                    @foreach([['100% Free', 'No paywall'], ['AI-ready', 'Smart feedback'], ['Real patterns', 'Exam format'], ['Track growth', 'Certificates']] as [$t, $s])
                        <div class="rounded-2xl border border-white/80 bg-white/90 p-4 shadow-sm">
                            <p class="font-display text-lg font-extrabold text-brand">{{ $t }}</p>
                            <p class="text-xs text-muted">{{ $s }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 pb-12 sm:px-6">
            <div class="grid gap-3 rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-white/80 p-3 shadow-premium backdrop-blur sm:grid-cols-2 lg:grid-cols-4 lg:p-4">
                @foreach([['100% Free', 'No hidden cost'], ['AI-Powered', 'Smarter practice'], ['Real Exam Pattern', 'True format'], ['Track Progress', 'See growth']] as [$label, $hint])
                    <div class="flex items-center gap-3 rounded-2xl px-3 py-3">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-brand-soft text-brand text-lg">★</span>
                        <div>
                            <p class="text-sm font-bold text-ink">{{ $label }}</p>
                            <p class="text-xs text-muted">{{ $hint }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#eef4ff] px-4 py-16 sm:px-6 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="grid items-start gap-8 lg:grid-cols-[0.9fr_1.15fr_0.95fr]">
                <div class="relative pt-2">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand">{{ $site['academyName'] ?? 'NextVerseSkill Academy' }}</p>
                    <h2 class="font-display mt-2 text-3xl font-extrabold text-ink sm:text-4xl">Hey, Future Achiever!</h2>
                    <p class="mt-4 text-base leading-relaxed text-muted">Tell us your level — Academy opens a full premium plan page with week-by-week detail and your total study time.</p>
                    <p class="hand-note mt-8 rotate-[-3deg] text-2xl">You have the plan — now let's make it happen! ♥</p>
                    <a href="{{ route('academy') }}" class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-brand">Visit Academy home →</a>
                </div>
                <div id="plan" class="relative">
                    <livewire:level-plan-wizard />
                </div>
                <div class="premium-card overflow-hidden bg-gradient-to-br from-[#fff7ed] to-white p-5">
                    <p class="font-display text-lg font-bold text-ink">Your Academy Plan</p>
                    <ul class="mt-4 space-y-3">
                        @foreach(['Know your level', 'Get personalized plan', 'Start practicing', 'Track your progress'] as $i => $item)
                            <li class="flex items-center gap-3 rounded-xl border border-[rgba(15,40,80,0.08)] bg-white px-3 py-2.5 text-sm font-semibold">
                                <span class="grid h-6 w-6 place-items-center rounded-full bg-brand text-[11px] font-bold text-white">{{ $i + 1 }}</span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <x-nvs.courses-explorer :courses="$courses" :site="$site" />

    <section id="free" class="bg-[#f8fbff] px-4 py-16 sm:px-6">
        <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[1fr_1.1fr]">
            <div>
                <div class="mb-5 grid h-16 w-16 place-items-center rounded-3xl bg-brand text-2xl text-white shadow-lg shadow-brand/30">🎁</div>
                <h2 class="font-display text-3xl font-extrabold text-brand sm:text-5xl">Everything is FREE!</h2>
                <p class="mt-4 max-w-md text-muted">Practice, mocks, plans, and certificates — no paywall on your core journey.</p>
                <p class="hand-note mt-6 rotate-[-4deg] text-2xl">Learn for Free! ♥</p>
            </div>
            <div class="premium-card p-6 sm:p-8">
                <ul class="grid gap-3 sm:grid-cols-2">
                    @foreach($freeItems as $item)
                        <li class="flex items-center gap-2.5 text-sm font-semibold text-ink"><span class="text-emerald-500">✓</span>{{ $item }}</li>
                    @endforeach
                </ul>
                <div class="mt-6 inline-flex rounded-full bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white">100% FREE — No Hidden Cost</div>
            </div>
        </div>
    </section>

    <section id="how" class="bg-white px-4 py-16 sm:px-6 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="font-display text-3xl font-extrabold text-ink sm:text-4xl">How It Works</h2>
                <p class="mt-3 text-muted">Four simple steps. Low time cost. High clarity.</p>
            </div>
            <div class="relative mt-12 grid gap-6 md:grid-cols-4">
                @foreach($howSteps as $i => $step)
                    <div class="relative text-center">
                        <div class="relative z-10 mx-auto grid h-16 w-16 place-items-center rounded-full text-lg font-extrabold text-white shadow-lg" style="background: {{ $step['color'] }}">{{ $i + 1 }}</div>
                        <h3 class="font-display mt-4 text-lg font-bold text-ink">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm text-muted">{{ $step['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white px-4 py-16 sm:px-6 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="font-display text-3xl font-extrabold text-ink sm:text-4xl">Everything You Need In One Place</h2>
            </div>
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($exams as $e)
                    <div class="flex items-center gap-4 rounded-[1.25rem] border border-[rgba(15,40,80,0.08)] bg-mist/60 p-5">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl text-sm font-bold text-white" style="background: {{ $e->accent }}">{{ $e->short_name }}</span>
                        <div>
                            <p class="font-display text-2xl font-extrabold text-ink">{{ number_format($e->question_count) }}+</p>
                            <p class="text-sm text-muted">{{ $e->name }} practice sets</p>
                        </div>
                    </div>
                @endforeach
                <div class="flex items-center gap-4 rounded-[1.25rem] border border-[rgba(15,40,80,0.08)] bg-mist/60 p-5">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber text-white">🎯</span>
                    <div>
                        <p class="font-display text-2xl font-extrabold text-ink">{{ $totalMocks }}+</p>
                        <p class="text-sm text-muted">Mock Tests</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 rounded-[1.25rem] border border-[rgba(15,40,80,0.08)] bg-mist/60 p-5">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-500 text-white">🏆</span>
                    <div>
                        <p class="font-display text-2xl font-extrabold text-ink">{{ $totalCertificates }}+</p>
                        <p class="text-sm text-muted">E-Certificates</p>
                    </div>
                </div>
            </div>
            <div class="mt-6 grid gap-3 rounded-[1.25rem] border border-dashed border-brand/25 bg-brand-soft/40 p-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($mockCounts as $slug => $count)
                    <div class="text-center text-sm font-bold text-ink">{{ strtoupper($slug) }} mocks <span class="text-brand">{{ $count }}</span></div>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="relative overflow-hidden bg-gradient-to-b from-[#0b1f44] to-[#06122a] text-white">
        <svg class="absolute inset-x-0 bottom-0 w-full opacity-40" viewBox="0 0 1200 180" preserveAspectRatio="none" aria-hidden>
            <path d="M0 180V90L180 40l140 55 160-70 170 80 150-45 200 60 200-50V180H0z" fill="#132a52"/>
            <path d="M0 180V120l220 10 160-55 180 45 140-30 190 50 160-35 150 40V180H0z" fill="#0f2348"/>
        </svg>
        <div class="relative mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:py-20">
            <p class="font-display text-sm font-bold uppercase tracking-[0.2em] text-sky-300">NextVerseSkill</p>
            <h2 class="font-display mt-3 text-3xl font-extrabold sm:text-5xl">Your Dream. Our Support.</h2>
            <p class="mx-auto mt-4 max-w-lg text-white/65">Be with us. Practice today. Don't stop. Everything is free — your spirit deserves a plan.</p>
            <a href="{{ route('dashboard') }}" class="mt-8 inline-flex rounded-full bg-white px-8 py-3.5 text-sm font-bold text-brand shadow-xl">Start Your Free Practice →</a>
            <div class="mt-10">
                @include('components.nvs.developer-credit', ['light' => true])
            </div>
        </div>
    </footer>
</div>
@endsection
