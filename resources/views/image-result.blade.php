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

<h2>Обработанное изображение ({{ $transformation }}):{{outputPath}}</h2>
<img src="{{ outputPath }}" alt="Обработанное изображение" style="max-width: 45%;">

<br><br>
<a href="/upload-image">Загрузить другое изображение</a>
</body>
</html>
