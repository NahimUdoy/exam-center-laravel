@extends('layouts.nvs-page')

@section('title', $desk['headline'].' — NextVerseSkill')

@section('content')
<div class="min-h-screen bg-aurora">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:py-8">
        <div class="overflow-hidden rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper shadow-premium">
            <div class="h-1.5" style="background: {{ $exam->accent }}"></div>
            <div class="grid gap-0 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="p-6 sm:p-8">
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">{{ $exam->short_name }} · free study desk</p>
                    <h1 class="font-display mt-2 text-3xl font-bold text-ink sm:text-4xl">{{ $desk['headline'] }}</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-muted">{{ $desk['promise'] }}</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ $desk['drills'][0]['href'] ?? route('dashboard', ['exam' => $exam->slug]) }}" class="inline-flex items-center gap-2 rounded-2xl bg-brand px-5 py-3 text-sm font-bold text-white shadow-lg shadow-brand/20">
                            Start today’s drill
                            <x-nvs.icon name="ArrowRight" class="h-4 w-4" />
                        </a>
                        <a href="{{ route('speaking.show', ['exam' => $exam->slug]) }}" class="inline-flex items-center gap-2 rounded-2xl border border-[rgba(49,36,24,0.1)] bg-paper px-5 py-3 text-sm font-bold text-ink">Speak 10 minutes</a>
                    </div>
                </div>
                <div class="border-t border-[rgba(49,36,24,0.06)] bg-mist/70 p-6 sm:p-8 lg:border-l lg:border-t-0">
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Today · {{ $desk['today']['minutes'] }} min</p>
                    <p class="font-display mt-2 text-xl font-bold text-ink">{{ $desk['today']['title'] }}</p>
                    <ol class="mt-4 space-y-2 text-sm text-ink">
                        @foreach($desk['today']['steps'] as $step)
                            <li class="flex gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand"></span><span>{{ $step }}</span></li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($desk['drills'] as $drill)
                <a href="{{ $drill['href'] }}" class="deck-card p-0">
                    <div class="deck-card-band" style="--deck: {{ $exam->accent }}"></div>
                    <div class="p-4">
                        <p class="font-display text-lg font-bold text-ink">{{ $drill['label'] }}</p>
                        <p class="mt-1 text-sm text-muted">{{ $drill['hint'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper p-6 shadow-premium">
                <h2 class="font-display text-xl font-bold text-ink">How scoring actually works</h2>
                <ul class="mt-4 space-y-3 text-sm leading-6 text-ink">
                    @foreach($desk['score'] as $line)
                        <li class="rounded-2xl bg-mist px-4 py-3">{{ $line }}</li>
                    @endforeach
                </ul>
            </section>
            <section class="rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper p-6 shadow-premium">
                <h2 class="font-display text-xl font-bold text-ink">14-day plan</h2>
                <ol class="mt-4 space-y-2 text-sm leading-6 text-ink">
                    @foreach($desk['plan'] as $i => $line)
                        <li><span class="mr-2 font-extrabold text-brand">{{ $i + 1 }}.</span>{{ $line }}</li>
                    @endforeach
                </ol>
            </section>
        </div>

        <section class="mt-6 rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper p-6 shadow-premium">
            <h2 class="font-display text-xl font-bold text-ink">Templates you can steal</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                @foreach($desk['templates'] as $tpl)
                    <div class="rounded-2xl bg-mist px-4 py-4">
                        <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-brand">{{ $tpl['title'] }}</p>
                        <p class="mt-2 text-sm leading-6 text-ink">{{ $tpl['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper p-6 shadow-premium">
                <h2 class="font-display text-xl font-bold text-ink">Phrases that keep you talking</h2>
                <ul class="mt-4 space-y-2 text-sm text-ink">
                    @foreach($desk['phrases'] as $phrase)
                        <li class="rounded-2xl border border-[rgba(49,36,24,0.06)] px-4 py-3">“{{ $phrase }}”</li>
                    @endforeach
                </ul>
            </section>
            <section class="rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper p-6 shadow-premium">
                <h2 class="font-display text-xl font-bold text-ink">Traps that cost marks</h2>
                <ul class="mt-4 space-y-2 text-sm leading-6 text-ink">
                    @foreach($desk['traps'] as $trap)
                        <li class="flex gap-2"><span class="text-brand">▸</span><span>{{ $trap }}</span></li>
                    @endforeach
                </ul>
            </section>
        </div>

        <section class="mt-6 rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper p-6 shadow-premium">
            <h2 class="font-display text-xl font-bold text-ink">Collocations & useful words</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach($desk['vocab'] as $row)
                    <div class="rounded-2xl bg-mist px-4 py-3">
                        <p class="text-sm font-extrabold text-brand">{{ $row['word'] }}</p>
                        <p class="mt-1 text-sm leading-6 text-ink">{{ $row['use'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        @if(count($desk['formulas']))
            <section id="formulas" class="mt-6 rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper p-6 shadow-premium">
                <h2 class="font-display text-xl font-bold text-ink">Formulas to keep on one page</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach($desk['formulas'] as $row)
                        <div class="rounded-2xl border border-[rgba(49,36,24,0.06)] px-4 py-3">
                            <p class="text-sm font-extrabold text-ink">{{ $row['name'] }}</p>
                            <p class="mt-1 text-sm leading-6 text-muted">{{ $row['rule'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
