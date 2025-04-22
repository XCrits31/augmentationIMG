import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.PUSHER_APP_KEY = "{{ env('PUSHER_APP_KEY') }}";
window.PUSHER_APP_CLUSTER = "{{ env('PUSHER_APP_CLUSTER') }}";

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: window.PUSHER_APP_KEY,
    cluster: window.PUSHER_APP_CLUSTER,
    forceTLS: true,
});

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
