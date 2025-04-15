@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Список трансформаций</h1>

        @if($transformations->isEmpty())
            <p>Нет сохранённых трансформаций.</p>
        @else
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Имя изображения</th>
                    <th>Трансформации</th>
                    <th>Обработанное изображение</th>
                    <th>Дата создания</th>
                </tr>
                </thead>
                <tbody>
                @foreach($transformations as $transformation)
                    <tr>
                        <td>{{ $transformation->id }}</td>
                        <td>{{ $transformation->image_name }}</td>
                        @if(!empty($transformations))
                            <ul>
                                @foreach(json_decode($transformations, true) as $index => $transformation)
                                    <li>
                                        <strong>Transformation {{ $index + 1 }}:</strong> {{ ucfirst($transformation['transformation']) }}
                                        <ul>
                                            @foreach($transformation['parameters'] as $key => $value)
                                                <li>{{ ucfirst($key) }}: {{ is_bool($value) ? ($value ? 'true' : 'false') : $value }}</li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <td>
                            <img src="{{ asset('storage/processed/' . $transformation->output_image) }}" alt="Output Image" style="width: 100px;">
                        </td>
                        <td>{{ $transformation->created_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
