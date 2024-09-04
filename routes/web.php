<?php

use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhook', [BotController::class, 'handleWebhook']);
