import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

    window.Echo.channel('image-processing')
        .listen('.batch.completed', (event) => {
            console.log('🔥 Новое изображение:', event);

            const col = document.createElement('div');
            col.className = 'col';

            const card = document.createElement('div');
            card.className = 'card';

            const img = document.createElement('img');
            img.src = event.image_path;
            img.className = 'card-img-top';

            const cardBody = document.createElement('div');
            cardBody.className = 'card-body';

            const text = document.createElement('p');
            text.className = 'card-text';
            text.innerText = event.message;

            cardBody.appendChild(text);
            card.appendChild(img);
            card.appendChild(cardBody);
            col.appendChild(card);

            document.getElementById('results').prepend(col);
        });

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('WebSocket connected!');
});

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
