<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-3xl font-extrabold">{{ $isNew ? 'Add '.$config['singular'] : 'Edit '.$config['singular'] }}</h1>
        @if(! $isNew)
            <button type="button" wire:click="deleteItem" wire:confirm="Delete this item?" class="text-sm font-bold text-rose-500">Delete</button>
        @endif
    </div>

    <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_360px]">
        <form wire:submit="save" class="space-y-6">
            @foreach($groups as $group => $fields)
                <section class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-5">
                    <h2 class="mb-4 text-xs font-extrabold uppercase tracking-[0.16em] text-muted">{{ $group }}</h2>
                    @if($group === 'Answers')
                        <p class="mb-4 rounded-xl bg-mist px-3 py-2 text-xs font-semibold text-muted">These boxes change with the task type. No JSON — tick the right answer or fill the blanks as you would on paper.</p>
                    @endif
                    <div class="space-y-4">
                        @foreach($fields as $field)
                            @php
                                $key = $field['key'];
                                $type = $field['type'];
                                $locked = !empty($field['lockedOnEdit']) && ! $isNew;
                            @endphp
                            <div>
                                <label class="block text-sm font-bold text-ink">{{ $field['label'] }}</label>
                                @if(!empty($field['hint']))
                                    <p class="mb-1 text-xs text-muted">{{ $field['hint'] }}</p>
                                @endif

                                @if($type === 'textarea' || $type === 'json')
                                    <textarea wire:model="form.{{ $key }}" rows="{{ $field['rows'] ?? 5 }}" @disabled($locked)
                                        class="mt-1 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 py-2 text-sm">{{ $form[$key] ?? '' }}</textarea>
                                @elseif($type === 'boolean')
                                    <label class="mt-2 inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" wire:model="form.{{ $key }}" class="rounded border-fog"> On
                                    </label>
                                @elseif($type === 'number')
                                    <input type="number" wire:model="form.{{ $key }}" @disabled($locked) class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm">
                                @elseif($type === 'select')
                                    @php $opts = $field['options'] ?? ($options[$field['optionsFrom'] ?? ''] ?? []); @endphp
                                    <select wire:model{{ !empty($field['live']) ? '.live' : '' }}="form.{{ $key }}" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm">
                                        @foreach($opts as $opt)
                                            <option value="{{ $opt['value'] }}" @selected((string) ($form[$key] ?? '') === (string) $opt['value'])>{{ $opt['label'] }}</option>
                                        @endforeach
                                    </select>
                                @elseif($type === 'multiselect')
                                    @php $opts = $options[$field['optionsFrom'] ?? ''] ?? []; $selected = (array) ($form[$key] ?? []); @endphp
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($opts as $opt)
                                            <label class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-bold {{ in_array($opt['value'], $selected, true) ? 'border-brand bg-brand-soft text-brand' : 'border-[rgba(15,40,80,0.08)]' }}">
                                                <input type="checkbox" wire:model="form.{{ $key }}" value="{{ $opt['value'] }}" class="hidden">
                                                {{ $opt['label'] }}
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif($type === 'color')
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($presets as $preset)
                                            <button type="button" wire:click="$set('form.{{ $key }}', '{{ $preset['value'] }}')"
                                                class="h-8 w-8 rounded-full border-2 {{ ($form[$key] ?? '') === $preset['value'] ? 'border-ink' : 'border-white' }} shadow"
                                                style="background: {{ $preset['value'] }}" title="{{ $preset['label'] }}"></button>
                                        @endforeach
                                    </div>
                                    <input type="text" wire:model.live.debounce.300ms="form.{{ $key }}" class="mt-2 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 font-mono text-sm">
                                @elseif($type === 'icon')
                                    <div class="mt-2 grid grid-cols-6 gap-2 sm:grid-cols-9">
                                        @foreach($icons as $icon => $label)
                                            <button type="button" wire:click="$set('form.{{ $key }}', '{{ $icon }}')" title="{{ $label }}"
                                                class="grid h-10 place-items-center rounded-xl border {{ ($form[$key] ?? '') === $icon ? 'border-brand bg-brand-soft text-brand' : 'border-[rgba(15,40,80,0.08)]' }}">
                                                <x-nvs.icon :name="$icon" class="h-4 w-4" />
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif($type === 'image')
                                    <div class="mt-2 overflow-hidden rounded-2xl border-2 border-dashed border-[rgba(15,40,80,0.12)] bg-mist/70">
                                        @if(!empty($form[$key]))
                                            <div class="relative">
                                                <img src="{{ $form[$key] }}" alt="" class="max-h-64 w-full bg-white object-contain">
                                                <div class="absolute inset-x-0 bottom-0 flex gap-2 bg-gradient-to-t from-black/55 to-transparent p-3">
                                                    <button type="button" wire:click="clearImage('{{ $key }}')" class="rounded-full bg-white/90 px-3 py-1.5 text-xs font-bold text-rose-500">Remove</button>
                                                </div>
                                            </div>
                                        @else
                                            <label class="flex cursor-pointer flex-col items-center gap-2 px-4 py-10 text-center" wire:click="startUpload('{{ $key }}')">
                                                <span class="text-sm font-bold">Drop an image or click to upload</span>
                                                <span class="text-xs text-muted">JPG, PNG, WEBP, GIF, SVG · up to 8MB</span>
                                                <input type="file" class="hidden" wire:model="upload" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml">
                                            </label>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <label class="inline-flex cursor-pointer rounded-full bg-ink px-3.5 py-2 text-xs font-bold text-white" wire:click="startUpload('{{ $key }}')">
                                            Upload
                                            <input type="file" class="hidden" wire:model="upload" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml">
                                        </label>
                                        <button type="button" wire:click="openLibrary('{{ $key }}')" class="rounded-full border border-[rgba(15,40,80,0.08)] bg-white px-3.5 py-2 text-xs font-bold">Choose from library</button>
                                    </div>
                                    <div wire:loading wire:target="upload" class="mt-1 text-xs font-semibold text-brand">Uploading…</div>
                                @elseif($type === 'modules')
                                    <div class="mt-2 space-y-2">
                                        @foreach($form['modules'] ?? [] as $i => $mod)
                                            <div class="grid gap-2 rounded-xl border border-[rgba(15,40,80,0.08)] p-3 sm:grid-cols-[1fr_8rem_5rem_auto]">
                                                <input wire:model="form.modules.{{ $i }}.name" class="h-10 rounded-lg border border-[rgba(15,40,80,0.08)] px-2 text-sm" placeholder="Name">
                                                <input wire:model="form.modules.{{ $i }}.count" class="h-10 rounded-lg border border-[rgba(15,40,80,0.08)] px-2 text-sm" placeholder="Count">
                                                <input type="number" wire:model="form.modules.{{ $i }}.value" class="h-10 rounded-lg border border-[rgba(15,40,80,0.08)] px-2 text-sm">
                                                <button type="button" wire:click="removeModule({{ $i }})" class="text-xs font-bold text-rose-500">Remove</button>
                                            </div>
                                        @endforeach
                                        <button type="button" wire:click="addModule" class="text-sm font-bold text-brand">+ Add module</button>
                                    </div>
                                @elseif($type === 'tags')
                                    <input type="text" wire:model="form.{{ $key }}" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm" placeholder="Comma-separated">
                                @elseif($type === 'mcq_options')
                                    <div class="mt-2 space-y-2">
                                        @foreach($form[$key] ?? [] as $i => $opt)
                                            <div class="flex items-start gap-2 rounded-xl border border-[rgba(15,40,80,0.08)] bg-mist/50 p-2.5">
                                                <button type="button" wire:click="setMcqCorrect({{ $i }})"
                                                    class="mt-1 grid h-8 w-8 shrink-0 place-items-center rounded-full border text-xs font-extrabold {{ !empty($opt['correct']) ? 'border-teal bg-teal text-white' : 'border-[rgba(15,40,80,0.12)] bg-white text-muted' }}"
                                                    title="{{ !empty($mcqMulti) ? 'Mark as correct' : 'This is the correct answer' }}">
                                                    {{ chr(65 + $i) }}
                                                </button>
                                                <input wire:model="form.{{ $key }}.{{ $i }}.text" class="h-10 min-w-0 flex-1 rounded-lg border border-[rgba(15,40,80,0.08)] bg-white px-3 text-sm" placeholder="Choice {{ chr(65 + $i) }}">
                                                <button type="button" wire:click="removeMcqOption({{ $i }})" class="mt-2 text-xs font-bold text-rose-500">Remove</button>
                                            </div>
                                        @endforeach
                                        <button type="button" wire:click="addMcqOption" class="text-sm font-bold text-brand">+ Add choice</button>
                                    </div>
                                @elseif($type === 'blank_rows')
                                    <div class="mt-2 space-y-3">
                                        <button type="button" wire:click="syncBlankRows" class="text-xs font-bold text-brand">Update blanks from passage</button>
                                        @foreach($form['blankRows'] ?? [] as $i => $row)
                                            <div class="rounded-xl border border-[rgba(15,40,80,0.08)] bg-white p-3">
                                                <div class="mb-2 flex items-center justify-between">
                                                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-muted">Blank {{ $i + 1 }}</p>
                                                    <button type="button" wire:click="removeBlankRow({{ $i }})" class="text-xs font-bold text-rose-500">Remove</button>
                                                </div>
                                                <label class="block text-xs font-bold text-ink">Correct word</label>
                                                <input wire:model="form.blankRows.{{ $i }}.correct" class="mt-1 h-10 w-full rounded-lg border border-teal/30 bg-teal/5 px-3 text-sm font-semibold" placeholder="right word">
                                                <label class="mt-2 block text-xs font-bold text-ink">Wrong words</label>
                                                <input wire:model="form.blankRows.{{ $i }}.others" class="mt-1 h-10 w-full rounded-lg border border-[rgba(15,40,80,0.08)] px-3 text-sm" placeholder="music, traffic, silence">
                                            </div>
                                        @endforeach
                                        <button type="button" wire:click="addBlankRow" class="text-sm font-bold text-brand">+ Add blank</button>
                                    </div>
                                @elseif($type === 'word_list')
                                    <div class="mt-2 space-y-2">
                                        @foreach($form[$key] ?? [] as $i => $word)
                                            <div class="flex gap-2">
                                                <input wire:model="form.{{ $key }}.{{ $i }}" class="h-10 flex-1 rounded-lg border border-[rgba(15,40,80,0.08)] px-3 text-sm" placeholder="Word {{ $i + 1 }}">
                                                <button type="button" wire:click="removeWord('{{ $key }}', {{ $i }})" class="text-xs font-bold text-rose-500">Remove</button>
                                            </div>
                                        @endforeach
                                        <button type="button" wire:click="addWord('{{ $key }}')" class="text-sm font-bold text-brand">+ Add word</button>
                                    </div>
                                @elseif($type === 'paragraph_list')
                                    <div class="mt-2 space-y-3">
                                        @foreach($form['paragraphRows'] ?? [] as $i => $row)
                                            <div class="rounded-xl border border-[rgba(15,40,80,0.08)] p-3">
                                                <div class="mb-2 flex items-center justify-between gap-2">
                                                    <p class="text-xs font-extrabold uppercase tracking-[0.14em] text-muted">Paragraph {{ chr(65 + $i) }} · correct position {{ $i + 1 }}</p>
                                                    <div class="flex gap-2">
                                                        <button type="button" wire:click="moveParagraph({{ $i }}, -1)" class="text-xs font-bold">Up</button>
                                                        <button type="button" wire:click="moveParagraph({{ $i }}, 1)" class="text-xs font-bold">Down</button>
                                                        <button type="button" wire:click="removeParagraph({{ $i }})" class="text-xs font-bold text-rose-500">Remove</button>
                                                    </div>
                                                </div>
                                                <textarea wire:model="form.paragraphRows.{{ $i }}.text" rows="3" class="w-full rounded-lg border border-[rgba(15,40,80,0.08)] px-3 py-2 text-sm"></textarea>
                                            </div>
                                        @endforeach
                                        <button type="button" wire:click="addParagraph" class="text-sm font-bold text-brand">+ Add paragraph</button>
                                    </div>
                                @elseif($type === 'hiw_words')
                                    <div class="mt-2 space-y-3">
                                        <textarea wire:model="form.hiwDraft" rows="3" class="w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 py-2 text-sm" placeholder="Paste the transcript students will see, then click Turn into words."></textarea>
                                        <button type="button" wire:click="splitHiw" class="rounded-full bg-ink px-3 py-1.5 text-xs font-bold text-white">Turn into words</button>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($form['hiwWords'] ?? [] as $i => $word)
                                                <button type="button" wire:click="toggleHiw({{ $i }})"
                                                    class="rounded-full border px-2.5 py-1 text-sm font-semibold {{ !empty($word['incorrect']) ? 'border-rose-400 bg-rose-50 text-rose-600 line-through' : 'border-[rgba(15,40,80,0.08)] bg-white text-ink' }}">
                                                    {{ $word['word'] ?? '' }}
                                                </button>
                                            @endforeach
                                        </div>
                                        <p class="text-xs text-muted">Red / struck words are the ones that do not match the audio.</p>
                                    </div>
                                @elseif($type === 'scored_options')
                                    <div class="mt-2 space-y-2">
                                        @foreach($form[$key] ?? [] as $i => $opt)
                                            <div class="grid gap-2 rounded-xl border border-[rgba(15,40,80,0.08)] p-3 sm:grid-cols-[1fr_6rem_auto]">
                                                <input wire:model="form.{{ $key }}.{{ $i }}.label" class="h-10 rounded-lg border border-[rgba(15,40,80,0.08)] px-3 text-sm" placeholder="Answer {{ chr(65 + $i) }}">
                                                <input type="number" wire:model="form.{{ $key }}.{{ $i }}.score" class="h-10 rounded-lg border border-[rgba(15,40,80,0.08)] px-3 text-sm" placeholder="Points">
                                                <button type="button" wire:click="removeScoredOption({{ $i }})" class="text-xs font-bold text-rose-500">Remove</button>
                                            </div>
                                        @endforeach
                                        <button type="button" wire:click="addScoredOption" class="text-sm font-bold text-brand">+ Add choice</button>
                                    </div>
                                @else
                                    <input type="text" wire:model="form.{{ $key }}" value="{{ $form[$key] ?? '' }}" @disabled($locked) class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm {{ $locked ? 'bg-mist text-muted' : '' }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            @if($error)
                <p class="text-sm font-semibold text-rose-500">{{ $error }}</p>
            @endif
            @if($saved)
                <p class="text-sm font-semibold text-emerald-600">Saved. The public website now uses this data.</p>
            @endif
            <button type="submit" class="rounded-full bg-ink px-6 py-3 text-sm font-bold text-white">Save changes</button>
        </form>

        <aside class="space-y-4">
            <div class="flex items-center justify-between gap-2">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-muted">Website preview</p>
                @if($liveHref)
                    <a href="{{ $liveHref }}" target="_blank" class="text-xs font-bold text-brand">Open live page</a>
                @endif
            </div>
            <div class="overflow-hidden rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-[#eef3fb] p-4">
                @include('livewire.admin.partials.preview-card', ['collection' => $config['key'], 'item' => $form])
            </div>
            @if($liveHref)
                <div class="overflow-hidden rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-white">
                    <div class="border-b border-[rgba(15,40,80,0.08)] px-3 py-2 text-[11px] font-semibold text-muted">{{ $liveHref }}</div>
                    <iframe title="Live website preview" src="{{ $liveHref }}" class="h-[420px] w-full bg-white"></iframe>
                </div>
            @endif
        </aside>
    </div>

    @if($showLibrary)
        <div class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4" wire:click="showLibrary = false">
            <div class="max-h-[80vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-5" wire:click.stop>
                <div class="mb-4 flex items-center justify-between">
                    <p class="font-display text-lg font-bold">Media library</p>
                    <button type="button" wire:click="$set('showLibrary', false)" class="text-sm font-bold">Close</button>
                </div>
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                    @forelse($mediaItems as $m)
                        <button type="button" wire:click="pickMedia('{{ $m->url }}')" class="overflow-hidden rounded-xl border border-[rgba(15,40,80,0.08)]">
                            <img src="{{ $m->url }}" alt="{{ $m->alt }}" class="h-24 w-full object-cover">
                            <p class="truncate px-2 py-1 text-[11px]">{{ $m->name }}</p>
                        </button>
                    @empty
                        <p class="col-span-4 text-sm text-muted">No uploads yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
