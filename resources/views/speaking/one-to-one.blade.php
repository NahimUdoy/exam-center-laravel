@extends('layouts.nvs-page')

@section('title', 'One-to-one speaking — NextVerseSkill')

@section('content')
<div class="min-h-screen bg-aurora"
     x-data="nvsSpeaking({{ Js::from($script) }})"
     x-init="boot()">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:py-8">
        <div x-show="screen === 'lobby'" class="overflow-hidden rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper shadow-premium">
            <div class="h-1.5 bg-brand"></div>
            <div class="grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="p-6 sm:p-8">
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Free examiner · {{ $exam->short_name }}</p>
                    <h1 class="font-display mt-2 text-3xl font-bold text-ink sm:text-4xl">One-to-one speaking</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-muted">A live practice interview in your browser. The examiner speaks, you answer, then it asks the next question. No paid API. About {{ $script['minutes'] }} minutes. Each start shuffles a new set from {{ $script['bank']['interviews'] }} warm-up questions and {{ $script['bank']['cueCards'] }} cue cards.</p>
                    <ol class="mt-6 space-y-3 text-sm">
                        <li class="flex gap-3"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-soft text-xs font-extrabold text-brand">1</span><span><strong class="text-ink">Part 1</strong> — five warm-up questions, then one follow-up from your words.</span></li>
                        <li class="flex gap-3"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-soft text-xs font-extrabold text-brand">2</span><span><strong class="text-ink">Cue card</strong> — one minute to prepare, up to two minutes to speak.</span></li>
                        <li class="flex gap-3"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-soft text-xs font-extrabold text-brand">3</span><span><strong class="text-ink">Part 3</strong> — three discussion questions. Then a practice score and transcript.</span></li>
                    </ol>
                    <p class="mt-6 rounded-2xl bg-mist px-4 py-3 text-xs font-medium leading-5 text-muted">Best in Chrome or Edge with a headset. If speech recognition is blocked, type your answer and press Send. Login is required so we can save the session.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <button type="button" @click="start()" class="inline-flex items-center gap-2 rounded-2xl bg-brand px-5 py-3 text-sm font-bold text-white shadow-lg shadow-brand/20">
                            <x-nvs.icon name="Mic" class="h-4 w-4" />
                            Start interview
                        </button>
                        <a href="{{ route('dashboard', ['exam' => $exam->slug]) }}" class="inline-flex items-center gap-2 rounded-2xl border border-[rgba(49,36,24,0.1)] bg-paper px-5 py-3 text-sm font-bold text-ink">Back to practice</a>
                    </div>
                </div>
                <div class="border-t border-[rgba(49,36,24,0.06)] bg-mist/70 p-6 sm:p-8 lg:border-l lg:border-t-0">
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">You will hear</p>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-2xl bg-paper px-4 py-3 shadow-sm">
                            <p class="text-xs font-bold text-brand">Examiner</p>
                            <p class="mt-1 text-sm text-ink">Hello. I am your free {{ $exam->short_name }} speaking examiner today…</p>
                        </div>
                        <div class="rounded-2xl bg-brand px-4 py-3 text-white shadow-sm">
                            <p class="text-xs font-bold text-white/70">You</p>
                            <p class="mt-1 text-sm">I usually study alone because it is quieter, for example in the library.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="screen === 'room'" x-cloak class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="overflow-hidden rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper shadow-premium">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[rgba(49,36,24,0.06)] px-5 py-4">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-muted" x-text="phaseLabel()"></p>
                        <p class="font-display text-lg font-bold text-ink">{{ $exam->short_name }} examiner</p>
                    </div>
                    <p class="text-xs font-semibold text-muted"><span x-text="stepIndex + 1"></span> / <span x-text="steps.length"></span> · <span x-text="format(elapsed)"></span></p>
                </div>
                <div class="max-h-[58vh] space-y-3 overflow-y-auto px-5 py-5 scrollbar-thin" x-ref="log">
                    <template x-for="(turn, i) in turns" :key="i">
                        <div class="flex" :class="turn.role === 'student' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm leading-6" :class="turn.role === 'student' ? 'bg-brand text-white' : 'bg-mist text-ink'">
                                <p class="text-[10px] font-extrabold uppercase tracking-wide" :class="turn.role === 'student' ? 'text-white/70' : 'text-muted'" x-text="turn.role === 'student' ? 'You' : 'Examiner'"></p>
                                <p class="mt-1" x-text="turn.text"></p>
                            </div>
                        </div>
                    </template>
                    <p x-show="interim" class="text-sm italic text-muted" x-text="'Listening: ' + interim"></p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper p-5 shadow-premium">
                    <p class="font-display text-lg font-bold text-ink" x-text="headline()"></p>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-mist">
                        <div class="h-full rounded-full bg-brand transition-all" :style="'width:' + progress + '%'"></div>
                    </div>
                    <p class="mt-2 text-xs font-semibold text-muted" x-text="statusLine()"></p>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button type="button" @click="toggleMic()"
                                class="relative grid h-14 w-14 place-items-center rounded-full text-white shadow-lg"
                                :class="mode === 'listen' ? 'bg-rose' : 'bg-brand'"
                                :disabled="mode === 'speak' || mode === 'prep'"
                                aria-label="Microphone">
                            <span x-show="mode === 'listen'" class="absolute inset-0 rounded-full bg-rose/30 animate-pulse"></span>
                            <x-nvs.icon name="Mic" class="h-6 w-6" />
                        </button>
                        <button type="button" @click="finishTurn()" class="rounded-2xl bg-ink px-4 py-2.5 text-sm font-bold text-white" :disabled="mode === 'speak'">Done</button>
                        <button type="button" @click="skip()" class="rounded-2xl px-4 py-2.5 text-sm font-bold text-muted hover:text-ink">Skip</button>
                    </div>

                    <label class="mt-4 block text-xs font-extrabold uppercase tracking-wide text-muted">Type if the mic fails
                        <textarea x-model="typed" rows="3" class="mt-1 w-full rounded-2xl border border-[rgba(49,36,24,0.08)] bg-mist/50 p-3 text-sm outline-none ring-brand/20 focus:ring-2" placeholder="Type your answer, then Done."></textarea>
                    </label>
                    <p class="mt-2 text-xs text-muted" x-show="!canHear">This browser may not support live speech recognition. Type your answers — the examiner still speaks.</p>
                </div>

                <div x-show="current && current.bullets && current.bullets.length" class="rounded-[1.75rem] border border-brand/15 bg-brand-soft/60 p-5">
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-brand">Cue card</p>
                    <p class="mt-2 text-sm font-semibold leading-6 text-ink" x-text="current?.text"></p>
                    <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-ink">
                        <template x-for="bullet in (current?.bullets || [])" :key="bullet">
                            <li x-text="bullet"></li>
                        </template>
                    </ul>
                </div>

                <button type="button" @click="endEarly()" class="w-full rounded-2xl border border-[rgba(49,36,24,0.08)] px-4 py-2 text-xs font-bold text-muted hover:text-ink">End and see score</button>
            </div>
        </div>

        <div x-show="screen === 'recap'" x-cloak class="overflow-hidden rounded-[1.75rem] border border-[rgba(49,36,24,0.06)] bg-paper shadow-premium">
            <div class="h-1.5 bg-brand"></div>
            <div class="p-6 sm:p-8">
                <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Practice score</p>
                <h2 class="font-display mt-2 text-3xl font-bold text-ink">Session complete</h2>
                <p class="mt-2 text-sm text-muted" x-text="score?.note"></p>
                <div class="mt-6 flex flex-wrap items-end gap-6">
                    <p class="font-display text-5xl font-extrabold text-brand" x-text="(score?.overall || 0) + '%'"></p>
                    <p class="text-sm font-semibold text-ink" x-text="score?.band"></p>
                </div>
                <div class="mt-6 grid gap-3 sm:grid-cols-4">
                    <template x-for="trait in (score?.traits || [])" :key="trait.label">
                        <div class="rounded-2xl bg-mist px-4 py-3">
                            <p class="text-xs font-bold text-muted" x-text="trait.label"></p>
                            <p class="mt-1 text-xl font-extrabold text-ink" x-text="trait.score"></p>
                        </div>
                    </template>
                </div>
                <ul class="mt-6 space-y-2 text-sm text-ink">
                    <template x-for="tip in (score?.tips || [])" :key="tip">
                        <li x-text="tip"></li>
                    </template>
                </ul>
                <div class="mt-8 space-y-3">
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Transcript</p>
                    <template x-for="(turn, i) in turns" :key="'r'+i">
                        <p class="text-sm leading-6"><span class="font-bold" x-text="turn.role === 'student' ? 'You: ' : 'Examiner: '"></span><span x-text="turn.text"></span></p>
                    </template>
                </div>
                <div class="mt-8 flex flex-wrap gap-3">
                    <button type="button" @click="restart()" class="rounded-2xl bg-brand px-5 py-3 text-sm font-bold text-white">New session</button>
                    <a href="{{ route('dashboard', ['exam' => $exam->slug]) }}" class="rounded-2xl border border-[rgba(49,36,24,0.1)] px-5 py-3 text-sm font-bold text-ink">Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('head')
