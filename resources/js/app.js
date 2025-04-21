import './bootstrap';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: window.PUSHER_APP_KEY,
    cluster: window.PUSHER_APP_CLUSTER,
    forceTLS: true,
});

// События
window.Echo.channel('image-processing')
    .listen('.batch.completed', (event) => {
        console.log('Batch Completed:', event.message);
        alert(event.message);
    });
