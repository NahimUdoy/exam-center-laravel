@extends('layouts.nvs-page')

@section('title', $lesson->title.' — English Basics')

@section('content')
@php
    $drills = $lesson->drills->map(fn ($d) => [
        'prompt' => $d->prompt,
        'sentence' => $d->sentence,
        'options' => $d->options,
        'correct' => (int) $d->correct,
        'explanation' => $d->explanation,
    ])->values();

    $mark = function (array $example): string {
        $html = e($example['sentence'] ?? '');
        foreach ($example['highlight'] ?? [] as $word) {
            $needle = preg_quote(e((string) $word), '/');
            $html = preg_replace('/\b('.$needle.')\b/u', '<mark class="lesson-mark">$1</mark>', $html, 1) ?? $html;
        }

        return $html;
    };
@endphp
<div class="min-h-screen bg-aurora"
     x-data="{
        step: 'idea',
        i: 0,
        picked: null,
        locked: false,
        score: 0,
        finished: false,
        drills: {{ Js::from($drills) }},
        completeUrl: {{ Js::from(route('english-basics.complete', ['topic' => $lesson->slug])) }},
        get current() { return this.drills[this.i] || null; },
        get total() { return this.drills.length; },
        go(s) {
            this.step = s;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        pick(n) {
            if (window.nvsNeedAuth && window.nvsNeedAuth()) return;
            if (this.locked || this.finished) return;
            this.picked = n;
            this.locked = true;
            if (n === this.current.correct) this.score++;
        },
        next() {
            if (this.i >= this.total - 1) {
                this.finished = true;
                this.save();
                return;
            }
            this.i++;
            this.picked = null;
            this.locked = false;
        },
        restart() {
            this.i = 0; this.picked = null; this.locked = false; this.score = 0; this.finished = false;
        },
        save() {
            fetch(this.completeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ correct: this.score, total: this.total }),
            }).then((res) => {
                if (res.status === 401) {
                    window.dispatchEvent(new CustomEvent('nvs-auth-required'));
                }
            });
        }
     }">
    <main class="mx-auto max-w-3xl px-4 py-6 sm:px-6 sm:py-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('english-basics.index', ['exam' => 'english-basic']) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-ink">
                <x-nvs.icon name="ArrowLeft" class="h-4 w-4" /> All lessons
            </a>
            <div class="flex gap-2 text-xs font-bold">
                @if($prev)
                    <a href="{{ route('english-basics.show', ['topic' => $prev->slug, 'exam' => 'english-basic']) }}" class="rounded-full bg-mist px-3 py-1.5 text-ink">← {{ $prev->title }}</a>
                @endif
                @if($next)
                    <a href="{{ route('english-basics.show', ['topic' => $next->slug, 'exam' => 'english-basic']) }}" class="rounded-full bg-mist px-3 py-1.5 text-ink">{{ $next->title }} →</a>
                @endif
            </div>
        </div>

        <header class="rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-paper p-5 shadow-sm sm:p-7">
            <div class="flex flex-wrap items-center gap-2">
                <span class="grid h-10 w-10 place-items-center rounded-2xl text-sm font-extrabold" style="background: {{ $meta['soft'] }}; color: {{ $meta['color'] }}">{{ $lesson->letter }}</span>
                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold" style="background: {{ $meta['soft'] }}; color: {{ $meta['color'] }}">{{ $meta['label'] }}</span>
                <span class="rounded-full bg-mist px-2.5 py-1 text-[11px] font-bold text-muted">{{ $lesson->level }} · 8 questions</span>
            </div>
            <h1 class="font-display mt-3 text-3xl font-extrabold text-ink">{{ $lesson->title }}</h1>
            <p class="mt-1 text-muted">{{ $lesson->subtitle }}</p>
        </header>

        <nav class="sticky top-16 z-10 mt-4 grid grid-cols-4 gap-1 rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white/95 p-1 shadow-sm backdrop-blur">
            @foreach([
                ['idea', '1 Idea', 'Lightbulb'],
                ['see', '2 See', 'Eye'],
                ['remember', '3 Rules', 'Bookmark'],
                ['try', '4 Try', 'Play'],
            ] as [$key, $label, $icon])
                <button type="button" class="learn-step flex items-center justify-center gap-1 rounded-xl px-1 py-2 text-[11px] font-extrabold text-muted sm:text-xs"
                        @click="go('{{ $key }}')"
                        :aria-current="step === '{{ $key }}' ? 'step' : false">
                    <x-nvs.icon :name="$icon" class="hidden h-3.5 w-3.5 sm:block" />
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        <section class="mt-5 rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-white p-6 sm:p-8" x-show="step === 'idea'">
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-emerald-700">The idea</p>
            <p class="font-display mt-2 text-2xl font-bold leading-snug text-ink">{{ $lesson->summary }}</p>
            @foreach(preg_split('/(?<=\.)\s+/', trim($lesson->body)) as $para)
                @if($para !== '')
                    <p class="mt-3 text-[15px] leading-7 text-ink/80">{{ $para }}</p>
                @endif
            @endforeach
            <button type="button" @click="go('see')" class="mt-6 inline-flex items-center gap-2 rounded-full bg-ink px-5 py-2.5 text-sm font-bold text-white">
                See it in a sentence <x-nvs.icon name="ArrowRight" class="h-4 w-4" />
            </button>
        </section>

        <section class="mt-5 rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-white p-6 sm:p-8" x-show="step === 'see'" x-cloak>
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-emerald-700">See it</p>
            <h2 class="font-display mt-1 text-xl font-bold text-ink">Look at the highlighted words</h2>
            <p class="mt-1 text-sm text-muted">Read the sentence first, then the note underneath.</p>
            <div class="mt-5 space-y-3">
                @foreach($lesson->examples as $n => $example)
                    <figure class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist p-4 sm:p-5">
                        <p class="text-[11px] font-extrabold uppercase tracking-wide text-muted">Example {{ $n + 1 }}</p>
                        <p class="mt-2 text-lg font-semibold leading-8 text-ink">{!! $mark($example) !!}</p>
                        <p class="mt-2 text-sm text-muted">{{ $example['note'] }}</p>
                    </figure>
                @endforeach
            </div>
            <button type="button" @click="go('remember')" class="mt-6 inline-flex items-center gap-2 rounded-full bg-ink px-5 py-2.5 text-sm font-bold text-white">
                Remember the rules <x-nvs.icon name="ArrowRight" class="h-4 w-4" />
            </button>
        </section>

        <section class="mt-5 rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-white p-6 sm:p-8" x-show="step === 'remember'" x-cloak>
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-emerald-700">Remember</p>
            <h2 class="font-display mt-1 text-xl font-bold text-ink">Keep these four points</h2>
            <ol class="mt-5 space-y-3">
                @foreach($lesson->rules as $n => $rule)
                    <li class="flex gap-3 rounded-2xl bg-mist px-4 py-3 text-sm text-ink">
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-white text-xs font-extrabold" style="color: {{ $meta['color'] }}">{{ $n + 1 }}</span>
                        <span class="pt-1">{{ $rule }}</span>
                    </li>
                @endforeach
            </ol>
            <button type="button" @click="go('try')" class="mt-6 inline-flex items-center gap-2 rounded-full bg-ink px-5 py-2.5 text-sm font-bold text-white">
                Try 8 questions <x-nvs.icon name="Play" class="h-4 w-4" />
            </button>
        </section>

        <section id="practice" class="mt-5 rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-paper p-6 shadow-sm sm:p-8" x-show="step === 'try'" x-cloak>
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-emerald-700">Try it</p>
                    <h2 class="font-display text-xl font-bold text-ink">Practice</h2>
                </div>
                <p class="text-xs font-bold text-muted" x-show="!finished">
                    <span x-text="i + 1"></span> / <span x-text="total"></span>
                </p>
            </div>
            <div class="mb-5 h-1.5 overflow-hidden rounded-full bg-mist" x-show="!finished">
                <div class="h-full rounded-full bg-emerald-600 transition-all" :style="'width: ' + (((i + (locked ? 1 : 0)) / total) * 100) + '%'"></div>
            </div>

            <div x-show="!finished">
                <p class="text-lg font-semibold leading-7 text-ink" x-text="current?.prompt"></p>
                <blockquote class="mt-4 rounded-2xl border border-emerald-600/15 bg-emerald-50 px-4 py-3 text-base font-semibold leading-7 text-ink"
                            x-show="current?.sentence"
                            x-text="current?.sentence"></blockquote>
                <p class="mt-4 text-[11px] font-extrabold uppercase tracking-wide text-muted">Tap one answer</p>
                @guest
                    <p class="mt-1 text-xs text-muted">You can read the question. Log in when you tap an answer so we can save your score.</p>
                @endguest
                <div class="mt-2 grid gap-2">
                    <template x-for="(opt, n) in (current?.options || [])" :key="n">
                        <button type="button" @click="pick(n)"
                                class="quiz-choice rounded-2xl border px-4 py-3 text-left text-sm font-medium transition"
                                :class="!locked ? 'border-[rgba(15,40,80,0.08)] bg-white hover:border-teal/40' : (n === current.correct ? 'border-teal bg-emerald-50 text-ink' : (n === picked ? 'border-rose bg-rose/10 text-ink' : 'border-[rgba(15,40,80,0.08)] bg-white opacity-60'))">
                            <span class="mr-2 font-extrabold text-muted" x-text="String.fromCharCode(65 + n)"></span>
                            <span x-text="opt"></span>
                        </button>
                    </template>
                </div>
                <div class="mt-4 rounded-2xl bg-white p-4 text-sm leading-6 text-ink" x-show="locked">
                    <p class="text-xs font-extrabold uppercase tracking-wide" :class="picked === current.correct ? 'text-teal' : 'text-rose'"
                       x-text="picked === current.correct ? 'That’s right' : 'Not this time'"></p>
                    <p class="mt-1 text-ink/80" x-text="current?.explanation"></p>
                </div>
                <button type="button" @click="next()" x-show="locked" class="mt-5 rounded-full bg-ink px-5 py-2.5 text-sm font-bold text-white">
                    <span x-text="i === total - 1 ? 'See my score' : 'Next question'"></span>
                </button>
            </div>

            <div x-show="finished" class="text-center">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-emerald-700">Lesson complete</p>
                <p class="font-display mt-2 text-4xl font-extrabold text-ink"><span x-text="score"></span>/<span x-text="total"></span></p>
                <p class="mt-2 text-sm text-muted">You can retry, review the rules, or continue the path.</p>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    <button type="button" @click="restart()" class="rounded-full bg-mist px-4 py-2 text-sm font-bold text-ink">Try again</button>
                    <button type="button" @click="go('remember')" class="rounded-full bg-mist px-4 py-2 text-sm font-bold text-ink">Review rules</button>
                    @if($pathNext)
                        <a href="{{ route('english-basics.show', ['topic' => $pathNext->slug, 'exam' => 'english-basic']) }}" class="rounded-full bg-ink px-4 py-2 text-sm font-bold text-white">Next: {{ $pathNext->title }}</a>
                    @else
                        <a href="{{ route('english-basics.index', ['exam' => 'english-basic']) }}" class="rounded-full bg-ink px-4 py-2 text-sm font-bold text-white">Back to path</a>
                    @endif
                </div>
            </div>
        </section>

        @if($related->isNotEmpty())
            <section class="mt-8">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Related in {{ $meta['label'] }}</p>
                <h2 class="font-display mt-1 text-lg font-bold text-ink">Study these next</h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach($related as $item)
                        @php $rm = $categories[$item->category] ?? $meta; @endphp
                        <a href="{{ route('english-basics.show', ['topic' => $item->slug, 'exam' => 'english-basic']) }}"
                           class="flex gap-3 rounded-[1.25rem] border border-[rgba(15,40,80,0.08)] bg-white p-4">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl text-sm font-extrabold" style="background: {{ $rm['soft'] }}; color: {{ $rm['color'] }}">{{ $item->letter }}</span>
                            <span class="min-w-0">
                                <span class="block text-sm font-bold text-ink">{{ $item->title }}</span>
                                <span class="mt-0.5 block text-xs text-muted">{{ $item->subtitle }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</div>
@endsection
