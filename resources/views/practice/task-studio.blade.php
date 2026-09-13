@php
    $locked = auth()->guest();
    $payload = $questions->map(fn ($q) => [
        'id' => $q->external_id,
        'title' => $locked ? 'Log in to open' : $q->title,
        'difficulty' => $q->difficulty,
        'appeared' => $q->appeared,
        'isPrediction' => (bool) $q->is_prediction,
        'practiced' => (bool) $q->practiced,
        'bookmarked' => (bool) $q->bookmarked,
        'href' => $locked ? '' : route('practice.question', ['skill' => $skill, 'task' => $taskType->slug, 'question' => $q->external_id, 'exam' => $exam->slug]),
    ])->values();
    $belongs = in_array($exam->slug, $taskType->exam_slugs ?? [], true)
        || empty($taskType->exam_slugs);
@endphp
<div class="min-h-screen bg-aurora"
     x-data="{
        filter: 'all',
        tab: 'all',
        q: '',
        locked: {{ $locked ? 'true' : 'false' }},
        items: {{ Js::from($payload) }},
        bookmarks: {{ Js::from($questions->mapWithKeys(fn ($q) => [$q->external_id => (bool) $q->bookmarked])) }},
        get list() {
            const needle = this.q.trim().toLowerCase();
            return this.items.filter((item) => {
                if (this.filter === 'practiced' && !item.practiced) return false;
                if (this.filter === 'not' && item.practiced) return false;
                if (this.tab === 'prediction' && !item.isPrediction) return false;
                if (needle) {
                    const hay = (item.id + ' ' + item.title).toLowerCase();
                    if (!hay.includes(needle)) return false;
                }
                return true;
            });
        },
        nextId() {
            const found = this.list.find((item) => !item.practiced);
            return found ? found.id : null;
        },
        tone(level) {
            if (level === 'Easy') return 'text-teal bg-teal/10';
            if (level === 'Difficult') return 'text-rose bg-rose/10';
            return 'text-amber bg-amber/10';
        }
     }">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <div class="overflow-hidden rounded-[1.75rem] border border-[rgba(49,36,24,0.04)] bg-paper shadow-premium">
            <div class="h-1.5 bg-brand"></div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[rgba(49,36,24,0.08)] px-5 py-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard', ['exam' => $exam->slug]) }}" class="grid h-9 w-9 place-items-center rounded-2xl bg-mist text-ink transition hover:bg-fog">
                        <x-nvs.icon name="ChevronLeft" class="h-4 w-4" />
                    </a>
                    <div class="flex items-center gap-2">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-soft text-brand">
                            <x-nvs.icon name="Layers" class="h-4 w-4" />
                        </span>
                        <div>
                            <h1 class="font-display text-xl font-bold text-ink sm:text-2xl">{{ $taskType->name }}</h1>
                            <p class="text-xs font-semibold text-muted">{{ $exam->short_name }} card deck</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-brand/30 px-4 py-2 text-sm font-semibold text-brand transition hover:bg-brand-soft">
                        <x-nvs.icon name="RotateCcw" class="h-3.5 w-3.5" />
                        Reset Practice
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-2xl bg-brand px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110">
                        <x-nvs.icon name="Download" class="h-3.5 w-3.5" />
                        Download Material
                    </button>
                </div>
            </div>

            <div class="space-y-4 px-5 py-5 sm:px-6">
                @unless($belongs)
                    <div class="rounded-2xl border border-amber/30 bg-amber/10 px-4 py-3 text-sm font-medium text-ink">
                        This task is built for another exam track. Switch the top exam bar to match, or open {{ $exam->short_name }} practice from the dashboard.
                    </div>
                @endunless
                @if($locked)
                    <div class="rounded-2xl border border-brand/20 bg-brand-soft/70 px-4 py-3 text-sm font-medium text-ink">
                        You can browse this task type. Log in to see the questions and submit answers.
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-4">
                    <template x-for="opt in [['all','All'],['practiced','Practiced'],['not','Not Practiced']]" :key="opt[0]">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-ink">
                            <input type="radio" name="practice-filter" class="accent-teal" :checked="filter === opt[0]" @change="filter = opt[0]">
                            <span x-text="opt[1]"></span>
                        </label>
                    </template>
                    <div class="ml-auto flex min-w-[220px] flex-1 gap-2 sm:max-w-sm">
                        <div class="relative flex-1">
                            <input x-model="q" placeholder="Content / Title / Number" class="w-full rounded-full border border-[rgba(15,40,80,0.08)] bg-mist py-2.5 pl-4 pr-10 text-sm outline-none ring-teal/30 focus:ring-2">
                            <x-nvs.icon name="Search" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-teal" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pb-1">
                    <div class="flex gap-2 text-sm font-semibold">
                        <button type="button" @click="tab = 'all'" class="rounded-2xl px-3 py-1.5 transition" :class="tab === 'all' ? 'bg-brand text-white' : 'bg-mist text-muted hover:text-ink'">All</button>
                        <button type="button" @click="tab = 'prediction'" class="rounded-2xl px-3 py-1.5 transition" :class="tab === 'prediction' ? 'bg-brand text-white' : 'bg-mist text-muted hover:text-ink'">Prediction</button>
                    </div>
                    <p class="text-xs font-semibold text-muted"><span x-text="list.length"></span> cards</p>
                </div>

                <div class="grid max-h-[64vh] gap-3 overflow-y-auto pr-1 scrollbar-thin sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="item in list" :key="item.id">
                        <a :href="locked ? '#' : item.href"
                           @click="if (locked) { $event.preventDefault(); window.nvsNeedAuth && window.nvsNeedAuth(); }"
                           class="deck-card outline-none"
                           :class="item.id === nextId() ? 'guide-next' : ''"
                           style="--guide: #c45c26">
                            <div class="deck-card-band"></div>
                            <div class="p-4">
                                <div class="mb-3 flex items-center justify-between gap-2">
                                    <span class="text-xs font-extrabold tracking-wide text-brand" x-text="locked ? 'Locked' : ('#' + item.id)"></span>
                                    <button type="button" class="rounded-full p-1.5 text-muted outline-none transition hover:bg-mist hover:text-brand" @click.prevent="bookmarks[item.id] = !bookmarks[item.id]" aria-label="Bookmark">
                                        <x-nvs.icon name="Bookmark" class="h-4 w-4" x-bind:class="bookmarks[item.id] ? 'fill-brand text-brand' : ''" />
                                    </button>
                                </div>
                                <p class="font-display text-lg font-bold leading-snug text-ink" x-text="item.title"></p>
                                <div class="mt-4 flex flex-wrap gap-1.5">
                                    <span x-show="item.id === nextId()" class="rounded-full bg-brand px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-white">Start next</span>
                                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold" :class="tone(item.difficulty)" x-text="item.difficulty"></span>
                                    <span x-show="item.isPrediction" class="rounded-full bg-rose/10 px-2.5 py-0.5 text-[11px] font-bold text-rose">Prediction</span>
                                    <span class="rounded-full bg-mist px-2.5 py-0.5 text-[11px] font-semibold text-muted" x-text="'Appeared ' + item.appeared"></span>
                                </div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
