@props(['analysis', 'practiceHref' => null, 'mocksHref' => null, 'onRetake' => true])

@php
    $practiceHref = $practiceHref ?? route('dashboard', ['exam' => request('exam')]);
    $mocksHref = $mocksHref ?? route('mock-tests.index', ['exam' => request('exam')]);
    $ring = "conic-gradient(#2563eb {$analysis['score_pct']}%, #e2e8f0 0)";
    $leakTone = [
        'strong' => 'border-emerald-200 bg-emerald-50',
        'ok' => 'border-sky/40 bg-sky/10',
        'leak' => 'border-amber/40 bg-amber/10',
        'critical' => 'border-rose/40 bg-rose/10',
    ];
    $barTone = [
        'strong' => 'bg-emerald-500',
        'ok' => 'bg-sky',
        'leak' => 'bg-amber',
        'critical' => 'bg-rose',
    ];
    $verdictTone = [
        'correct' => 'border-emerald-200/80',
        'incorrect' => 'border-rose-200/80',
        'skipped' => 'border-amber-200/80',
        'attempted' => 'border-sky/40',
    ];
    $verdictPill = [
        'correct' => ['Correct', 'bg-emerald-500 text-white'],
        'incorrect' => ['Mistake', 'bg-rose-500 text-white'],
        'skipped' => ['Skipped', 'bg-amber-500 text-white'],
        'attempted' => ['Needs review', 'bg-sky text-white'],
    ];
@endphp

