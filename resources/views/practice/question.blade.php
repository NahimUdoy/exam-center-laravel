@extends('layouts.nvs-page')

@section('title', '#'.$item->external_id.' | '.$item->title.' — Practice')

@section('content')
@php
    $mode = $kind['mode'];
    $passage = $payload['passage'] ?? $item->passage ?: \App\Support\PracticeBank::PASSAGE;
    $words = preg_split('/\s+/', trim($passage)) ?: [];
    $prev = $index > 0 ? $questions[$index - 1] : null;
    $next = $index < $questions->count() - 1 ? $questions[$index + 1] : null;
    $shuffle = $questions->count() > 1
        ? $questions->values()->except($index)->random()
        : $item;
    $prevUrl = $prev ? route('practice.question', ['skill' => $skill, 'task' => $taskType->slug, 'question' => $prev->external_id, 'exam' => $exam->slug]) : null;
    $nextUrl = $next ? route('practice.question', ['skill' => $skill, 'task' => $taskType->slug, 'question' => $next->external_id, 'exam' => $exam->slug]) : null;
    $shuffleUrl = route('practice.question', ['skill' => $skill, 'task' => $taskType->slug, 'question' => $shuffle->external_id, 'exam' => $exam->slug]);
    $listUrl = route('practice.task', ['skill' => $skill, 'task' => $taskType->slug, 'exam' => $exam->slug]);
    $paraMap = collect($payload['paragraphs'] ?? [])->keyBy('id');
    $blankMap = [];
    foreach ($payload['blanks'] ?? [] as $blank) {
        $blankMap[(string) ($blank['id'] ?? '')] = '';
    }
    if ($mode === 'fib-bank') {
        $n = 0;
        foreach ($payload['passage_tokens'] ?? [] as $token) {
            if ($token === '___') {
                $blankMap[(string) $n] = '';
                $n++;
            }
        }
    }
