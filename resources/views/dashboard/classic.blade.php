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
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em]" style="color: {{ $exam->accent }}">{{ $exam->short_name }} dashboard</p>
                <h1 class="font-display mt-1 text-3xl font-bold text-ink">{{ $exam->name }} <span style="color: {{ $exam->accent }}">practice hub</span></h1>
                <p class="mt-1 max-w-xl text-sm text-muted">{{ $exam->description }}</p>
            </div>
            <div class="flex items-center gap-2 rounded-2xl border border-[rgba(15,40,80,0.08)] bg-paper px-4 py-2 shadow-sm">
                <x-nvs.icon name="Flame" class="h-4 w-4 text-coral" />
                <span class="text-sm font-semibold text-ink">4-day streak</span>
                <span class="text-xs text-muted">{{ $exam->short_name }} focus</span>
            </div>
        </div>

        @if($exam->slug === 'english-basic')
            <a href="{{ route('english-basics.index', ['exam' => 'english-basic']) }}" class="mb-6 flex flex-col gap-2 rounded-[1.5rem] border border-emerald-600/15 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-emerald-700">Start here</p>
                    <p class="font-display mt-1 text-xl font-bold text-ink">English Basics A–Z</p>
                    <p class="mt-1 text-sm text-muted">Idea → example sentence → 8 questions. Follow the path if you are new.</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-sm font-bold text-white">Open grammar hub <x-nvs.icon name="ArrowRight" class="h-4 w-4" /></span>
            </a>
        @endif

        <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $basicsHref = route('english-basics.index', ['exam' => 'english-basic']);
                $shortcuts = $exam->slug === 'english-basic'
                    ? [
                        ['Grammar A–Z', 'Noun, verb, tense, article — explain + practice', '#059669', '#d1fae5', $basicsHref, 'BookOpen', true],
                        ['One-to-one speaking', 'Free examiner interview — talk and get a follow-up', '#2563eb', '#dbeafe', route('speaking.show', ['exam' => $exam->slug]), 'Mic', false],
                        ['Mock Tests', 'Timed papers, real exam flow', '#4f46e5', '#e0e7ff', route('mock-tests.index', ['exam' => $exam->slug]), 'Timer', false],
                        ['Certificates', 'Pathway and module credentials', '#047857', '#d1fae5', route('certificates.index', ['exam' => $exam->slug]), 'Award', false],
                    ]
                    : [
                        ['One-to-one speaking', 'Free examiner interview — Parts 1 to 3', '#2563eb', '#dbeafe', route('speaking.show', ['exam' => $exam->slug]), 'Mic', true],
                        ['Predictions', 'Likely questions for your exam', '#ea580c', '#ffedd5', $practiceHref, 'Sparkles', false],
                        ['Mock Tests', 'Timed papers, real exam flow', '#4f46e5', '#e0e7ff', route('mock-tests.index', ['exam' => $exam->slug]), 'Timer', false],
                        ['Certificates', 'Pathway and module credentials', '#047857', '#d1fae5', route('certificates.index', ['exam' => $exam->slug]), 'Award', false],
                    ];
            @endphp
            @foreach($shortcuts as [$label, $hint, $color, $soft, $href, $icon, $next])
                @php $class = 'app-card group relative flex items-start gap-3 overflow-hidden rounded-[1.35rem] border border-[rgba(15,40,80,0.08)] bg-white p-4 text-left outline-none'.($next ? ' guide-next' : ''); @endphp
                @if($href)
                    <a href="{{ $href }}" class="{{ $class }}">
                @else
                    <button type="button" class="{{ $class }}" @click="tab = 'performance'">
                @endif
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl {{ $next ? 'guide-pulse' : '' }}" style="background: {{ $soft }}; color: {{ $color }}">
                        <x-nvs.icon :name="$icon" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-2">
                            <span class="text-sm font-bold text-ink">{{ $label }}</span>
                            @if($next)
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-white" style="background: {{ $color }}">Next</span>
                            @endif
                        </span>
                        <span class="mt-0.5 block text-xs text-muted">{{ $hint }}</span>
                    </span>
                    <x-nvs.icon name="ArrowRight" class="mt-1 h-4 w-4 shrink-0 text-muted" />
                @if($href)
                    </a>
                @else
                    </button>
                @endif
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.7fr_1fr]">
            <section id="practice" class="scroll-mt-32 space-y-5">
                <div class="rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-paper p-4 shadow-sm sm:p-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="tab = 'practice'" class="rounded-full px-4 py-2 text-sm font-semibold" :class="tab === 'practice' ? 'text-white' : 'bg-mist text-muted'" :style="tab === 'practice' ? 'background: {{ $exam->accent }}' : ''">Practice Progress</button>
                        <button type="button" @click="tab = 'performance'" class="rounded-full px-4 py-2 text-sm font-semibold" :class="tab === 'performance' ? 'text-white' : 'bg-mist text-muted'" :style="tab === 'performance' ? 'background: {{ $exam->accent }}' : ''">Performance Progress</button>
                    </div>
                    <div class="relative mt-4">
                        <x-nvs.icon name="Search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                        <input x-model="q" type="search" placeholder="Search {{ $exam->short_name }} questions or tasks..." class="w-full rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist py-3 pl-10 pr-4 text-sm outline-none ring-teal/30 transition focus:ring-2">
                    </div>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach($summary as $card)
                            <div class="app-card rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-3.5">
                                <div class="flex items-start justify-between">
                                    <p class="font-display text-2xl font-bold text-ink">{{ number_format($card['count']) }}</p>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold" style="background: {{ $card['tone'] }}22; color: {{ $card['tone'] }}">{{ $card['percent'] }}%</span>
                                </div>
                                <p class="mt-1 text-xs font-semibold text-muted">{{ $card['label'] }} Questions</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div x-show="tab === 'practice'" class="rounded-[1.5rem] border border-[rgba(15,40,80,0.14)] bg-paper p-4 shadow-[0_16px_36px_rgba(15,40,80,0.08)] sm:p-6">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <h2 class="font-display text-xl font-bold text-ink">{{ $exam->short_name }} practice progress</h2>
                        <select class="rounded-full border border-[rgba(15,40,80,0.08)] bg-mist px-3 py-1.5 text-xs font-semibold text-ink outline-none">
                            <option>All days</option>
                            <option>Last 7 days</option>
                            <option>Last 30 days</option>
                        </select>
                    </div>
                    <div class="grid gap-6 md:grid-cols-2">
                        @foreach($skills as $skill)
                            @php $items = $tasks[$skill->slug] ?? collect(); @endphp
                            @if($items->isEmpty()) @continue @endif
                            <div class="section-card rounded-2xl p-3.5 sm:p-4">
                                <div class="absolute inset-y-0 left-0 w-1" style="background: {{ $skill->color }}"></div>
                                <div class="mb-3 flex items-center gap-2 rounded-xl px-2.5 py-2" style="background: {{ $skill->soft }}">
                                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-white" style="color: {{ $skill->color }}">
                                        <x-nvs.icon :name="$skill->icon" class="h-4 w-4" />
                                    </span>
                                    <h3 class="font-display text-lg font-bold" style="color: {{ $skill->color }}">{{ $skill->label }}</h3>
                                </div>
                                <ul class="space-y-2">
                                    @foreach($items as $task)
                                        @php
                                            $isNext = $nextTask && $nextTask->slug === $task->slug;
                                            $progress = $task->total ? (int) round(($task->done / $task->total) * 100) : 0;
                                        @endphp
                                        <li x-show="!q || '{{ addslashes(strtolower($task->name)) }}'.includes(q.toLowerCase())">
                                            <a href="{{ route('practice.task', ['skill' => $task->skill, 'task' => $task->slug, 'exam' => $exam->slug]) }}"
                                               class="section-task group flex items-center gap-3 rounded-xl px-3 py-3 outline-none {{ $isNext ? 'guide-next bg-white' : '' }}"
                                               @if($isNext) style="--guide: {{ $exam->accent }}" @endif>
                                                <span class="min-w-0 flex-1">
                                                    <span class="flex items-center gap-2">
                                                        <span class="text-sm font-semibold text-ink group-hover:text-teal">{{ $task->name }}</span>
                                                        @if($isNext)
                                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-white" style="background: {{ $exam->accent }}">Start next</span>
                                                        @endif
                                                    </span>
                                                    <span class="mt-1.5 block h-1.5 overflow-hidden rounded-full bg-fog">
                                                        <span class="progress-fill block h-full rounded-full" style="width: {{ $progress }}%; background: {{ $skill->color }}"></span>
                                                    </span>
                                                </span>
                                                <span class="shrink-0 text-sm font-bold text-coral">{{ $task->done }}/{{ $task->total }}</span>
                                                <x-nvs.icon name="ChevronRight" class="h-4 w-4 shrink-0 text-muted" />
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>

                    @if(isset($tasks['mock']) && $tasks['mock']->isNotEmpty())
                        <div id="mocks" class="mt-8 border-t border-[rgba(15,40,80,0.08)] pt-6">
                            <h3 class="font-display mb-3 text-lg font-bold text-rose">Mock Tests</h3>
                            <ul class="grid gap-2 sm:grid-cols-2">
                                @foreach($tasks['mock'] as $task)
                                    <li>
                                        <a href="{{ route('mock-tests.index', ['exam' => $exam->slug]) }}" class="app-card flex items-center justify-between rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white px-3 py-3 text-sm font-medium outline-none">
                                            <span>{{ $task->name }}</span>
                                            <span class="font-bold text-coral">{{ $task->done }}/{{ $task->total }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ route('mock-tests.index', ['exam' => $exam->slug]) }}" class="mt-3 inline-flex text-sm font-semibold hover:underline" style="color: {{ $exam->accent }}">Browse all {{ $exam->short_name }} mock tests →</a>
                        </div>
                    @endif

                    <a href="{{ $practiceHref }}" class="mt-6 flex w-full items-center justify-center gap-2 rounded-2xl py-3.5 text-sm font-bold text-white shadow-lg outline-none transition hover:brightness-110" style="background: linear-gradient(90deg, {{ $exam->accent }}, {{ $exam->accent }}cc)">
                        Continue {{ $exam->short_name }} practice
                        <x-nvs.icon name="ChevronRight" class="h-4 w-4" />
                    </a>
                </div>

                <div x-show="tab === 'performance'" x-cloak class="rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-paper p-5">
                    <h2 class="font-display text-lg font-bold">Today's skill mix</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach($skills as $skill)
                            <div class="rounded-xl p-4" style="background: {{ $skill->soft }}">
                                <p class="text-sm font-bold" style="color: {{ $skill->color }}">{{ $skill->label }}</p>
                                <p class="font-display mt-1 text-2xl font-extrabold text-ink">{{ $today[$skill->slug]['percent'] ?? 0 }}%</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <aside class="space-y-5">
                <div class="app-card overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-ink via-ink-soft to-ink-mid p-5 text-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-teal-glow">Premium unlock</p>
                            <h3 class="font-display mt-2 text-xl font-bold">Personalized {{ $exam->short_name }} plan</h3>
                            <p class="mt-2 text-sm text-white/65">Adaptive daily targets for {{ $exam->name }} with AI score coaching.</p>
                        </div>
                        <x-nvs.icon name="Sparkles" class="h-5 w-5 text-amber" />
                    </div>
                    <button type="button" class="mt-5 w-full rounded-full bg-white py-2.5 text-sm font-bold text-ink">Upgrade to Premium</button>
                </div>

                <div class="app-card rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-paper p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-display text-lg font-bold">Today's progress</h3>
                        <x-nvs.icon name="Clock3" class="h-4 w-4 text-muted" />
                    </div>
                    <div class="mb-4 grid grid-cols-2 gap-3 rounded-2xl bg-mist p-3 text-center">
                        <div>
                            <p class="text-2xl font-bold text-ink">58</p>
                            <p class="text-[11px] font-semibold text-muted">Avg score</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-ink">00:42</p>
                            <p class="text-[11px] font-semibold text-muted">Practice time</p>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        @foreach($skills as $skill)
                            <li>
                                <div class="mb-1 flex items-center justify-between text-xs font-semibold">
                                    <span class="flex items-center gap-2 text-ink">
                                        <span class="grid h-7 w-7 place-items-center rounded-lg" style="background: {{ $skill->soft }}; color: {{ $skill->color }}">
                                            <x-nvs.icon :name="$skill->icon" class="h-3.5 w-3.5" />
                                        </span>
                                        {{ $skill->label }}
                                    </span>
                                    <span style="color: {{ $skill->color }}">{{ $today[$skill->slug]['percent'] ?? 0 }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-fog">
                                    <div class="progress-fill h-full rounded-full" style="width: {{ $today[$skill->slug]['percent'] ?? 0 }}%; background: {{ $skill->color }}"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="grid grid-cols-2 overflow-hidden rounded-[1.5rem] shadow-[0_20px_50px_rgba(37,99,235,0.1)]">
                    <div class="bg-ink p-4 text-white">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-white/60">Your target</p>
                        <p class="font-display mt-2 text-3xl font-bold text-teal-glow">65–79</p>
                        <p class="mt-1 text-xs text-white/60">Proficient band</p>
                    </div>
                    <div class="p-4 text-white" style="background: {{ $exam->accent }}">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-white/80">Exam countdown</p>
                        <div class="mt-2 grid grid-cols-2 gap-1 text-center">
                            @foreach([['18','Days'],['06','Hrs'],['42','Min'],['11','Sec']] as [$v, $l])
                                <div class="rounded-lg bg-white/15 px-1 py-1.5">
                                    <p class="font-display text-lg font-bold leading-none">{{ $v }}</p>
                                    <p class="text-[9px] uppercase tracking-wide opacity-80">{{ $l }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="app-card rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-paper p-5">
                    <p class="text-xs font-bold uppercase tracking-wide text-brand">Study plan</p>
                    <h3 class="font-display mt-1 text-lg font-bold text-ink">{{ $completion }}% of this track complete</h3>
                    <p class="mt-2 text-sm text-muted">Keep the streak. Finish one task block, then take a sectional mock.</p>
                    <a href="{{ $practiceHref }}" class="mt-4 inline-flex rounded-full bg-brand px-4 py-2 text-sm font-bold text-white">Open next task</a>
                </div>
            </aside>
        </div>
    </main>
</div>
