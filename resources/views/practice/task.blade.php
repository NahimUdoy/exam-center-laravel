@extends('layouts.nvs-page')

@section('title', $taskType->name.' — Practice')

@section('content')
    @include(($theme ?? 'studio') === 'classic' ? 'practice.task-classic' : 'practice.task-studio')
@endsection
