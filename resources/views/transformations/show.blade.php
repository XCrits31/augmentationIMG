@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Transformation Details</h1>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">{{ $transformation->image_name }}</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Original Image:</strong><br>
                        <img src="{{ asset('storage/uploads/' . $transformation->image_name) }}" alt="Original" style="width: 100%;">
                    </div>
                    <div class="col-md-6">
                        <strong>Processed Image:</strong><br>
                        <img src="{{ asset('storage/processed/' . $transformation->output_image) }}" alt="Processed" style="width: 100%;">
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Transformations:</strong>
                    @if(!empty($transformation->transformations))
                        <ul>
                            @foreach(json_decode($transformation->transformations, true) as $index => $t)
                                <li>
                                    <strong>{{ ucfirst($t['transformation']) }}</strong>
                                    <ul>
                                        @foreach($t['parameters'] as $key => $value)
                                            <li>{{ ucfirst($key) }}: {{ is_bool($value) ? ($value ? 'true' : 'false') : $value }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>No transformations recorded.</p>
                    @endif
                </div>

                <a href="{{ route('transformations.index') }}" class="btn btn-primary">Back to List</a>
            </div>
        </div>
    </div>
@endsection
