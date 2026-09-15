<div>
    <h1 class="font-display text-3xl font-extrabold">Content overview</h1>
    <p class="mt-2 max-w-2xl text-sm text-muted">Yes — you can add new exams, courses, question types, and questions here. The public site updates as soon as you save.</p>

    <div class="mt-6 overflow-hidden rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-white shadow-sm">
        <div class="border-b border-[rgba(15,40,80,0.06)] bg-mist/70 px-5 py-4">
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Add prep content</p>
            <p class="mt-1 text-sm text-ink">Do it in this order so the dashboard and player know where the new items belong.</p>
        </div>
        <div class="grid gap-0 sm:grid-cols-2 xl:grid-cols-4">
            @php
                $steps = [
                    ['1', 'New exam or course', 'TOEFL, GRE, SAT, or your own track. Also add a homepage course card if you want it on the marketing page.', 'exams', 'Add exam', 'courses', 'Add course card'],
                    ['2', 'New question type', 'Example: GRE Quant, TOEFL independent speaking. Pick a skill (speaking / writing / reading / listening) and tick the exams.', 'tasks', 'Add task', null, null],
                    ['3', 'New questions', 'Open Practice questions, pick the task, paste the prompt, and save the answer key. Students see it after login.', 'questions', 'Add question', 'mocks', 'Add mock'],
                    ['4', 'Optional extras', 'PTE bank, reading passages, level-check items, and certificates all have their own Add button.', 'pte', 'Add PTE item', 'diagnostics', 'Add level-check'],
                ];
            @endphp
            @foreach($steps as [$num, $title, $hint, $path, $cta, $path2, $cta2])
                <div class="border-t border-[rgba(15,40,80,0.06)] p-5 xl:border-l xl:border-t-0 xl:first:border-l-0">
                    <p class="text-xs font-extrabold text-brand">Step {{ $num }}</p>
                    <p class="mt-1 font-display text-lg font-bold text-ink">{{ $title }}</p>
                    <p class="mt-2 text-sm leading-6 text-muted">{{ $hint }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.collection.create', $path) }}" class="rounded-full bg-ink px-3 py-1.5 text-xs font-bold text-white">{{ $cta }}</a>
                        @if($path2)
                            <a href="{{ route('admin.collection.create', $path2) }}" class="rounded-full border border-[rgba(15,40,80,0.1)] px-3 py-1.5 text-xs font-bold text-ink">{{ $cta2 }}</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($collections as $item)
            <a href="{{ route('admin.collection', $item['path']) }}" class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-premium">
                <div class="flex items-center justify-between">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-soft text-brand">
                        <x-nvs.icon :name="$item['icon']" class="h-5 w-5" />
                    </span>
                    <span class="text-2xl font-extrabold text-ink">{{ $counts[$item['key']] ?? 0 }}</span>
                </div>
                <p class="mt-4 font-display text-lg font-bold">{{ $item['label'] }}</p>
                <p class="mt-1 text-sm text-muted">{{ $item['description'] }}</p>
            </a>
        @endforeach
        <a href="{{ route('admin.site') }}" class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-5 shadow-sm">
            <p class="font-display text-lg font-bold">Site & Academy</p>
            <p class="mt-1 text-sm text-muted">Hero copy, ticker, how-it-works steps, and logo.</p>
        </a>
        <a href="{{ route('admin.media') }}" class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-5 shadow-sm">
            <p class="font-display text-lg font-bold">Media library</p>
            <p class="mt-1 text-sm text-muted">{{ $counts['media'] ?? 0 }} uploaded files</p>
        </a>
    </div>
</div>
