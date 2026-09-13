@extends('layouts.nvs-page')

@section('title', $taskType->name.' — Practice')

@section('content')
@php
    $payload = $questions->map(fn ($q) => [
        'id' => $q->external_id,
        'title' => $q->title,
        'difficulty' => $q->difficulty,
        'appeared' => $q->appeared,
        'isPrediction' => (bool) $q->is_prediction,
        'practiced' => (bool) $q->practiced,
        'bookmarked' => (bool) $q->bookmarked,
        'href' => route('practice.question', ['skill' => $skill, 'task' => $taskType->slug, 'question' => $q->external_id, 'exam' => $exam->slug]),
    ])->values();
    $belongs = in_array($exam->slug, $taskType->exam_slugs ?? [], true)
        || empty($taskType->exam_slugs);
@endphp
<div class="min-h-screen bg-[#0b1522]"
     x-data="{
        filter: 'all',
        tab: 'all',
        q: '',
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
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="overflow-hidden rounded-[1.75rem] border border-white/10 bg-paper shadow-2xl">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[rgba(15,40,80,0.08)] px-5 py-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard', ['exam' => $exam->slug]) }}" class="grid h-9 w-9 place-items-center rounded-full bg-mist text-ink transition hover:bg-fog">
                        <x-nvs.icon name="ChevronLeft" class="h-4 w-4" />
                    </a>
                    <div class="flex items-center gap-2">
                        <span class="grid h-9 w-9 place-items-center rounded-full bg-coral/15 text-coral">
                            <x-nvs.icon name="Mic2" class="h-4 w-4" />
                        </span>
                        <h1 class="font-display text-xl font-bold text-coral sm:text-2xl">{{ $taskType->name }}</h1>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide" style="background: {{ $exam->accent_soft ?? '#dbeafe' }}; color: {{ $exam->accent }}">{{ $exam->short_name }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-coral/40 px-4 py-2 text-sm font-semibold text-coral transition hover:bg-coral/5">
                        <x-nvs.icon name="RotateCcw" class="h-3.5 w-3.5" />
                        Reset Practice
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full bg-coral px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110">
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

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[rgba(15,40,80,0.08)] pb-3">
                    <div class="flex gap-4 text-sm font-semibold">
                        <button type="button" @click="tab = 'all'" class="pb-2 transition" :class="tab === 'all' ? 'border-b-2 border-coral text-coral' : 'text-muted hover:text-ink'">All</button>
                        <button type="button" @click="tab = 'prediction'" class="pb-2 transition" :class="tab === 'prediction' ? 'border-b-2 border-coral text-coral' : 'text-muted hover:text-ink'">Prediction</button>
                        <button type="button" class="inline-flex items-center gap-1 pb-2 text-muted">
                            Level <x-nvs.icon name="SlidersHorizontal" class="h-3.5 w-3.5" />
                        </button>
                    </div>
                    <p class="text-xs font-semibold text-muted"><span x-text="list.length"></span> Questions (New to old)</p>
                </div>

                <ul class="max-h-[58vh] space-y-2.5 overflow-y-auto pr-1 scrollbar-thin">
                    <template x-for="item in list" :key="item.id">
                        <li>
                            <a :href="item.href"
                               class="app-card group flex flex-wrap items-center gap-3 rounded-2xl border bg-white px-4 py-3 outline-none"
                               :class="item.id === nextId() ? 'guide-next border-coral/30' : 'border-teal/20'">
                                <span class="font-semibold text-teal" x-text="'#' + item.id"></span>
                                <span class="font-semibold text-ink group-hover:text-teal" x-text="item.title"></span>
                                <span x-show="item.id === nextId()" class="rounded-full bg-coral px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-white">Start next</span>
                                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold" :class="tone(item.difficulty)" x-text="'● ' + item.difficulty"></span>
                                <span x-show="item.isPrediction" class="rounded-full bg-rose/10 px-2.5 py-0.5 text-[11px] font-bold text-rose">New Prediction</span>
                                <span class="rounded-full border border-teal/30 px-2.5 py-0.5 text-[11px] font-semibold text-teal" x-text="'Appeared (' + item.appeared + ')'"></span>
                                <button type="button" class="ml-auto rounded-full p-1.5 text-teal outline-none transition hover:bg-teal/10" @click.prevent="bookmarks[item.id] = !bookmarks[item.id]" aria-label="Bookmark">
                                    <x-nvs.icon name="Bookmark" class="h-4 w-4" x-bind:class="bookmarks[item.id] ? 'fill-teal' : ''" />
                                </button>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
