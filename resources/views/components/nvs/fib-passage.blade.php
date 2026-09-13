@props([
    'kind' => 'rw-fib',
    'tokens' => [],
    'blanks' => [],
    'wordBank' => [],
    'map' => [],
    'qid' => '',
    'bind' => 'livewire',
])
@php
    $map = is_array($map) ? $map : [];
    $blankRows = [];
    $n = 0;
    if ($kind === 'rw-fib') {
        foreach ($blanks as $blank) {
            $id = (string) ($blank['id'] ?? ++$n);
            $blankRows[] = [
                'id' => $id,
                'label' => 'Blank '.$id,
                'choices' => array_values($blank['choices'] ?? []),
            ];
        }
    } elseif ($kind === 'r-fib') {
        $count = 0;
        foreach ($tokens as $token) {
            if ($token === '___') {
                $id = (string) $count++;
                $blankRows[] = [
                    'id' => $id,
                    'label' => 'Blank '.($count),
                    'choices' => array_values($wordBank),
                ];
            }
        }
    } else {
        $count = 0;
        foreach ($tokens as $token) {
            if ($token === '___') {
                $id = (string) $count++;
                $blankRows[] = [
                    'id' => $id,
                    'label' => 'Blank '.($count),
                    'choices' => [],
                ];
            }
        }
    }
    $firstId = $blankRows[0]['id'] ?? '1';
    $used = array_values(array_filter($map));
    $alpine = $bind === 'alpine';
