<?php

namespace App\Http\Controllers;

use App\Models\Transformation;
use Illuminate\Http\Request;
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
            'image' => 'required|image|max:10240', // максимум 10 МБ
            'transformations_data' => 'required|string',
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

        $args = [
            $pythonInterpreter,
            $scriptPath,
            $inputPath,
            $outputDir,
            json_encode($transformations),
        ];

        $process = new Process($args);
        $process->run();
        $output = json_decode($process->getOutput(), true);
        //dd($output);
        $processedPath = $output['processed'];
        $out = asset('storage/processed/' . basename($processedPath));
        if (!$process->isSuccessful()) {
            return back()->with('error', $process->getErrorOutput());
        }

        $output = json_decode($process->getOutput(), true);
        if (isset($output['error'])) {
            return back()->with('error', $output['error']);
        }

        $originalUrl = asset('storage/' . $storagePath);

        $transformation = Transformation::create([
            'image_name' => $originalName,
            'transformations' => json_encode($transformations),
            'output_image' => basename($processedPath),
        ]);
        return view('image-result', compact('originalUrl', 'out', 'transformations'));
    }
    public function showTransformations()
    {
        $transformations = Transformation::all();

        return view('transformations.index', compact('transformations'));
    }

}
