@extends('layouts.nvs-page')

@section('title', 'Your Academy Plan — NextVerseSkill')

@section('content')
@if(!$plan)
    <div class="mx-auto flex max-w-xl flex-col items-center px-4 py-24 text-center">
        <h1 class="font-display text-3xl font-extrabold text-ink">No Academy plan yet</h1>
        <p class="mt-3 text-muted">Take the free 1-minute level check and we will prepare your full personal plan.</p>
        <a href="{{ route('home') }}#plan" class="mt-8 inline-flex rounded-full bg-gradient-to-r from-brand to-brand-deep px-6 py-3 text-sm font-bold text-white">Tell us your level →</a>
    </div>
@else
<div class="min-h-screen bg-[#f3f6ff] text-ink">
    <section class="relative overflow-hidden border-b border-[rgba(15,40,80,0.08)] bg-gradient-to-br from-[#0b1f44] via-[#123066] to-[#1d4ed8] text-white">
        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:py-16">
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-sky-200">NextVerseSkill Academy</span>
                <span class="rounded-full bg-emerald-400/20 px-3 py-1 text-xs font-bold text-emerald-200">100% Free personal plan</span>
            </div>
            <div class="mt-6 grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
                <div>
                    <p class="hand-note text-2xl text-sky-200">Your journey is already in motion ♥</p>
                    <h1 class="font-display mt-3 max-w-3xl text-3xl font-extrabold leading-tight sm:text-5xl">{{ $plan['headline'] }}</h1>
                    <p class="mt-4 max-w-2xl text-base text-white/70 sm:text-lg">{{ $plan['summary'] }}</p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold">{{ $plan['level_label'] }}</span>
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold">{{ $plan['exam_name'] }} track</span>
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold">{{ $plan['tier'] }} pathway</span>
                    </div>
                </div>
                <div class="rounded-[1.5rem] border border-white/15 bg-white/10 p-5 backdrop-blur-md shadow-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-sky-200">Total Academy use time</p>
                    <p class="font-display mt-2 text-4xl font-extrabold text-white sm:text-5xl">{{ $plan['total_hours_label'] }}</p>
                    <p class="mt-2 text-sm text-white/65">{{ $plan['total_minutes'] }} minutes across {{ $plan['weeks'] }} weeks · {{ $plan['weekly_minutes'] }} min every week</p>
                    <div class="mt-5 grid grid-cols-3 gap-2 text-center text-sm">
                        <div class="rounded-xl bg-white/10 p-2"><p class="font-bold">{{ $plan['daily_minutes'] }}m</p><p class="text-[10px] text-white/60">Daily</p></div>
                        <div class="rounded-xl bg-white/10 p-2"><p class="font-bold">{{ $plan['days_per_week'] }}</p><p class="text-[10px] text-white/60">Days/wk</p></div>
                        <div class="rounded-xl bg-white/10 p-2"><p class="font-bold">{{ $plan['weekend_minutes'] }}m</p><p class="text-[10px] text-white/60">Weekend</p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="mx-auto max-w-7xl space-y-8 px-4 py-10 sm:px-6 lg:py-14">
        <section class="premium-card overflow-hidden">
            <div class="border-b border-[rgba(15,40,80,0.08)] bg-gradient-to-r from-brand-soft/70 to-white px-6 py-5">
                <h2 class="font-display text-xl font-bold text-ink sm:text-2xl">One study day — exact minutes</h2>
            </div>
            <div class="grid gap-4 p-6 lg:grid-cols-3">
                @foreach($plan['session_blocks'] as $i => $block)
                    <div class="relative rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist/40 p-5">
                        <span class="absolute right-4 top-4 font-display text-4xl font-extrabold text-brand/10">0{{ $i + 1 }}</span>
                        <p class="inline-flex rounded-full bg-brand px-2.5 py-1 text-xs font-bold text-white">{{ $block['minutes'] }} min</p>
                        <h3 class="font-display mt-3 text-lg font-bold text-ink">{{ $block['title'] }}</h3>
                        <p class="mt-2 text-sm text-muted">{{ $block['detail'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section>
            <h2 class="font-display text-2xl font-extrabold text-ink">Your {{ $plan['days_per_week'] }}-day schedule</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($plan['day_templates'] as $day)
                    <div class="rounded-[1.35rem] border border-[rgba(15,40,80,0.08)] bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold uppercase text-brand">{{ $day['day_label'] }}</p>
                            <p class="text-sm font-bold text-muted">{{ $day['minutes'] }} min</p>
                        </div>
                        <h3 class="font-display mt-2 text-lg font-bold text-ink">{{ $day['title'] }}</h3>
                        <ul class="mt-3 space-y-2 text-sm text-muted">
                            @foreach($day['blocks'] as $b)
                                <li class="flex gap-2"><span class="text-brand">✓</span>{{ $b }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </section>

        <section>
            <h2 class="font-display text-2xl font-extrabold text-ink">Week-by-week detailed plan</h2>
            <div class="mt-5 space-y-4">
                @foreach($plan['week_plan'] as $week)
                    <article class="overflow-hidden rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-white shadow-premium">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[rgba(15,40,80,0.08)] bg-gradient-to-r from-[#eef4ff] to-white px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-brand font-display text-lg font-extrabold text-white">W{{ $week['week'] }}</span>
                                <div>
                                    <h3 class="font-display text-xl font-bold text-ink">{{ $week['title'] }}</h3>
                                    <p class="text-sm text-muted"><span class="font-semibold text-ink">Goal:</span> {{ $week['goal'] }}</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-brand-soft px-3 py-1.5 text-xs font-bold text-brand">{{ $week['minutes_this_week'] }} min this week</span>
                        </div>
                        <div class="grid gap-4 p-5 sm:grid-cols-2">
                            <div class="rounded-2xl bg-mist/70 p-4 text-sm">{{ $week['daily_focus'] }}</div>
                            <div class="rounded-2xl bg-mist/70 p-4 text-sm">{{ $week['weekend_focus'] }}</div>
                        </div>
                        <div class="flex flex-wrap gap-2 border-t border-[rgba(15,40,80,0.08)] px-5 py-4">
                            @foreach($week['focus_skills'] as $skill)
                                <span class="rounded-full border border-brand/15 bg-brand-soft/60 px-3 py-1 text-xs font-bold text-brand">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid gap-5 lg:grid-cols-2">
            <div class="premium-card p-6">
                <h2 class="font-display text-xl font-bold text-ink">Academy milestones</h2>
                <ul class="mt-5 space-y-3 text-sm">
                    @foreach($plan['milestones'] as $m)
                        <li class="flex gap-2"><span class="text-emerald-500">✓</span>{{ $m }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="premium-card p-6">
                <h2 class="font-display text-xl font-bold text-ink">Consistency rules</h2>
                <ul class="mt-5 space-y-3 text-sm">
                    @foreach($plan['consistency_rules'] as $r)
                        <li class="flex gap-2"><span class="text-brand">✓</span>{{ $r }}</li>
                    @endforeach
                </ul>
            </div>
        </section>

        <div class="text-center">
            <a href="{{ $plan['first_action']['href'] ?? route('dashboard') }}" class="inline-flex rounded-full bg-gradient-to-r from-brand to-brand-deep px-8 py-3.5 text-sm font-bold text-white">{{ $plan['first_action']['label'] ?? 'Start practicing' }} →</a>
        </div>
    </main>
</div>
@endif
@endsection
