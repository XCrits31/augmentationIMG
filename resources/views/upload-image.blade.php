<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка и трансформация изображения</title>
    <script>
        // Показ или скрытие полей для параметров в зависимости от выбранной трансформации
        function updateFormFields() {
            const transformationFields = document.querySelectorAll(".parameters");
            transformationFields.forEach(field => field.style.display = "none"); // Скрываем все поля

            const selectedTransforms = Array.from(document.querySelectorAll(".transformation:checked"));
            selectedTransforms.forEach(selected => {
                const paramDiv = document.getElementById(selected.value + "-parameters");
                if (paramDiv) {
                    paramDiv.style.display = "block"; // Показываем параметры для выбранной трансформации
                }
            });
        }

        // Сбор всех выбранных трансформаций и их параметров
        function collectTransformations() {
            const transformations = [];

            // Получаем все выбранные трансформации
            const selectedTransforms = Array.from(document.querySelectorAll(".transformation:checked"));

            selectedTransforms.forEach(checkbox => {
                const transformName = checkbox.value;
                const params = {};

                // Для каждой трансформации собираем параметры (если есть)
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

                // Добавляем трансформацию и её параметры в массив
                transformations.push({ transformation: transformName, parameters: params });
            });

            // Отправляем собранные данные в скрытое поле формы
            const hiddenField = document.getElementById("transformations-data");
            if (hiddenField) {
                hiddenField.value = JSON.stringify(transformations);
            }
        }

        // При отправке формы вызываем сбор трансформаций
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector("form");
            if (form) {
                form.addEventListener("submit", collectTransformations);
            }
        });
    </script>
</head>
<body>
<h1>Загрузите изображение и выберите трансформации</h1>

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

    <!-- Параметры для Adjust Contrast -->
    <div id="contrast-parameters" class="parameters" style="display: none;">
        <h4>Parameters for Adjust Contrast</h4>
        <label for="contrast-prob">Probability:</label>
        <input type="number" step="0.1" name="contrast[prob]" id="contrast-prob">
        <label for="contrast-gamma">Gamma:</label>
        <input type="number" step="0.1" name="contrast[gamma]" id="contrast-gamma">
    </div>

    <!-- Параметры для Flip -->
    <div id="flip-parameters" class="parameters" style="display: none;">
        <h4>Parameters for Flip</h4>
        <label for="flip-prob">Probability:</label>
        <input type="number" step="0.1" name="flip[prob]" id="flip-prob">
        <label for="flip-axis">Axis:</label>
        <input type="number" name="flip[axis]" id="flip-axis">
    </div>

    <!-- Параметры для Rotate -->
    <div id="rotate-parameters" class="parameters" style="display: none;">
        <h4>Parameters for Rotate</h4>
        <label for="rotate-range">Range:</label>
        <input type="number" step="1" name="rotate[range]" id="rotate-range">
        <label for="rotate-prob">Probability:</label>
        <input type="number" step="0.1" name="rotate[prob]" id="rotate-prob">
        <label for="rotate-keep">Keep Size:</label>
        <input type="checkbox" name="rotate[keep_size]" id="rotate-keep">
    </div>

    <!-- Параметры для Zoom -->
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
        <label for="noise-prob">Probability:</label>
        <input type="number" step="0.1" min="0" max="1" name="noise[prob]" id="noise-prob">
    </div>

    <div id="scale_intensity-parameters" class="parameters" style="display: none;">
        <h4>Parameters for Scale Intensity</h4>
        <label for="scale-min">Min Factor:</label>
        <input type="number" step="0.1" name="scale_intensity[min]" id="scale-min">
        <label for="scale-max">Max Factor:</label>
        <input type="number" step="0.1" name="scale_intensity[max]" id="scale-max">
        <label for="scale-prob">Probability:</label>
        <input type="number" step="0.1" min="0" max="1" name="scale_intensity[prob]" id="scale-prob">
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
        <label for="elastic-prob">Probability:</label>
        <input type="number" step="0.1" min="0" max="1" name="elastic[prob]" id="elastic-prob">
    </div>



    <!-- Скрытое поле для передачи всех трансформаций -->
    <input type="hidden" id="transformations-data" name="transformations_data" value="">

    <button type="submit">Обработать</button>
</form>
</body>
</html>
