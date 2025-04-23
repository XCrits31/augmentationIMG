@extends('layouts.app')

@section('title', 'Repeat Transformations')

@section('content')
    <div class="container mt-5">
        <h2>Загрузка изображений с готовыми трансформациями</h2>

        <form action="{{ route('images.process.multiple') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="images">Выберите изображения:</label>
                <input type="file" name="images[]" id="images" multiple required class="form-control">
            </div>

            <h4>Выбранные трансформации:</h4>
            @php
                $decoded = json_decode($presetTransformations, true);
            @endphp

            <ul class="list-group mb-3">
                @foreach($decoded as $transformation)
                    <li class="list-group-item">
                        <strong>{{ $transformation['transformation'] }}:</strong>
                        {{ json_encode($transformation['parameters'], JSON_UNESCAPED_UNICODE) }}
                    </li>
                @endforeach
            </ul>

            <input type="hidden" name="transformations_data" value="{{ $presetTransformations }}">

            <button type="submit" class="btn btn-primary">Отправить</button>
        </form>
    </div>
@endsection
