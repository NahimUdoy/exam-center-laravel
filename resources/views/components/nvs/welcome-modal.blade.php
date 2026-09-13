@if(session('show_welcome_promo'))
<div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" role="dialog">
    <button type="button" class="absolute inset-0 bg-[#0b1f44]/65 backdrop-blur-md" @click="open=false" aria-label="Close"></button>
    <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-[1.75rem] border border-white/20 bg-white shadow-premium-lg animate-rise">
        <div class="relative overflow-hidden bg-gradient-to-br from-[#0b1f44] via-[#123066] to-brand px-6 pb-8 pt-6 text-white sm:px-8">
            <button type="button" @click="open=false" class="absolute right-4 top-4 rounded-full border border-white/20 bg-white/10 p-2 text-white/80">✕</button>
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-sky-200">Welcome to NextVerseSkill</p>
            <h2 class="font-display mt-3 text-2xl font-extrabold sm:text-3xl">You're in. Let's make today count.</h2>
            <p class="mt-2 text-sm text-white/70">Premium access unlocked — and right now, <span class="font-bold text-white">everything is free</span>.</p>
            <div class="mt-5 inline-flex rounded-full bg-emerald-400 px-3.5 py-1.5 text-xs font-extrabold text-[#064e3b]">100% FREE — No hidden cost</div>
        </div>
        <div class="space-y-2.5 px-5 py-5 sm:px-6 text-sm">
            <div class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist/50 p-3.5 font-semibold">Everything is FREE now — practice, mocks, plans & certificates.</div>
            <div class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist/50 p-3.5 font-semibold">NextVerseSkill Academy plan — 1-minute level check → full roadmap.</div>
            <div class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist/50 p-3.5 font-semibold">Real exam patterns for IELTS · PTE · MBA · BBA.</div>
            <div class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist/50 p-3.5 font-semibold">Premium certificates as you grow.</div>
        </div>
        <div class="flex flex-col gap-2.5 border-t border-[rgba(15,40,80,0.08)] bg-mist/40 px-5 py-4 sm:flex-row sm:px-6">
            <a href="{{ url('/#plan') }}" class="inline-flex flex-1 items-center justify-center rounded-full bg-gradient-to-r from-brand to-brand-deep px-5 py-3 text-sm font-bold text-white">Build Academy plan</a>
            <button type="button" @click="open=false" class="inline-flex flex-1 items-center justify-center rounded-full border border-[rgba(15,40,80,0.08)] bg-white px-5 py-3 text-sm font-bold text-ink">Start practicing</button>
        </div>
    </div>
</div>
@php session()->forget('show_welcome_promo'); @endphp
@endif
