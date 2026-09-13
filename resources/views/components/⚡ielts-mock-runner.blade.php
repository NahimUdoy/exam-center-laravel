<?php

use App\Models\MockSection;
use App\Models\MockTest;
use App\Services\MockAnalyzer;
use Livewire\Component;

new class extends Component
{
    public string $mockSlug;

    public string $phase = 'intro';

    public int $secondsLeft = 0;

    public int $activeQ = 1;

    public int $part = 1;

    public bool $reviewMode = false;

    public bool $notepadOpen = false;

    public bool $helpOpen = false;

    public bool $submitOpen = false;

    public string $notes = '';

    /** @var array<int|string, int> */
    public array $answers = [];

    /** @var array<int|string, bool> */
    public array $flagged = [];

    /** @var array<string, mixed>|null */
    public ?array $analysis = null;

    public function mount(string $mockSlug): void
    {
        $this->mockSlug = $mockSlug;
        $mock = $this->mock();
        $this->secondsLeft = ($mock->duration_min ?? 60) * 60;
        $first = $mock->examQuestions->first();
        if ($first) {
            $this->activeQ = $first->number;
            $this->part = $first->part ?: 1;
        }
    }

    public function start(): void
    {
        if (! auth()->check()) {
            $this->js('window.dispatchEvent(new CustomEvent("nvs-auth-required"))');

            return;
        }

        $this->phase = 'exam';
    }

    public function tick(): void
    {
        if ($this->phase === 'exam' && $this->secondsLeft > 0) {
            $this->secondsLeft--;
            if ($this->secondsLeft === 0) {
                $this->confirmSubmit();
            }
        }
    }

    public function selectAnswer(int $number, int $index): void
    {
        $this->answers[$number] = $index;
        $this->activeQ = $number;
    }

    public function goToQuestion(int $number, int $part): void
    {
        $this->reviewMode = false;
        $this->activeQ = $number;
        $this->part = $part;
    }

    public function setPart(int $part): void
    {
        $this->reviewMode = false;
        $this->part = $part;
        $first = $this->mock()->examQuestions->firstWhere('part', $part);
        if ($first) {
            $this->activeQ = $first->number;
        }
    }

    public function toggleReview(): void
    {
        $this->reviewMode = true;
    }

    public function toggleFlag(int $number): void
    {
        $this->flagged[$number] = ! ($this->flagged[$number] ?? false);
    }

    public function toggleNotepad(): void
    {
        $this->notepadOpen = ! $this->notepadOpen;
    }

    public function openHelp(): void
    {
        $this->helpOpen = true;
    }

    public function closeHelp(): void
    {
        $this->helpOpen = false;
    }

    public function openSubmit(): void
    {
        $this->submitOpen = true;
    }

    public function closeSubmit(): void
    {
        $this->submitOpen = false;
    }

    public function confirmSubmit(): void
    {
        $mock = $this->mock();
        $this->analysis = MockAnalyzer::analyzeIeltsReading($mock, $this->answers);
        $this->phase = 'results';
        $this->submitOpen = false;
    }

    public function retake(): void
    {
        if (! auth()->check()) {
            $this->js('window.dispatchEvent(new CustomEvent("nvs-auth-required"))');

            return;
        }

        $this->phase = 'exam';
        $this->answers = [];
        $this->flagged = [];
        $this->analysis = null;
        $this->reviewMode = false;
        $this->notepadOpen = false;
        $this->helpOpen = false;
        $this->submitOpen = false;
        $this->notes = '';
        $mock = $this->mock();
        $this->secondsLeft = ($mock->duration_min ?? 60) * 60;
        $first = $mock->examQuestions->first();
        $this->activeQ = $first?->number ?? 1;
        $this->part = $first?->part ?: 1;
    }

    protected function mock(): MockTest
    {
        return MockTest::query()
            ->where('slug', $this->mockSlug)
            ->firstOrFail()
            ->loadExamBank();
    }

    public function render()
    {
        $mock = $this->mock();
        $questions = $mock->examQuestions;
        $visible = $this->reviewMode
            ? $questions
            : $questions->where('part', $this->part)->values();
        $section = MockSection::query()->where('slug', $mock->section)->first();
        $exam = $mock->exam_slug;

        return $this->view([
            'mock' => $mock,
            'questions' => $questions,
            'visible' => $visible,
            'section' => $section,
            'examSlug' => $exam,
            'answeredCount' => count($this->answers),
            'urgent' => $this->secondsLeft <= 300,
        ]);
    }
};
?>

