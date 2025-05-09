@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>transformations list</h1>

        @if($transformations->isEmpty())
            <p>no transformations yet</p>
        @else
            <div class="mb-3">
                <form action="{{ route('transformations.deleteAll') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete all transformations?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete All</button>
                </form>
            </div>
            <form id="download-form" method="POST" action="{{ route('transformations.downloadSelected') }}">
                @csrf
                <div class="mb-2">
                    <label for="file_type">Choose file type:</label>
                    <select name="file_type" id="file_type" class="form-select w-auto d-inline-block">
                        <option value="png">PNG</option>
                        <option value="pt">Tensor (.pt)</option>
                    </select>
                </div>
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
                <button type="submit" class="btn btn-success mt-3">Download Selected</button>
            </form>
        @endif
    </div>
    <script>
        document.getElementById('select-all').addEventListener('click', function() {
            const checked = this.checked;
            document.querySelectorAll('input[name="selected[]"]').forEach(cb => cb.checked = checked);
        });
    </script>
@endsection
