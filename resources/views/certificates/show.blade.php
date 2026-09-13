@extends('layouts.nvs-page')

@section('title', $certificate->title.' — Certificate')

@push('head')
    @include('certificates.partials.fonts')
@endpush

@section('content')
@php
    $earned = $certificate->status === 'earned';
    $listUrl = route('certificates.index', ['exam' => $exam->slug]);
@endphp
<div class="min-h-screen {{ $earned ? 'bg-[linear-gradient(180deg,#0b1220_0%,#152033_40%,#1a2438_100%)]' : 'bg-aurora' }} print:bg-white">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:py-10">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
            <a href="{{ $listUrl }}" class="inline-flex items-center gap-2 text-sm font-semibold {{ $earned ? 'text-white/70 hover:text-white' : 'text-brand' }}">
                <x-nvs.icon name="ArrowLeft" class="h-4 w-4" />
                All certificates
            </a>
            @if($earned)
                <div class="flex gap-2">
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/5 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10">
                        <x-nvs.icon name="Printer" class="h-4 w-4" />
                        Print
                    </button>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold text-ink" style="background: linear-gradient(90deg, #e8d5a3, #c4a35a)">
                        <x-nvs.icon name="Download" class="h-4 w-4" />
                        Save PDF
                    </button>
                </div>
            @endif
        </div>

        @if($earned)
            <div class="animate-rise">
                <x-nvs.premium-certificate :certificate="$certificate" :exam="$exam" />
            </div>
            <p class="mt-6 text-center text-xs text-white/45 print:hidden">Tip: use Save PDF / Print for a high-quality shareable copy.</p>
        @else
            <div class="mx-auto max-w-lg rounded-[1.75rem] border border-[rgba(15,40,80,0.08)] bg-paper px-6 py-14 text-center shadow-premium-lg">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-full" style="background: conic-gradient(from 20deg, #f0e0a8, #9a7b2f, #d4af37)">
                    <div class="grid h-12 w-12 place-items-center rounded-full bg-[#0a0e17] text-[#f0e0a8]">
                        <x-nvs.icon name="Lock" class="h-6 w-6" />
                    </div>
                </div>
                <p class="font-cert-display mt-5 text-[10px] uppercase tracking-[0.4em] text-[#9a7b2f]">{{ ucfirst($certificate->status) }} credential</p>
                <h1 class="font-display mt-2 text-2xl font-bold text-ink">{{ $certificate->title }}</h1>
                <p class="mt-2 text-sm text-muted">{{ $certificate->subtitle }}</p>
                <a href="{{ $certificate->status === 'pending' ? route('mock-tests.index', ['exam' => $exam->slug]) : route('dashboard', ['exam' => $exam->slug]) }}" class="mt-6 inline-flex rounded-full bg-ink px-5 py-2.5 text-sm font-bold text-white">
                    {{ $certificate->status === 'pending' ? 'Finish to unlock' : 'Complete requirements' }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
