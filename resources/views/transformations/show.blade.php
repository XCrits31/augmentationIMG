@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Transformation Details</h1>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">{{ $detail->output_image }}</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Original Image:</strong><br>
                        <img src="{{ asset('storage/uploads/' . $detail->image_name) }}" alt="Original" style="width: 100%;">
                    </div>
                    <div class="col-md-6">
                        <strong>Processed Image:</strong><br>
                        <img src="{{ asset('storage/processed/' . $detail->output_image) }}" alt="Processed" style="width: 100%;">
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Created at:</strong> {{ $detail->created_at }}
                </div>

                <div class="mb-3">
                    <strong>Download:</strong><br>
                    <a href="{{ asset('storage/processed/' . $detail->output_image) }}" class="btn btn-sm btn-outline-success" download>Download PNG</a>
                    @php
                        $tensorFile = preg_replace('/\.png$/', '.pt', $detail->output_image);
                    @endphp
                    <a href="{{ asset('storage/processed/' . $tensorFile) }}" class="btn btn-sm btn-outline-info">Download .pt</a>
                </div>

                <div class="mb-3">
                    <strong>Transformations:</strong>
                    @if(!empty($detail->transformations))
                        <ul>
                            @foreach(json_decode($detail->transformations, true) as $index => $t)
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
                <form action="{{ route('transformations.delete', $detail->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this transformation?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
                <div class="mb-3">
                    <form method="GET" action="{{ route('images.upload.withPreset') }}">
                        <input type="hidden" name="transformations" value="{{ $detail->transformations }}">
                        <button type="submit" class="btn btn-primary">Use Again</button>
                    </form>
                </div>
                <h3>Same transformations</h3>

                <form id="download-form" method="POST" action="{{ route('transformations.downloadSelected') }}">
                    @csrf
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>id</th>
                            <th>image name</th>
                            <th>original image</th>
                            <th>transformations</th>
                            <th>image</th>
                            <th>Created at</th>
                            <th>Download</th>
                            <th>Use Again</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($transformations as $transformation)
                            <tr>
                                <td><input type="checkbox" name="selected[]" value="{{ $transformation->output_image }}"></td>
                                <td>{{ $transformation->id }}</td>
                                <td><a href="{{ route('transformations.show', $transformation->id) }}" class="btn btn-info btn-sm">{{ $transformation->output_image }}</a></td>
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
                                        <button type="submit" class="btn btn-danger">png</button>
                                    </a>
                                    @php
                                        $tensorFile = preg_replace('/\.png$/', '.pt', $transformation->output_image);
                                    @endphp
                                    <a href="{{ asset('storage/processed/' . $tensorFile) }}"> <button type="submit" class="btn btn-danger"> Tensor (.pt) </button> </a>
                                </td>
                                <td>
                                    <form method="GET" action="{{ route('images.upload.withPreset') }}">
                                        <input type="hidden" name="transformations" value="{{ $transformation->transformations }}">
                                        <button type="submit" class="btn btn-outline-primary btn-sm">use</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-success mt-3">Download Selected PNGs</button>
                </form>

                <a href="{{ route('transformations.index') }}" class="btn btn-primary">Back to List</a>
            </div>
        </div>
    </div>
@endsection
