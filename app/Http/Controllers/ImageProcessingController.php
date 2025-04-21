<?php

namespace App\Http\Controllers;

use App\Events\ProcessImageCompleted;
use App\Jobs\ProcessImageJob;
use App\Models\Transformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ImageProcessingController extends Controller
{
    public function showUploadForm()
    {
        return view('upload-image');
    }

    public function processImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
            'transformations_data' => 'required|string',
            'repeat' => 'required|integer|min:1',
        ]);
        $uploadedFile = $request->file('image');
        $originalName = $uploadedFile->getClientOriginalName();
        $storagePath = $uploadedFile->storeAs('uploads', $originalName, 'public');
        $inputPath = storage_path('app/public/' . $storagePath);


        $outputDir = storage_path('app/public/processed');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $transformations = json_decode($request->input('transformations_data'), true);
        //dd($transformations);
        $scriptPath = base_path('scripts/monai_processing.py');

        $pythonInterpreter = base_path('venv/bin/python3');
        $repeatCount = (int)$request->input('repeat');
        $jobs = [];

        // Создаем задания на обработку изображения
        for ($i = 0; $i < $repeatCount; $i++) {
            $jobs[] = new ProcessImageJob($inputPath, $outputDir, $transformations);
        }

        // Группируем задания в batch
        Bus::batch($jobs)
            ->then(function () use ($originalName, $transformations) {
                // Отправляем уведомление через событие, когда все задачи выполнены
                event(new ProcessImageCompleted([
                    'message' => 'All transformations completed!',
                    'image_name' => $originalName,
                    'transformations' => $transformations,
                ]));
            })
            ->catch(function (\Throwable $e) {
                // Обрабатываем ошибки
                \Log::error('An error occurred in the batch: ' . $e->getMessage());
            })
            ->finally(function () {
                \Log::info('Batch processing finished.');
            })
            ->dispatch();

        return response()->json(['message' => 'Batch job started for image processing!']);
    }

    public function showTransformations()
    {
        $transformations = Transformation::all();

        return view('transformations.index', compact('transformations'));
    }

}
