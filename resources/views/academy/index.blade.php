@extends('layouts.nvs-page')

@section('title', 'Academy — NextVerseSkill')

@section('content')
<div class="bg-[#eef4ff]">
    <section class="border-b border-[rgba(15,40,80,0.08)] bg-gradient-to-br from-[#0b1f44] via-[#123066] to-brand px-4 py-14 text-white sm:px-6">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-sky-200">{{ $site['academyName'] ?? 'NextVerseSkill Academy' }}</p>
            <h1 class="font-display mt-3 text-4xl font-extrabold sm:text-5xl">{{ $site['academyHeroTitle'] ?? 'Your exam Academy.' }}</h1>
            <p class="mt-4 max-w-2xl text-white/70">{{ $site['academyHeroSubtitle'] ?? 'One minute to tell us where you are — we open a full premium roadmap with weekly minutes and milestones.' }}</p>
        </div>
    </section>
    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
        <livewire:level-plan-wizard />
        @if(session('academy_plan'))
            <div class="mt-8 text-center">
                <a href="{{ route('academy.plan') }}" class="inline-flex rounded-full bg-gradient-to-r from-brand to-brand-deep px-6 py-3 text-sm font-bold text-white">View your saved plan →</a>
            </div>
        @endif
    </div>
</div>
@endsection
