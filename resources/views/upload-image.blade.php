@extends('layouts.app')

@section('content')
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            color: #333;
            text-align: center;
        }

        h1 {
            background-color: #343a40;
            color: #fff;
            padding: 20px 0;
            margin: 0;
        }

        form {
            display: inline-block;
            background: #fff;
            padding: 20px;
            margin: 20px auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        form div {
            margin-bottom: 15px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        input[type="file"], input[type="text"] {
            font-size: 14px;
            padding: 8px;
            margin-top: 10px;
            width: 100%;
            box-sizing: border-box;
        }

        input[type="checkbox"] {
            margin-right: 10px;
        }

        button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .parameters {
            display: none;
            margin-left: 25px;
            padding: 10px;
            background: #f1f1f1;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .error {
            color: red;
        }
    </style>

    <script>
        function updateFormFields() {
            const transformationFields = document.querySelectorAll(".parameters");
            transformationFields.forEach(field => field.style.display = "none");

            const selectedTransforms = Array.from(document.querySelectorAll(".transformation:checked"));
            selectedTransforms.forEach(selected => {
                const paramDiv = document.getElementById(selected.value + "-parameters");
                if (paramDiv) {
                    paramDiv.style.display = "block";
                }
            });
        }

        function collectTransformations() {
            const transformations = [];

            const selectedTransforms = Array.from(document.querySelectorAll(".transformation:checked"));

            selectedTransforms.forEach(checkbox => {
                const transformName = checkbox.value;
                const params = {};

                const paramDiv = document.getElementById(transformName + "-parameters");
                if (paramDiv) {
                    const inputs = paramDiv.querySelectorAll("input");
                    inputs.forEach(input => {
                        if (input.type === "checkbox") {
                            params[input.name.split('[')[1].replace(']', '')] = input.checked; // Для чекбоксов
                        } else {
                            params[input.name.split('[')[1].replace(']', '')] = input.value; // Для других input
                        }
                    });
                }

                transformations.push({ transformation: transformName, parameters: params });
            });

            const hiddenField = document.getElementById("transformations-data");
            if (hiddenField) {
                hiddenField.value = JSON.stringify(transformations);
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector("form");
            if (form) {
                form.addEventListener("submit", collectTransformations);
            }
        });
    </script>
<h1>transformations</h1>

@if(session('error'))
    <p style="color: red;">{{ session('error') }}</p>
@endif

<form action="/process-image" method="POST" enctype="multipart/form-data">
    @csrf
    <div>
        <label for="image">Выберите изображение:</label>
        <input type="file" name="image" id="image" required>
    </div>
    <br>

    <h3>Выберите трансформации:</h3>
    <div>
        <label><input type="checkbox" class="transformation" name="transformations[]" value="contrast" onclick="updateFormFields()"> Adjust Contrast</label>
        <label><input type="checkbox" class="transformation" name="transformations[]" value="flip" onclick="updateFormFields()"> Flip</label>
        <label><input type="checkbox" class="transformation" name="transformations[]" value="rotate" onclick="updateFormFields()"> Rotate</label>
        <label><input type="checkbox" class="transformation" name="transformations[]" value="zoom" onclick="updateFormFields()"> Zoom</label>
        <label><input type="checkbox" class="transformation" name="transformations[]" value="noise" onclick="updateFormFields()"> Gaussian Noise</label>
        <label><input type="checkbox" class="transformation" name="transformations[]" value="scale_intensity" onclick="updateFormFields()"> Scale Intensity</label>
        <label><input type="checkbox" class="transformation" name="transformations[]" value="elastic" onclick="updateFormFields()"> Elastic Transform</label>
    </div>
    <br>


    <div id="contrast-parameters" class="parameters" style="display: none;">
        <h4>Parameters for Adjust Contrast</h4>
        <label for="contrast-gamma">Gamma:</label>
        <input type="number" step="0.1" name="contrast[gamma]" id="contrast-gamma">
    </div>


    <div id="flip-parameters" class="parameters" style="display: none;">
        <h4>Parameters for Flip</h4>
        <label for="flip-axis">Axis:</label>
        <input type="number" name="flip[axis]" id="flip-axis">
    </div>

    <div id="rotate-parameters" class="parameters" style="display: none;">
        <h4>Parameters for Rotate</h4>
        <label for="rotate-range">Range:</label>
        <input type="number" step="1" name="rotate[range]" id="rotate-range">
        <label for="rotate-keep">Keep Size:</label>
        <input type="checkbox" name="rotate[keep_size]" id="rotate-keep">
    </div>

    <div id="zoom-parameters" class="parameters" style="display: none;">
        <h4>Parameters for Zoom</h4>
        <label for="zoom-zoom">Zoom:</label>
        <input type="number" step="0.1" name="zoom[zoom]" id="zoom-zoom">
    </div>

    <div id="noise-parameters" class="parameters" style="display: none;">
        <h4>Parameters for Gaussian Noise</h4>
        <label for="noise-mean">Mean:</label>
        <input type="number" step="0.1" name="noise[mean]" id="noise-mean">
        <label for="noise-std">Standard Deviation:</label>
        <input type="number" step="0.1" name="noise[std]" id="noise-std">
    </div>

    <div id="scale_intensity-parameters" class="parameters" style="display: none;">
        <h4>Parameters for Scale Intensity</h4>
        <label for="scale-min">Min Factor:</label>
        <input type="number" step="0.1" name="scale_intensity[min]" id="scale-min">
        <label for="scale-max">Max Factor:</label>
        <input type="number" step="0.1" name="scale_intensity[max]" id="scale-max">
    </div>


    <div id="elastic-parameters" class="parameters" style="display: none;">
        <h4>Parameters for Elastic Transformation</h4>
        <label for="elastic-min-mag">Min Magnitude:</label>
        <input type="number" step="0.1" name="elastic[min_el]" id="elastic-min-mag">
        <label for="elastic-max-mag">Max Magnitude:</label>
        <input type="number" step="0.1" name="elastic[max_el]" id="elastic-max-mag">
        <label for="elastic-space1">Spacing Dimension 1:</label>
        <input type="number" step="1" name="elastic[space1]" id="elastic-space1">
        <label for="elastic-space2">Spacing Dimension 2:</label>
        <input type="number" step="1" name="elastic[space2]" id="elastic-space2">
    </div>



    <input type="hidden" id="transformations-data" name="transformations_data" value="">

    <button type="submit">Submit</button>
</form>
@endsection
