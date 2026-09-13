@extends('layouts.nvs-page')

@section('title', 'English Basics A–Z — NextVerseSkill')

@section('content')
@php
    $letters = range('A', 'Z');
    $progress = $progress ?? [];
    $doneCount = collect($progress)->count();
    $totalLessons = $lessons->count();
    $pct = $totalLessons ? (int) round(($doneCount / $totalLessons) * 100) : 0;
    $continueDone = $continue && isset($progress[$continue->slug]);
@endphp
<div class="min-h-screen bg-aurora" x-data="{ q: '', cat: 'all', az: false }">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:py-10">
        <div class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#064e3b] via-[#047857] to-[#0d9488] p-6 text-white shadow-[0_28px_70px_rgba(5,150,105,0.28)] sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0 max-w-2xl">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-100">Easy path · one idea at a time</p>
                    <h1 class="font-display mt-2 text-3xl font-extrabold sm:text-4xl">Learn English in small steps</h1>
                    <p class="mt-3 text-sm text-white/80 sm:text-base">Read the idea, look at a real sentence, then try 8 short questions. You can jump around later — start with the path if you are new.</p>
                    @if($continue)
                        <a href="{{ route('english-basics.show', ['topic' => $continue->slug, 'exam' => 'english-basic']) }}"
                           class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-extrabold text-emerald-800 shadow-sm">
                            <x-nvs.icon name="Play" class="h-4 w-4" />
                            {{ $continueDone ? 'Review' : ($doneCount ? 'Continue' : 'Start here') }}: {{ $continue->title }}
                        </a>
                    @endif
                </div>
                <div class="flex items-center gap-4 rounded-[1.5rem] bg-white/10 px-5 py-4">
                    <div class="progress-ring grid h-[4.5rem] w-[4.5rem] place-items-center rounded-full" style="--pct: {{ $pct }}%">
                        <div class="grid h-14 w-14 place-items-center rounded-full bg-[#064e3b] text-sm font-extrabold">{{ $pct }}%</div>
                    </div>
                    <div>
                        <p class="font-display text-2xl font-bold">{{ $doneCount }}/{{ $totalLessons }}</p>
                        <p class="text-xs font-semibold text-white/70">lessons practised</p>
                        <p class="mt-1 text-xs text-white/60">{{ $lessons->sum('drills_count') }} questions in the bank</p>
                    </div>
                </div>
            </div>
        </div>

        <section class="mt-6 grid gap-3 md:grid-cols-3">
            @foreach([
                ['Lightbulb', '1. The idea', 'One short explanation. No wall of text.'],
                ['Eye', '2. See it', 'Real sentences with the key words marked.'],
                ['Play', '3. Try it', 'Eight questions from those sentences, with why.'],
            ] as [$icon, $title, $hint])
                <div class="rounded-[1.35rem] border border-[rgba(15,40,80,0.08)] bg-white p-4">
                    <span class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-50 text-emerald-700">
                        <x-nvs.icon :name="$icon" class="h-5 w-5" />
                    </span>
                    <p class="mt-3 text-sm font-extrabold text-ink">{{ $title }}</p>
                    <p class="mt-1 text-xs leading-5 text-muted">{{ $hint }}</p>
                </div>
            @endforeach
        </section>

        <section class="mt-8">
            <div class="mb-3 flex items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-emerald-700">Suggested order</p>
                    <h2 class="font-display text-xl font-bold text-ink">Your learning path</h2>
                </div>
                <p class="hidden text-xs font-semibold text-muted sm:block">Follow the numbers if you are starting out</p>
            </div>
            <ol class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach($path as $i => $lesson)
                    @php
                        $meta = $categories[$lesson->category] ?? ['label' => 'Lesson', 'color' => '#059669', 'soft' => '#d1fae5'];
                        $done = isset($progress[$lesson->slug]);
                        $isNext = $continue && $continue->slug === $lesson->slug && ! $done;
                    @endphp
                    <li>
                        <a href="{{ route('english-basics.show', ['topic' => $lesson->slug, 'exam' => 'english-basic']) }}"
                           class="app-card flex gap-3 rounded-[1.35rem] border bg-white p-4 outline-none {{ $isNext ? 'guide-next border-emerald-600/30' : 'border-[rgba(15,40,80,0.08)]' }}">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl text-sm font-extrabold {{ $done ? 'bg-teal/10 text-teal' : '' }}"
                                  style="{{ $done ? '' : 'background: '.$meta['soft'].'; color: '.$meta['color'] }}">
                                @if($done)
                                    <x-nvs.icon name="CheckCircle2" class="h-5 w-5" />
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-bold text-ink">{{ $lesson->title }}</span>
                                    @if($isNext)
                                        <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-white">Up next</span>
                                    @endif
                                </span>
                                <span class="mt-0.5 block text-xs text-muted">{{ $lesson->subtitle }}</span>
                                <span class="mt-2 text-[11px] font-bold text-muted">{{ $lesson->level }} · {{ $lesson->drills_count }} questions</span>
                            </span>
                            <x-nvs.icon name="ChevronRight" class="mt-1 h-4 w-4 shrink-0 text-muted" />
                        </a>
                    </li>
                @endforeach
            </ol>
        </section>

        <div class="mt-8 flex flex-col gap-3 lg:flex-row lg:items-center">
            <div class="relative min-w-0 flex-1">
                <x-nvs.icon name="Search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                <input x-model="q" type="search" placeholder="Search nouns, tenses, articles…" class="w-full rounded-2xl border border-[rgba(15,40,80,0.08)] bg-paper py-3 pl-10 pr-4 text-sm outline-none ring-teal/30 focus:ring-2">
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="cat = 'all'" class="rounded-full px-3 py-1.5 text-xs font-bold" :class="cat === 'all' ? 'bg-ink text-white' : 'bg-mist text-muted'">All skills</button>
                @foreach($categories as $key => $meta)
                    <button type="button" @click="cat = '{{ $key }}'" class="rounded-full px-3 py-1.5 text-xs font-bold" :class="cat === '{{ $key }}' ? 'text-white' : 'bg-mist text-muted'" :style="cat === '{{ $key }}' ? 'background: {{ $meta['color'] }}' : ''">{{ $meta['label'] }}</button>
                @endforeach
            </div>
        </div>

        <div class="mt-8 space-y-8">
            @foreach($categoryOrder as $key)
                @php
                    $group = $byCategory->get($key, collect());
                    $meta = $categories[$key] ?? ['label' => 'Lesson', 'color' => '#059669', 'soft' => '#d1fae5'];
                @endphp
                @if($group->isEmpty()) @continue @endif
                <section x-show="cat === 'all' || cat === '{{ $key }}'" id="skill-{{ $key }}">
                    <div class="mb-3 flex items-center gap-3">
                        <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $meta['color'] }}"></span>
                        <h2 class="font-display text-xl font-bold text-ink">{{ $meta['label'] }}</h2>
                        <span class="text-xs font-bold text-muted">{{ $group->count() }} lessons</span>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($group as $lesson)
                            @php $done = isset($progress[$lesson->slug]); @endphp
                            <a href="{{ route('english-basics.show', ['topic' => $lesson->slug, 'exam' => 'english-basic']) }}"
                               x-show="!q || '{{ addslashes(strtolower($lesson->title.' '.$lesson->subtitle.' '.$lesson->summary)) }}'.includes(q.toLowerCase())"
                               class="app-card flex gap-3 rounded-[1.35rem] border border-[rgba(15,40,80,0.08)] bg-white p-4 outline-none">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl text-sm font-extrabold" style="background: {{ $meta['soft'] }}; color: {{ $meta['color'] }}">{{ $lesson->letter }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-ink">{{ $lesson->title }}</span>
                                        @if($done)
                                            <x-nvs.icon name="CheckCircle2" class="h-4 w-4 text-teal" />
                                        @endif
                                    </span>
                                    <span class="mt-0.5 block text-xs text-muted">{{ $lesson->subtitle }}</span>
                                    <span class="mt-2 flex flex-wrap gap-2 text-[11px] font-bold">
                                        <span class="rounded-full bg-mist px-2 py-0.5 text-muted">{{ $lesson->level }}</span>
                                        <span class="rounded-full bg-mist px-2 py-0.5 text-muted">{{ $lesson->drills_count }} practice</span>
                                    </span>
                                </span>
                                <x-nvs.icon name="ChevronRight" class="mt-1 h-4 w-4 shrink-0 text-muted" />
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <section class="mt-10 rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-white p-5 sm:p-6">
            <button type="button" class="flex w-full items-center justify-between gap-3 text-left" @click="az = !az">
                <span>
                    <span class="block text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Find by letter</span>
                    <span class="font-display text-lg font-bold text-ink">A–Z jump list</span>
                </span>
                <span class="rounded-full bg-mist px-3 py-1 text-xs font-bold text-ink" x-text="az ? 'Hide' : 'Show'"></span>
            </button>
            <div class="mt-4" x-show="az" x-cloak>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($letters as $letter)
                        @php $has = $byLetter->has($letter); @endphp
                        <a href="#letter-{{ $letter }}"
                           class="grid h-9 w-9 place-items-center rounded-xl text-sm font-extrabold {{ $has ? 'bg-mist text-ink' : 'bg-fog text-muted/40 pointer-events-none' }}">
                            {{ $letter }}
                        </a>
                    @endforeach
                </div>
                <div class="mt-6 space-y-6">
                    @foreach($letters as $letter)
                        @php $group = $byLetter->get($letter, collect()); @endphp
                        @if($group->isEmpty()) @continue @endif
                        <div id="letter-{{ $letter }}" class="scroll-mt-28">
                            <p class="mb-2 text-sm font-extrabold text-ink">{{ $letter }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($group as $lesson)
                                    <a href="{{ route('english-basics.show', ['topic' => $lesson->slug, 'exam' => 'english-basic']) }}"
                                       class="rounded-full bg-mist px-3 py-1.5 text-xs font-bold text-ink hover:bg-ink hover:text-white">{{ $lesson->title }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
