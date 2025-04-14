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
Route::get('/python-path', function () {
    return shell_exec('which python3');
});
Route::get('/test-numpy', function () {
    $output = shell_exec('/usr/bin/python3 -c "import numpy; print(numpy.__version__)" 2>&1');
    return response()->json(['numpy_version' => $output]);
});
