<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pusher Image Test</title>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <style>
        .image-card {
            margin: 1em;
            padding: 1em;
            border: 1px solid #ccc;
            max-width: 300px;
        }
        .image-card img {
            max-width: 100%;
        }
        .card {
            width: 100%;
            max-width: 300px;
            word-wrap: break-word;
            overflow: hidden;
        }

        .card-body {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal; /* или 'nowrap' если хочешь в одну строку */
            word-break: break-word;
        }
    </style>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/upload-image') }}">Single photo</a>
        <a class="navbar-brand" href="{{ url('/upload-images') }}">Main Creation</a>
        <a class="navbar-brand" href="{{ url('/transformations') }}">List</a>
    </div>
</nav>
<h5>Pusher Image Feed</h5>
<div id="image-feed" style="display: flex; flex-wrap: wrap;"></div>

<script>
    Pusher.logToConsole = true;

    const pusher = new Pusher('2b5d64a15fe154fa385d', {
        cluster: 'eu'
    });

    const channel = pusher.subscribe('image-processing');

    channel.bind('batch.completed', function(data) {
        console.log('Event received:', data);

        // Если сервер отправляет строку JSON, парсим
        if (typeof data === 'string') {
            data = JSON.parse(data);
        }

        const card = document.createElement('div');
        card.className = 'image-card';

        const img = document.createElement('img');
        img.src = data.image_path;
        img.alt = 'Image';

        const p = document.createElement('p');
        p.innerText = data.message || 'Без сообщения';

        const list = document.createElement('ul');
        data.transformations.forEach(t => {
            const item = document.createElement('li');
            item.innerText = `${t.transformation}: ${JSON.stringify(t.parameters)}`;
            list.appendChild(item);
        });
        card.appendChild(list);
        card.appendChild(img);
        card.appendChild(p);
        document.getElementById('image-feed').prepend(card);
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
