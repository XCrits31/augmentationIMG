<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Результат обработки изображения</title>
</head>
<body>
<h1>Результат обработки</h1>

<h2>Исходное изображение:{{ $originalUrl }}"</h2>
<img src="{{ $originalUrl }}" alt="Исходное изображение" style="max-width: 45%;">
<h2>Применённые трансформации:</h2>
@if(!empty($transformations))
    <ul>
        @foreach($transformations as $transformation)
            <li>
                <strong>{{ ucfirst($transformation['transformation']) }}</strong>
                @if(!empty($transformation['parameters']))
                    <ul>
                        @foreach($transformation['parameters'] as $paramName => $paramValue)
                            <li>
                                {{ ucfirst($paramName) }}:
                                @if(is_bool($paramValue))
                                    {{ $paramValue ? 'true' : 'false' }}
                                @else
                                    {{ $paramValue }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    (No parameters)
                @endif
            </li>
        @endforeach
    </ul>
@else
    <p>Трансформации не были выбраны.</p>
@endif

<h2>Обработанное изображение :{{$outputPath}}</h2>
<img src="{{ $outputPath }}" alt="Обработанное изображение" style="max-width: 45%;">

<br><br>
<a href="/upload-image">Загрузить другое изображение</a>
</body>
</html>
