@extends('layouts.nvs-page')

@section('title', 'Certificates — NextVerseSkill')

@push('head')
    @include('certificates.partials.fonts')
@endpush

@section('content')
@php
    $payload = $items->map(fn ($c) => ['status' => $c->status, 'category' => $c->category])->values();
@endphp
<div class="min-h-screen bg-aurora"
     x-data="{
        status: 'all',
        category: 'all',
        certs: {{ Js::from($payload) }},
        visible(itemStatus, itemCat) {
            return (this.status === 'all' || this.status === itemStatus)
                && (this.category === 'all' || this.category === itemCat);
        },
        has(cat) {
            return this.certs.some((c) => c.category === cat && this.visible(c.status, c.category));
        },
        get empty() {
            return !this.certs.some((c) => this.visible(c.status, c.category));
        }
     }">
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:py-8">
        <div class="mb-8 overflow-hidden rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-paper shadow-sm">
            <div class="relative px-6 py-8 text-white sm:px-8" style="background: linear-gradient(125deg, {{ $exam->accent }} 0%, #0b1a2b 70%)">
                <div class="pointer-events-none absolute -right-10 top-0 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
                <div class="pointer-events-none absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #d4af37 1px, transparent 1px); background-size: 22px 22px"></div>
                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/70">
                            <x-nvs.icon name="Award" class="h-4 w-4" />
                            Course credentials
                        </p>
                        <h1 class="font-display mt-2 text-3xl font-bold sm:text-4xl">{{ $exam->short_name }} Certificates</h1>
                        <p class="mt-2 text-sm text-white/70 sm:text-base">
                            Pathway, module, and mock credentials for <span class="font-semibold text-white">{{ $exam->name }}</span>.
                            Switch exams in the top bar to view other courses.
                        </p>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach([['Earned', $counts['earned']], ['Pending', $counts['pending']], ['Locked', $counts['locked']]] as $stat)
                            <div class="min-w-[88px] rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-center backdrop-blur">
                                <p class="font-display text-2xl font-bold">{{ $stat[1] }}</p>
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-white/65">{{ $stat[0] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3 flex flex-wrap gap-2">
            @foreach([['all', 'All courses', $counts['all']], ['pathway', 'Pathways', $counts['pathway']], ['module', 'Modules', $counts['module']], ['mock', 'Mocks', $counts['mock']]] as $pill)
                <button type="button" @click="category = '{{ $pill[0] }}'"
                    class="rounded-full px-4 py-2 text-sm font-semibold transition"
                    :class="category === '{{ $pill[0] }}' ? 'text-white shadow-md' : 'bg-paper text-muted ring-1 ring-[rgba(15,40,80,0.08)] hover:text-ink'"
                    :style="category === '{{ $pill[0] }}' ? 'background: {{ $exam->accent }}' : ''">
                    {{ $pill[1] }} ({{ $pill[2] }})
                </button>
            @endforeach
        </div>
        <div class="mb-8 flex flex-wrap gap-2">
            @foreach([['all', 'All', $counts['all']], ['earned', 'Earned', $counts['earned']], ['pending', 'Pending', $counts['pending']], ['locked', 'Locked', $counts['locked']]] as $pill)
                <button type="button" @click="status = '{{ $pill[0] }}'"
                    class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition"
                    :class="status === '{{ $pill[0] }}' ? 'bg-ink text-white' : 'bg-mist text-muted hover:text-ink'">
                    {{ $pill[1] }} ({{ $pill[2] }})
                </button>
            @endforeach
        </div>

        <div class="space-y-10">
            @foreach($certificates as $category => $groupItems)
                <section x-show="has('{{ $category }}')" x-cloak>
                    <div class="mb-4 flex items-end justify-between gap-3">
                        <div>
                            <p class="inline-flex items-center gap-2 text-sm font-bold text-ink">
                                @php $icon = $category === 'pathway' ? 'Trophy' : ($category === 'module' ? 'BookOpen' : 'Layers'); @endphp
                                <x-nvs.icon name="{{ $icon }}" class="h-4 w-4" style="color: {{ $exam->accent }}" />
                                {{ $categories[$category]->label ?? ucfirst($category) }}
                            </p>
                            @if(!empty($categories[$category]->hint))
                                <p class="mt-0.5 text-xs text-muted">{{ $categories[$category]->hint }}</p>
                            @endif
                        </div>
                        <span class="text-xs font-semibold text-muted">{{ $groupItems->count() }} credential{{ $groupItems->count() === 1 ? '' : 's' }}</span>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($groupItems as $cert)
                            @php
                                $lockedCard = $cert->status !== 'earned';
                                $showUrl = route('certificates.show', ['certificate' => $cert, 'exam' => $exam->slug]);
                            @endphp
                            <article x-show="visible('{{ $cert->status }}', '{{ $cert->category }}')" class="group flex flex-col overflow-hidden rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-paper shadow-[0_18px_40px_rgba(15,40,80,0.08)] transition hover:-translate-y-0.5 hover:shadow-[0_24px_50px_rgba(15,40,80,0.12)]">
                                <div class="relative h-44 overflow-hidden px-5 py-4"
                                     style="background: {{ $lockedCard ? 'linear-gradient(135deg, #334155, #0f172a)' : 'linear-gradient(145deg, #1a1520 0%, #0c1220 55%, #1c1830 100%)' }}">
                                    @unless($lockedCard)
                                        <div class="absolute inset-x-3 inset-y-3 rounded-lg border opacity-70" style="border-color: #c4a35a66"></div>
                                        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-[#c4a35a]/25 blur-2xl"></div>
                                        <div class="absolute right-4 top-1/2 h-9 w-9 -translate-y-1/2 rounded-full opacity-90" style="background: conic-gradient(from 20deg, #f0e0a8, #9a7b2f, #d4af37, #f0e0a8)"></div>
                                        <div class="absolute right-5 top-1/2 grid h-7 w-7 -translate-y-1/2 place-items-center rounded-full bg-[#0a0e17] font-cert-display text-[8px] font-bold tracking-widest text-[#f0e0a8]">NV</div>
                                    @endunless
                                    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 18px 18px"></div>
                                    <div class="relative flex h-full flex-col justify-between text-white">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="rounded-sm px-2 py-1 font-cert-display text-[10px] font-bold uppercase tracking-[0.12em]"
                                                      style="background: {{ $lockedCard ? 'rgba(255,255,255,0.15)' : '#c4a35a33' }}; color: {{ $lockedCard ? '#fff' : '#e8d5a3' }}">{{ $cert->level() }}</span>
                                                <span class="rounded-sm bg-white/10 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-white/80">{{ $categories[$cert->category]->label ?? $cert->category }}</span>
                                            </div>
                                            @if($cert->status === 'earned')
                                                <span class="rounded-full bg-teal px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide">Earned</span>
                                            @elseif($cert->status === 'pending')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-ink">Pending</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide">
                                                    <x-nvs.icon name="Lock" class="h-3 w-3" /> Locked
                                                </span>
                                            @endif
                                        </div>
                                        <div class="pr-12">
                                            <p class="font-cert-display text-[15px] font-semibold leading-snug text-white">{{ $cert->title }}</p>
                                            @if($cert->score_label)
                                                <p class="mt-1 text-xs font-semibold" style="color: {{ $lockedCard ? 'rgba(255,255,255,0.7)' : '#e8d5a3' }}">{{ $cert->score_label }}: {{ $cert->score_value }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-1 flex-col p-5">
                                    <p class="text-sm text-muted">{{ $cert->subtitle }}</p>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach(array_slice($cert->modules(), 0, 4) as $module)
                                            <span class="rounded-full bg-mist px-2.5 py-1 text-[11px] font-semibold text-ink">{{ $module }}</span>
                                        @endforeach
                                    </div>
                                    <div class="mt-4 flex items-center justify-between text-xs font-semibold text-muted">
                                        <span>{{ $cert->issued_on ? 'Issued '.$cert->issued_on : 'Not issued yet' }}</span>
                                        <span>{{ $cert->hoursCompleted() }}h logged</span>
                                    </div>
                                    <div class="mt-5 flex gap-2">
                                        @if($cert->status === 'earned')
                                            <a href="{{ $showUrl }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-full py-2.5 text-sm font-bold text-white transition hover:brightness-110" style="background: {{ $exam->accent }}">
                                                <x-nvs.icon name="Eye" class="h-4 w-4" />
                                                View certificate
                                            </a>
                                            <a href="{{ $showUrl }}" class="inline-flex items-center justify-center rounded-full border border-[rgba(15,40,80,0.08)] px-3 text-ink transition hover:bg-mist" aria-label="Open">
                                                <x-nvs.icon name="Download" class="h-4 w-4" />
                                            </a>
                                        @else
                                            <a href="{{ $cert->status === 'pending' ? route('mock-tests.index', ['exam' => $exam->slug]) : route('dashboard', ['exam' => $exam->slug]) }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-ink py-2.5 text-sm font-bold text-white">
                                                @if($cert->status === 'pending')
                                                    <x-nvs.icon name="Sparkles" class="h-4 w-4" />
                                                    Finish to unlock
                                                @else
                                                    <x-nvs.icon name="Lock" class="h-4 w-4" />
                                                    Complete requirements
                                                @endif
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div x-show="empty" x-cloak class="rounded-[1.5rem] border border-dashed border-[rgba(15,40,80,0.08)] bg-paper px-6 py-16 text-center">
            <x-nvs.icon name="Award" class="mx-auto h-8 w-8 text-muted" />
            <p class="font-display mt-3 text-lg font-bold text-ink">No {{ $exam->short_name }} certificates in this filter</p>
            <p class="mt-1 text-sm text-muted">Try another course type or status, or switch exams in the top bar.</p>
        </div>
    </main>
</div>
@endsection
