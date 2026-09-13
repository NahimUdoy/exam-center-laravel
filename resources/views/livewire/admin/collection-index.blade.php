<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-display text-3xl font-extrabold">{{ $config['label'] }}</h1>
            <p class="mt-1 text-sm text-muted">{{ $config['description'] }}</p>
        </div>
        <a href="{{ route('admin.collection.create', $config['path']) }}" class="rounded-full bg-ink px-5 py-2.5 text-sm font-bold text-white">Add {{ strtolower($config['singular']) }}</a>
    </div>
    <input type="search" wire:model.live.debounce.250ms="q" placeholder="Search…" class="mb-4 h-11 w-full max-w-md rounded-2xl border border-[rgba(15,40,80,0.08)] px-4 text-sm">
    <div class="overflow-x-auto rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-mist/80 text-xs font-bold uppercase tracking-wide text-muted">
                <tr>
                    @foreach($config['columns'] as $col)
                        <th class="px-4 py-3">{{ $col['label'] }}</th>
                    @endforeach
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="border-t border-[rgba(15,40,80,0.06)]">
                        @foreach($config['columns'] as $col)
                            @php $val = $item[$col['key']] ?? ''; $kind = $col['kind'] ?? 'text'; @endphp
                            <td class="px-4 py-3">
                                @if($kind === 'image')
                                    @if($val)
                                        <img src="{{ $val }}" alt="" class="h-10 w-14 rounded-lg object-cover">
                                    @else
                                        <span class="text-xs text-muted">—</span>
                                    @endif
                                @elseif($kind === 'color')
                                    <span class="inline-flex items-center gap-2"><span class="h-4 w-4 rounded-full border" style="background: {{ $val }}"></span>{{ $val }}</span>
                                @elseif($kind === 'icon')
                                    <x-nvs.icon :name="$val ?: 'Sparkles'" class="h-5 w-5" />
                                @elseif($kind === 'badge')
                                    <span class="rounded-full bg-mist px-2 py-0.5 text-xs font-bold">{{ $val }}</span>
                                @else
                                    <span class="font-semibold text-ink">{{ is_array($val) ? implode(', ', $val) : $val }}</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.collection.edit', [$config['path'], $item['id'] ?? $item['label']]) }}" class="text-sm font-bold text-brand">Edit</a>
                            <button type="button" wire:click="deleteItem('{{ $item['id'] ?? $item['label'] }}')" wire:confirm="Delete this item?" class="ml-3 text-sm font-bold text-rose-500">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-8 text-muted" colspan="{{ count($config['columns']) + 1 }}">No items yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
