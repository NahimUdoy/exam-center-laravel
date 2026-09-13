<div>
    <h1 class="font-display text-3xl font-extrabold">Content overview</h1>
    <p class="mt-2 max-w-2xl text-sm text-muted">Everything the public website shows — exams, images, colors, mocks, certificates — is edited here.</p>
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
