<?php

use App\Models\MockTest;
use App\Models\PteQuestion;
use App\Models\PteTaskType;
use App\Services\MockAnalyzer;
use App\Support\PteBank;
use Livewire\Component;

new class extends Component
{
    public string $mockSlug;

    public string $phase = 'intro';

    public int $index = 0;

    public int $sectionSeconds = 0;

    public ?int $itemSeconds = null;

    public string $speakPhase = 'idle';

    public int $prepLeft = 0;

    public int $recLeft = 0;

    public bool $audioDone = false;

    /** @var array<string, mixed> */
    public array $answers = [];

    /** @var array<string, mixed>|null */
    public ?array $analysis = null;

    public function mount(string $mockSlug): void
    {
        $this->mockSlug = $mockSlug;
        $mock = MockTest::query()->where('slug', $mockSlug)->firstOrFail();
        $this->sectionSeconds = ($mock->duration_min ?: 90) * 60;
    }

    public function start(): void
    {
        if (! auth()->check()) {
            $this->js('window.dispatchEvent(new CustomEvent("nvs-auth-required"))');

            return;
        }

        $this->index = 0;
        $this->phase = 'section';
        $mock = MockTest::query()->where('slug', $this->mockSlug)->firstOrFail();
        $this->sectionSeconds = ($mock->duration_min ?: 90) * 60;
    }

    public function enterItem(): void
    {
        $this->phase = 'item';
        $this->bootItem();
    }

    public function tick(): void
    {
        if (! in_array($this->phase, ['item', 'section'], true)) {
            return;
        }
        if ($this->sectionSeconds > 0) {
            $this->sectionSeconds--;
        }

        if ($this->phase !== 'item') {
            return;
        }

        if ($this->speakPhase === 'prep') {
            if ($this->prepLeft <= 1) {
                $this->speakPhase = 'record';
            } else {
                $this->prepLeft--;
            }
        } elseif ($this->speakPhase === 'record') {
            if ($this->recLeft <= 1) {
                $this->speakPhase = 'done';
                $current = $this->current();
                if ($current && empty($this->answers[$current->external_id])) {
                    $this->answers[$current->external_id] = 'spoken';
                }
            } else {
                $this->recLeft--;
            }
        }

        if ($this->itemSeconds !== null) {
            if ($this->itemSeconds <= 1) {
                $this->itemSeconds = 0;
                $this->next();
            } else {
                $this->itemSeconds--;
            }
        }
    }

    public function setAnswer(string $key, mixed $value): void
    {
        $this->answers[$key] = $value;
    }

    public function setText(string $key, $value): void
    {
        $this->answers[$key] = (string) $value;
    }

    public function toggleMulti(string $key, int $index): void
    {
        $existing = collect((array) ($this->answers[$key] ?? []))->map(fn ($v) => (int) $v)->values();
        if ($existing->contains($index)) {
            $this->answers[$key] = $existing->reject(fn ($v) => $v === $index)->values()->all();
        } else {
            $this->answers[$key] = $existing->push($index)->values()->all();
        }
    }

    public function setBlank(string $key, string $blankId, string $choice): void
    {
        $existing = (array) ($this->answers[$key] ?? []);
        $existing[$blankId] = $choice;
        $this->answers[$key] = $existing;
    }

    public function moveOrder(string $key, int $from, int $dir): void
    {
        $order = array_values((array) ($this->answers[$key] ?? []));
        $to = $from + $dir;
        if (! isset($order[$from], $order[$to])) {
            return;
        }
        [$order[$from], $order[$to]] = [$order[$to], $order[$from]];
        $this->answers[$key] = array_values($order);
    }

    public function toggleHiWord(string $key, string $word): void
    {
        $existing = collect((array) ($this->answers[$key] ?? []));
        if ($existing->contains($word)) {
            $this->answers[$key] = $existing->reject(fn ($v) => $v === $word)->values()->all();
        } else {
            $this->answers[$key] = $existing->push($word)->values()->all();
        }
    }

    public function next(): void
    {
        $bank = PteBank::forMock($this->mockSlug)->values();
        $current = $bank->get($this->index);
        if ($this->index + 1 >= $bank->count()) {
            $this->finish();

            return;
        }
        $this->index++;
        $next = $bank->get($this->index);
        if ($current && $next && $next->section !== $current->section) {
            $this->phase = 'section';
        } else {
            $this->phase = 'item';
            $this->bootItem();
        }
    }

    public function finish(): void
    {
        $mock = MockTest::query()->where('slug', $this->mockSlug)->firstOrFail();
        $bank = PteBank::forMock($this->mockSlug);
        $this->analysis = MockAnalyzer::analyzePte($mock, $bank, $this->answers);
        $this->phase = 'results';
    }

    public function retake(): void
    {
        $this->phase = 'intro';
        $this->index = 0;
        $this->answers = [];
        $this->analysis = null;
        $this->speakPhase = 'idle';
        $mock = MockTest::query()->where('slug', $this->mockSlug)->firstOrFail();
        $this->sectionSeconds = ($mock->duration_min ?: 90) * 60;
    }

    protected function bootItem(): void
    {
        $q = $this->current();
        if (! $q) {
            return;
        }
        $payload = $q->payload ?? [];
        $this->speakPhase = 'idle';
        $this->audioDone = false;
        $this->itemSeconds = $payload['time_limit_seconds'] ?? null;
        $this->prepLeft = (int) ($payload['prep_seconds'] ?? 3);
        $this->recLeft = (int) ($payload['record_seconds'] ?? 15);

        $speaking = in_array($q->type, ['read-aloud', 'repeat-sentence', 'describe-image', 'retell-lecture', 'answer-short'], true);
        if ($speaking) {
            $this->speakPhase = 'prep';
        }
        if ($q->type === 'reorder' && empty($this->answers[$q->external_id])) {
            $this->answers[$q->external_id] = collect($payload['paragraphs'] ?? [])->pluck('id')->all();
        }
    }

    protected function current(): ?PteQuestion
    {
        return PteBank::forMock($this->mockSlug)->values()->get($this->index);
    }

    public function render()
    {
        $mock = MockTest::query()->where('slug', $this->mockSlug)->firstOrFail();
        $bank = PteBank::forMock($this->mockSlug)->values();
        $current = $bank->get($this->index);
        $progress = $bank->count() ? (($this->index + ($this->phase === 'item' ? 1 : 0)) / $bank->count()) * 100 : 0;
        $taskType = $current
            ? PteTaskType::query()->where('slug', $current->type)->first()
            : null;
        $sectionTitle = [
            'speaking-writing' => 'Speaking & Writing',
            'reading' => 'Reading',
            'listening' => 'Listening',
        ];

        return $this->view([
            'mock' => $mock,
            'bank' => $bank,
            'current' => $current,
            'progress' => $progress,
            'taskType' => $taskType,
            'sectionTitle' => $sectionTitle,
        ]);
    }
};
?>

