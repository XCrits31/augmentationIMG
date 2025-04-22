@extends('layouts.app')

@section('title', 'Live Transformations')

@section('content')
    <h1>Live Results</h1>
    <div id="results" class="row row-cols-1 row-cols-md-3 g-4 mt-3">
        <!-- Сюда будут добавляться результаты -->
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
        });
    </script>
@endpush
