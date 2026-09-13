@extends('layouts.nvs-page')

@section('title', $exam->short_name.' Dashboard — NextVerseSkill')

@section('content')
    @include(($theme ?? 'studio') === 'classic' ? 'dashboard.classic' : 'dashboard.studio')
@endsection
