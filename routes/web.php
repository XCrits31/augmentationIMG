<?php

use App\Events\ProcessImageCompleted;
use App\Http\Controllers\BotController;
use App\Http\Controllers\ImagesProcessingController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ImageProcessingController;

Route::get('/upload-image', [ImageProcessingController::class, 'showUploadForm']);
Route::post('/process-image', [ImageProcessingController::class, 'processImage']);
Route::get('/transformations', [ImageProcessingController::class, 'showTransformations'])->name('transformations.index');
Route::delete('/transformations/{id}', [ImageProcessingController::class, 'deleteTransformation'])->name('transformations.delete');
Route::delete('/transformations', [ImageProcessingController::class, 'deleteAllTransformations'])->name('transformations.deleteAll');
Route::get('/transformations/live', function () {
    return view('transformations.live');
})->name('transformations.live');
Route::get('/upload-images', [ImagesProcessingController::class, 'showUploadForm'])->name('images.upload.form');
Route::post('/process-multiple-images', [ImagesProcessingController::class, 'processMultipleImages'])->name('images.process.multiple');
Route::get('/upload-with-preset', [ImagesProcessingController::class, 'showUploadWithPreset'])->name('images.upload.withPreset');
Route::get('/transformations/{id}', [ImagesProcessingController::class, 'show'])->name('transformations.show');
