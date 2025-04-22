@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>transformations list</h1>

        @if($transformations->isEmpty())
            <p>no transformations yet</p>
        @else
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>id</th>
                    <th>image name</th>
                    <th>original image</th>
                    <th>transformations</th>
                    <th>image</th>
                    <th>Created at</th>
                    <th>Download</th>
                    <th>Delete</th>
                </tr>
                </thead>
                <tbody>
                @foreach($transformations as $transformation)
                    <tr>
                        <td>{{ $transformation->id }}</td>
                        <td>{{ $transformation->image_name }}</td>
                        <td>
                            <img src="{{ asset('storage/uploads/' . $transformation->image_name) }}" alt="original Image" style="width: 100px;">
                        </td>
                        <td>@if(!empty($transformation->transformations))
                            <ul>
                                @foreach(json_decode($transformation->transformations, true) as $index => $transformationItem)
                                    <li>
                                        <strong>Transformation {{ $index + 1 }}:</strong> {{ ucfirst($transformationItem['transformation']) }}
                                        <ul>
                                            @foreach($transformationItem['parameters'] as $key => $value)
                                                <li>{{ ucfirst($key) }}: {{ is_bool($value) ? ($value ? 'true' : 'false') : $value }}</li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        @endif</td>
                        <td>
                            <img src="{{ asset('storage/processed/' . $transformation->output_image) }}" alt="Output Image" style="width: 100px;">
                        </td>
                        <td>{{ $transformation->created_at }}</td>
                        <td> <a href="{{ asset('storage/processed/' . $transformation->output_image) }}" download>
                                Download
                            </a>
                        </td>
                        <td>
                            <form action="{{ route('transformations.delete', $transformation->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this transformation?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

@endsection