<div>
@if($phase === 'results' && $analysis)
    @include('components.nvs.mock-result-report', [
        'analysis' => $analysis,
        'practiceHref' => route('dashboard', ['exam' => $mock->exam_slug]),
        'mocksHref' => route('mock-tests.index', ['exam' => $mock->exam_slug]),
    ])
@elseif($phase === 'intro')
    <div class="grid h-[100svh] place-items-center bg-mist px-4">
        <div class="w-full max-w-xl rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-paper p-6 shadow-[0_20px_50px_rgba(37,99,235,0.1)] sm:p-8">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-sky">Ready when you are</p>
            <h1 class="font-display mt-2 text-3xl font-bold">{{ $mock->title }}</h1>
            <p class="mt-3 text-sm text-muted">Look through the paper first if you like. Starting the timer needs an account so we can save your score.</p>
            <ul class="mt-5 space-y-2 text-sm text-ink">
                <li class="flex items-center gap-2"><x-nvs.icon name="Clock3" class="h-4 w-4 text-amber" /> ~{{ $mock->duration_min ?? 60 }} min · {{ $questions->count() }} questions</li>
                <li class="flex items-center gap-2"><x-nvs.icon name="BookOpen" class="h-4 w-4 text-sky" /> {{ $section->label ?? ucfirst($mock->section) }}</li>
            </ul>
            <div class="mt-6 flex flex-wrap gap-3">
                <button type="button" wire:click="start" class="rounded-full bg-ink px-5 py-2.5 text-sm font-bold text-white">Begin test</button>
                <a href="{{ route('mock-tests.index', ['exam' => $mock->exam_slug]) }}" class="rounded-full px-5 py-2.5 text-sm font-semibold text-muted hover:bg-mist">Back to list</a>
            </div>
        </div>
    </div>
