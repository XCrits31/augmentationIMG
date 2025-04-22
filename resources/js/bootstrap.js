import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY, // Убедитесь, что ключ совпадает с PUSHER_APP_KEY из .env
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER, // Совпадает с PUSHER_APP_CLUSTER из .env
    forceTLS: true,
});

/*
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    wsHost: 'xcrits31.su',
    wsPort: 6001,
    wssPort: 6001,
    forceTLS: true,
    encrypted: true,
    disableStats: true,
});*/

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('WebSocket connected!');
});

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
