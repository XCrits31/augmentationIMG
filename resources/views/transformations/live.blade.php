@extends('layouts.app')

@section('title', 'Live Transformations')

@section('content')
    <h1>Live Results</h1>
    <div id="results" class="row row-cols-1 row-cols-md-3 g-4 mt-3">
        <!-- Куда добавляются карточки -->
    </div>
@endsection

@push('scripts')
    <script>
        window.Echo.channel('image-processing')
            .listen('.batch.completed', (event) => {
                const data = event.data;

                if (!data || !data.image_path) {
                    console.warn('❗ Нет картинки в событии:', data);
                    return;
                }

                console.log('🖼 Получено изображение:', data.image_path);

                const col = document.createElement('div');
                col.className = 'col';

                const card = document.createElement('div');
                card.className = 'card';

                const img = document.createElement('img');
                img.src = data.image_path;
                img.alt = 'Обработанное изображение';
                img.className = 'card-img-top';

                const body = document.createElement('div');
                body.className = 'card-body';
                body.innerText = data.message;

                card.appendChild(img);
                card.appendChild(body);
                col.appendChild(card);

                document.getElementById('results')?.prepend(col);
            });
    </script>
@endpush
