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

class ImagesProcessingController extends Controller
{
    public function showUploadForm()
    {
        return view('upload-images');
    }

    public function processMultipleImages(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:10240',
            'transformations_data' => 'required|string',
        ]);
        $transformations = json_decode($request->input('transformations_data'), true);

        $outputDir = storage_path('app/public/processed');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        foreach ($request->file('images') as $uploadedFile) {
            $filename =  $uploadedFile->getClientOriginalName();
            $storagePath = $uploadedFile->storeAs('uploads', $filename, 'public');
            $inputPath = storage_path('app/public/' . $storagePath);

            ProcessImageJob::dispatch($inputPath, $outputDir, $transformations);
        }

        return redirect()->route('transformations.live');
    }
}
