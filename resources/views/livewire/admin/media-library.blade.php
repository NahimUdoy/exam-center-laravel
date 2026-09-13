<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-display text-3xl font-extrabold">Media library</h1>
            <p class="mt-1 text-sm text-muted">Uploads used across exams, courses, mocks, and Describe Image.</p>
        </div>
        <label class="cursor-pointer rounded-full bg-ink px-5 py-2.5 text-sm font-bold text-white">
            Upload image
            <input type="file" class="hidden" wire:model="upload" accept="image/*">
        </label>
    </div>
    <div wire:loading wire:target="upload" class="mb-4 text-sm font-semibold text-brand">Uploading…</div>
    @if($error) <p class="mb-4 text-sm font-semibold text-rose-500">{{ $error }}</p> @endif
    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @forelse($items as $item)
            <article class="overflow-hidden rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white">
                <img src="{{ $item->url }}" alt="{{ $item->alt }}" class="h-36 w-full object-contain bg-mist">
                <div class="p-3">
                    <p class="truncate text-sm font-bold">{{ $item->name }}</p>
                    <p class="truncate text-xs text-muted">{{ $item->url }}</p>
                    <button type="button" wire:click="deleteItem({{ $item->id }})" class="mt-2 text-xs font-bold text-rose-500">Remove from library</button>
                </div>
            </article>
        @empty
            <p class="text-muted">No media yet.</p>
        @endforelse
    </div>
</div>