@endphp
<div class="space-y-6" @if(! $alpine) x-data="{ activeBlank: {{ Js::from($firstId) }} }" @endif>
    <div class="rounded-3xl border border-[rgba(15,40,80,0.08)] bg-white p-5 sm:p-8">
        <p class="text-[1.22rem] leading-[2.6] text-ink sm:text-[1.4rem] sm:leading-[2.75]">
            @php $blankIndex = 0; @endphp
            @foreach($tokens as $token)
                @php
                    $isRw = $kind === 'rw-fib' && str_starts_with((string) $token, '___');
                    $isGap = in_array($kind, ['r-fib', 'l-fib'], true) && $token === '___';
                @endphp
                @if($isRw)
                    @php
                        preg_match('/___(\d+)/', $token, $m);
                        $id = $m[1] ?? '1';
                        $picked = $map[$id] ?? '';
                    @endphp
                    <button type="button"
                        @click="activeBlank = {{ Js::from($id) }}"
                        class="mx-1 my-1 inline-flex min-w-[7.5rem] max-w-full items-center justify-center rounded-xl border-2 px-3 py-1.5 align-middle text-[1.05rem] font-bold leading-none shadow-sm {{ ! $alpine && $picked !== '' ? 'border-teal/50 bg-teal/5 text-ink' : '' }} {{ ! $alpine && $picked === '' ? 'border-dashed border-ink/25 bg-mist text-muted' : '' }}"
                        :class="activeBlank === {{ Js::from($id) }} ? 'border-teal bg-teal/10 text-teal' : {{ $alpine ? "((blanks['{$id}'] || '') ? 'border-teal/50 bg-teal/5 text-ink' : 'border-dashed border-ink/25 bg-mist text-muted')" : "''" }}">
                        @if($alpine)
                            <span class="truncate" x-text="blanks[{{ Js::from($id) }}] || {{ Js::from('Blank '.$id) }}"></span>
                        @else
                            <span class="truncate">{{ $picked !== '' ? $picked : 'Blank '.$id }}</span>
                        @endif
                    </button>
                @elseif($isGap)
                    @php
                        $id = (string) $blankIndex++;
                        $picked = $map[$id] ?? '';
                        $labelN = $blankIndex;
                    @endphp
                    @if($kind === 'l-fib')
                        @if($alpine)
                            <input x-model="blanks[{{ Js::from($id) }}]"
                                class="mx-1 my-1 inline-flex h-11 min-w-[9.5rem] rounded-xl border-2 border-teal/40 bg-white px-3 align-middle text-[1rem] font-semibold outline-none focus:border-teal"
                                placeholder="Blank {{ $labelN }}">
                        @else
                            <input value="{{ $picked }}"
                                class="mx-1 my-1 inline-flex h-11 min-w-[9.5rem] rounded-xl border-2 border-teal/40 bg-white px-3 align-middle text-[1rem] font-semibold outline-none focus:border-teal"
                                placeholder="Blank {{ $labelN }}"
                                wire:change="setBlank({{ Js::from($qid) }}, {{ Js::from($id) }}, $event.target.value)">
                        @endif
                    @else
                        <button type="button"
                            @click="activeBlank = {{ Js::from($id) }}"
                            class="mx-1 my-1 inline-flex min-w-[7.5rem] items-center justify-center rounded-xl border-2 px-3 py-1.5 align-middle text-[1.05rem] font-bold leading-none shadow-sm {{ ! $alpine && $picked !== '' ? 'border-teal/50 bg-teal/5 text-ink' : '' }} {{ ! $alpine && $picked === '' ? 'border-dashed border-ink/25 bg-mist text-muted' : '' }}"
                            :class="activeBlank === {{ Js::from($id) }} ? 'border-teal bg-teal/10 text-teal' : {{ $alpine ? "((blanks['{$id}'] || '') ? 'border-teal/50 bg-teal/5 text-ink' : 'border-dashed border-ink/25 bg-mist text-muted')" : "''" }}">
                            @if($alpine)
                                <span class="truncate" x-text="blanks[{{ Js::from($id) }}] || {{ Js::from('Blank '.$labelN) }}"></span>
                            @else
                                <span class="truncate">{{ $picked !== '' ? $picked : 'Blank '.$labelN }}</span>
                            @endif
                        </button>
                    @endif
                @else
                    <span>{{ $token }} </span>
                @endif
            @endforeach
        </p>
    </div>

    @if($kind !== 'l-fib')
        <div class="space-y-3">
            <p class="text-sm font-bold text-ink">Choose one word for each blank.</p>
            @foreach($blankRows as $row)
                @php $picked = $map[$row['id']] ?? ''; @endphp
                <div class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-4"
                     :class="activeBlank === {{ Js::from($row['id']) }} ? 'ring-2 ring-teal' : ''">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">{{ $row['label'] }}</p>
                        @if($alpine)
                            <p class="text-sm font-bold text-teal" x-show="blanks[{{ Js::from($row['id']) }}]" x-text="blanks[{{ Js::from($row['id']) }}]"></p>
                        @elseif($picked !== '')
                            <p class="text-sm font-bold text-teal">{{ $picked }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($row['choices'] as $choice)
                            @php
                                $on = $picked === $choice;
                                $taken = $kind === 'r-fib' && in_array($choice, $used, true) && ! $on;
                            @endphp
                            @if($alpine)
                                <button type="button"
                                    @click="activeBlank = {{ Js::from($row['id']) }}; blanks[{{ Js::from($row['id']) }}] = {{ Js::from($choice) }}"
                                    class="rounded-full border px-4 py-2 text-sm font-bold transition"
                                    :class="(blanks[{{ Js::from($row['id']) }}] || '') === {{ Js::from($choice) }} ? 'border-teal bg-teal text-white' : 'border-[rgba(15,40,80,0.12)] bg-mist text-ink hover:border-teal/40 hover:bg-teal/5'">
                                    {{ $choice }}
                                </button>
                            @else
                                <button type="button"
                                    @click="activeBlank = {{ Js::from($row['id']) }}"
                                    wire:click="setBlank({{ Js::from($qid) }}, {{ Js::from($row['id']) }}, {{ Js::from($choice) }})"
                                    @disabled($taken)
                                    class="rounded-full border px-4 py-2 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-40 {{ $on ? 'border-teal bg-teal text-white' : 'border-[rgba(15,40,80,0.12)] bg-mist text-ink hover:border-teal/40 hover:bg-teal/5' }}">
                                    {{ $choice }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
