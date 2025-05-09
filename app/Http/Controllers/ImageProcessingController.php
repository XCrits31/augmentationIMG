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
use Illuminate\Support\Facades\Storage;
use ZipArchive;

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
            ProcessImageJob::dispatch($inputPath, $outputDir, $transformations);
        }

        return redirect()->route('transformations.live');
    }

    public function showTransformations()
    {
        $transformations = Transformation::all();

        return view('transformations.index', compact('transformations'));
    }
    public function deleteTransformation($id)
    {
        $transformation = Transformation::find($id);
        if ($transformation) {
            $originalImagePath = storage_path('app/public/uploads/' . $transformation->image_name);
            $processedImagePath = storage_path('app/public/processed/' . $transformation->output_image);

            if (file_exists($originalImagePath)) {
                unlink($originalImagePath);
            }

            if (file_exists($processedImagePath)) {
                unlink($processedImagePath);
            }

            $transformation->delete();
        }

        return redirect()->route('transformations.index')->with('success', 'Transformation deleted successfully.');
    }
    public function deleteAllTransformations()
    {
        $transformations = Transformation::all();

        foreach ($transformations as $transformation) {
            $originalImagePath = storage_path('app/public/uploads/' . $transformation->image_name);
            $processedImagePath = storage_path('app/public/processed/' . $transformation->output_image);

            if (file_exists($originalImagePath)) {
                unlink($originalImagePath);
            }

            if (file_exists($processedImagePath)) {
                unlink($processedImagePath);
            }
        }

        Transformation::truncate();

        return redirect()->route('transformations.index')->with('success', 'All transformations deleted successfully.');
    }
    public function show($id)
    {
        $transformation = Transformation::findOrFail($id);
        $all = Transformation::all();

        $sameTransformations = $all->filter(function ($item) use ($transformation) {
            return $item->id !== $transformation->id &&
                $item->transformations === $transformation->transformations;
        });

        return view('transformations.show', [
            'detail' => $transformation,
            'transformations' => $sameTransformations,
        ]);
    }

    public function downloadSelected(Request $request)
    {
        $files = $request->input('selected', []);
        $type = $request->input('file_type', 'png'); // default to png

        if (empty($files)) {
            return back()->with('error', 'No files selected.');
        }

        if (!in_array($type, ['png', 'pt'])) {
            return back()->with('error', 'Invalid file type selected.');
        }

        $zipFile = storage_path("app/public/processed_selected.{$type}.zip");
        $zip = new ZipArchive;

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Failed to create ZIP archive.');
        }

        $added = 0;
        foreach ($files as $file) {
            $file = preg_replace('/\.png$/', ".$type", $file);  // convert to .pt if needed
            $fullPath = storage_path("app/public/processed/" . $file);

            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, basename($fullPath));
                $added++;
            }
        }

        $zip->close();

        if ($added === 0) {
            return back()->with('error', 'No valid files found to add to archive.');
        }

        return response()->download($zipFile)->deleteFileAfterSend(true);
    }

}
