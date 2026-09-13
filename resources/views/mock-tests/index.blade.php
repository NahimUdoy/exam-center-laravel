@extends('layouts.nvs-page')

@section('title', 'Mock Tests — NextVerseSkill')

@section('content')
    @include(($theme ?? 'studio') === 'classic' ? 'mock-tests.classic' : 'mock-tests.studio')
@endsection
