@php
    use App\Support\NewsModal;
    $news = NewsModal::payload();
@endphp
@if(auth()->check() && session('show_news_modal') && $news)
<div x-data="{ open: true }" x-show="open" x-cloak class="nvs-modal-layer fixed inset-0 z-[200] flex items-end justify-center p-0 sm:items-center sm:p-6" role="dialog" aria-modal="true">
    <button type="button" class="absolute inset-0 bg-[#0b1f44]/65 backdrop-blur-md" @click="open=false" aria-label="Close"></button>
    <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-t-[1.75rem] border border-white/20 bg-white shadow-premium-lg sm:rounded-[1.75rem]">
        <div class="relative overflow-hidden bg-gradient-to-br from-[#0b1f44] via-[#123066] to-brand px-6 pb-8 pt-6 text-white sm:px-8">
            <button type="button" @click="open=false" class="absolute right-4 top-4 rounded-full border border-white/20 bg-white/10 p-2 text-white/80">✕</button>
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-sky-200">{{ $news['eyebrow'] }}</p>
            <h2 class="font-display mt-3 text-2xl font-extrabold sm:text-3xl">{{ $news['title'] }}</h2>
            <p class="mt-2 text-sm text-white/70">{{ $news['body'] }}</p>
        </div>
        @if(!empty($news['items']))
            <div class="space-y-2.5 px-5 py-5 sm:px-6 text-sm">
                @foreach($news['items'] as $item)
                    <div class="rounded-2xl border border-[rgba(15,40,80,0.08)] bg-mist/50 p-3.5">
                        <p class="font-bold text-ink">{{ $item['title'] ?? '' }}</p>
                        <p class="mt-0.5 text-muted">{{ $item['body'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        @endif
        <div class="flex flex-col gap-2.5 border-t border-[rgba(15,40,80,0.08)] bg-mist/40 px-5 py-4 sm:flex-row sm:px-6">
            @if(!empty($news['cta_url']))
                <a href="{{ url($news['cta_url']) }}" class="inline-flex flex-1 items-center justify-center rounded-full bg-gradient-to-r from-brand to-brand-deep px-5 py-3 text-sm font-bold text-white">{{ $news['cta_label'] ?? 'Continue' }}</a>
            @endif
            <button type="button" @click="open=false" class="inline-flex flex-1 items-center justify-center rounded-full border border-[rgba(15,40,80,0.08)] bg-white px-5 py-3 text-sm font-bold text-ink">Got it</button>
        </div>
    </div>
</div>
@php session()->forget('show_news_modal'); @endphp
@endif
