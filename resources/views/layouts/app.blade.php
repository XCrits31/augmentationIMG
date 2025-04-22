<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laravel')</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/upload-image') }}">Main Creation</a>
        <a class="navbar-brand" href="{{ url('/transformations') }}">List</a>
    </div>
</nav>

<div class="container mt-4">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
<script>

    // События
    window.Echo.channel('image-processing')
        .listen('.batch.completed', (event) => {
            console.log('Batch Completed:', event);

            // Создание простого уведомления на странице
            const notificationBox = document.createElement('div');
            notificationBox.className = 'alert alert-success mt-4';
            notificationBox.innerText = event.data.message || 'All transformations completed!';

            document.querySelector('.container').prepend(notificationBox);

            // Добавление автоудаления уведомления
            setTimeout(() => notificationBox.remove(), 5000);
        });
</script>
</body>
</html>
