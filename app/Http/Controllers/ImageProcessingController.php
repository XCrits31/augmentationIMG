<?php

namespace App\Http\Controllers;

use App\Events\ProcessImageCompleted;
use App\Jobs\ProcessImageJob;
use App\Models\Transformation;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Event\Code\Throwable;
use Illuminate\Bus\Batchable;
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
        $batchId = uniqid('batch_', true);
        // Создаем задания на обработку изображения
        for ($i = 0; $i < $repeatCount; $i++) {
            $jobs[] = new ProcessImageJob($inputPath, $outputDir, $transformations);
        }

        // Группируем задания в batch
        $batch = Bus::batch($jobs)
            ->then(function (Batch $batch) {
                // All jobs completed successfully...
            })
            ->catch(function (Batch $batch, Throwable $e) {
                // First batch job failure detected...
            })
            ->finally(function (Batch $batch) {
                // The batch has finished executing...
            })
            ->dispatch();
        $transformations = Transformation::all();
        return view('transformations.index', compact('transformations'));
    }

    public function showTransformations()
    {
        $transformations = Transformation::all();

        return view('transformations.index', compact('transformations'));
    }

}
