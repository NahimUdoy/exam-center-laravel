<div>
    <h1 class="font-display mb-6 text-3xl font-extrabold">Site & Academy</h1>
    <form wire:submit="save" class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-6">
            <section class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-5 space-y-4">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Brand</h2>
                <label class="block text-sm font-bold">Academy name<input wire:model="site.academyName" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
                <label class="block text-sm font-bold">Brand name<input wire:model="site.brandName" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
                <label class="block text-sm font-bold">Logo image
                    <input type="file" wire:model="upload" class="mt-2 text-sm" wire:change="$set('uploadField', 'logoImage')">
                </label>
                @if(!empty($site['logoImage']))
                    <img src="{{ $site['logoImage'] }}" alt="" class="h-12">
                @endif
            </section>
            <section class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-5 space-y-4">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Hero</h2>
                <label class="block text-sm font-bold">Hero title<input wire:model="site.heroTitle" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
                <label class="block text-sm font-bold">Hero subtitle<input wire:model="site.heroSubtitle" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
                <label class="block text-sm font-bold">Handwritten note<input wire:model="site.heroNote" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
            </section>
            <section class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-5 space-y-4">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Courses section</h2>
                <label class="block text-sm font-bold">Eyebrow<input wire:model="site.coursesEyebrow" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
                <label class="block text-sm font-bold">Title<input wire:model="site.coursesTitle" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
                <label class="block text-sm font-bold">Subtitle<textarea wire:model="site.coursesSubtitle" rows="3" class="mt-1 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 py-2 text-sm"></textarea></label>
                <label class="block text-sm font-bold">Ticker (one per line)<textarea wire:model="tickerText" rows="6" class="mt-1 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 py-2 text-sm"></textarea></label>
            </section>
            <section class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-5 space-y-4">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">How it works</h2>
                @foreach($site['howSteps'] ?? [] as $i => $step)
                    <div class="grid gap-2 rounded-xl border border-[rgba(15,40,80,0.08)] p-3 sm:grid-cols-2">
                        <input wire:model="site.howSteps.{{ $i }}.title" class="h-10 rounded-lg border px-2 text-sm">
                        <input wire:model="site.howSteps.{{ $i }}.color" class="h-10 rounded-lg border px-2 font-mono text-sm">
                        <textarea wire:model="site.howSteps.{{ $i }}.body" rows="2" class="sm:col-span-2 rounded-lg border px-2 py-1 text-sm"></textarea>
                    </div>
                @endforeach
            </section>
            <section class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-5 space-y-4">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Free items (one per line)</h2>
                <textarea wire:model="freeText" rows="6" class="w-full rounded-xl border px-3 py-2 text-sm"></textarea>
            </section>
            <section class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-5 space-y-4">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Academy</h2>
                <label class="block text-sm font-bold">Academy hero title<input wire:model="site.academyHeroTitle" class="mt-1 h-11 w-full rounded-xl border px-3 text-sm"></label>
                <label class="block text-sm font-bold">Academy subtitle<textarea wire:model="site.academyHeroSubtitle" rows="4" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"></textarea></label>
            </section>
            <section class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-white p-5 space-y-4">
                <h2 class="text-xs font-extrabold uppercase tracking-[0.16em] text-muted">Login news / events modal</h2>
                <p class="text-sm text-muted">Shown after a student logs in or registers. Turn it off, or swap the copy, whenever you have an event or announcement. Change the ID when you publish a new event so it shows again.</p>
                <label class="flex items-center gap-2 text-sm font-bold">
                    <input type="checkbox" wire:model.boolean="site.newsModal.enabled" class="rounded border-fog text-brand"> Show this modal after login
                </label>
                <label class="block text-sm font-bold">ID (change this when you publish a new event)<input wire:model="site.newsModal.id" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
                <label class="block text-sm font-bold">Eyebrow<input wire:model="site.newsModal.eyebrow" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
                <label class="block text-sm font-bold">Title<input wire:model="site.newsModal.title" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
                <label class="block text-sm font-bold">Body<textarea wire:model="site.newsModal.body" rows="3" class="mt-1 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 py-2 text-sm"></textarea></label>
                <label class="block text-sm font-bold">Button label<input wire:model="site.newsModal.cta_label" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
                <label class="block text-sm font-bold">Button URL<input wire:model="site.newsModal.cta_url" class="mt-1 h-11 w-full rounded-xl border border-[rgba(15,40,80,0.08)] px-3 text-sm"></label>
                @foreach($site['newsModal']['items'] ?? [] as $i => $item)
                    <div class="grid gap-2 rounded-xl border border-[rgba(15,40,80,0.08)] p-3">
                        <input wire:model="site.newsModal.items.{{ $i }}.title" class="h-10 rounded-lg border px-2 text-sm" placeholder="Card title">
                        <textarea wire:model="site.newsModal.items.{{ $i }}.body" rows="2" class="rounded-lg border px-2 py-1 text-sm" placeholder="Card body"></textarea>
                    </div>
                @endforeach
                <button type="button" wire:click="addNewsItem" class="rounded-full bg-mist px-4 py-2 text-xs font-bold text-ink">Add news card</button>
            </section>
            @if($error) <p class="text-sm font-semibold text-rose-500">{{ $error }}</p> @endif
            @if($saved) <p class="text-sm font-semibold text-emerald-600">Saved.</p> @endif
            <button class="rounded-full bg-ink px-6 py-3 text-sm font-bold text-white">Save site settings</button>
        </div>
        <aside>
            <div class="rounded-[1.5rem] border bg-white p-5">
                <p class="hand-note text-xl">{{ $site['heroNote'] ?? '' }}</p>
                <p class="font-display mt-3 text-2xl font-extrabold">{{ $site['heroTitle'] ?? '' }}</p>
                <p class="mt-2 text-sm text-muted">{{ $site['heroSubtitle'] ?? '' }}</p>
            </div>
        </aside>
    </form>
</div>
