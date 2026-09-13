@extends('layouts.nvs-exam')

@section('title', $mock->title.' — Mock')

@section('content')
    @if($mock->runner === 'pte')
        <livewire:pte-mock-runner :mock-slug="$mock->slug" />
    @elseif($mock->runner === 'ielts-reading' || $mock->examQuestions()->exists() || $mock->exam_slug !== 'pte')
        <livewire:ielts-mock-runner :mock-slug="$mock->slug" />
    @else
        <div class="grid h-[100svh] place-items-center bg-mist px-4">
            <div class="max-w-md rounded-[1.5rem] border border-[rgba(15,40,80,0.08)] bg-paper p-8 text-center shadow-lg">
                <h1 class="font-display text-2xl font-bold text-ink">{{ $mock->title }}</h1>
                <p class="mt-2 text-muted">{{ $mock->subtitle }}</p>
                <a href="{{ route('mock-tests.index', ['exam' => $exam->slug]) }}" class="mt-6 inline-flex rounded-full bg-brand px-5 py-2.5 text-sm font-bold text-white">Back to list</a>
            </div>
        </div>
    @endif
@endsection
