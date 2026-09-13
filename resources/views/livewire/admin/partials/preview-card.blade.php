@php
    $collection = $collection ?? '';
    $item = $item ?? [];
    $color = $item['color'] ?? $item['accent'] ?? '#2563eb';
    $name = $item['name'] ?? $item['shortName'] ?? $item['title'] ?? 'Preview';
@endphp

@if(in_array($collection, ['exams', 'courses'], true))
    @php $cover = $item['coverImage'] ?? ''; $modules = $item['modules'] ?? []; @endphp
    <article class="overflow-hidden rounded-[1.4rem] bg-[#0b1f44] text-white shadow-lg">
        <div class="relative h-32" style="background: {{ $cover ? "url($cover) center/cover" : "linear-gradient(145deg, {$color}, #0b1f44)" }}">
            <div class="absolute inset-0 bg-black/25"></div>
            <div class="relative z-10 flex h-full flex-col justify-between p-4">
                <span class="w-fit rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-bold uppercase">{{ $item['tag'] ?? $item['tagline'] ?? 'Exam path' }}</span>
                <p class="font-display text-2xl font-extrabold">{{ $name }}</p>
            </div>
        </div>
        <div class="space-y-2 p-4">
            @forelse(array_slice($modules, 0, 4) as $module)
                <div class="flex items-center justify-between text-sm">
                    <span>{{ $module['name'] ?? 'Module' }}</span>
                    <span class="text-xs text-white/60">{{ $module['count'] ?? '' }}</span>
                </div>
            @empty
                <p class="text-sm text-white/70">{{ $item['tagline'] ?? 'Practice this exam' }}</p>
            @endforelse
            <span class="mt-2 inline-flex w-full items-center justify-center rounded-xl py-2.5 text-sm font-bold" style="background: {{ $color }}">
                {{ $item['total'] ?? 'Open on website' }}
            </span>
        </div>
    </article>
@elseif($collection === 'certificates')
    <article class="overflow-hidden rounded-[1.4rem] bg-white shadow-sm">
        <div class="relative h-32 p-4 text-white" style="background: {{ !empty($item['coverImage']) ? 'url('.$item['coverImage'].') center/cover' : 'linear-gradient(145deg, #1a1520, #0c1220)' }}">
            <p class="font-display mt-8 text-lg font-bold">{{ $item['title'] ?? '' }}</p>
            <p class="text-xs text-[#e8d5a3]">{{ $item['scoreLabel'] ?? 'Score' }}: {{ $item['scoreValue'] ?? '' }}</p>
        </div>
        <p class="p-4 text-sm text-muted">{{ $item['subtitle'] ?? '' }}</p>
    </article>
@elseif($collection === 'mocks')
    <article class="overflow-hidden rounded-[1.35rem] bg-white">
        @if(!empty($item['coverImage']))
            <img src="{{ $item['coverImage'] }}" alt="" class="h-24 w-full object-cover">
        @else
            <div class="h-2.5 bg-gradient-to-r from-blue-600 to-sky-500"></div>
        @endif
        <div class="p-4">
            <p class="font-display text-lg font-bold">{{ $item['title'] ?? '' }}</p>
            <p class="mt-1 text-sm text-muted">{{ $item['subtitle'] ?? '' }}</p>
            <p class="mt-3 text-xs font-semibold text-muted">{{ $item['durationMin'] ?? 0 }} min · {{ $item['questions'] ?? 0 }} Qs</p>
        </div>
    </article>
@elseif(in_array($collection, ['pteQuestions', 'questions'], true))
    <div class="rounded-2xl border border-dashed border-coral/40 bg-gradient-to-br from-[#fff4ef] to-white p-5 text-center">
        @if(!empty($item['imageUrl']))
            <img src="{{ $item['imageUrl'] }}" alt="" class="mx-auto mb-3 max-h-48 rounded-xl object-contain">
        @else
            <div class="mx-auto mb-3 flex h-28 max-w-xs items-end justify-around gap-2 rounded-xl bg-ink/5 p-3">
                @foreach([40,70,55,35] as $h)
                    <div class="w-7 rounded-t-md bg-gradient-to-t from-coral to-amber" style="height: {{ $h }}%"></div>
                @endforeach
            </div>
        @endif
        <p class="font-display text-lg font-bold">{{ $item['imageTitle'] ?? $item['title'] ?? '' }}</p>
        <p class="mt-1 text-sm text-muted">{{ $item['imageHint'] ?? 'This is how students will see it.' }}</p>
    </div>
@elseif(in_array($collection, ['skills', 'mockSections', 'pteTaskTypes'], true))
    <div class="flex items-center gap-3 rounded-2xl bg-white p-4">
        <span class="grid h-12 w-12 place-items-center rounded-2xl" style="background: {{ $item['soft'] ?? $item['accentSoft'] ?? '#dbeafe' }}; color: {{ $color }}">
            <x-nvs.icon :name="$item['icon'] ?? 'Sparkles'" class="h-5 w-5" />
        </span>
        <div>
            <p class="font-bold">{{ $item['label'] ?? $item['short'] ?? $name }}</p>
            <p class="text-xs text-muted">{{ $color }}</p>
        </div>
    </div>
@elseif($collection === 'tasks')
    <div class="rounded-2xl bg-white p-4">
        <p class="text-xs font-bold uppercase text-muted">{{ $item['skill'] ?? '' }}</p>
        <p class="font-display mt-1 text-lg font-bold">{{ $item['name'] ?? '' }}</p>
        <p class="mt-2 text-sm text-muted">{{ $item['done'] ?? 0 }}/{{ $item['total'] ?? 0 }} items</p>
    </div>
@else
    <div class="rounded-2xl bg-white p-4 text-sm text-muted">Save to see this item on the public site.</div>
@endif
