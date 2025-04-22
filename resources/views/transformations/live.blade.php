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
    </style>
</head>
<body>
<h1>Pusher Image Feed</h1>
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

        card.appendChild(img);
        card.appendChild(p);
        document.getElementById('image-feed').prepend(card);
    });
</script>
</body>
</html>