<div class="min-h-screen bg-[#f3f6ff] text-ink" x-data="{ filter: 'incorrect' }">
    <div class="relative overflow-hidden bg-gradient-to-br from-[#0b1f44] via-[#123066] to-brand text-white">
        <div class="pointer-events-none absolute -left-20 top-0 h-64 w-64 rounded-full bg-sky-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute right-0 bottom-0 h-56 w-56 rounded-full bg-indigo-300/20 blur-3xl"></div>
        <div class="relative mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:py-12">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-sky-200">NextVerseSkill · Mock result report</p>
            <div class="mt-4 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <h1 class="font-display text-3xl font-extrabold sm:text-4xl">{{ $analysis['mock_title'] }}</h1>
                    <p class="mt-2 text-white/70">{{ $analysis['exam_label'] }}</p>
                    <p class="mt-4 text-base text-white/80">{{ $analysis['summary'] }}</p>
                    <p class="mt-3 inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-bold text-sky-100">{{ $analysis['band_label'] }}</p>
                </div>
                <div class="flex items-center gap-5 rounded-[1.5rem] border border-white/15 bg-white/10 p-5 backdrop-blur">
                    <div class="grid h-28 w-28 place-items-center rounded-full" style="background: {{ $ring }}">
                        <div class="grid h-[5.25rem] w-[5.25rem] place-items-center rounded-full bg-[#0b1f44]">
                            <div class="text-center">
                                <p class="font-display text-2xl font-extrabold">{{ $analysis['score_pct'] }}%</p>
                                <p class="text-[10px] font-semibold uppercase text-white/55">score</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2"><x-nvs.icon name="CheckCircle2" class="h-4 w-4 text-emerald-300" /><span class="text-white/70">Correct</span><span class="ml-auto font-bold">{{ $analysis['correct'] }}</span></div>
                        <div class="flex items-center gap-2"><x-nvs.icon name="XCircle" class="h-4 w-4 text-rose-300" /><span class="text-white/70">Incorrect</span><span class="ml-auto font-bold">{{ $analysis['incorrect'] }}</span></div>
                        <div class="flex items-center gap-2"><x-nvs.icon name="CircleDashed" class="h-4 w-4 text-amber-300" /><span class="text-white/70">Skipped</span><span class="ml-auto font-bold">{{ $analysis['skipped'] }}</span></div>
                        @if(($analysis['attempted_unscored'] ?? 0) > 0)
                            <div class="flex items-center gap-2"><x-nvs.icon name="Target" class="h-4 w-4 text-sky-300" /><span class="text-white/70">Needs review</span><span class="ml-auto font-bold">{{ $analysis['attempted_unscored'] }}</span></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:py-10">
        <section class="premium-card p-5 sm:p-6">
            <div class="flex items-center gap-2">
                <x-nvs.icon name="AlertTriangle" class="h-5 w-5 text-amber" />
                <h2 class="font-display text-xl font-bold">Where marks are leaking</h2>
            </div>
            <p class="mt-1 text-sm text-muted">Red / amber skills need priority practice. Green skills are safer.</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($analysis['leaks'] as $leak)
                    <div class="rounded-2xl border p-4 {{ $leakTone[$leak['level']] ?? 'border-sky/40 bg-sky/10' }}">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-display font-bold text-ink">{{ $leak['skill'] }}</p>
                            <span class="text-sm font-extrabold text-ink">{{ $leak['accuracy'] }}%</span>
                        </div>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-muted">{{ $leak['section'] }}</p>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/80">
                            <div class="h-full rounded-full {{ $barTone[$leak['level']] ?? 'bg-sky' }}" style="width: {{ $leak['accuracy'] }}%"></div>
                        </div>
                        <p class="mt-2 text-xs text-ink/80">✓ {{ $leak['correct'] }} · ✕ {{ $leak['incorrect'] }} · skip {{ $leak['skipped'] }}</p>
                        <p class="mt-2 text-sm leading-relaxed text-ink">{{ $leak['advice'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
            <section class="premium-card p-5 sm:p-6">
                <div class="flex items-center gap-2">
                    <x-nvs.icon name="Lightbulb" class="h-5 w-5 text-brand" />
                    <h2 class="font-display text-xl font-bold">Fix these next</h2>
                </div>
                <ul class="mt-4 space-y-3">
                    @foreach($analysis['top_fixes'] as $i => $fix)
                        <li class="flex gap-3 rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist/50 px-3 py-3 text-sm text-ink">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand text-[11px] font-bold text-white">{{ $i + 1 }}</span>
                            {{ $fix }}
                        </li>
                    @endforeach
                </ul>
                <div class="mt-5 rounded-2xl bg-brand-soft/60 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-brand">Strengths to keep</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($analysis['strengths'] as $s)
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-brand">{{ $s }}</span>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-[1.25rem] border border-[rgba(15,40,80,0.08)] bg-white shadow-[0_20px_50px_rgba(37,99,235,0.1)]">
                <div class="border-b border-[rgba(15,40,80,0.08)] bg-gradient-to-r from-[#eef4ff] via-white to-white px-5 py-4 sm:px-6">
                    <h2 class="font-display text-xl font-extrabold text-ink">Mistake review</h2>
                    <p class="mt-1 text-sm text-muted">Compare your answer vs the key. Read the tip before moving on.</p>
                    <div class="mt-4 grid grid-cols-4 gap-1 rounded-full bg-mist p-1" role="tablist">
                        @foreach([
                            ['incorrect', 'Mistakes', $analysis['incorrect']],
                            ['skipped', 'Skipped', $analysis['skipped']],
                            ['correct', 'Correct', $analysis['correct']],
                            ['all', 'All', count($analysis['items'])],
                        ] as [$id, $label, $count])
                            <button type="button" @click="filter = '{{ $id }}'" class="rounded-full px-2 py-2 text-center text-[11px] font-bold transition sm:text-xs"
                                :class="filter === '{{ $id }}' ? 'bg-white text-ink shadow-sm' : 'text-muted hover:text-ink'">
                                {{ $label }}
                                <span class="ml-1 tabular-nums" :class="filter === '{{ $id }}' ? 'text-brand' : 'text-muted/80'">{{ $count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="max-h-[32rem] space-y-3 overflow-y-auto bg-[#f7f9ff] p-4 scrollbar-thin sm:p-5">
                    @foreach($analysis['items'] as $item)
                        @php
                            $pill = $verdictPill[$item['verdict']] ?? $verdictPill['attempted'];
                            $isWrong = $item['verdict'] === 'incorrect';
                            $isSkip = $item['verdict'] === 'skipped';
                            $yourTone = ($isWrong || $isSkip) ? 'bad' : ($item['verdict'] === 'correct' ? 'good' : 'neutral');
                        @endphp
                        <article x-show="filter === 'all' || filter === '{{ $item['verdict'] }}'" x-cloak
                            class="overflow-hidden rounded-2xl border bg-white shadow-[0_10px_30px_rgba(15,40,80,0.06)] {{ $verdictTone[$item['verdict']] ?? 'border-sky/40' }}">
                            <div class="flex items-start justify-between gap-3 px-4 pt-4 sm:px-5">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-muted">Q{{ $item['number'] }} · {{ $item['skill'] }} · {{ $item['section'] }}</p>
                                    <h3 class="mt-1.5 font-display text-[15px] font-bold leading-snug text-ink sm:text-base">{{ $item['title'] }}</h3>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide {{ $pill[1] }}">{{ $pill[0] }}</span>
                            </div>
                            <div class="mx-4 mt-4 grid overflow-hidden rounded-xl border border-[rgba(15,40,80,0.08)] sm:mx-5 sm:grid-cols-2">
                                <div class="px-3.5 py-3 {{ $yourTone === 'bad' ? 'bg-rose-50/80' : ($yourTone === 'good' ? 'bg-emerald-50/80' : 'bg-mist/70') }}">
                                    <p class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wide {{ $yourTone === 'bad' ? 'text-rose-600' : ($yourTone === 'good' ? 'text-emerald-700' : 'text-muted') }}">Your answer</p>
                                    <p class="mt-1.5 text-sm leading-snug text-ink">{{ $item['your_answer'] ?? 'No answer' }}</p>
                                </div>
                                <div class="px-3.5 py-3 bg-emerald-50/80">
                                    <p class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-wide text-emerald-700">Correct / expected</p>
                                    <p class="mt-1.5 text-sm font-semibold leading-snug text-ink">{{ $item['correct_answer'] ?? 'See examiner notes' }}</p>
                                </div>
                            </div>
                            @if(!empty($item['leakage']))
                                <p class="mx-4 mt-3 rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold leading-relaxed text-rose-700 sm:mx-5">{{ $item['leakage'] }}</p>
                            @endif
                            <div class="m-4 flex items-start gap-2.5 rounded-xl bg-[#eef4ff] px-3 py-2.5 sm:m-5 sm:mt-3">
                                <x-nvs.icon name="Lightbulb" class="mt-0.5 h-4 w-4 shrink-0 text-brand" />
                                <p class="text-sm leading-relaxed text-ink/80">{{ $item['tip'] }}</p>
                            </div>
                        </article>
                    @endforeach
                    @php
                        $shown = collect($analysis['items'])->pluck('verdict')->unique()->values();
                    @endphp
                    <p x-show="filter !== 'all' && !{{ Js::from($shown) }}.includes(filter)" x-cloak class="py-10 text-center text-sm text-muted">Nothing in this filter — nice work.</p>
                </div>
            </section>
        </div>

        <section class="flex flex-col gap-3 rounded-[1.5rem] bg-gradient-to-br from-[#0b1f44] to-brand p-6 text-white sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-sky-200">
                    <x-nvs.icon name="GraduationCap" class="h-3.5 w-3.5" />
                    NextVerseSkill Academy
                </p>
                <h3 class="font-display mt-2 text-xl font-bold sm:text-2xl">Turn this report into a repair plan</h3>
                <p class="mt-1 text-sm text-white/70">Practice the leak skills first — then retake when accuracy rises.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $practiceHref }}" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-brand">
                    Practice weak skills
                    <x-nvs.icon name="ArrowRight" class="h-4 w-4" />
                </a>
                <a href="{{ url('/#plan') }}" class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-5 py-2.5 text-sm font-bold">Academy plan</a>
                @if($onRetake)
                    <button type="button" wire:click="retake" class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-white/80 hover:text-white">Retake mock</button>
                @else
                    <a href="{{ $mocksHref }}" class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-white/80 hover:text-white">Back to mocks</a>
                @endif
            </div>
        </section>
    </main>
</div>
