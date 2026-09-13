@php
    $cards = $mocks->map(fn ($mock) => [
        'id' => $mock->slug,
        'title' => $mock->title,
        'subtitle' => $mock->subtitle,
        'section' => $mock->section,
        'difficulty' => $mock->difficulty,
        'duration' => $mock->duration_min,
        'attempts' => (int) $mock->attempts,
        'questions' => (int) $mock->questions,
        'cover' => $mock->cover_image,
        'href' => route('mock-tests.show', ['mock' => $mock->slug, 'exam' => $exam->slug]),
        'label' => optional($sections[$mock->section] ?? null)->label ?? ucfirst($mock->section),
        'color' => optional($sections[$mock->section] ?? null)->color ?? $exam->accent,
        'soft' => optional($sections[$mock->section] ?? null)->soft ?? '#dbeafe',
        'band' => optional($sections[$mock->section] ?? null)->band ?? 'from-brand to-brand-deep',
    ])->values();
@endphp
<div class="min-h-screen bg-aurora"
     x-data="{
        filter: 'all',
        query: '',
        mocks: {{ Js::from($cards) }},
        get list() {
            const needle = this.query.trim().toLowerCase();
            return this.mocks.filter((m) => {
                if (this.filter !== 'all' && m.section !== this.filter) return false;
                if (needle && !(m.title.toLowerCase().includes(needle) || (m.subtitle || '').toLowerCase().includes(needle))) return false;
                return true;
            });
        },
        nextId() {
            const found = this.list.find((m) => m.attempts === 0);
            return found ? found.id : null;
        }
     }">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:py-8">
        <section>
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: {{ $exam->accent }}">{{ $exam->short_name }} · Mock catalog</p>
                    <h1 class="font-display mt-1 text-3xl font-bold text-ink">Mock Tests</h1>
                    <p class="mt-1 text-sm text-muted">Timed {{ $exam->name }} mocks — filtered by your top exam selection.</p>
                </div>
                <div class="relative w-full sm:max-w-xs">
                    <x-nvs.icon name="Search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                    <input x-model="query" placeholder="Search {{ $exam->short_name }} mocks…" class="w-full rounded-full border border-[rgba(15,40,80,0.08)] bg-paper py-2.5 pl-10 pr-4 text-sm outline-none ring-teal/30 focus:ring-2">
                </div>
            </div>

            <div class="mb-6 flex flex-wrap gap-2">
                @foreach([
                    ['all', 'All', $counts['all']],
                    ['speaking', 'Speaking', $counts['speaking']],
                    ['reading', 'Reading', $counts['reading']],
                    ['listening', 'Listening', $counts['listening']],
                    ['writing', 'Writing', $counts['writing']],
                    ['full', 'Full Mock', $counts['full']],
                ] as [$key, $label, $count])
                    <button type="button" @click="filter = '{{ $key }}'"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition"
                        :class="filter === '{{ $key }}' ? 'text-white shadow-md' : 'bg-paper text-muted ring-1 ring-[rgba(15,40,80,0.08)] hover:text-ink'"
                        :style="filter === '{{ $key }}' ? 'background: {{ $exam->accent }}' : ''">
                        {{ $label }} <span class="opacity-80">({{ $count }})</span>
                    </button>
                @endforeach
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <template x-for="mock in list" :key="mock.id">
                    <article class="deck-card" :class="mock.id === nextId() ? 'guide-next' : ''">
                        <template x-if="mock.cover">
                            <img :src="mock.cover" alt="" class="h-28 w-full object-cover">
                        </template>
                        <template x-if="!mock.cover">
                            <div class="h-2.5 bg-gradient-to-r" :class="mock.band"></div>
                        </template>
                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide" :style="'background:' + mock.soft + ';color:' + mock.color" x-text="mock.label"></span>
                                <span class="text-[11px] font-semibold text-muted" x-text="mock.difficulty"></span>
                            </div>
                            <h2 class="font-display text-lg font-bold leading-snug text-ink" x-text="mock.title"></h2>
                            <p class="mt-1 text-sm text-muted" x-text="mock.subtitle"></p>
                            <div class="mt-4 flex flex-wrap gap-3 text-xs font-semibold text-muted">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-nvs.icon name="Clock3" class="h-3.5 w-3.5 text-teal" />
                                    <span x-text="mock.duration + ' min'"></span>
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-nvs.icon name="Trophy" class="h-3.5 w-3.5 text-amber" />
                                    <span x-text="mock.attempts + (mock.attempts === 1 ? ' attempt' : ' attempts')"></span>
                                </span>
                                <span class="rounded-full bg-mist px-2 py-0.5" x-text="mock.questions + ' Qs'"></span>
                            </div>
                            <a :href="mock.href"
                               class="mt-5 inline-flex items-center justify-center gap-2 rounded-full py-2.5 text-sm font-bold text-white outline-none transition hover:brightness-110"
                               :class="mock.attempts > 0 ? 'bg-ink' : 'shadow-md'"
                               :style="mock.attempts > 0 ? '' : 'background: linear-gradient(90deg, {{ $exam->accent }}, {{ $exam->accent }}cc)'">
                                <template x-if="mock.attempts > 0">
                                    <span class="inline-flex items-center gap-2"><x-nvs.icon name="RotateCcw" class="h-4 w-4" /> Retake</span>
                                </template>
                                <template x-if="mock.attempts === 0">
                                    <span class="inline-flex items-center gap-2"><x-nvs.icon name="Play" class="h-4 w-4 fill-current" /> Start Test</span>
                                </template>
                            </a>
                        </div>
                    </article>
                </template>
            </div>

            <div x-show="list.length === 0" x-cloak class="rounded-[1.5rem] border border-dashed border-[rgba(15,40,80,0.08)] bg-paper/70 px-6 py-16 text-center">
                <p class="font-display text-lg font-bold text-ink">No {{ $exam->short_name }} mocks match that filter</p>
                <p class="mt-1 text-sm text-muted">Try another section or clear your search.</p>
            </div>
        </section>
    </div>
</div>
