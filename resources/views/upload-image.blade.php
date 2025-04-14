<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка и обработка изображения</title>
</head>
<body>
<h1>Загрузите изображение и выберите трансформацию</h1>

@if(session('error'))
    <p style="color: red;">{{ session('error') }}</p>
@endif

<form action="/process-image" method="POST" enctype="multipart/form-data">
    @csrf
    <div>
        <label for="image">Выберите изображение:</label>
        <input type="file" name="image" id="image" required>
    </div>
    <br>
    <div>
        <label for="transformation">Выберите трансформацию:</label>
        <select name="transformation" id="transformation" required>
            <option value="resize">Resize</option>
            <option value="grayscale">Grayscale</option>
            <option value="flip">Flip</option>
            <option value="default" selected>Default</option>
        </select>
    </div>
    <br>
    <button type="submit">Обработать изображение</button>
</form>
</body>
</html>
