<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Результат обработки изображения</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            text-align: center;
        }

        h1, h2 {
            background-color: #343a40;
            color: #fff;
            margin: 0;
            padding: 20px 0;
        }

        h2 {
            background-color: #495057;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            background: #fff;
            border: 1px solid #ddd;
            margin: 10px auto;
            padding: 10px;
            border-radius: 4px;
            text-align: left;
            max-width: 50%;
        }

        ul ul {
            margin-left: 20px;
        }

        img {
            display: block;
            margin: 20px auto;
            max-width: 80%;
            border: 2px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        a {
            display: inline-block;
            margin: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
        }

        a:hover {
            background: #0056b3;
        }

        .no-transformations {
            color: red;
        }
    </style>
</head>
<body>
<h1>Результат обработки</h1>

<h2>Исходное изображение</h2>
<img src="{{ $originalUrl }}" alt="Исходное изображение">

<h2>Применённые трансформации</h2>
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
                    (Нет параметров)
                @endif
            </li>
        @endforeach
    </ul>
@else
    <p class="no-transformations">Трансформации не были выбраны.</p>
@endif

<h2>Обработанное изображение</h2>
<img src="{{ $out }}" alt="Обработанное изображение">

<a href="/upload-image">Загрузить другое изображение</a>
</body>
</html>
