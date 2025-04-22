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
        console.log('Batch Completed (from JS):', event);

        const box = document.createElement('div');
        box.className = 'alert alert-success mt-4';
        box.innerText = event.data.message || 'All transformations completed!';
        document.querySelector('.container')?.prepend(box);
        setTimeout(() => box.remove(), 5000);
    });
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('WebSocket connected!');
});

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