<div>
@if($phase === 'results' && $analysis)
    @include('components.nvs.mock-result-report', [
        'analysis' => $analysis,
        'practiceHref' => route('dashboard', ['exam' => 'pte']),
        'mocksHref' => route('mock-tests.index', ['exam' => 'pte']),
    ])
@else
@php
    $payload = $current?->payload ?? [];
    $speakingTypes = ['read-aloud', 'repeat-sentence', 'describe-image', 'retell-lecture', 'answer-short'];
    $choiceTypes = ['mc-multi', 'mc-single', 'l-mc-multi', 'l-mc-single', 'highlight-summary', 'missing-word'];
    $writeTypes = ['swt', 'essay', 'sst', 'wfd'];
    $audioText = $payload['audio_text'] ?? $payload['audioText'] ?? null;
    $needsAudio = $audioText || ($current && $current->section === 'listening');
    $prepTotal = (int) ($payload['prep_seconds'] ?? 3);
    $recTotal = (int) ($payload['record_seconds'] ?? 15);
    $speakPct = $speakPhase === 'prep' && $prepTotal
        ? (($prepTotal - $prepLeft) / $prepTotal) * 100
        : ($speakPhase === 'record' && $recTotal ? (($recTotal - $recLeft) / $recTotal) * 100 : ($speakPhase === 'done' ? 100 : 0));
    $answer = $current ? ($answers[$current->external_id] ?? null) : null;
