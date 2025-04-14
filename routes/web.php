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

Route::get('/test-numpy-debug', function () {
    $output = shell_exec('/usr/bin/python3 -c "import sys; print(sys.path)" 2>&1');
    return response()->json(['debug_sys_path' => $output]);
});
Route::get('/test-numpy', function () {
    $output = shell_exec('PYTHONPATH=$HOME/.local/lib/python3.10/site-packages /usr/bin/python3 -c "import numpy; print(numpy.__version__)" 2>&1');
    return response()->json(['numpy_version' => $output]);
});