@else
<div class="flex h-[100svh] flex-col bg-[#e8edf3] text-ink" @if($phase === 'exam') wire:poll.1s="tick" @endif>
    <header class="flex shrink-0 items-center justify-between gap-3 bg-ink px-3 py-2.5 text-white sm:px-5">
        <div class="flex min-w-0 items-center gap-2 sm:gap-3">
            <a href="{{ route('mock-tests.index', ['exam' => $mock->exam_slug]) }}" class="rounded-full p-1.5 hover:bg-white/10" aria-label="Exit">
                <x-nvs.icon name="ChevronLeft" class="h-4 w-4" />
            </a>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold">NextVerseSkill · NV-033128</p>
                <p class="truncate text-[11px] text-white/55">{{ strtoupper($mock->exam_slug) }} · {{ $mock->title }}</p>
            </div>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-bold tabular-nums sm:px-4 {{ $urgent ? 'bg-rose text-white animate-pulse' : 'bg-white/10' }}">
            <x-nvs.icon name="Clock3" class="h-4 w-4" />
            <span class="hidden sm:inline">{{ intdiv($secondsLeft, 60) }} Minutes Left</span>
            <span class="sm:hidden">{{ sprintf('%d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) }}</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="openHelp" class="rounded-full border border-white/20 px-3 py-1.5 text-xs font-semibold hover:bg-white/10">Help?</button>
            <button type="button" onclick="document.documentElement.requestFullscreen?.()" class="rounded-full border border-white/20 p-1.5 hover:bg-white/10" aria-label="Fullscreen">
                <x-nvs.icon name="Maximize2" class="h-4 w-4" />
            </button>
        </div>
    </header>

    <div class="grid min-h-0 flex-1 grid-cols-1 lg:grid-cols-2">
        <section class="min-h-0 overflow-y-auto border-b border-black/10 bg-white lg:border-b-0 lg:border-r">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-black/5 bg-white/95 px-4 py-3 backdrop-blur sm:px-6">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em]" style="color: {{ $section->color ?? '#0ea5e9' }}">{{ $section->label ?? ucfirst($mock->section) }} passage</p>
                    <h1 class="font-display text-lg font-bold sm:text-xl">{{ $mock->passages->first()?->label === 'A' || $mock->passages->isNotEmpty() ? ($mock->title === 'Land of the Rising Sum' ? 'LAND OF THE RISING SUM' : strtoupper($mock->title)) : 'LAND OF THE RISING SUM' }}</h1>
                </div>
                <span class="rounded-full bg-mist px-2.5 py-1 text-[11px] font-semibold text-muted">{{ $answeredCount }}/{{ $questions->count() }} answered</span>
            </div>
            <article class="space-y-5 px-4 py-5 sm:px-6 sm:py-6">
                @forelse($mock->passages as $passage)
                    <div>
                        <p class="mb-2 font-display text-base font-bold text-ink">{{ $passage->label }}</p>
                        <p class="text-[15px] leading-7 text-ink/90">{{ $passage->text }}</p>
                    </div>
                @empty
                    <p class="text-[15px] leading-7 text-ink/90">Read each question carefully and select the best answer.</p>
                @endforelse
            </article>
        </section>

        <section class="min-h-0 overflow-y-auto bg-[#f3f5f8]">
            <div class="sticky top-0 z-10 border-b border-black/5 bg-[#f3f5f8]/95 px-4 py-3 backdrop-blur sm:px-6">
                <p class="text-sm font-semibold text-ink">
                    Questions
                    @if($reviewMode)
                        1–{{ $questions->count() }}
                    @elseif($visible->isNotEmpty())
                        {{ $visible->first()->number }}–{{ $visible->last()->number }}
                    @endif
                </p>
            </div>
            <div class="space-y-5 px-4 py-5 sm:px-6">
                @foreach($visible as $q)
                    <div class="rounded-2xl border bg-white p-4 shadow-sm transition {{ $activeQ === $q->number ? 'border-teal ring-2 ring-teal/20' : 'border-black/5' }}">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <p class="text-sm font-semibold leading-relaxed text-ink">
                                <span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-md bg-ink text-[11px] font-bold text-white">{{ $q->number }}</span>
                                {{ $q->prompt }}
                            </p>
                            <button type="button" wire:click="toggleFlag({{ $q->number }})" class="shrink-0 rounded-full p-1.5 {{ ($flagged[$q->number] ?? false) ? 'bg-amber/15 text-amber' : 'text-muted hover:bg-mist' }}">
                                <x-nvs.icon name="Flag" class="h-4 w-4 {{ ($flagged[$q->number] ?? false) ? 'fill-current' : '' }}" />
                            </button>
                        </div>
                        <ul class="space-y-2">
                            @foreach($q->options as $idx => $opt)
                                @php $selected = ($answers[$q->number] ?? null) === $idx; @endphp
                                <li>
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border px-3 py-2.5 text-sm transition {{ $selected ? 'border-teal bg-teal/5' : 'border-transparent bg-mist/70 hover:border-black/10' }}">
                                        <input type="radio" name="q-{{ $q->number }}" class="mt-0.5 accent-teal" @checked($selected) wire:click="selectAnswer({{ $q->number }}, {{ $idx }})">
                                        <span>
                                            <span class="mr-1.5 font-bold text-muted">{{ chr(65 + $idx) }}.</span>
                                            {{ $opt }}
                                        </span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <footer class="shrink-0 border-t border-black/10 bg-white px-2 py-2 sm:px-4">
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button" wire:click="toggleReview" class="rounded-md px-2.5 py-1.5 text-xs font-bold {{ $reviewMode ? 'bg-ink text-white' : 'bg-mist text-muted' }}">Review</button>
                @foreach([1, 2, 3] as $p)
                    <button type="button" wire:click="setPart({{ $p }})" class="rounded-md px-2.5 py-1.5 text-xs font-bold transition {{ ! $reviewMode && $part === $p ? 'bg-sky text-white' : 'bg-mist text-muted hover:text-ink' }}">Part {{ $p }}</button>
                @endforeach
            </div>
            <div class="mx-auto flex max-w-full flex-1 items-center justify-center gap-1 overflow-x-auto py-1 scrollbar-thin">
                @foreach($questions as $q)
                    @php
                        $answered = array_key_exists($q->number, $answers);
                        $isFlag = $flagged[$q->number] ?? false;
                        $isActive = $activeQ === $q->number;
                    @endphp
                    <button type="button" wire:click="goToQuestion({{ $q->number }}, {{ $q->part }})"
                        class="relative grid h-8 min-w-8 place-items-center rounded-md border text-xs font-bold transition {{ $isActive ? 'border-teal bg-teal text-white' : ($answered ? 'border-teal/40 bg-teal/10 text-teal' : 'border-black/10 bg-white text-ink') }}">
                        {{ $q->number }}
                        @if($isFlag)
                            <span class="absolute -right-0.5 -top-0.5 h-2 w-2 rounded-full bg-amber"></span>
                        @endif
                    </button>
                @endforeach
            </div>
            <div class="ml-auto flex items-center gap-2">
                <button type="button" wire:click="toggleNotepad" class="rounded-md border border-black/10 p-2 {{ $notepadOpen ? 'bg-teal/10 text-teal' : 'text-muted' }}">
                    <x-nvs.icon name="NotebookPen" class="h-4 w-4" />
                </button>
                <button type="button" wire:click="openSubmit" class="inline-flex items-center gap-1.5 rounded-md bg-emerald-500 px-3 py-2 text-xs font-bold text-white sm:px-4 sm:text-sm">
                    <x-nvs.icon name="CheckCircle2" class="h-4 w-4" />
                    Submit Test
                </button>
            </div>
        </div>
    </footer>

    @if($notepadOpen)
        <div class="fixed bottom-20 right-4 z-40 w-[min(360px,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-[rgba(15,40,80,0.08)] bg-paper shadow-2xl">
            <div class="flex items-center justify-between border-b border-[rgba(15,40,80,0.08)] px-4 py-3">
                <p class="inline-flex items-center gap-2 text-sm font-bold">
                    <x-nvs.icon name="StickyNote" class="h-4 w-4 text-amber" />
                    Notepad
                </p>
                <button type="button" wire:click="toggleNotepad"><x-nvs.icon name="X" class="h-4 w-4 text-muted" /></button>
            </div>
            <textarea wire:model.live="notes" class="h-40 w-full resize-none bg-mist/40 p-4 text-sm outline-none"></textarea>
        </div>
    @endif

    @if($helpOpen)
        <div class="fixed inset-0 z-50 grid place-items-center bg-ink/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-paper p-5">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="font-display inline-flex items-center gap-2 text-lg font-bold">
                        <x-nvs.icon name="HelpCircle" class="h-4 w-4 text-teal" /> Help
                    </h2>
                    <button type="button" wire:click="closeHelp"><x-nvs.icon name="X" class="h-4 w-4" /></button>
                </div>
                <p class="text-sm text-muted">Use parts to move through the paper, flag questions to revisit, take notes, then submit when you are ready. Unanswered items are marked skipped on the result report.</p>
            </div>
        </div>
    @endif

    @if($submitOpen)
        <div class="fixed inset-0 z-50 grid place-items-center bg-ink/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-paper p-5">
                <h2 class="font-display text-lg font-bold">Submit mock?</h2>
                <p class="mt-2 text-sm text-muted">Answered {{ $answeredCount }}/{{ $questions->count() }}.</p>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="closeSubmit" class="rounded-full px-4 py-2 text-sm font-semibold text-muted">Keep working</button>
                    <button type="button" wire:click="confirmSubmit" class="rounded-full bg-emerald-500 px-4 py-2 text-sm font-bold text-white">Confirm submit</button>
                </div>
            </div>
        </div>
    @endif
</div>
@endif
</div>
