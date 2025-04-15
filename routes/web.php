<?php

use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ImageProcessingController;

Route::get('/upload-image', [ImageProcessingController::class, 'showUploadForm']);
Route::post('/process-image', [ImageProcessingController::class, 'processImage']);

Route::get('/', [BotController::class, 'index']);
Route::get('/python-check', function () {
    $output = shell_exec('/usr/bin/python3 -c "import sys; print(sys.executable)"');
    return response()->json(['python_path' => $output]);
});
Route::get('/transformations', [ImageProcessingController::class, 'showTransformations'])->name('transformations.index');