<script>
function nvsSpeaking(script) {
    return {
        script,
        steps: script.steps || [],
        followUps: script.followUps || [],
        screen: 'lobby',
        stepIndex: -1,
        turns: [],
        mode: 'idle',
        typed: '',
        interim: '',
        left: 0,
        elapsed: 0,
        listenStarted: 0,
        timer: null,
        clock: null,
        recognition: null,
        canHear: false,
        saving: false,
        score: null,
        current: null,
        get progress() {
            if (!this.current || !this.current.record && this.mode !== 'prep') return 0;
            const total = this.mode === 'prep' ? (this.current.prep || 1) : (this.current.record || 1);
            return Math.max(0, Math.min(100, ((total - this.left) / total) * 100));
        },
        boot() {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.canHear = Boolean(SR);
        },
        phaseLabel() {
            return { greet: 'Warm-up', part1: 'Part 1', follow: 'Follow-up', part2: 'Cue card', part3: 'Part 3' }[this.current?.phase] || 'Interview';
        },
        headline() {
            if (this.mode === 'speak') return 'Examiner is speaking…';
            if (this.mode === 'prep') return 'Prepare · ' + this.left + 's';
            if (this.mode === 'listen') return 'Your turn · ' + this.format(this.left);
            return 'Ready';
        },
        statusLine() {
            if (this.mode === 'prep') return 'Use the cue card. Speaking starts automatically.';
            if (this.mode === 'listen') return this.canHear ? 'Speak now. Tap Done when you finish.' : 'Type your answer, then tap Done.';
            return 'Listen to the examiner.';
        },
        format(total) {
            const n = Math.max(0, total);
            return String(Math.floor(n / 60)).padStart(2, '0') + ':' + String(n % 60).padStart(2, '0');
        },
        start() {
            if (window.nvsNeedAuth && window.nvsNeedAuth()) return;
            this.screen = 'room';
            this.turns = [];
            this.elapsed = 0;
            this.score = null;
            this.clock = setInterval(() => this.elapsed++, 1000);
            this.stepIndex = -1;
            this.nextStep();
        },
        nextStep() {
            this.stopListen();
            this.typed = '';
            this.interim = '';
            this.stepIndex++;
            if (this.stepIndex >= this.steps.length) {
                this.finishSession();
                return;
            }
            const step = { ...this.steps[this.stepIndex] };
            if (step.dynamic) {
                step.text = this.pickFollowUp();
            }
            this.current = step;
            this.ask(step);
        },
        pickFollowUp() {
            const last = [...this.turns].reverse().find((t) => t.role === 'student');
            const hay = (last?.text || '').toLowerCase();
            const hit = this.followUps.find((row) => (row.keys || []).some((key) => hay.includes(key)));
            return (hit || this.followUps[this.followUps.length - 1]).text;
        },
        ask(step) {
            this.turns.push({ role: 'examiner', text: step.text, seconds: 0, phase: step.phase });
            this.scrollLog();
            this.mode = 'speak';
            this.speak(step.text, () => {
                if (step.listenOnly) {
                    this.nextStep();
                    return;
                }
                if ((step.prep || 0) > 0) {
                    this.mode = 'prep';
                    this.left = step.prep;
                    this.tickDown(() => this.beginListen(step.record));
                    return;
                }
                this.beginListen(step.record);
            });
        },
        speak(text, done) {
            if (!window.speechSynthesis) {
                setTimeout(done, 600);
                return;
            }
            window.speechSynthesis.cancel();
            const u = new SpeechSynthesisUtterance(text);
            u.rate = 0.95;
            u.onend = done;
            u.onerror = done;
            window.speechSynthesis.speak(u);
        },
        beginListen(seconds) {
            this.mode = 'listen';
            this.left = seconds || 40;
            this.listenStarted = Date.now();
            this.startRecognition();
            this.tickDown(() => this.finishTurn());
        },
        tickDown(onZero) {
            this.clearTimer();
            this.timer = setInterval(() => {
                if (this.left <= 1) {
                    this.clearTimer();
                    this.left = 0;
                    onZero();
                    return;
                }
                this.left--;
            }, 1000);
        },
        clearTimer() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },
        startRecognition() {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SR) return;
            this.stopListen();
            const rec = new SR();
            rec.continuous = true;
            rec.interimResults = true;
            rec.lang = 'en-US';
            rec.onresult = (event) => {
                let finalText = '';
                let live = '';
                for (let i = 0; i < event.results.length; i++) {
                    const chunk = event.results[i][0].transcript;
                    if (event.results[i].isFinal) finalText += chunk + ' ';
                    else live += chunk;
                }
                if (finalText) this.typed = (this.typed + ' ' + finalText).trim();
                this.interim = live;
            };
            rec.onerror = () => { this.canHear = false; };
            try { rec.start(); this.recognition = rec; } catch (e) {}
        },
        stopListen() {
            if (this.recognition) {
                try { this.recognition.stop(); } catch (e) {}
                this.recognition = null;
            }
        },
        toggleMic() {
            if (this.mode === 'listen') this.finishTurn();
            else if (this.current && this.mode === 'idle') this.beginListen(this.current.record);
        },
        finishTurn() {
            if (this.mode === 'speak') return;
            if (this.mode === 'prep' && this.current) {
                this.clearTimer();
                this.beginListen(this.current.record);
                return;
            }
            this.clearTimer();
            this.stopListen();
            const text = (this.typed || this.interim || '').trim();
            const seconds = this.listenStarted ? Math.round((Date.now() - this.listenStarted) / 1000) : 0;
            if (text || this.mode === 'listen' || this.mode === 'prep') {
                this.turns.push({
                    role: 'student',
                    text: text || '(no answer)',
                    seconds,
                    phase: this.current?.phase || '',
                });
                this.scrollLog();
            }
            this.interim = '';
            this.typed = '';
            this.mode = 'idle';
            this.nextStep();
        },
        skip() {
            this.typed = this.typed || '';
            this.finishTurn();
        },
        endEarly() {
            this.clearTimer();
            this.stopListen();
            if (window.speechSynthesis) window.speechSynthesis.cancel();
            this.finishSession();
        },
        async finishSession() {
            this.clearTimer();
            if (this.clock) { clearInterval(this.clock); this.clock = null; }
            this.stopListen();
            if (window.speechSynthesis) window.speechSynthesis.cancel();
            this.saving = true;
            try {
                const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                const res = await fetch(this.script.saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        turns: this.turns,
                        seconds: this.elapsed,
                        status: 'completed',
                    }),
                });
                const data = await res.json();
                if (res.status === 401) {
                    window.dispatchEvent(new CustomEvent('nvs-auth-required'));
                    return;
                }
                this.score = data.score || null;
            } catch (e) {
                this.score = { overall: 0, band: 'Could not save', traits: [], tips: ['Check your connection and try again.'], note: '' };
            } finally {
                this.saving = false;
                this.screen = 'recap';
            }
        },
        restart() {
            this.screen = 'lobby';
            this.turns = [];
            this.current = null;
            this.score = null;
        },
        scrollLog() {
            this.$nextTick(() => {
                const box = this.$refs.log;
                if (box) box.scrollTop = box.scrollHeight;
            });
        },
    };
}
</script>
@endpush