@endphp
<div class="flex h-[100svh] flex-col bg-[#f4f7fb] text-ink" @if(in_array($phase, ['item','section'], true)) wire:poll.1s="tick" @endif>
    <header class="flex shrink-0 items-center justify-between gap-3 border-b border-[rgba(15,40,80,0.08)] bg-ink px-4 py-3 text-white">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-teal-glow">PTE Academic Mock</p>
            <p class="font-display text-sm font-bold sm:text-base">{{ $mock->title }}</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden text-right text-xs sm:block">
                <p class="text-white/60">Progress</p>
                <p class="font-bold">{{ min($index + ($phase === 'item' ? 1 : 0), $bank->count()) }}/{{ $bank->count() }}</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-bold tabular-nums {{ $sectionSeconds < 300 ? 'bg-rose' : 'bg-white/10' }}">
                <x-nvs.icon name="Clock3" class="h-4 w-4" />
                {{ sprintf('%02d:%02d', intdiv($sectionSeconds, 60), $sectionSeconds % 60) }}
            </div>
        </div>
    </header>
    <div class="h-1 w-full bg-white/20">
        <div class="progress-fill h-full bg-gradient-to-r from-teal-bright to-coral" style="width: {{ $progress }}%"></div>
    </div>

    @if($phase === 'intro')
        <div class="grid flex-1 place-items-center px-4">
            <div class="w-full max-w-xl rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-paper p-6 shadow-[0_20px_50px_rgba(37,99,235,0.1)] sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-teal">Device check</p>
                <h1 class="font-display mt-2 text-3xl font-bold">Start PTE Mock</h1>
                <p class="mt-3 text-sm text-muted">This simulation mirrors the Alfa-style full mock flow: timed items, speaking prep/record bars, writing word counts, reading interactions, and listening audio prompts across all major PTE task types.</p>
                <ul class="mt-5 space-y-2 text-sm text-ink">
                    <li class="flex items-center gap-2"><x-nvs.icon name="Mic" class="h-4 w-4 text-coral" /> Headset + mic recommended</li>
                    <li class="flex items-center gap-2"><x-nvs.icon name="Headphones" class="h-4 w-4 text-teal" /> Audio plays once per item (browser TTS)</li>
                    <li class="flex items-center gap-2"><x-nvs.icon name="Clock3" class="h-4 w-4 text-amber" /> ~{{ $mock->duration_min }} min · {{ $bank->count() }} questions</li>
                </ul>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="button" wire:click="start" class="rounded-full bg-gradient-to-r from-teal to-teal-bright px-5 py-2.5 text-sm font-bold text-white">Begin test</button>
                    <a href="{{ route('mock-tests.index', ['exam' => 'pte']) }}" class="rounded-full px-5 py-2.5 text-sm font-semibold text-muted hover:bg-mist">Exit</a>
                </div>
                <p class="mt-4 text-xs text-muted">Original NextVerseSkill question bank — not scraped from third-party sites.</p>
            </div>
        </div>
    @elseif($phase === 'section' && $current)
        <div class="grid flex-1 place-items-center px-4">
            <div class="w-full max-w-lg rounded-[1.5rem] bg-ink p-8 text-center text-white shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-glow">Next section</p>
                <h2 class="font-display mt-3 text-3xl font-bold">{{ $sectionTitle[$current->section] ?? $current->section }}</h2>
                <p class="mt-3 text-sm text-white/65">Stay focused. You generally cannot return to previous PTE items once you move on.</p>
                <button type="button" wire:click="enterItem" class="mt-6 rounded-full bg-white px-6 py-2.5 text-sm font-bold text-ink">Continue</button>
            </div>
        </div>
    @elseif($phase === 'item' && $current)
        <main class="mx-auto flex min-h-0 w-full max-w-5xl flex-1 flex-col px-4 py-4 sm:px-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-white" style="background: {{ $taskType->color ?? '#0d9488' }}">
                        {{ $taskType->short ?? strtoupper($current->type) }} · {{ $taskType->label ?? $current->title }}
                    </span>
                    <p class="mt-2 text-sm text-muted">{{ $current->instructions }}</p>
                </div>
                @if($itemSeconds !== null)
                    <div class="rounded-full bg-paper px-3 py-1.5 text-sm font-bold shadow-sm">Item time {{ sprintf('%02d:%02d', intdiv($itemSeconds, 60), $itemSeconds % 60) }}</div>
                @endif
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto rounded-[1.25rem] border border-[rgba(15,40,80,0.08)] bg-[#eef3fb] p-4 sm:p-6">
                <div class="space-y-5">
                    @if($needsAudio)
                        <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-mist px-4 py-3" x-data="{ playing: false, done: @js($audioDone) }"
                             x-init="if (@js((bool) $audioText) && !done) { playing = true; const u = new SpeechSynthesisUtterance(@js($audioText ?? '')); u.onend = () => { playing = false; done = true; }; speechSynthesis.speak(u); }">
                            <x-nvs.icon name="Volume2" class="h-5 w-5 text-teal" x-bind:class="playing ? 'text-coral animate-pulse' : 'text-teal'" />
                            <p class="text-sm font-semibold" x-text="playing ? 'Playing audio…' : (done ? 'Audio finished' : 'Audio ready')"></p>
                            <button type="button" x-show="done" x-cloak class="ml-auto rounded-full border border-teal/30 px-3 py-1 text-xs font-bold text-teal"
                                @click="playing = true; const u = new SpeechSynthesisUtterance(@js($audioText ?? '')); u.onend = () => { playing = false; done = true; }; speechSynthesis.speak(u);">Replay (practice)</button>
                        </div>
                    @endif

                    @if(!empty($payload['text']))
                        <div class="rounded-2xl border border-teal/20 bg-mist/50 p-4 text-[15px] leading-7">{{ $payload['text'] }}</div>
                    @endif

                    @if(!empty($payload['prompt']) && ! in_array($current->type, $speakingTypes, true) && $current->type !== 'essay')
                        <p class="font-semibold">{{ $payload['prompt'] }}</p>
                    @endif

                    @if($current->type === 'describe-image')
                        <div class="grid place-items-center rounded-2xl border border-dashed border-coral/40 bg-gradient-to-br from-[#fff4ef] to-white p-6 text-center">
                            @if(!empty($current->image_url) || !empty($payload['image_url']))
                                <img src="{{ $current->image_url ?: $payload['image_url'] }}" alt="{{ $payload['image_title'] ?? $current->title }}" class="mb-4 max-h-72 w-full rounded-xl object-contain">
                            @else
                                <div class="mb-4 flex h-40 w-full max-w-md items-end justify-around gap-2 rounded-xl bg-ink/5 p-4">
                                    @foreach([40, 70, 55, 35] as $h)
                                        <div class="w-10 rounded-t-md bg-gradient-to-t from-coral to-amber" style="height: {{ $h }}%"></div>
                                    @endforeach
                                </div>
                            @endif
                            <p class="font-display text-lg font-bold">{{ $payload['image_title'] ?? $current->title }}</p>
                            <p class="mt-2 text-sm text-muted">{{ $payload['image_hint'] ?? '' }}</p>
                        </div>
                    @endif

                    @if(in_array($current->type, $speakingTypes, true))
                        <div class="rounded-2xl border border-coral/20 bg-gradient-to-br from-[#fff4ef] to-[#ffe8df] p-4">
                            <div class="flex items-center gap-4">
                                <div class="relative grid h-14 w-14 place-items-center rounded-full text-white {{ $speakPhase === 'record' ? 'bg-rose' : 'bg-coral' }}">
                                    @if($speakPhase === 'record')
                                        <span class="absolute inset-0 rounded-full bg-rose/40 animate-pulse-ring"></span>
                                    @endif
                                    <x-nvs.icon name="Mic" class="h-6 w-6" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-display text-lg font-bold">
                                        @if($speakPhase === 'idle') Waiting…
                                        @elseif($speakPhase === 'prep') Recording in {{ $prepLeft }}s
                                        @elseif($speakPhase === 'record') Recording {{ sprintf('%02d:%02d', intdiv($recTotal - $recLeft, 60), ($recTotal - $recLeft) % 60) }} / {{ sprintf('%02d:%02d', intdiv($recTotal, 60), $recTotal % 60) }}
                                        @else Response captured
                                        @endif
                                    </p>
                                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/70">
                                        <div class="progress-fill h-full rounded-full bg-gradient-to-r from-coral to-amber" style="width: {{ $speakPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(in_array($current->type, $writeTypes, true))
                        @php
                            $textVal = is_string($answer) ? $answer : '';
                            $words = str($textVal)->trim() === '' ? 0 : count(preg_split('/\s+/', trim($textVal)));
                            $limit = $payload['word_limit'] ?? $payload['wordLimit'] ?? null;
                        @endphp
                        <div>
                            @if($current->type === 'essay' && !empty($payload['prompt']))
                                <p class="mb-3 font-semibold">{{ $payload['prompt'] }}</p>
                            @endif
                            <textarea rows="{{ $current->type === 'essay' ? 12 : 6 }}"
                                class="w-full rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist/40 p-4 text-sm leading-6 outline-none ring-teal/30 focus:ring-2"
                                placeholder="Type your response here…"
                                wire:change="setText('{{ $current->external_id }}', $event.target.value)">{{ $textVal }}</textarea>
                            <div class="mt-2 flex justify-between text-xs font-semibold text-muted">
                                <span>Word count: <span class="{{ $limit && ($words < ($limit['min'] ?? 0) || $words > ($limit['max'] ?? 9999)) ? 'text-rose' : '' }}">{{ $words }}</span>
                                    @if($limit) (target {{ $limit['min'] }}–{{ $limit['max'] }}) @endif
                                </span>
                            </div>
                        </div>
                    @endif

                    @if(in_array($current->type, $choiceTypes, true) && !empty($payload['options']))
                        @php
                            $multi = !empty($payload['multi']) || in_array($current->type, ['mc-multi', 'l-mc-multi'], true);
                            $selected = is_array($answer) ? $answer : (is_numeric($answer) ? [(int) $answer] : []);
                        @endphp
                        <ul class="space-y-2">
                            @foreach($payload['options'] as $idx => $opt)
                                @php $on = in_array($idx, array_map('intval', $selected), true); @endphp
                                <li>
                                    <button type="button"
                                        wire:click="{{ $multi ? "toggleMulti('{$current->external_id}', {$idx})" : "setAnswer('{$current->external_id}', {$idx})" }}"
                                        class="flex w-full items-start gap-3 rounded-xl border px-3 py-3 text-left text-sm transition {{ $on ? 'border-teal bg-teal/5' : 'border-transparent bg-mist hover:border-black/10' }}">
                                        <span class="mt-0.5 grid h-5 w-5 place-items-center border text-[10px] font-bold {{ $multi ? 'rounded-md' : 'rounded-full' }} {{ $on ? 'border-teal bg-teal text-white' : 'border-muted text-muted' }}">
                                            {{ $multi ? ($on ? '✓' : '') : chr(65 + $idx) }}
                                        </span>
                                        <span>@unless($multi)<span class="mr-1 font-bold text-muted">{{ chr(65 + $idx) }}.</span>@endunless{{ $opt }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if(in_array($current->type, ['rw-fib', 'r-fib', 'l-fib'], true))
                        @include('components.nvs.fib-passage', [
                            'kind' => $current->type,
                            'tokens' => $payload['passage_tokens'] ?? [],
                            'blanks' => $payload['blanks'] ?? [],
                            'wordBank' => $payload['word_bank'] ?? [],
                            'map' => is_array($answer) ? $answer : [],
                            'qid' => $current->external_id,
                            'bind' => 'livewire',
                        ])
                    @endif

                    @if($current->type === 'reorder')
                        @php
                            $paras = collect($payload['paragraphs'] ?? []);
                            $order = is_array($answer) && $answer ? $answer : $paras->pluck('id')->all();
                            $byId = $paras->keyBy('id');
                        @endphp
                        <ul class="space-y-2">
                            @foreach($order as $i => $pid)
                                <li class="flex items-start gap-3 rounded-xl border border-[rgba(15,40,80,0.08)] bg-mist/60 p-3">
                                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-ink text-xs font-bold text-white">{{ $i + 1 }}</span>
                                    <p class="flex-1 text-sm leading-6">{{ data_get($byId[$pid], 'text') }}</p>
                                    <div class="flex flex-col gap-1">
                                        <button type="button" class="rounded bg-paper px-2 text-xs font-bold" wire:click="moveOrder('{{ $current->external_id }}', {{ $i }}, -1)">↑</button>
                                        <button type="button" class="rounded bg-paper px-2 text-xs font-bold" wire:click="moveOrder('{{ $current->external_id }}', {{ $i }}, 1)">↓</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if($current->type === 'highlight-incorrect')
                        @php $selected = collect((array) $answer); @endphp
                        <p class="text-[15px] leading-8">
                            @foreach($payload['transcript_with_errors'] ?? [] as $idx => $item)
                                @php $word = is_array($item) ? ($item['word'] ?? '') : (string) $item; @endphp
                                <button type="button" wire:click="toggleHiWord('{{ $current->external_id }}', @js($word))"
                                    class="mr-1 rounded px-0.5 {{ $selected->contains($word) ? 'bg-rose/20 font-bold text-rose underline' : '' }}">{{ $word }}</button>
                            @endforeach
                        </p>
                    @endif
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3">
                <p class="text-xs text-muted">Question {{ $index + 1 }} of {{ $bank->count() }} · {{ $sectionTitle[$current->section] ?? $current->section }}</p>
                <button type="button" wire:click="next" class="inline-flex items-center gap-2 rounded-full bg-teal px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-teal/25 hover:brightness-110">
                    {{ $index + 1 >= $bank->count() ? 'Finish' : 'Next' }}
                    <x-nvs.icon name="ChevronRight" class="h-4 w-4" />
                </button>
            </div>
        </main>
    @endif
</div>
@endif
</div>
