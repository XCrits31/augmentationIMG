<?php

use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ImageProcessingController;

Route::get('/upload-image', [ImageProcessingController::class, 'showUploadForm']);
Route::post('/process-image', [ImageProcessingController::class, 'processImage']);
Route::get('/transformations', [ImageProcessingController::class, 'showTransformations'])->name('transformations.index');
Route::delete('/transformations/{id}', [ImageProcessingController::class, 'deleteTransformation'])->name('transformations.delete');