@endphp
<div class="min-h-screen bg-aurora"
     x-data="{
        mode: {{ Js::from($mode) }},
        usesMic: {{ $kind['uses_mic'] ? 'true' : 'false' }},
        usesAudio: {{ $kind['uses_audio'] ? 'true' : 'false' }},
        phase: 'idle',
        prepLeft: {{ (int) $kind['prep'] }},
        recLeft: {{ (int) $kind['record'] }},
        duration: {{ (int) $kind['record'] }},
        prepTotal: {{ (int) $kind['prep'] }},
        difficulty: '{{ $item->difficulty }}',
        scoreTab: 'mine',
        bookmarked: {{ $item->bookmarked ? 'true' : 'false' }},
        timer: null,
        submitting: false,
        submitted: false,
        score: null,
        notice: '',
        textAnswer: '',
        selected: null,
        selectedMulti: [],
        blanks: {{ Js::from($blankMap) }},
        activeBlank: '1',
        order: {{ Js::from($payload['display_order'] ?? collect($payload['paragraphs'] ?? [])->pluck('id')->values()->all()) }},
        paras: {{ Js::from($paraMap->toArray()) }},
        hiWords: [],
        audioText: {{ Js::from($payload['audio_text'] ?? '') }},
        audioPlaying: false,
        audioDone: false,
        submitUrl: {{ Js::from(route('practice.submit', ['skill' => $skill, 'task' => $taskType->slug, 'question' => $item->external_id, 'exam' => $exam->slug])) }},
        get progress() {
            return (this.phase === 'recording' || this.phase === 'done')
                ? ((this.duration - this.recLeft) / this.duration) * 100
                : 0;
        },
        get wordCount() {
            const t = (this.textAnswer || '').trim();
            return t ? t.split(/\s+/).length : 0;
        },
        pad(n) { return String(n).padStart(2, '0'); },
        format(total) { return this.pad(Math.floor(total / 60)) + ':' + this.pad(total % 60); },
        clearTimer() { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
        playAudio() {
            if (!this.audioText || !window.speechSynthesis) { this.audioDone = true; return; }
            window.speechSynthesis.cancel();
            this.audioPlaying = true;
            const u = new SpeechSynthesisUtterance(this.audioText);
            u.onend = () => { this.audioPlaying = false; this.audioDone = true; };
            window.speechSynthesis.speak(u);
        },
        start() {
            if (window.nvsNeedAuth && window.nvsNeedAuth()) return;
            this.clearTimer();
            this.prepLeft = this.prepTotal;
            this.recLeft = this.duration;
            this.phase = 'prep';
            this.submitted = false;
            this.score = null;
            this.notice = '';
            if (this.usesAudio && this.audioText) this.playAudio();
            this.timer = setInterval(() => this.tick(), 1000);
        },
        redo() {
            this.clearTimer();
            if (window.speechSynthesis) window.speechSynthesis.cancel();
            this.phase = 'idle';
            this.prepLeft = this.prepTotal;
            this.recLeft = this.duration;
            this.submitted = false;
            this.score = null;
            this.notice = '';
            this.submitting = false;
            this.textAnswer = '';
            this.selected = null;
            this.selectedMulti = [];
            this.hiWords = [];
            this.audioPlaying = false;
            this.audioDone = false;
            Object.keys(this.blanks).forEach(k => this.blanks[k] = '');
        },
        tick() {
            if (this.phase === 'prep') {
                if (this.prepLeft <= 1) { this.phase = 'recording'; this.recLeft = this.duration; return; }
                this.prepLeft--;
            } else if (this.phase === 'recording') {
                if (this.recLeft <= 1) { this.phase = 'done'; this.recLeft = 0; this.clearTimer(); return; }
                this.recLeft--;
            }
        },
        status() {
            if (this.submitted) return 'Scored';
            if (!this.usesMic) return this.audioPlaying ? 'Playing audio…' : 'Ready when you are';
            if (this.phase === 'idle') return 'Ready when you are';
            if (this.phase === 'prep') return 'Recording in ' + this.prepLeft + ' sec';
            if (this.phase === 'recording') return 'Recording…';
            return 'Response captured';
        },
        headline() {
            if (this.submitted) return 'Score ready below';
            if (this.phase === 'idle') return 'Click to Start';
            if (this.phase === 'prep') return 'Get ready · ' + this.prepLeft + 's';
            if (this.phase === 'recording') return this.format(this.duration - this.recLeft) + ' / ' + this.format(this.duration);
            return 'Click Submit when ready';
        },
        pick(i) { this.selected = i; this.notice = ''; },
        toggleHi(word) {
            if (this.hiWords.includes(word)) this.hiWords = this.hiWords.filter(w => w !== word);
            else this.hiWords = [...this.hiWords, word];
        },
        move(from, dir) {
            const to = from + dir;
            if (to < 0 || to >= this.order.length) return;
            const next = [...this.order];
            const tmp = next[from];
            next[from] = next[to];
            next[to] = tmp;
            this.order = next;
        },
        currentAnswer() {
            if (this.usesMic) return this.phase === 'idle' ? '' : 'spoken';
            if (this.mode === 'write' || this.mode === 'listen-write') return this.textAnswer;
            if (this.mode === 'mcq' || this.mode === 'listen-mcq') return this.selected;
            if (this.mode === 'fib-select' || this.mode === 'fib-bank') return this.blanks;
            if (this.mode === 'reorder') return this.order;
            if (this.mode === 'hiw') return this.hiWords;
            return this.textAnswer;
        },
        readyToSubmit() {
            if (this.usesMic) return this.phase === 'recording' || this.phase === 'done';
            if (this.mode === 'write' || this.mode === 'listen-write') return this.wordCount > 0;
            if (this.mode === 'mcq' || this.mode === 'listen-mcq') return this.selected !== null;
            if (this.mode === 'fib-select' || this.mode === 'fib-bank') return Object.values(this.blanks).every(v => v);
            if (this.mode === 'reorder') return this.order.length > 0;
            if (this.mode === 'hiw') return this.hiWords.length > 0;
            return true;
        },
        async submit() {
            if (window.nvsNeedAuth && window.nvsNeedAuth()) return;
            if (this.submitting) return;
            if (!this.readyToSubmit()) {
                this.notice = this.usesMic
                    ? 'Click the mic, record your answer, then press Submit.'
                    : 'Complete the task, then press Submit.';
                return;
            }
            this.notice = '';
            if (this.phase === 'recording') {
                this.phase = 'done';
                this.clearTimer();
            }
            this.submitting = true;
            this.scoreTab = 'mine';
            const recorded = this.usesMic ? Math.max(0, this.duration - this.recLeft) : 0;
            try {
                const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                const res = await fetch(this.submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        recorded_seconds: recorded,
                        difficulty: this.difficulty,
                        answer: this.currentAnswer(),
                    }),
                });
                const data = await res.json();
                if (res.status === 401) {
                    window.dispatchEvent(new CustomEvent('nvs-auth-required'));
                    return;
                }
                if (!res.ok || !data.score) {
                    this.notice = data.message || 'Could not score this attempt. Try Submit again.';
                    return;
                }
                this.score = data.score;
                this.submitted = true;
            } catch (e) {
                this.notice = 'Could not save this attempt. Check your connection and try Submit again.';
            } finally {
                this.submitting = false;
            }
        }
     }"
     x-init="if (usesAudio && !usesMic) { setTimeout(() => playAudio(), 600); } $watch('phase', value => { if (value === 'idle' || value === 'done') clearTimer(); })">
    <main class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:py-8">
        <div class="mb-4 flex items-center gap-3">
            <a href="{{ $listUrl }}" class="grid h-9 w-9 place-items-center rounded-full border border-[rgba(15,40,80,0.08)] bg-paper text-ink">
                <x-nvs.icon name="ChevronLeft" class="h-4 w-4" />
            </a>
            <p class="text-sm text-muted">
                <span class="font-semibold text-ink">{{ $taskType->name }}</span>
                · {{ $kind['instructions'] }}
            </p>
        </div>

        <div class="overflow-hidden rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-paper shadow-[0_20px_50px_rgba(37,99,235,0.1)]">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-teal px-4 py-3 text-white sm:px-5">
                <p class="font-display text-sm font-bold sm:text-base">#{{ $item->external_id }} | {{ $item->title }}</p>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide">{{ str_replace('-', ' ', $mode) }}</span>
                    <button type="button" @click="bookmarked = !bookmarked" class="rounded-full bg-white/15 p-1.5" aria-label="Bookmark">
                        <x-nvs.icon name="Bookmark" class="h-3.5 w-3.5" x-bind:class="bookmarked ? 'fill-white' : ''" />
                    </button>
                    <span class="rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-semibold">Appeared ({{ $item->appeared }})</span>
                </div>
            </div>

            <div class="space-y-4 p-4 sm:p-6">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full px-3 py-1 text-xs font-bold" :class="phase === 'recording' ? 'bg-rose/15 text-rose animate-pulse' : 'bg-teal/10 text-teal'" x-text="status()"></span>
                    <span class="rounded-full border border-amber/40 bg-amber/10 px-3 py-1 text-xs font-bold text-amber" x-text="difficulty"></span>
                </div>

                @if($kind['uses_audio'] && !empty($payload['audio_text']))
                    <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-mist px-4 py-3">
                        <x-nvs.icon name="Volume2" class="h-5 w-5 text-teal" x-bind:class="audioPlaying ? 'text-coral animate-pulse' : 'text-teal'" />
                        <p class="text-sm font-semibold" x-text="audioPlaying ? 'Playing audio…' : (audioDone ? 'Audio finished' : 'Audio ready')"></p>
                        <button type="button" @click="playAudio()" class="ml-auto rounded-full border border-teal/30 px-3 py-1 text-xs font-bold text-teal">Play audio</button>
                    </div>
                @endif

                @if($item->image_url || $mode === 'speak-image')
                    <div class="overflow-hidden rounded-2xl border border-teal/20 bg-white p-3">
                        @if($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="mx-auto max-h-72 w-full object-contain">
                        @else
                            <div class="flex h-40 items-end justify-around gap-2 rounded-xl bg-ink/5 p-4">
                                @foreach([40, 70, 55, 35] as $h)
                                    <div class="w-10 rounded-t-md bg-gradient-to-t from-coral to-amber" style="height: {{ $h }}%"></div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @if(in_array($mode, ['speak-read', 'speak-prompt', 'write', 'mcq'], true) || ($mode === 'speak-audio' && $taskType->slug !== 'repeat-sentence'))
                    @if(!empty($payload['prompt']) && $mode !== 'speak-read')
                        <p class="font-semibold text-ink">{{ $payload['prompt'] }}</p>
                    @endif
                    @if($mode !== 'write' || ! in_array($taskType->slug, ['essay', 'case-write'], true))
                        <div class="rounded-2xl border border-teal/25 bg-mist/40 p-5 leading-8 text-ink sm:text-[1.05rem]">
                            @foreach($words as $word)
                                <span class="mr-1 inline-block rounded px-0.5">{{ $word }}</span>
                            @endforeach
                        </div>
                    @endif
                @endif

                @if($mode === 'write' && in_array($taskType->slug, ['essay', 'case-write'], true) && !empty($payload['passage']) && $payload['passage'] !== $payload['prompt'])
                    <div class="rounded-2xl border border-teal/25 bg-mist/40 p-5 leading-7 text-sm text-ink">{{ $payload['passage'] }}</div>
                @endif

                @if(in_array($mode, ['mcq', 'listen-mcq'], true) && !empty($payload['options']))
                    <ul class="space-y-2">
                        @foreach($payload['options'] as $idx => $opt)
                            <li>
                                <button type="button" @click="pick({{ $idx }})"
                                    class="flex w-full items-start gap-3 rounded-xl border px-3 py-3 text-left text-sm transition"
                                    :class="selected === {{ $idx }} ? 'border-teal bg-teal/5' : 'border-transparent bg-mist hover:border-black/10'">
                                    <span class="mt-0.5 grid h-5 w-5 place-items-center rounded-full border text-[10px] font-bold"
                                          :class="selected === {{ $idx }} ? 'border-teal bg-teal text-white' : 'border-muted text-muted'">{{ chr(65 + $idx) }}</span>
                                    <span>{{ $opt }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if($mode === 'fib-select')
                    @include('components.nvs.fib-passage', [
                        'kind' => 'rw-fib',
                        'tokens' => $payload['passage_tokens'] ?? [],
                        'blanks' => $payload['blanks'] ?? [],
                        'wordBank' => [],
                        'map' => [],
                        'qid' => $item->external_id,
                        'bind' => 'alpine',
                    ])
                @endif

                @if($mode === 'fib-bank')
                    @include('components.nvs.fib-passage', [
                        'kind' => 'r-fib',
                        'tokens' => $payload['passage_tokens'] ?? [],
                        'blanks' => $payload['blanks'] ?? [],
                        'wordBank' => $payload['word_bank'] ?? [],
                        'map' => [],
                        'qid' => $item->external_id,
                        'bind' => 'alpine',
                    ])
                @endif

                @if($mode === 'reorder')
                    <ul class="space-y-2">
                        <template x-for="(pid, i) in order" :key="pid">
                            <li class="flex items-start gap-3 rounded-xl border border-[rgba(15,40,80,0.08)] bg-mist/60 p-3">
                                <span class="grid h-7 w-7 place-items-center rounded-lg bg-ink text-xs font-bold text-white" x-text="i + 1"></span>
                                <p class="flex-1 text-sm leading-6" x-text="paras[pid]?.text || pid"></p>
                                <div class="flex flex-col gap-1">
                                    <button type="button" class="rounded bg-paper px-2 text-xs font-bold" @click="move(i, -1)">↑</button>
                                    <button type="button" class="rounded bg-paper px-2 text-xs font-bold" @click="move(i, 1)">↓</button>
                                </div>
                            </li>
                        </template>
                    </ul>
                @endif

                @if($mode === 'hiw')
                    <p class="text-[15px] leading-8">
                        @foreach($payload['transcript_with_errors'] ?? [] as $row)
                            @php $word = is_array($row) ? ($row['word'] ?? '') : (string) $row; @endphp
                            <button type="button" @click="toggleHi({{ Js::from($word) }})"
                                class="mr-1 rounded px-0.5"
                                :class="hiWords.includes({{ Js::from($word) }}) ? 'bg-rose/20 font-bold text-rose underline' : ''">{{ $word }}</button>
                        @endforeach
                    </p>
                    <p class="text-xs text-muted">Click every word that does not match the audio.</p>
                @endif

                @if($kind['uses_mic'])
                    <div class="rounded-2xl border border-coral/20 bg-gradient-to-br from-[#fff4ef] to-[#ffe8df] p-4 sm:p-5">
                        <div class="flex flex-wrap items-center gap-4">
                            <button type="button" @click="(phase === 'idle' || phase === 'done') && start()"
                                class="relative grid h-14 w-14 place-items-center rounded-full text-white shadow-lg transition"
                                :class="phase === 'recording' ? 'bg-rose' : 'bg-gradient-to-br from-coral to-[#e85a3c] hover:brightness-110'"
                                aria-label="Start recording">
                                <span x-show="phase === 'recording'" class="absolute inset-0 rounded-full bg-rose/40 animate-pulse-ring"></span>
                                <x-nvs.icon name="Mic" class="h-6 w-6" />
                            </button>
                            <div class="min-w-0 flex-1">
                                <p class="font-display text-lg font-bold text-ink" x-text="headline()"></p>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/70">
                                    <div class="progress-fill h-full rounded-full bg-gradient-to-r from-coral to-amber" :style="'width:' + progress + '%'"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($kind['uses_text'])
                    @php $limit = $payload['word_limit'] ?? null; @endphp
                    <div>
                        <textarea rows="{{ $taskType->slug === 'essay' ? 12 : 6 }}" x-model="textAnswer"
                            class="w-full rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist/40 p-4 text-sm leading-6 outline-none ring-teal/30 focus:ring-2"
                            placeholder="Type your response here…"></textarea>
                        <div class="mt-2 text-xs font-semibold text-muted">
                            Word count: <span x-text="wordCount"></span>
                            @if($limit) (target {{ $limit['min'] }}–{{ $limit['max'] }}) @endif
                        </div>
                    </div>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="submit()" :disabled="submitting"
                            class="rounded-full border px-4 py-2 text-sm font-semibold transition disabled:opacity-60"
                            :class="submitted ? 'border-teal bg-teal text-white' : (readyToSubmit() ? 'border-teal bg-teal text-white hover:brightness-110' : 'border-teal text-teal hover:bg-teal/5')">
                            <span x-text="submitting ? 'Scoring…' : (submitted ? 'Submitted' : 'Submit')"></span>
                        </button>
                        <button type="button" @click="redo()" class="inline-flex items-center gap-1.5 rounded-full border border-[rgba(15,40,80,0.08)] px-4 py-2 text-sm font-semibold text-muted hover:bg-mist">
                            <x-nvs.icon name="RotateCcw" class="h-3.5 w-3.5" />
                            Redo
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ $shuffleUrl }}" class="rounded-full border border-[rgba(15,40,80,0.08)] p-2 text-muted hover:bg-mist" aria-label="Shuffle">
                            <x-nvs.icon name="Shuffle" class="h-4 w-4" />
                        </a>
                        @if($prevUrl)
                            <a href="{{ $prevUrl }}" class="rounded-full border border-[rgba(15,40,80,0.08)] p-2 text-ink">
                                <x-nvs.icon name="ChevronLeft" class="h-4 w-4" />
                            </a>
                        @else
                            <span class="rounded-full border border-[rgba(15,40,80,0.08)] p-2 text-ink opacity-30">
                                <x-nvs.icon name="ChevronLeft" class="h-4 w-4" />
                            </span>
                        @endif
                        <span class="rounded-full bg-mist px-3 py-1.5 text-xs font-bold text-ink">{{ $index + 1 }} / {{ $questions->count() }}</span>
                        @if($nextUrl)
                            <a href="{{ $nextUrl }}" class="rounded-full border border-[rgba(15,40,80,0.08)] p-2 text-ink">
                                <x-nvs.icon name="ChevronRight" class="h-4 w-4" />
                            </a>
                        @else
                            <span class="rounded-full border border-[rgba(15,40,80,0.08)] p-2 text-ink opacity-30">
                                <x-nvs.icon name="ChevronRight" class="h-4 w-4" />
                            </span>
                        @endif
                    </div>
                </div>
                <p x-show="notice" x-cloak class="mt-2 text-sm font-semibold text-coral" x-text="notice"></p>

                <div>
                    <p class="mb-2 text-sm font-semibold text-ink">How difficult was this question?</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['Easy', 'Medium', 'Difficult'] as $level)
                            <button type="button" @click="difficulty = '{{ $level }}'" class="rounded-full px-4 py-1.5 text-sm font-semibold transition"
                                :class="difficulty === '{{ $level }}' ? 'bg-ink text-white' : 'bg-mist text-muted hover:text-ink'">{{ $level }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col gap-2 text-xs text-muted">
                    @foreach($kind['tips'] as $i => $tip)
                        <p class="inline-flex items-start gap-2">
                            <x-nvs.icon name="Info" class="mt-0.5 h-3.5 w-3.5 shrink-0 {{ $i === 0 ? 'text-teal' : 'text-coral' }}" />
                            {{ $tip }}
                        </p>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-[rgba(15,40,80,0.08)] px-4 py-4 sm:px-6">
                <div class="mb-3 flex gap-4 text-sm font-semibold">
                    <button type="button" @click="scoreTab = 'forum'" class="pb-2 transition" :class="scoreTab === 'forum' ? 'border-b-2 border-coral text-coral' : 'text-muted hover:text-ink'">Forum</button>
                    <button type="button" @click="scoreTab = 'mine'" class="pb-2 transition" :class="scoreTab === 'mine' ? 'border-b-2 border-coral text-coral' : 'text-muted hover:text-ink'">My Score</button>
                    <button type="button" @click="scoreTab = 'community'" class="pb-2 transition" :class="scoreTab === 'community' ? 'border-b-2 border-coral text-coral' : 'text-muted hover:text-ink'">Community Score</button>
                </div>
                <div class="rounded-2xl bg-sky/10 px-4 py-8 text-sm font-medium text-muted">
                    <div x-show="scoreTab === 'mine' && !score" class="grid place-items-center py-2">
                        No record available yet — submit to unlock scoring.
                    </div>
                    <template x-if="scoreTab === 'mine' && score">
                        <div class="grid gap-5 sm:grid-cols-[auto_1fr] sm:items-center">
                            <div class="mx-auto grid h-28 w-28 place-items-center rounded-full"
                                 :style="'background: conic-gradient(#0d9488 ' + score.overall + '%, #e2e8f0 0)'">
                                <div class="grid h-[5.25rem] w-[5.25rem] place-items-center rounded-full bg-paper">
                                    <div class="text-center">
                                        <p class="font-display text-2xl font-extrabold text-ink" x-text="score.overall"></p>
                                        <p class="text-[10px] font-semibold uppercase text-muted">score</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <p class="font-display text-lg font-bold text-ink" x-text="score.band"></p>
                                <p class="mt-1 text-xs text-muted" x-text="score.note"></p>
                                <p class="mt-2 text-xs font-semibold text-teal" x-show="score.reveal" x-text="score.reveal"></p>
                                <div class="mt-4 space-y-2">
                                    <template x-for="trait in score.traits" :key="trait.label">
                                        <div>
                                            <div class="mb-1 flex items-center justify-between text-xs font-bold text-ink">
                                                <span x-text="trait.label"></span>
                                                <span x-text="trait.score"></span>
                                            </div>
                                            <div class="h-1.5 overflow-hidden rounded-full bg-white">
                                                <div class="h-full rounded-full bg-teal" :style="'width:' + trait.score + '%'"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <ul class="mt-4 space-y-1 text-xs text-ink">
                                    <template x-for="tip in score.tips" :key="tip">
                                        <li class="flex gap-2"><span class="text-teal">•</span><span x-text="tip"></span></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </template>
                    <div x-show="scoreTab === 'forum'" x-cloak class="grid place-items-center py-2">Be the first to discuss this question.</div>
                    <div x-show="scoreTab === 'community'" x-cloak class="grid place-items-center py-2">Community averages appear after enough attempts.</div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
