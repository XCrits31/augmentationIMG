<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ImageProcessingController extends Controller
{
    // Метод для отображения формы загрузки и выбора трансформаций
    public function showUploadForm()
    {
        return view('upload-image');
    }

    // Метод для обработки загруженного изображения
    public function processImage(Request $request)
    {
        // Валидируем входные данные: изображение и выбранный тип трансформации
        $request->validate([
            'image' => 'required|image|max:10240', // максимум 10 МБ
            'transformation' => 'required|string',
        ]);

        // Сохраняем загруженное изображение в storage/app/public/uploads
        $uploadedFile = $request->file('image');
        $originalName = $uploadedFile->getClientOriginalName();
        $storagePath = $uploadedFile->storeAs('uploads', $originalName, 'public');
        $inputPath = storage_path('app/public/' . $storagePath);

        // Выходная директория для обработанного изображения
        $outputDir = storage_path('app/public/processed');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Получаем выбранный тип трансформации (например: resize, grayscale, flip, default)
        $transformation = $request->input('transformation', 'default');

        // Путь к Python-скрипту, который осуществляет обработку (его надо разместить, например, в /scripts)
        $scriptPath = base_path('scripts/monai_processing.py');

        // Если используете системный python3 (или укажите путь к python из venv)
        $pythonInterpreter = base_path('venv/bin/python3');

        // Формируем аргументы для вызова python-скрипта:
        // Мы передаём: путь к входному файлу, путь к выходной директории и выбранную трансформацию
        // (Предполагается, что ваш скрипт сможет принимать дополнительный аргумент с типом трансформации)
        $args = [
            $pythonInterpreter,
            $scriptPath,
            $inputPath,
            $outputDir,
            $transformation
        ];

        $process = new Process($args);
        $process->run();

        // Если произошла ошибка при выполнении скрипта, возвращаем её
        if (!$process->isSuccessful()) {
            return back()->with('error', $process->getErrorOutput());
        }

        // Декодируем JSON-ответ от python-скрипта
        $output = json_decode($process->getOutput(), true);
        if (isset($output['error'])) {
            return back()->with('error', $output['error']);
        }

        // Формируем URL для исходного и обработанного изображений
        $originalUrl = asset('storage/' . $storagePath);
        // Здесь предполагается, что Python-скрипт сохраняет файл под именем <original>_processed.png
        $processedFilename = pathinfo($originalName, PATHINFO_FILENAME) . '_processed.png';
        $processedUrl = asset('scripts/out/' . $processedFilename);
        $basePath = 'storage/processed/';

        $baseName = pathinfo($originalName, PATHINFO_FILENAME); // получаем имя файла без расширения
        $outputPath = asset($basePath, $processedFilename);

        // Передаем данные в представление результата
        return view('image-result', compact('originalUrl', 'outputPath', 'transformation'));
    }
}
