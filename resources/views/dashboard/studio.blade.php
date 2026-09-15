@php
    $skillMap = $skills->keyBy('slug');
    $flat = $tasks->flatten();
    $nextTask = $flat->first(fn ($t) => $t->skill !== 'mock' && $t->done < $t->total) ?? $flat->first(fn ($t) => $t->skill !== 'mock');
    $practiceHref = $nextTask
        ? route('practice.task', ['skill' => $nextTask->skill, 'task' => $nextTask->slug, 'exam' => $exam->slug])
        : route('mock-tests.index', ['exam' => $exam->slug]);
    $today = collect($summary)->keyBy('key');
    $doneSum = $flat->sum('done');
    $totalSum = max(1, $flat->sum('total'));
    $completion = (int) round(($doneSum / $totalSum) * 100);
@endphp
<div class="min-h-screen bg-aurora" x-data="{ tab: 'practice', q: '' }">
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:py-8">
        <div class="mb-6 overflow-hidden rounded-[1.75rem] border border-[rgba(49,36,24,0.04)] bg-paper shadow-premium">
            <div class="h-1.5" style="background: {{ $exam->accent }}"></div>
            <div class="flex flex-col gap-5 p-5 sm:p-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-muted">{{ $exam->short_name }} studio</p>
                    <h1 class="font-display mt-1 text-3xl font-bold text-ink sm:text-4xl">Pick a card. Start a set.</h1>
                    <p class="mt-2 max-w-xl text-sm text-muted">{{ $exam->description }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 rounded-2xl bg-mist px-4 py-2">
                        <x-nvs.icon name="Flame" class="h-4 w-4 text-brand" />
                        <span class="text-sm font-semibold text-ink">4-day streak</span>
                    </div>
                    <a href="{{ $practiceHref }}" class="inline-flex items-center gap-2 rounded-2xl bg-brand px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand/20">
                        Continue next card
                        <x-nvs.icon name="ArrowRight" class="h-4 w-4" />
                    </a>
                </div>
            </div>
        </div>

        @if($exam->slug === 'english-basic')
            <a href="{{ route('english-basics.index', ['exam' => 'english-basic']) }}" class="deck-card mb-6 p-0">
                <div class="deck-card-band" style="--deck: #2d6a4f"></div>
                <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-teal">Start here</p>
                        <p class="font-display mt-1 text-xl font-bold text-ink">English Basics A–Z</p>
                        <p class="mt-1 text-sm text-muted">Idea → example sentence → 8 questions. Follow the path if you are new.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-2xl bg-teal px-4 py-2 text-sm font-bold text-white">Open grammar hub <x-nvs.icon name="ArrowRight" class="h-4 w-4" /></span>
                </div>
            </a>
        @endif

        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @php
                $basicsHref = route('english-basics.index', ['exam' => 'english-basic']);
                $shortcuts = $exam->slug === 'english-basic'
                    ? [
                        ['Grammar A–Z', 'Noun, verb, tense, article — explain + practice', '#2d6a4f', '#d8efe4', $basicsHref, 'BookOpen', true],
                        ['Study desk', 'Daily drill, phrases, and a 14-day path', '#c45c26', '#f6e4d6', route('study.show', ['exam' => $exam->slug]), 'BookOpenCheck', false],
                        ['Mock Tests', 'Timed papers, real exam flow', '#4f7c8a', '#dce8eb', route('mock-tests.index', ['exam' => $exam->slug]), 'Timer', false],
                        ['Certificates', 'Pathway and module credentials', '#c4a35a', '#f4ecd4', route('certificates.index', ['exam' => $exam->slug]), 'Award', false],
                    ]
                    : [
                        ['Study desk', 'Scoring, 14-day plan, templates, and today’s drill', '#c45c26', '#f6e4d6', route('study.show', ['exam' => $exam->slug]), 'BookOpenCheck', true],
                        ['One-to-one speaking', 'Free examiner interview — Parts 1 to 3', '#b4534a', '#f6e0dc', route('speaking.show', ['exam' => $exam->slug]), 'Mic', false],
                        ['Mock Tests', 'Timed papers, real exam flow', '#4f7c8a', '#dce8eb', route('mock-tests.index', ['exam' => $exam->slug]), 'Timer', false],
                        ['Certificates', 'Pathway and module credentials', '#c4a35a', '#f4ecd4', route('certificates.index', ['exam' => $exam->slug]), 'Award', false],
                    ];
            @endphp
            @foreach($shortcuts as [$label, $hint, $color, $soft, $href, $icon, $next])
                @php $class = 'deck-card text-left outline-none'.($next ? ' guide-next' : ''); @endphp
                @if($href)
                    <a href="{{ $href }}" class="{{ $class }}" @if($next) style="--guide: {{ $color }}" @endif>
                @else
                    <button type="button" class="{{ $class }} w-full" @click="tab = 'performance'">
                @endif
                    <div class="deck-card-band" style="--deck: {{ $color }}"></div>
                    <span class="flex items-start gap-3 p-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl {{ $next ? 'guide-pulse' : '' }}" style="background: {{ $soft }}; color: {{ $color }}">
                            <x-nvs.icon :name="$icon" class="h-5 w-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-2">
                                <span class="font-display text-lg font-bold text-ink">{{ $label }}</span>
                                @if($next)
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-white" style="background: {{ $color }}">Next</span>
                                @endif
                            </span>
                            <span class="mt-1 block text-xs leading-5 text-muted">{{ $hint }}</span>
                        </span>
                    </span>
                @if($href)
                    </a>
                @else
                    </button>
                @endif
            @endforeach
        </div>

        <div class="mb-6 flex flex-wrap items-center gap-2">
            <button type="button" @click="tab = 'practice'" class="rounded-2xl px-4 py-2 text-sm font-semibold" :class="tab === 'practice' ? 'text-white' : 'bg-paper text-muted'" :style="tab === 'practice' ? 'background: {{ $exam->accent }}' : ''">Practice cards</button>
            <button type="button" @click="tab = 'performance'" class="rounded-2xl px-4 py-2 text-sm font-semibold" :class="tab === 'performance' ? 'text-white' : 'bg-paper text-muted'" :style="tab === 'performance' ? 'background: {{ $exam->accent }}' : ''">Today’s mix</button>
            <div class="relative ml-auto w-full sm:max-w-xs">
                <x-nvs.icon name="Search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                <input x-model="q" type="search" placeholder="Search a task card…" class="w-full rounded-2xl border border-[rgba(49,36,24,0.05)] bg-paper py-2.5 pl-10 pr-4 text-sm outline-none ring-brand/20 transition focus:ring-2">
            </div>
        </div>

        <div class="mb-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach($summary as $card)
                <div class="deck-card p-4">
                    <p class="font-display text-3xl font-bold text-ink">{{ number_format($card['count']) }}</p>
                    <p class="mt-1 text-xs font-semibold text-muted">{{ $card['label'] }} questions</p>
                    <span class="mt-3 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold" style="background: {{ $card['tone'] }}22; color: {{ $card['tone'] }}">{{ $card['percent'] }}%</span>
                </div>
            @endforeach
        </div>

        <div x-show="tab === 'practice'" class="space-y-8">
            @foreach($skills as $skill)
                @php $items = $tasks[$skill->slug] ?? collect(); @endphp
                @if($items->isEmpty()) @continue @endif
                <section>
                    <div class="mb-4 flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl" style="background: {{ $skill->soft }}; color: {{ $skill->color }}">
                            <x-nvs.icon :name="$skill->icon" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="font-display text-2xl font-bold text-ink">{{ $skill->label }}</h2>
                            <p class="text-xs font-semibold text-muted">{{ $items->count() }} decks in this skill</p>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($items as $task)
                            @php
                                $isNext = $nextTask && $nextTask->slug === $task->slug;
                                $progress = $task->total ? (int) round(($task->done / $task->total) * 100) : 0;
                            @endphp
                            <a href="{{ route('practice.task', ['skill' => $task->skill, 'task' => $task->slug, 'exam' => $exam->slug]) }}"
                               x-show="!q || '{{ addslashes(strtolower($task->name)) }}'.includes(q.toLowerCase())"
                               class="deck-card outline-none {{ $isNext ? 'guide-next' : '' }}"
                               style="--deck: {{ $skill->color }}{{ $isNext ? '; --guide: '.$exam->accent : '' }}">
                                <div class="deck-card-band"></div>
                                <div class="flex flex-1 flex-col p-5">
                                    <div class="mb-4 flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-[11px] font-extrabold uppercase tracking-[0.14em]" style="color: {{ $skill->color }}">{{ $skill->label }}</p>
                                            <h3 class="font-display mt-1 text-xl font-bold leading-snug text-ink">{{ $task->name }}</h3>
                                        </div>
                                        <div class="deck-ring grid h-14 w-14 shrink-0 place-items-center rounded-full" style="--pct: {{ $progress }}%; --deck: {{ $skill->color }}">
                                            <div class="grid h-11 w-11 place-items-center rounded-full bg-paper text-[11px] font-extrabold text-ink">{{ $progress }}%</div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-muted">{{ $task->done }} of {{ $task->total }} cards practised</p>
                                    <div class="mt-4 flex items-center justify-between">
                                        @if($isNext)
                                            <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-white" style="background: {{ $exam->accent }}">Start next</span>
                                        @else
                                            <span class="text-xs font-semibold text-muted">Open deck</span>
                                        @endif
                                        <span class="inline-flex items-center gap-1 text-sm font-bold" style="color: {{ $skill->color }}">
                                            Practice <x-nvs.icon name="ArrowRight" class="h-4 w-4" />
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach

            @if(isset($tasks['mock']) && $tasks['mock']->isNotEmpty())
                <section>
                    <h2 class="font-display mb-4 text-2xl font-bold text-ink">Mock tests</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($tasks['mock'] as $task)
                            <a href="{{ route('mock-tests.index', ['exam' => $exam->slug]) }}" class="deck-card p-5">
                                <p class="font-display text-xl font-bold text-ink">{{ $task->name }}</p>
                                <p class="mt-2 text-sm text-muted">{{ $task->done }}/{{ $task->total }} papers in this bank</p>
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ route('mock-tests.index', ['exam' => $exam->slug]) }}" class="mt-4 inline-flex text-sm font-semibold text-brand hover:underline">Browse all {{ $exam->short_name }} mock tests →</a>
                </section>
            @endif
        </div>

        <div x-show="tab === 'performance'" x-cloak class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($skills as $skill)
                <div class="deck-card p-5" style="--deck: {{ $skill->color }}">
                    <div class="deck-card-band"></div>
                    <p class="mt-4 text-sm font-bold" style="color: {{ $skill->color }}">{{ $skill->label }}</p>
                    <p class="font-display mt-1 text-3xl font-extrabold text-ink">{{ $today[$skill->slug]['percent'] ?? 0 }}%</p>
                    <p class="mt-1 text-xs text-muted">Today’s mix</p>
                </div>
            @endforeach
        </div>
    </main>
</div>
