@extends('layouts.app')

@section('title', 'Live Transformations')

@section('content')
    <h1>Live Results</h1>
    <div id="results" class="row row-cols-1 row-cols-md-3 g-4 mt-3">
        <!-- Сюда будут добавляться результаты -->
    </div>
@endsection

@push('scripts')

@endpush
